export default {
    methods: {
        can(permission) {
            return this.$page.props.permissions.includes(permission);
        },
        canWild(wildcard) {
            return this.$page.props.permissions.some(permission => {
                return permission.includes(wildcard);
            });
        },
        isStaff(user) {
            if (!user) return false;

            return user.roles.some(role => {
                return role.is_staff;
            });
        }
    }
};
