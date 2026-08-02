import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

// Basis points to a readable percentage: 1500 -> "15%", 1550 -> "15.5%". Safe to divide here — a
// percentage has no minor unit, unlike money.
function percentFromBasisPoints(bp) {
    return `${Math.round(Number(bp) / 10) / 10}%`;
}

/**
 * Each discount applying to a package, stated as configured.
 *
 * Reported by the server rather than inferred from the prices. A saving is rounded down to whole
 * minor units per package, so working the percentage back out of it turned one flat 15% sale into
 * "14.8% off" on a $1.49 key and "14.9% off" on a $9.99 rank.
 *
 * A package's own discount and a sale can both apply — the sale is calculated on the already
 * reduced price — so they are listed separately rather than added together into a figure that
 * matches neither.
 */
export function discountParts(storePackage) {
    const parts = [];
    const own = Number(storePackage.discount_bp ?? 0);
    const sale = Number(storePackage.sale_discount_bp ?? 0);

    if (own > 0) {
        parts.push(percentFromBasisPoints(own));
    }

    if (sale > 0) {
        parts.push(percentFromBasisPoints(sale));
    } else if (storePackage.sale_amount_formatted) {
        // A fixed-amount sale has no percentage to name, so the money is the honest label.
        parts.push(storePackage.sale_amount_formatted);
    }

    return parts;
}

/**
 * The badge text, or null when nothing is discounted.
 */
export function discountLabel(storePackage) {
    const parts = discountParts(storePackage);

    return parts.length ? __(":discount off", { discount: parts.join(" + ") }) : null;
}

/**
 * What a shopper would have to spend to put this package on sale, or null when nothing is waiting.
 *
 * A sale gated on a cart total cannot be priced into a card, because a listing has no cart to
 * measure — the card would advertise a price the cart would then refuse to honour. So the price
 * stays undiscounted and the offer is stated as the condition it actually is.
 */
export function unlockLabel(storePackage) {
    if (! storePackage.conditional_sale_minimum_formatted) {
        return null;
    }

    const bp = Number(storePackage.conditional_sale_discount_bp ?? 0);
    // A fixed-amount sale has no percentage to name, so the money is the honest label — the same
    // rule discountParts() follows.
    const discount = bp > 0
        ? percentFromBasisPoints(bp)
        : storePackage.conditional_sale_amount_formatted;

    if (! discount) {
        return null;
    }

    return __("Spend :amount to get :discount off", {
        amount: storePackage.conditional_sale_minimum_formatted,
        discount,
    });
}
