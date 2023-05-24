import {usePage} from '@inertiajs/vue3';

export function useAuthorizable() {
    const page = usePage();

    function can(permission) {
        return page.props.permissions.includes(permission);
    }

    function canWild(wildcard) {
        return page.props.permissions.some((permission) => {
            return permission.includes(wildcard);
        });
    }

    function isStaff(user) {
        if (!user) return false;

        return user.roles.some((role) => {
            return role.is_staff;
        });
    }

    return {
        can,
        canWild,
        isStaff,
    };
}
