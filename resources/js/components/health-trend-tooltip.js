export default () => ({
    points: [],
    active: null,
    tooltipLeft: '0px',

    init() {
        this.points = Array.from(this.$refs.plot.querySelectorAll('circle[data-date]'), point => ({
            x: Number(point.getAttribute('cx')),
            y: Number(point.getAttribute('cy')),
            ...point.dataset,
        }));
    },

    get current() {
        return this.active === null ? null : this.points[this.active];
    },

    show(index) {
        if (!this.points.length) return;
        this.active = Math.max(0, Math.min(index, this.points.length - 1));
        const width = this.$refs.plot.getBoundingClientRect().width;
        const tooltipWidth = Math.min(240, width);
        const left = this.current.x / 640 * width - tooltipWidth / 2;
        this.tooltipLeft = `${Math.max(0, Math.min(left, width - tooltipWidth))}px`;
    },

    pointAt(event) {
        const bounds = this.$refs.plot.getBoundingClientRect();
        if (!bounds.width || !this.points.length) return;
        const x = (event.clientX - bounds.left) / bounds.width * 640;
        let nearest = 0;
        for (let i = 1; i < this.points.length; i++) {
            if (Math.abs(this.points[i].x - x) < Math.abs(this.points[nearest].x - x)) nearest = i;
        }
        this.show(nearest);
    },

    step(direction) {
        this.show(this.active === null ? (direction > 0 ? 0 : this.points.length - 1) : this.active + direction);
    },

    hide() {
        this.active = null;
    },
});
