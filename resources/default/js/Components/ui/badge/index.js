import { cva } from "class-variance-authority";

export { default as Badge } from "./Badge.vue";

export const badgeVariants = cva(
    "inline-flex gap-1 items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-hidden focus:ring-2 focus:ring-ring focus:ring-offset-2",
    {
        variants: {
            variant: {
                default:
          "border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80",
                secondary:
          "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
                destructive:
          "border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80",
                outline: "text-foreground",
                // Soft tones for status chips: a tinted background with a matching border, readable
                // in both themes without the weight of the solid variants above.
                success: "border-success/30 bg-success/15 text-success",
                warning: "border-amber-500/30 bg-amber-500/15 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300",
                info: "border-sky-500/30 bg-sky-500/15 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300",
                danger: "border-destructive/30 bg-destructive/15 text-destructive",
                muted: "border-muted-foreground/30 bg-muted-foreground/15 text-muted-foreground",
            },
        },
        defaultVariants: {
            variant: "default",
        },
    },
);
