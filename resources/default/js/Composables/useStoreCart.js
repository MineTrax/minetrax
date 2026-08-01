import { router } from "@inertiajs/vue3";

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
