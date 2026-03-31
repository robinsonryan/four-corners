<script setup lang="ts">
/**
 * PreviewModal - Modal dialog for previewing transformed image
 *
 * Shows a preview of the perspective-corrected document
 * before the user confirms the annotation.
 */
import { ref, watch, onMounted, onUnmounted } from 'vue';

interface Props {
  show: boolean;
  canvas: HTMLCanvasElement | null;
  title?: string;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Preview',
});

const emit = defineEmits<{
  close: [];
}>();

const previewContainer = ref<HTMLDivElement | null>(null);

// Append canvas to container when shown
watch(() => [props.show, props.canvas], () => {
  if (props.show && props.canvas && previewContainer.value) {
    // Clear previous content
    previewContainer.value.innerHTML = '';

    // Clone canvas to avoid moving the original
    const displayCanvas = document.createElement('canvas');
    displayCanvas.width = props.canvas.width;
    displayCanvas.height = props.canvas.height;
    displayCanvas.className = 'four-corners-preview-canvas';

    const ctx = displayCanvas.getContext('2d');
    if (ctx) {
      ctx.drawImage(props.canvas, 0, 0);
    }

    previewContainer.value.appendChild(displayCanvas);
  }
}, { immediate: true });

const handleClose = () => {
  emit('close');
};

const handleOverlayClick = (e: MouseEvent) => {
  if (e.target === e.currentTarget) {
    handleClose();
  }
};

// Handle escape key
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.show) {
    handleClose();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="four-corners-modal-overlay"
      @click="handleOverlayClick"
    >
      <div
        class="four-corners-modal four-corners-preview-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="preview-modal-title"
      >
        <div class="four-corners-modal-header">
          <h3 id="preview-modal-title" class="four-corners-modal-title">
            {{ title }}
          </h3>
          <button
            type="button"
            class="four-corners-modal-close"
            aria-label="Close"
            @click="handleClose"
          >
            &times;
          </button>
        </div>

        <div class="four-corners-modal-body four-corners-preview-body">
          <div
            ref="previewContainer"
            class="four-corners-preview-container"
          >
            <div v-if="!canvas" class="four-corners-preview-placeholder">
              No preview available
            </div>
          </div>
        </div>

        <div class="four-corners-modal-footer">
          <button
            type="button"
            class="four-corners-btn four-corners-btn-outline"
            @click="handleClose"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.four-corners-preview-modal {
  max-width: 90vw;
  max-height: 90vh;
}

.four-corners-preview-body {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  min-height: 200px;
}

.four-corners-preview-container {
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 100%;
  max-height: 60vh;
  overflow: auto;
}

.four-corners-preview-container :deep(.four-corners-preview-canvas) {
  max-width: 100%;
  max-height: 60vh;
  object-fit: contain;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.four-corners-preview-placeholder {
  color: #6c757d;
  font-style: italic;
}
</style>
