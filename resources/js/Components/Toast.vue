<template />

<script>

export default {
    props: {
        toast: Object,
        popstate: String
    },
    data() {
        return {
            milliseconds: this.toast && this.toast.milliseconds ? this.toast.milliseconds : 3000,
            id: null,
        };
    },

    watch: {
        toast: {
            deep: true,
            handler(o, n) {
                this.fireToast();
            }
        }
    },

    mounted() {
        this.fireToast();
    },

    methods: {
        fireToast() {
            if (!this.toast || sessionStorage.getItem('toast-' + this.popstate)) {
                return;
            }

            this.milliseconds = this.toast.milliseconds ?? 3000;
            const icon = this.toast.type === 'danger' ? 'error' : this.toast.type;
            Toast.fire({
                icon: icon,
                title: this.toast.title,
                text: this.toast.body,
                timer: this.milliseconds,
            });

            sessionStorage.setItem('toast-' + this.popstate, '1');
        }
    }
};
</script>
