import { router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

/**
 * How many of each package are already in the cart.
 *
 * Read from the quantities HandleInertiaRequests shares on every response, so a listing knows
 * without asking and stays right the instant an add, a removal or a quantity change comes back —
 * the same mechanism the navbar badge uses.
 *
 * A shopper scanning a grid cannot otherwise tell what they have already picked up, which is how
 * the same rank gets added twice and how a half-built basket gets abandoned mid-browse.
 *
 * Returns a lookup rather than a bound value, for the layouts that iterate packages in the
 * template and so cannot call a composable per row.
 */
export function useCartQuantities() {
    const page = usePage();

    const quantities = computed(() => page.props.store?.cartQuantities ?? {});

    // Keyed by id, and JSON object keys are strings — an unstringified lookup silently misses.
    const quantityInCart = (storePackage) => Number(quantities.value[String(storePackage?.id)] ?? 0);

    return { quantityInCart, isInCart: (storePackage) => quantityInCart(storePackage) > 0 };
}

/**
 * The single-package form, for a component that renders exactly one card.
 *
 * Takes a getter as well as a plain object so a `props.storePackage` stays reactive.
 */
export function useCartMembership(storePackage) {
    const { quantityInCart: lookup } = useCartQuantities();

    const resolved = computed(() => (typeof storePackage === "function" ? storePackage() : storePackage));
    const quantityInCart = computed(() => lookup(resolved.value));

    return { quantityInCart, isInCart: computed(() => quantityInCart.value > 0) };
}

/**
 * Whether a package can go into the cart straight from a listing.
 *
 * Anything priced by the buyer or carrying variables has questions to answer first, so it gets a
 * link to its own page instead of a button that would only fail. Out of stock is not "add it and
 * find out at checkout" either.
 *
 * Shared rather than repeated per layout so the grid, the listing and the stacked rows cannot drift
 * apart — and so this stays the mirror of what StoreCartController re-checks when the post lands.
 */
export function canAddToCart(storePackage) {
    return !storePackage.needs_configuring && !storePackage.is_out_of_stock;
}

/**
 * Add a package to the cart.
 *
 * Quantity defaults to the package's own minimum rather than 1: a package sold in fives would
 * otherwise be added at a quantity the server has to silently clamp anyway.
 */
export function addToCart(storePackage, quantity = null) {
    router.post(route("store.cart.store"), {
        package_id: storePackage.id,
        quantity: quantity ?? storePackage.min_quantity ?? 1,
    }, {
        preserveScroll: true,
    });
}
