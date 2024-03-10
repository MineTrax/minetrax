<script>
export default {
    data() {
        return {
            status: 'idle',
        };
    },
    methods: {
        copy(text) {
            // create textarea
            const el = document.createElement('textarea');

            // assign value
            el.value = text;

            // style textarea
            el.style.position = 'absolute';
            el.style.left = '-90000px';
            document.body.appendChild(el);

            // select its contents
            el.select();

            // execute copy
            document.execCommand('copy');

            // remove textarea
            document.body.removeChild(el);

            this.status = 'copied';
            setTimeout(() => this.status = 'idle', 1000);
        },
    },
    render() {
        return this.$slots.default({
            status: this.status,
            copy: this.copy
        });
    }
};
</script>
