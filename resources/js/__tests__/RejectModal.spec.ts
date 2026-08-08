import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import RejectModal from '../Components/RejectModal.vue';
import type { RejectionReason } from '../Types';
import { emittedEvents } from "./helpers";

describe('RejectModal', () => {
  const mockReasons: RejectionReason[] = [
    { id: 1, code: 'blurry', label: 'Image is blurry', description: 'The image is too blurry to process' },
    { id: 2, code: 'partial', label: 'Document partially visible', description: 'Part of the document is cut off' },
    { id: 3, code: 'glare', label: 'Glare or reflection', description: 'There is glare affecting readability' },
  ];

  it('renders when show is true', () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    expect(wrapper.text()).toContain('Reject Image');
  });

  it('does not render when show is false', () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: false,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    expect(wrapper.text()).not.toContain('Reject Image');
  });

  it('renders all rejection reasons in dropdown', () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    const select = wrapper.find('select');
    const options = select.findAll('option');

    expect(options.length).toBe(3);
    expect(options[0].text()).toBe('Image is blurry');
    expect(options[1].text()).toBe('Document partially visible');
    expect(options[2].text()).toBe('Glare or reflection');
  });

  it('emits close event when cancel button clicked', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    const cancelButton = wrapper.findAll('button').find(b => b.text().includes('Cancel'));
    await cancelButton?.trigger('click');

    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('emits close event when close button clicked', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    const closeButton = wrapper.find('.four-corners-modal-close');
    await closeButton.trigger('click');

    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('emits reject event with first reason when confirm clicked', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    // Wait for next tick to allow watcher to initialize
    await wrapper.vm.$nextTick();

    const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Confirm'));
    await confirmButton?.trigger('click');

    expect(wrapper.emitted('reject')).toBeTruthy();
    // First reason is selected by default
    expect(emittedEvents(wrapper, 'reject')[0]).toEqual([1, null]);
  });

  it('includes notes in reject event when provided', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    // Wait for next tick to allow watcher to initialize
    await wrapper.vm.$nextTick();

    const textarea = wrapper.find('textarea');
    await textarea.setValue('Additional context about the issue');

    const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Confirm'));
    await confirmButton?.trigger('click');

    expect(wrapper.emitted('reject')).toBeTruthy();
    expect(emittedEvents(wrapper, 'reject')[0]).toEqual([1, 'Additional context about the issue']);
  });

  it('trims whitespace from notes', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    // Wait for next tick to allow watcher to initialize
    await wrapper.vm.$nextTick();

    const textarea = wrapper.find('textarea');
    await textarea.setValue('   Some notes with whitespace   ');

    const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Confirm'));
    await confirmButton?.trigger('click');

    expect(wrapper.emitted('reject')).toBeTruthy();
    expect(emittedEvents(wrapper, 'reject')[0]).toEqual([1, 'Some notes with whitespace']);
  });

  it('returns null for empty notes', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: mockReasons,
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    // Wait for next tick to allow watcher to initialize
    await wrapper.vm.$nextTick();

    const textarea = wrapper.find('textarea');
    await textarea.setValue('   ');

    const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Confirm'));
    await confirmButton?.trigger('click');

    expect(wrapper.emitted('reject')).toBeTruthy();
    expect(emittedEvents(wrapper, 'reject')[0]).toEqual([1, null]);
  });

  it('disables confirm button when no reason selected', async () => {
    const wrapper = mount(RejectModal, {
      props: {
        show: true,
        reasons: [],
      },
      global: {
        stubs: {
          teleport: true,
        },
      },
    });

    const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Confirm'));
    expect(confirmButton?.attributes('disabled')).toBeDefined();
  });
});
