import fs from 'fs';
import path from 'path';

const VIRTUAL_MODULE_ID = 'virtual:theme-pages';
const RESOLVED_VIRTUAL_ID = '\0' + VIRTUAL_MODULE_ID;

/**
 * Normalize a path to use forward slashes (required for Vite on Windows).
 */
function normalizePath(p) {
    return p.split(path.sep).join('/');
}

/**
 * Recursively scan a directory for .vue files and populate a pages map.
 * Keys are in the format './Pages/SomePage.vue' to match resolvePageComponent expectations.
 */
function scanPagesDir(dir, pagesRoot, pages) {
    if (!fs.existsSync(dir)) {
        return;
    }

    const entries = fs.readdirSync(dir, { withFileTypes: true });
    for (const entry of entries) {
        const fullPath = path.resolve(dir, entry.name);
        if (entry.isDirectory()) {
            scanPagesDir(fullPath, pagesRoot, pages);
        } else if (entry.name.endsWith('.vue')) {
            const relative = normalizePath(path.relative(pagesRoot, fullPath));
            const key = './Pages/' + relative;
            pages[key] = normalizePath(fullPath);
        }
    }
}

/**
 * Try to resolve a file path, checking the exact path first, then common extensions.
 * Returns the resolved path or null if not found.
 */
function tryResolve(basePath) {
    // Try exact path
    if (fs.existsSync(basePath)) {
        const stat = fs.statSync(basePath);
        if (stat.isFile()) {
            return normalizePath(basePath);
        }
    }

    // Try common extensions
    const extensions = ['.js', '.ts', '.vue', '.json', '.mjs'];
    for (const ext of extensions) {
        const withExt = basePath + ext;
        if (fs.existsSync(withExt)) {
            return normalizePath(withExt);
        }
    }

    // Try index files in directory
    const indexExtensions = ['/index.js', '/index.ts', '/index.mjs'];
    for (const idx of indexExtensions) {
        const indexPath = basePath + idx;
        if (fs.existsSync(indexPath)) {
            return normalizePath(indexPath);
        }
    }

    return null;
}

/**
 * Vite plugin that enables theme inheritance for MineTrax.
 *
 * - Resolves @/ alias imports with fallback: theme dir first, then default dir.
 * - Provides a virtual:theme-pages module with merged page maps for Inertia.
 * - Watches for page file changes in dev mode for HMR support.
 *
 * @param {object} options
 * @param {string} options.theme - Active theme name (from APP_THEME)
 * @param {string} [options.defaultTheme='default'] - Base theme to fall back to
 * @param {string} [options.root=process.cwd()] - Project root directory
 */
export default function themeInheritance(options = {}) {
    const {
        theme = 'default',
        defaultTheme = 'default',
        root = process.cwd(),
    } = options;

    const isDefault = theme === defaultTheme;

    const themeJsDir = normalizePath(path.resolve(root, 'resources', theme, 'js'));
    const defaultJsDir = normalizePath(path.resolve(root, 'resources', defaultTheme, 'js'));

    // Cache resolved paths to avoid repeated fs.existsSync calls during build
    const resolveCache = new Map();

    return {
        name: 'theme-inheritance',
        enforce: 'pre',

        resolveId(source) {
            // Handle virtual module
            if (source === VIRTUAL_MODULE_ID) {
                return RESOLVED_VIRTUAL_ID;
            }

            // Skip fallback if using default theme
            if (isDefault) {
                return null;
            }

            // Handle @/ alias imports with fallback
            if (source.startsWith('@/')) {
                // Check cache first
                if (resolveCache.has(source)) {
                    return resolveCache.get(source);
                }

                const relativePath = source.slice(2);
                let resolved = null;

                // Check theme directory first
                const themeFull = path.resolve(themeJsDir, relativePath);
                resolved = tryResolve(themeFull);

                // Fall back to default directory
                if (!resolved) {
                    const defaultFull = path.resolve(defaultJsDir, relativePath);
                    resolved = tryResolve(defaultFull);
                }

                resolveCache.set(source, resolved);
                return resolved;
            }

            return null;
        },

        load(id) {
            if (id !== RESOLVED_VIRTUAL_ID) {
                return null;
            }

            const pages = {};

            // Scan default pages first (base layer)
            const defaultPagesDir = path.resolve(root, 'resources', defaultTheme, 'js', 'Pages');
            scanPagesDir(defaultPagesDir, defaultPagesDir, pages);

            // Scan theme pages on top (overrides default for same relative path)
            if (!isDefault) {
                const themePagesDir = path.resolve(root, 'resources', theme, 'js', 'Pages');
                scanPagesDir(themePagesDir, themePagesDir, pages);
            }

            // Generate the virtual module code
            const entries = Object.entries(pages)
                .map(([key, absPath]) => `  ${JSON.stringify(key)}: () => import(${JSON.stringify(absPath)})`)
                .join(',\n');

            return `export default {\n${entries}\n};\n`;
        },

        configureServer(server) {
            // The default theme's Pages directory is always part of the virtual page map — load()
            // scans it whatever the active theme is — so it always has to be watched. Skipping it
            // when the active theme *is* default meant a newly added page was invisible to the
            // running dev server until someone restarted it, with a bare "Page not found" as the
            // only clue.
            const watchedJsDirs = [defaultJsDir];
            const watchedPagesDirs = [
                normalizePath(path.resolve(root, 'resources', defaultTheme, 'js', 'Pages')),
            ];

            if (!isDefault) {
                watchedJsDirs.push(themeJsDir);
                watchedPagesDirs.push(
                    normalizePath(path.resolve(root, 'resources', theme, 'js', 'Pages'))
                );
            }

            const startsWithAny = (value, prefixes) => prefixes.some((prefix) => value.startsWith(prefix));

            const invalidateOnChange = (file) => {
                const normalized = normalizePath(file);

                // Clear resolve cache when any watched JS file changes
                if (startsWithAny(normalized, watchedJsDirs)) {
                    resolveCache.clear();
                }

                // Invalidate the virtual page map when pages are added/removed
                if (normalized.endsWith('.vue') && startsWithAny(normalized, watchedPagesDirs)) {
                    const mod = server.moduleGraph.getModuleById(RESOLVED_VIRTUAL_ID);
                    if (mod) {
                        server.moduleGraph.invalidateModule(mod);
                        server.ws.send({ type: 'full-reload' });
                    }
                }
            };

            server.watcher.on('add', invalidateOnChange);
            server.watcher.on('unlink', invalidateOnChange);
        },
    };
}
