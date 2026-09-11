export default () => ({
    loaded: false,

    open() {
        this.loaded = true;
        this.$refs.dialog.showModal();
        document.documentElement.classList.add('image-lightbox-open');
    },

    close() {
        this.$refs.dialog.close();
    },

    onClose() {
        document.documentElement.classList.remove('image-lightbox-open');
    },

    destroy() {
        if (this.$refs.dialog.open) this.onClose();
    },
});
