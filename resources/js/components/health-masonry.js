export default () => {
    let observer;
    let frame;
    let grid;
    let cards = [];
    let width = 0;

    const layout = () => {
        const styles = getComputedStyle(grid);
        const columns = Number(styles.getPropertyValue('--health-columns')) || 1;
        const gap = parseFloat(styles.columnGap) || 0;
        const columnWidth = (grid.clientWidth - gap * (columns - 1)) / columns;
        const heights = Array(columns).fill(0);

        // Place each card in the shortest column, preserving DOM/date order.
        cards.forEach(card => {
            const column = heights.indexOf(Math.min(...heights));
            card.style.left = `${column * (columnWidth + gap)}px`;
            card.style.top = `${heights[column]}px`;
            heights[column] += card.getBoundingClientRect().height + gap;
        });
        grid.style.height = `${Math.max(0, ...heights) - (cards.length ? gap : 0)}px`;
    };

    const schedule = () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(layout);
    };

    return {
        init() {
            grid = this.$el;
            cards = Array.from(grid.children);
            // Without ResizeObserver, keep the readable CSS grid fallback.
            if (!window.ResizeObserver) return;
            grid.classList.add('is-masonry');
            layout();
            observer = new ResizeObserver(entries => {
                if (entries.some(entry => entry.target !== grid || entry.contentRect.width !== width)) {
                    width = grid.clientWidth;
                    schedule();
                }
            });
            observer.observe(grid);
            cards.forEach(card => observer.observe(card));
        },

        destroy() {
            observer?.disconnect();
            cancelAnimationFrame(frame);
            grid.classList.remove('is-masonry');
            grid.style.removeProperty('height');
            cards.forEach(card => {
                card.style.removeProperty('left');
                card.style.removeProperty('top');
            });
        },
    };
};
