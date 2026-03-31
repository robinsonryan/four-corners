import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RotationControls from '../Components/RotationControls.vue';

describe('RotationControls', () => {
  it('renders with correct rotation value', () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 90,
      },
    });

    expect(wrapper.text()).toContain('90°');
  });

  it('displays 0° rotation', () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 0,
      },
    });

    expect(wrapper.text()).toContain('0°');
  });

  it('displays 270° rotation', () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 270,
      },
    });

    expect(wrapper.text()).toContain('270°');
  });

  it('emits rotate event with -90 when counter-clockwise button clicked', async () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 0,
      },
    });

    const buttons = wrapper.findAll('button');
    await buttons[0].trigger('click');

    expect(wrapper.emitted('rotate')).toBeTruthy();
    expect(wrapper.emitted('rotate')![0]).toEqual([-90]);
  });

  it('emits rotate event with 90 when clockwise button clicked', async () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 0,
      },
    });

    const buttons = wrapper.findAll('button');
    await buttons[1].trigger('click');

    expect(wrapper.emitted('rotate')).toBeTruthy();
    expect(wrapper.emitted('rotate')![0]).toEqual([90]);
  });

  it('disables buttons when disabled prop is true', () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 0,
        disabled: true,
      },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons[0].attributes('disabled')).toBeDefined();
    expect(buttons[1].attributes('disabled')).toBeDefined();
  });

  it('enables buttons when disabled prop is false', () => {
    const wrapper = mount(RotationControls, {
      props: {
        rotation: 0,
        disabled: false,
      },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons[0].attributes('disabled')).toBeUndefined();
    expect(buttons[1].attributes('disabled')).toBeUndefined();
  });
});
