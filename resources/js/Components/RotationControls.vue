<script setup lang="ts">
/**
 * RotationControls - Rotation buttons for document orientation
 *
 * Provides buttons to rotate the document in 90-degree increments
 * counter-clockwise and clockwise.
 */
import type { Rotation } from '../Types';

interface Props {
  rotation: Rotation;
  disabled?: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  rotate: [delta: 90 | -90];
}>();

const rotateCounterClockwise = () => {
  emit('rotate', -90);
};

const rotateClockwise = () => {
  emit('rotate', 90);
};
</script>

<template>
  <div class="four-corners-rotation-controls">
    <span class="four-corners-control-label">Rotation</span>
    <div class="four-corners-rotation-buttons">
      <button
        type="button"
        class="four-corners-btn four-corners-btn-outline four-corners-btn-icon"
        :disabled="disabled"
        title="Rotate counter-clockwise"
        @click="rotateCounterClockwise"
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
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
          <path d="M3 3v5h5" />
        </svg>
      </button>

      <span class="four-corners-rotation-value">{{ rotation }}°</span>

      <button
        type="button"
        class="four-corners-btn four-corners-btn-outline four-corners-btn-icon"
        :disabled="disabled"
        title="Rotate clockwise"
        @click="rotateClockwise"
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
          <path d="M21 12a9 9 0 1 1-9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
          <path d="M21 3v5h-5" />
        </svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.four-corners-rotation-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.four-corners-rotation-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.four-corners-rotation-value {
  min-width: 45px;
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
