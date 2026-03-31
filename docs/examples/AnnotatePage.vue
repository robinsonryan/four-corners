<!--
  Example Inertia.js page component for document annotation.

  This example shows how to integrate the DocumentAnnotator component
  into a Laravel + Inertia.js + Vue 3 application.

  Prerequisites:
  1. Install the package: composer require robinsonryan/four-corners
  2. Publish components: php artisan vendor:publish --tag=four-corners-components
  3. Import CSS in your app: @import '@/vendor/four-corners/css/four-corners.css';
-->

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { DocumentAnnotator } from '@/vendor/four-corners';
import type {
  CompletePayload,
  RejectPayload,
  DocumentType,
  RejectionReason
} from '@/vendor/four-corners';

// Props passed from Laravel controller
interface Props {
  imageUrl: string;
  imageId: string;
  annotationId?: string;
  documentType: DocumentType;
  rejectionReasons: RejectionReason[];
  opencvUrl: string;
  // Optional: pre-detected corners from previous session
  suggestedCorners?: {
    topLeft: { x: number; y: number };
    topRight: { x: number; y: number };
    bottomRight: { x: number; y: number };
    bottomLeft: { x: number; y: number };
  };
  suggestedRotation?: 0 | 90 | 180 | 270;
}

const props = defineProps<Props>();

// Page data from Inertia
const page = usePage();

// Loading state
const isSubmitting = ref(false);
const error = ref<string | null>(null);

// CSRF token for API requests
const csrfToken = computed(() =>
  (page.props as any).csrf_token ||
  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
);

/**
 * Handle annotation completion.
 *
 * This is called when the user clicks "Accept & Process" after
 * adjusting the document corners and rotation.
 */
const handleComplete = async (payload: CompletePayload) => {
  isSubmitting.value = true;
  error.value = null;

  try {
    // Option 1: Use Inertia router (recommended for Inertia apps)
    router.post(
      `/annotations/${props.imageId}/complete`,
      {
        document_type_id: props.documentType.id,
        final_corners: {
          top_left: payload.finalCorners.topLeft,
          top_right: payload.finalCorners.topRight,
          bottom_right: payload.finalCorners.bottomRight,
          bottom_left: payload.finalCorners.bottomLeft,
        },
        final_rotation: payload.finalRotation,
        time_spent_seconds: payload.timeSpentSeconds,
        display_image: payload.displayImageBase64,
        archive_image: payload.archiveImageBase64,
        auto_detection: payload.autoDetection ? {
          method: payload.autoDetection.method,
          corners: payload.autoDetection.corners ? {
            top_left: payload.autoDetection.corners.topLeft,
            top_right: payload.autoDetection.corners.topRight,
            bottom_right: payload.autoDetection.corners.bottomRight,
            bottom_left: payload.autoDetection.corners.bottomLeft,
          } : null,
          rotation_suggestion: payload.autoDetection.rotationSuggestion,
          confidence: payload.autoDetection.confidence,
          detection_time_ms: payload.autoDetection.detectionTimeMs,
        } : null,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          // Redirect handled by server response
        },
        onError: (errors) => {
          error.value = Object.values(errors).flat().join(', ');
          isSubmitting.value = false;
        },
      }
    );

    // Option 2: Use fetch API (for non-Inertia endpoints)
    // const response = await fetch(`/api/annotations/${props.imageId}/complete`, {
    //   method: 'POST',
    //   headers: {
    //     'Content-Type': 'application/json',
    //     'X-CSRF-TOKEN': csrfToken.value || '',
    //   },
    //   body: JSON.stringify({
    //     final_corners: payload.finalCorners,
    //     final_rotation: payload.finalRotation,
    //     time_spent_seconds: payload.timeSpentSeconds,
    //     display_image: payload.displayImageBase64,
    //     archive_image: payload.archiveImageBase64,
    //   }),
    // });
    //
    // if (!response.ok) {
    //   throw new Error('Failed to save annotation');
    // }
    //
    // router.visit('/images');

  } catch (e) {
    console.error('Annotation completion failed:', e);
    error.value = e instanceof Error ? e.message : 'An error occurred';
    isSubmitting.value = false;
  }
};

/**
 * Handle annotation rejection.
 *
 * This is called when the user selects a rejection reason and
 * confirms the rejection in the modal.
 */
const handleReject = async (payload: RejectPayload) => {
  isSubmitting.value = true;
  error.value = null;

  try {
    router.post(
      `/annotations/${props.imageId}/reject`,
      {
        rejection_reason_id: payload.rejectionReasonId,
        notes: payload.notes,
        time_spent_seconds: payload.timeSpentSeconds,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          // Redirect handled by server response
        },
        onError: (errors) => {
          error.value = Object.values(errors).flat().join(', ');
          isSubmitting.value = false;
        },
      }
    );
  } catch (e) {
    console.error('Annotation rejection failed:', e);
    error.value = e instanceof Error ? e.message : 'An error occurred';
    isSubmitting.value = false;
  }
};

/**
 * Handle cancellation.
 *
 * Navigate back to the image list without saving.
 */
const handleCancel = () => {
  router.visit('/images');
};
</script>

<template>
  <Head :title="`Annotate Image - ${imageId}`" />

  <div class="annotation-page">
    <!-- Header with navigation -->
    <header class="annotation-header">
      <button
        type="button"
        class="back-button"
        @click="handleCancel"
      >
        &larr; Back to Images
      </button>
      <h1>Annotate Document</h1>
      <span class="image-id">ID: {{ imageId }}</span>
    </header>

    <!-- Error message -->
    <div v-if="error" class="error-banner">
      {{ error }}
      <button @click="error = null">&times;</button>
    </div>

    <!-- Loading overlay -->
    <div v-if="isSubmitting" class="loading-overlay">
      <div class="loading-spinner"></div>
      <span>Saving annotation...</span>
    </div>

    <!-- Main annotator component -->
    <main class="annotation-content">
      <DocumentAnnotator
        :image-url="imageUrl"
        :document-type="documentType"
        :rejection-reasons="rejectionReasons"
        :annotation-id="annotationId"
        :suggested-corners="suggestedCorners"
        :suggested-rotation="suggestedRotation"
        :opencv-url="opencvUrl"
        @complete="handleComplete"
        @reject="handleReject"
        @cancel="handleCancel"
      />
    </main>
  </div>
</template>

<style scoped>
.annotation-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f5f5f5;
}

.annotation-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 24px;
  background: #fff;
  border-bottom: 1px solid #dee2e6;
}

.annotation-header h1 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  flex: 1;
}

.image-id {
  color: #6c757d;
  font-size: 13px;
  font-family: monospace;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 8px 12px;
  background: transparent;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  color: #495057;
  transition: all 0.15s;
}

.back-button:hover {
  background: #f8f9fa;
  border-color: #adb5bd;
}

.error-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 24px;
  background: #f8d7da;
  border-bottom: 1px solid #f5c6cb;
  color: #721c24;
}

.error-banner button {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: inherit;
  padding: 0 8px;
}

.loading-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  background: rgba(255, 255, 255, 0.9);
  z-index: 1000;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e9ecef;
  border-top-color: #007bff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.annotation-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Import the four-corners styles */
@import '@/vendor/four-corners/css/four-corners.css';
</style>
