import test from 'node:test';
import assert from 'node:assert/strict';
import tooltip from '../../resources/js/components/health-trend-tooltip.js';

function chart(width = 640) {
    const component = tooltip();
    component.points = [{x: 12, y: 100, date: '2026-09-15', display: '0 kg'}, {x: 628, y: 80, date: '2026-09-16', display: '71.82 kg'}];
    component.$refs = {plot: {getBoundingClientRect: () => ({left: 50, width})}};
    return component;
}

test('pointer chooses the nearest published day at desktop and mobile sizes', () => {
    for (const width of [640, 280]) {
        const component = chart(width);
        component.pointAt({clientX: 50 + width * .1});
        assert.equal(component.current.display, '0 kg');
        component.pointAt({clientX: 50 + width * .9});
        assert.equal(component.current.date, '2026-09-16');
    }
});
test('tooltip stays within the plot at both edges and on very narrow screens', () => {
    for (const width of [900, 280, 180]) {
        const component = chart(width);
        component.show(0);
        assert.equal(component.tooltipLeft, '0px');
        component.show(1);
        assert.ok(parseFloat(component.tooltipLeft) + Math.min(width, 240) <= width);
    }
});
test('keyboard navigation is bounded, Escape clears, and navigation can restart', () => {
    const component = chart();
    component.step(1);
    assert.equal(component.active, 0);
    component.step(1);
    component.step(1);
    assert.equal(component.active, 1);
    component.hide();
    assert.equal(component.current, null);
    component.step(-1);
    assert.equal(component.active, 1);
    component.show(-100);
    assert.equal(component.active, 0);
});
test('single-day and empty charts avoid invalid tooltip values', () => {
    const component = chart();
    component.points = component.points.slice(0, 1);
    component.pointAt({clientX: 600});
    assert.equal(component.active, 0);
    component.hide();
    component.points = [];
    component.step(1);
    component.pointAt({clientX: 100});
    assert.equal(component.current, null);
});
