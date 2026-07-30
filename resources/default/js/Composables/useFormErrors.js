/**
 * Reading validation errors off an Inertia form, including the indexed ones.
 *
 * Laravel reports a per-item array failure under the item's own key — `country_codes.0`, not
 * `country_codes` — so a template checking the bare field name renders nothing and a rejected save
 * looks to the admin like a save that silently did nothing. Every Multiselect field in the admin has
 * that shape, because they all validate `field.*`.
 */
export function useFormErrors() {
    /**
     * The error for a field, falling back to the first indexed error beneath it.
     *
     * @param {Object} errors  form.errors from useForm
     * @param {String} field   the field name, e.g. "country_codes"
     * @returns {String|undefined}
     */
    function fieldError(errors, field) {
        if (! errors) {
            return undefined;
        }

        if (errors[field]) {
            return errors[field];
        }

        const prefix = `${field}.`;

        return Object.entries(errors).find(([key]) => key.startsWith(prefix))?.[1];
    }

    return { fieldError };
}
