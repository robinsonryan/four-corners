<script setup lang="ts">
/**
 * RejectModal - Modal dialog for rejecting images
 *
 * Allows users to select a rejection reason and optionally
 * add notes when marking an image as unsuitable for annotation.
 */
import { ref, watch } from 'vue';
import type { RejectionReason } from '../Types';

interface Props {
  show: boolean;
  reasons: RejectionReason[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
  close: [];
  reject: [reasonId: number | string, notes: string | null];
}>();

const selectedReasonId = ref<number | string | null>(null);
const notes = ref('');

// Reset form when modal opens
watch(() => props.show, (newShow) => {
  if (newShow) {
    selectedReasonId.value = props.reasons[0]?.id ?? null;
    notes.value = '';
  }
}, { immediate: true });

const handleClose = () => {
  emit('close');
};

const handleConfirm = () => {
  if (selectedReasonId.value !== null) {
    emit('reject', selectedReasonId.value, notes.value.trim() || null);
  }
};

const handleOverlayClick = (e: MouseEvent) => {
  if (e.target === e.currentTarget) {
    handleClose();
  }
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="four-corners-modal-overlay"
      @click="handleOverlayClick"
    >
      <div
        class="four-corners-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="reject-modal-title"
      >
        <div class="four-corners-modal-header">
          <h3 id="reject-modal-title" class="four-corners-modal-title">
            Reject Image
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

        <div class="four-corners-modal-body">
          <div class="four-corners-form-group">
            <label for="rejection-reason" class="four-corners-form-label">
              Reason for rejection
            </label>
            <select
              id="rejection-reason"
              v-model="selectedReasonId"
              class="four-corners-form-control"
            >
              <option
                v-for="reason in reasons"
                :key="reason.id"
                :value="reason.id"
              >
                {{ reason.label }}
              </option>
            </select>
            <p
              v-if="selectedReasonId"
              class="four-corners-form-help"
            >
              {{ reasons.find(r => r.id === selectedReasonId)?.description }}
            </p>
          </div>

          <div class="four-corners-form-group">
            <label for="rejection-notes" class="four-corners-form-label">
              Additional notes (optional)
            </label>
            <textarea
              id="rejection-notes"
              v-model="notes"
              class="four-corners-form-control"
              rows="3"
              placeholder="Add any additional context..."
            />
          </div>
        </div>

        <div class="four-corners-modal-footer">
          <button
            type="button"
            class="four-corners-btn four-corners-btn-outline"
            @click="handleClose"
          >
            Cancel
          </button>
          <button
            type="button"
            class="four-corners-btn four-corners-btn-danger"
            :disabled="!selectedReasonId"
            @click="handleConfirm"
          >
            Confirm Rejection
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.four-corners-form-help {
  margin-top: 4px;
  font-size: 13px;
  color: #6c757d;
}
</style>
