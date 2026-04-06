<script setup>
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import Image from "@tiptap/extension-image";
import Underline from "@tiptap/extension-underline";
import TextAlign from "@tiptap/extension-text-align";
import { Markdown } from "tiptap-markdown";
import { watch, onBeforeUnmount } from "vue";
import {
    Bold,
    Italic,
    Strikethrough,
    Code,
    List,
    ListOrdered,
    Quote,
    Undo,
    Redo,
    Link as LinkIcon,
    Image as ImageIcon,
    Heading1,
    Heading2,
    Heading3,
    AlignLeft,
    AlignCenter,
    AlignRight,
    Underline as UnderlineIcon,
    Minus
} from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const editor = useEditor({
    extensions: [
        StarterKit,
        Markdown.configure({
            html: false,
            transformPastedText: true,
            transformCopiedText: true,
        }),
        Link.configure({
            openOnClick: false,
        }),
        Image,
        Underline,
        TextAlign.configure({
            types: ["heading", "paragraph"],
        }),
    ],
    content: props.modelValue,
    onUpdate: ({ editor }) => {
    // We want to emit markdown
        const markdown = editor.storage.markdown.getMarkdown();
        emit("update:modelValue", markdown);
    },
    editorProps: {
        attributes: {
            class: "prose prose-sm sm:prose-base dark:prose-invert max-w-none focus:outline-hidden min-h-[200px] p-4",
        },
    },
});

// Watch for external changes
watch(() => props.modelValue, (value) => {
    // If the editor content (markdown) is different from the prop, update it
    // This check is important to avoid cursor jumping or infinite loops
    if (editor.value && editor.value.storage.markdown.getMarkdown() !== value) {
        editor.value.commands.setContent(value);
    }
});

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
    }
});

const setLink = () => {
    const previousUrl = editor.value.getAttributes("link").href;
    const url = window.prompt("URL", previousUrl);

    // cancelled
    if (url === null) {
        return;
    }

    // empty
    if (url === "") {
        editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
        return;
    }

    // update
    editor.value.chain().focus().extendMarkRange("link").setLink({ href: url }).run();
};

const addImage = () => {
    const url = window.prompt("URL");

    if (url) {
        editor.value.chain().focus().setImage({ src: url }).run();
    }
};
</script>

<template>
  <div class="border rounded-lg bg-card text-card-foreground shadow-sm w-full overflow-hidden">
    <div
      v-if="editor"
      class="border-b bg-muted/50 p-2 flex flex-wrap gap-1"
    >
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('bold') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Bold"
        @click="editor.chain().focus().toggleBold().run()"
      >
        <Bold class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('italic') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Italic"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        <Italic class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('strike') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Strike"
        @click="editor.chain().focus().toggleStrike().run()"
      >
        <Strikethrough class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('underline') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Underline"
        @click="editor.chain().focus().toggleUnderline().run()"
      >
        <UnderlineIcon class="w-4 h-4" />
      </button>
      
      <div class="w-px h-6 bg-border mx-1 self-center" />

      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('heading', { level: 1 }) }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Heading 1"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
      >
        <Heading1 class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('heading', { level: 2 }) }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Heading 2"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
      >
        <Heading2 class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('heading', { level: 3 }) }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Heading 3"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
      >
        <Heading3 class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-border mx-1 self-center" />

      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive({ textAlign: 'left' }) }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Align Left"
        @click="editor.chain().focus().setTextAlign('left').run()"
      >
        <AlignLeft class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive({ textAlign: 'center' }) }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Align Center"
        @click="editor.chain().focus().setTextAlign('center').run()"
      >
        <AlignCenter class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive({ textAlign: 'right' }) }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Align Right"
        @click="editor.chain().focus().setTextAlign('right').run()"
      >
        <AlignRight class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-border mx-1 self-center" />

      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('bulletList') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Bullet List"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        <List class="w-4 h-4" />
      </button>
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('orderedList') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Ordered List"
        @click="editor.chain().focus().toggleOrderedList().run()"
      >
        <ListOrdered class="w-4 h-4" />
      </button>

      <div class="w-px h-6 bg-border mx-1 self-center" />
      
      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('blockquote') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Quote"
        @click="editor.chain().focus().toggleBlockquote().run()"
      >
        <Quote class="w-4 h-4" />
      </button>
      <button
        type="button"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Horizontal Rule"
        @click="editor.chain().focus().setHorizontalRule().run()"
      >
        <Minus class="w-4 h-4" />
      </button>
      
      <div class="w-px h-6 bg-border mx-1 self-center" />

      <button
        type="button"
        :class="{ 'bg-primary/20 text-primary': editor.isActive('link') }"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Link"
        @click="setLink"
      >
        <LinkIcon class="w-4 h-4" />
      </button>
      <button
        type="button"
        class="p-2 rounded hover:bg-muted transition-colors"
        title="Image"
        @click="addImage"
      >
        <ImageIcon class="w-4 h-4" />
      </button>
      
      <div class="w-px h-6 bg-border mx-1 self-center" />

      <button
        type="button"
        :disabled="!editor.can().chain().focus().undo().run()"
        class="p-2 rounded hover:bg-muted transition-colors disabled:opacity-50"
        title="Undo"
        @click="editor.chain().focus().undo().run()"
      >
        <Undo class="w-4 h-4" />
      </button>
      <button
        type="button"
        :disabled="!editor.can().chain().focus().redo().run()"
        class="p-2 rounded hover:bg-muted transition-colors disabled:opacity-50"
        title="Redo"
        @click="editor.chain().focus().redo().run()"
      >
        <Redo class="w-4 h-4" />
      </button>
    </div>
    
    <editor-content :editor="editor" />
  </div>
</template>

<style>
/* Scoped styles for the editor content if needed, though Tailwind typography (prose) handles most of it */
.ProseMirror {
  min-height: 200px;
}
.ProseMirror p.is-editor-empty:first-child::before {
  color: #adb5bd;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
