import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ActionButtons from '../Components/ActionButtons.vue';

describe('ActionButtons', () => {
  const defaultProps = {
    canProcess: true,
    isProcessing: false,
    hasChanges: false,
  };

  it('renders all action buttons', () => {
    const wrapper = mount(ActionButtons, {
      props: defaultProps,
    });

    expect(wrapper.text()).toContain('Reject');
    expect(wrapper.text()).toContain('Preview');
    expect(wrapper.text()).toContain('Accept & Process');
  });

  it('hides reset button when hasChanges is false', () => {
    const wrapper = mount(ActionButtons, {
      props: {
        ...defaultProps,
        hasChanges: false,
      },
    });

    expect(wrapper.text()).not.toContain('Reset');
  });

  it('shows reset button when hasChanges is true', () => {
    const wrapper = mount(ActionButtons, {
      props: {
        ...defaultProps,
        hasChanges: true,
      },
    });

    expect(wrapper.text()).toContain('Reset');
  });

  it('disables preview and accept buttons when canProcess is false', () => {
    const wrapper = mount(ActionButtons, {
      props: {
        ...defaultProps,
        canProcess: false,
      },
    });

    const buttons = wrapper.findAll('button');
    const previewButton = buttons.find(b => b.text().includes('Preview'));
    const acceptButton = buttons.find(b => b.text().includes('Accept'));

    expect(previewButton?.attributes('disabled')).toBeDefined();
    expect(acceptButton?.attributes('disabled')).toBeDefined();
  });

  it('enables preview and accept buttons when canProcess is true', () => {
    const wrapper = mount(ActionButtons, {
      props: defaultProps,
    });

    const buttons = wrapper.findAll('button');
    const previewButton = buttons.find(b => b.text().includes('Preview'));
    const acceptButton = buttons.find(b => b.text().includes('Accept'));

    expect(previewButton?.attributes('disabled')).toBeUndefined();
    expect(acceptButton?.attributes('disabled')).toBeUndefined();
  });

  it('shows processing state when isProcessing is true', () => {
    const wrapper = mount(ActionButtons, {
      props: {
        ...defaultProps,
        isProcessing: true,
      },
    });

    expect(wrapper.text()).toContain('Processing...');
    expect(wrapper.text()).not.toContain('Accept & Process');
  });

  it('emits reject event when reject button clicked', async () => {
    const wrapper = mount(ActionButtons, {
      props: defaultProps,
    });

    const rejectButton = wrapper.findAll('button').find(b => b.text().includes('Reject'));
    await rejectButton?.trigger('click');

    expect(wrapper.emitted('reject')).toBeTruthy();
  });

  it('emits preview event when preview button clicked', async () => {
    const wrapper = mount(ActionButtons, {
      props: defaultProps,
    });

    const previewButton = wrapper.findAll('button').find(b => b.text().includes('Preview'));
    await previewButton?.trigger('click');

    expect(wrapper.emitted('preview')).toBeTruthy();
  });

  it('emits accept event when accept button clicked', async () => {
    const wrapper = mount(ActionButtons, {
      props: defaultProps,
    });

    const acceptButton = wrapper.findAll('button').find(b => b.text().includes('Accept'));
    await acceptButton?.trigger('click');

    expect(wrapper.emitted('accept')).toBeTruthy();
  });

  it('emits reset event when reset button clicked', async () => {
    const wrapper = mount(ActionButtons, {
      props: {
        ...defaultProps,
        hasChanges: true,
      },
    });

    const resetButton = wrapper.findAll('button').find(b => b.text().includes('Reset'));
    await resetButton?.trigger('click');

    expect(wrapper.emitted('reset')).toBeTruthy();
  });

  it('disables reject button when processing', () => {
    const wrapper = mount(ActionButtons, {
      props: {
        ...defaultProps,
        isProcessing: true,
      },
    });

    const rejectButton = wrapper.findAll('button').find(b => b.text().includes('Reject'));
    expect(rejectButton?.attributes('disabled')).toBeDefined();
  });
});
