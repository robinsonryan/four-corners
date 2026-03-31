<script setup lang="ts">
/**
 * ActionButtons - Main action buttons for annotation workflow
 *
 * Provides Reject, Preview, and Accept buttons for the
 * document annotation workflow.
 */
interface Props {
  canProcess: boolean;
  isProcessing: boolean;
  hasChanges: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  reject: [];
  preview: [];
  accept: [];
  reset: [];
}>();
</script>

<template>
  <div class="four-corners-action-buttons">
    <!-- Reset button (only shows when there are changes) -->
    <button
      v-if="hasChanges"
      type="button"
      class="four-corners-btn four-corners-btn-outline"
      :disabled="isProcessing"
      @click="emit('reset')"
    >
      Reset
    </button>

    <div class="four-corners-action-spacer" />

    <!-- Reject button -->
    <button
      type="button"
      class="four-corners-btn four-corners-btn-danger-outline"
      :disabled="isProcessing"
      @click="emit('reject')"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="four-corners-btn-icon"
      >
        <circle cx="12" cy="12" r="10" />
        <line x1="15" y1="9" x2="9" y2="15" />
        <line x1="9" y1="9" x2="15" y2="15" />
      </svg>
      Reject
    </button>

    <!-- Preview button -->
    <button
      type="button"
      class="four-corners-btn four-corners-btn-outline"
      :disabled="!canProcess"
      @click="emit('preview')"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="four-corners-btn-icon"
      >
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
      Preview
    </button>

    <!-- Accept button -->
    <button
      type="button"
      class="four-corners-btn four-corners-btn-success"
      :disabled="!canProcess"
      @click="emit('accept')"
    >
      <svg
        v-if="!isProcessing"
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="four-corners-btn-icon"
      >
        <polyline points="20 6 9 17 4 12" />
      </svg>
      <span v-if="isProcessing" class="four-corners-spinner-small" />
      {{ isProcessing ? 'Processing...' : 'Accept & Process' }}
    </button>
  </div>
</template>

<style scoped>
.four-corners-action-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.four-corners-action-spacer {
  flex: 1;
}

.four-corners-btn-icon {
  margin-right: 6px;
  vertical-align: -2px;
}

.four-corners-btn-danger-outline {
  background-color: transparent;
  border: 1px solid #dc3545;
  color: #dc3545;
}

.four-corners-btn-danger-outline:hover:not(:disabled) {
  background-color: #dc3545;
  color: #fff;
}

.four-corners-spinner-small {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: fc-spin 0.8s linear infinite;
  margin-right: 6px;
  vertical-align: -2px;
}

@keyframes fc-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
