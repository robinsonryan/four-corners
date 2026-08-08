import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ZoomControls from '../Components/ZoomControls.vue';
import { emittedEvents } from "./helpers";

describe('ZoomControls', () => {

  it('displays zoom percentage', () => {
    const wrapper = mount(ZoomControls, {
      props: { zoom: 1 },
    });

    expect(wrapper.text()).toContain('100%');
  });

  it('displays 50% for zoom 0.5', () => {
    const wrapper = mount(ZoomControls, {
      props: { zoom: 0.5 },
    });

    expect(wrapper.text()).toContain('50%');
  });

  it('displays 200% for zoom 2', () => {
    const wrapper = mount(ZoomControls, {
      props: { zoom: 2 },
    });

    expect(wrapper.text()).toContain('200%');
  });

  it('emits update:zoom with increased value when zoom in clicked', async () => {
    const wrapper = mount(ZoomControls, {
      props: { zoom: 1 },
    });

    const buttons = wrapper.findAll('button');
    const zoomInButton = buttons[1]; // Zoom in is second button
    await zoomInButton.trigger('click');

    expect(wrapper.emitted('update:zoom')).toBeTruthy();
    expect(emittedEvents(wrapper, 'update:zoom')[0][0]).toBeGreaterThan(1);
  });

  it('emits update:zoom with decreased value when zoom out clicked', async () => {
    const wrapper = mount(ZoomControls, {
      props: { zoom: 1 },
    });

    const buttons = wrapper.findAll('button');
    const zoomOutButton = buttons[0]; // Zoom out is first button
    await zoomOutButton.trigger('click');

    expect(wrapper.emitted('update:zoom')).toBeTruthy();
    expect(emittedEvents(wrapper, 'update:zoom')[0][0]).toBeLessThan(1);
  });

  it('emits fit event when fit button clicked', async () => {
    const wrapper = mount(ZoomControls, {
      props: { zoom: 1 },
    });

    const fitButton = wrapper.findAll('button').find(b => b.text().includes('Fit'));
    await fitButton?.trigger('click');

    expect(wrapper.emitted('fit')).toBeTruthy();
  });

  it('disables zoom out when at minimum zoom', () => {
    const wrapper = mount(ZoomControls, {
      props: {
        zoom: 0.25,
        minZoom: 0.25,
      },
    });

    const buttons = wrapper.findAll('button');
    const zoomOutButton = buttons[0];

    expect(zoomOutButton.attributes('disabled')).toBeDefined();
  });

  it('disables zoom in when at maximum zoom', () => {
    const wrapper = mount(ZoomControls, {
      props: {
        zoom: 4,
        maxZoom: 4,
      },
    });

    const buttons = wrapper.findAll('button');
    const zoomInButton = buttons[1];

    expect(zoomInButton.attributes('disabled')).toBeDefined();
  });

  it('disables all buttons when disabled prop is true', () => {
    const wrapper = mount(ZoomControls, {
      props: {
        zoom: 1,
        disabled: true,
      },
    });

    const buttons = wrapper.findAll('button');
    buttons.forEach(button => {
      expect(button.attributes('disabled')).toBeDefined();
    });
  });

  it('zoom in respects maximum zoom', async () => {
    const wrapper = mount(ZoomControls, {
      props: {
        zoom: 3.5,
        maxZoom: 4,
      },
    });

    const buttons = wrapper.findAll('button');
    const zoomInButton = buttons[1];
    await zoomInButton.trigger('click');

    const emitted = emittedEvents(wrapper, 'update:zoom')[0][0] as number;
    expect(emitted).toBeLessThanOrEqual(4);
  });

  it('zoom out respects minimum zoom', async () => {
    const wrapper = mount(ZoomControls, {
      props: {
        zoom: 0.3,
        minZoom: 0.25,
      },
    });

    const buttons = wrapper.findAll('button');
    const zoomOutButton = buttons[0];
    await zoomOutButton.trigger('click');

    const emitted = emittedEvents(wrapper, 'update:zoom')[0][0] as number;
    expect(emitted).toBeGreaterThanOrEqual(0.25);
  });
});
