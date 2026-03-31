<script setup lang="ts">
/**
 * ZoomControls - Zoom interface for canvas
 *
 * Provides zoom in, zoom out, and fit-to-screen controls
 * for navigating large document images.
 */
interface Props {
  zoom: number;
  minZoom?: number;
  maxZoom?: number;
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  minZoom: 0.25,
  maxZoom: 4,
  disabled: false,
});

const emit = defineEmits<{
  'update:zoom': [value: number];
  fit: [];
}>();

const zoomIn = () => {
  const newZoom = Math.min(props.zoom * 1.25, props.maxZoom);
  emit('update:zoom', Math.round(newZoom * 100) / 100);
};

const zoomOut = () => {
  const newZoom = Math.max(props.zoom / 1.25, props.minZoom);
  emit('update:zoom', Math.round(newZoom * 100) / 100);
};

const fitToScreen = () => {
  emit('fit');
};

const zoomPercentage = Math.round(props.zoom * 100);
</script>

<template>
  <div class="four-corners-zoom-controls">
    <span class="four-corners-control-label">Zoom</span>
    <div class="four-corners-zoom-buttons">
      <button
        type="button"
        class="four-corners-btn four-corners-btn-outline four-corners-btn-icon"
        :disabled="disabled || zoom <= minZoom"
        title="Zoom out"
        @click="zoomOut"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
          <line x1="8" y1="11" x2="14" y2="11" />
        </svg>
      </button>

      <span class="four-corners-zoom-value">{{ zoomPercentage }}%</span>

      <button
        type="button"
        class="four-corners-btn four-corners-btn-outline four-corners-btn-icon"
        :disabled="disabled || zoom >= maxZoom"
        title="Zoom in"
        @click="zoomIn"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
          <line x1="11" y1="8" x2="11" y2="14" />
          <line x1="8" y1="11" x2="14" y2="11" />
        </svg>
      </button>

      <button
        type="button"
        class="four-corners-btn four-corners-btn-outline"
        :disabled="disabled"
        title="Fit to screen"
        @click="fitToScreen"
      >
        Fit
      </button>
    </div>
  </div>
</template>

<style scoped>
.four-corners-zoom-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.four-corners-zoom-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.four-corners-zoom-value {
  min-width: 50px;
  text-align: center;
  font-weight: 500;
  font-variant-numeric: tabular-nums;
}

.four-corners-btn-icon {
  padding: 8px;
  line-height: 1;
}

.four-corners-btn-icon svg {
  display: block;
}
</style>
