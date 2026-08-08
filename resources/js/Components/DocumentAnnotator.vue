<script setup lang="ts">
/**
 * DocumentAnnotator - Main component for document annotation workflow
 *
 * This component orchestrates the annotation workflow:
 * 1. Loads and displays the document image
 * 2. Runs auto-detection using OpenCV.js
 * 3. Allows user to adjust corner positions
 * 4. Applies rotation controls
 * 5. Generates perspective-corrected output images
 *
 * @example
 * <DocumentAnnotator
 *   :image-url="imageUrl"
 *   :document-type="documentType"
 *   :rejection-reasons="rejectionReasons"
 *   @complete="handleComplete"
 *   @reject="handleReject"
 * />
 */
import { ref, onMounted, computed, watch } from 'vue';
import type {
  DocumentType,
  RejectionReason,
  Corners,
  Rotation,
  CompletePayload,
  RejectPayload,
  CornerKey,
  Point,
  ExifOrientationResult,
} from '../Types';
import { useOpenCV } from '../Composables/useOpenCV';
import { useCornerDetection } from '../Composables/useCornerDetection';
import { usePerspectiveTransform } from '../Composables/usePerspectiveTransform';
import { useAnnotationState } from '../Composables/useAnnotationState';
import { useExifOrientation } from '../Composables/useExifOrientation';

// Child components
import CornerHandle from './CornerHandle.vue';
import QuadrilateralOverlay from './QuadrilateralOverlay.vue';
import RotationControls from './RotationControls.vue';
import ZoomControls from './ZoomControls.vue';
import ActionButtons from './ActionButtons.vue';
import RejectModal from './RejectModal.vue';
import PreviewModal from './PreviewModal.vue';

// Props
interface Props {
  imageUrl: string;
  imageFile?: File;
  documentType: DocumentType;
  annotationId?: string;
  suggestedCorners?: Corners;
  suggestedRotation?: Rotation;
  autoDetectionConfidence?: number;
  autoDetectionMethod?: string;
  rejectionReasons: RejectionReason[];
  opencvUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
  opencvUrl: 'https://docs.opencv.org/4.9.0/opencv.js',
});

// Emits
const emit = defineEmits<{
  complete: [payload: CompletePayload];
  reject: [payload: RejectPayload];
  cancel: [];
}>();

// OpenCV
const { isLoaded: opencvLoaded, isLoading: opencvLoading, load: loadOpenCV } = useOpenCV(props.opencvUrl);
const { isDetecting, detect } = useCornerDetection();
const { transform, toBase64 } = usePerspectiveTransform();
const { getOrientation } = useExifOrientation();

// EXIF orientation state
const exifOrientation = ref<ExifOrientationResult | null>(null);
const rotationSuggestion = ref<Rotation | null>(null);

// Annotation state
const {
  corners,
  rotation,
  autoDetection,
  timeSpentSeconds,
  hasChanges,
  updateCorner,
  rotate,
  reset,
  setAutoDetection,
} = useAnnotationState(props.suggestedCorners, props.suggestedRotation);

// UI State
const imageElement = ref<HTMLImageElement | null>(null);
const imageLoaded = ref(false);
const showRejectModal = ref(false);
const showPreviewModal = ref(false);
const previewCanvas = ref<HTMLCanvasElement | null>(null);
const isProcessing = ref(false);

// Canvas/Stage state
const containerRef = ref<HTMLDivElement | null>(null);
const stageWidth = ref(800);
const stageHeight = ref(600);
const zoom = ref(1);
const imageWidth = ref(0);
const imageHeight = ref(0);

// Konva config
const stageConfig = computed(() => ({
  width: stageWidth.value,
  height: stageHeight.value,
}));

const imageConfig = computed(() => ({
  x: 0,
  y: 0,
  image: imageElement.value,
  width: imageWidth.value * zoom.value,
  height: imageHeight.value * zoom.value,
}));

// Computed
const isReady = computed(() => opencvLoaded.value && imageLoaded.value);
const canProcess = computed(() => isReady.value && !isProcessing.value);

// Scale corners based on zoom
const scaledCorners = computed<Corners>(() => ({
  topLeft: { x: corners.value.topLeft.x * zoom.value, y: corners.value.topLeft.y * zoom.value },
  topRight: { x: corners.value.topRight.x * zoom.value, y: corners.value.topRight.y * zoom.value },
  bottomRight: { x: corners.value.bottomRight.x * zoom.value, y: corners.value.bottomRight.y * zoom.value },
  bottomLeft: { x: corners.value.bottomLeft.x * zoom.value, y: corners.value.bottomLeft.y * zoom.value },
}));

// Methods
const loadImage = async () => {
  // Read EXIF orientation if file is provided
  if (props.imageFile) {
    try {
      exifOrientation.value = await getOrientation(props.imageFile);
      if (exifOrientation.value.rotation !== 0) {
        rotationSuggestion.value = exifOrientation.value.rotation;
      }
    } catch (error) {
      console.warn('Failed to read EXIF orientation:', error);
    }
  }

  const img = new Image();
  img.crossOrigin = 'anonymous';
  img.onload = () => {
    imageElement.value = img;
    imageWidth.value = img.naturalWidth;
    imageHeight.value = img.naturalHeight;
    imageLoaded.value = true;

    // Fit to container
    fitToContainer();
  };
  img.src = props.imageUrl;
};

const applyRotationSuggestion = () => {
  if (rotationSuggestion.value === null) {
    return;
  }

  // The suggestion is an ABSOLUTE orientation from EXIF (0/90/180/270); rotate()
  // takes a DELTA (90/-90/180). They were passed through directly, so a 270
  // suggestion could never be applied — it is not a valid delta. 0 means the
  // image is already upright and needs no call at all.
  const delta = { 90: 90, 180: 180, 270: -90 } as const;
  const suggestion = rotationSuggestion.value;

  if (suggestion !== 0) {
    rotate(delta[suggestion]);
  }

  rotationSuggestion.value = null;
};

const dismissRotationSuggestion = () => {
  rotationSuggestion.value = null;
};

const fitToContainer = () => {
  if (!containerRef.value || !imageElement.value) return;

  const containerWidth = containerRef.value.clientWidth;
  const containerHeight = containerRef.value.clientHeight;

  const scaleX = containerWidth / imageWidth.value;
  const scaleY = containerHeight / imageHeight.value;
  const newZoom = Math.min(scaleX, scaleY, 1);

  zoom.value = Math.round(newZoom * 100) / 100;
  updateStageSize();
};

const updateStageSize = () => {
  stageWidth.value = Math.ceil(imageWidth.value * zoom.value);
  stageHeight.value = Math.ceil(imageHeight.value * zoom.value);
};

const handleZoomChange = (newZoom: number) => {
  zoom.value = newZoom;
  updateStageSize();
};

const handleCornerDrag = (cornerKey: CornerKey, position: Point) => {
  // Convert scaled position back to original coordinates
  updateCorner(cornerKey, {
    x: position.x / zoom.value,
    y: position.y / zoom.value,
  });
};

const runAutoDetection = async () => {
  if (!imageElement.value) return;

  try {
    const cv = await loadOpenCV();
    const result = await detect(imageElement.value, cv);
    setAutoDetection(result);

    // If auto-detection found corners and suggests rotation, show suggestion
    // (only if no EXIF-based suggestion was already shown)
    if (result.corners && result.rotationSuggestion !== 0 && rotationSuggestion.value === null) {
      rotationSuggestion.value = result.rotationSuggestion;
    }
  } catch (error) {
    console.error('Auto-detection failed:', error);
  }
};

const generatePreview = async () => {
  if (!imageElement.value || !canProcess.value) return;

  isProcessing.value = true;
  try {
    const cv = await loadOpenCV();
    const canvas = await transform(
      imageElement.value,
      corners.value,
      props.documentType.displayWidth,
      props.documentType.displayHeight,
      rotation.value,
      cv
    );
    previewCanvas.value = canvas;
    showPreviewModal.value = true;
  } catch (error) {
    console.error('Preview generation failed:', error);
  } finally {
    isProcessing.value = false;
  }
};

const handleComplete = async () => {
  if (!imageElement.value || !canProcess.value) return;

  isProcessing.value = true;
  try {
    const cv = await loadOpenCV();

    // Generate display image
    const displayCanvas = await transform(
      imageElement.value,
      corners.value,
      props.documentType.displayWidth,
      props.documentType.displayHeight,
      rotation.value,
      cv
    );

    // Generate archive image
    const archiveCanvas = await transform(
      imageElement.value,
      corners.value,
      props.documentType.archiveWidth,
      props.documentType.archiveHeight,
      rotation.value,
      cv
    );

    const payload: CompletePayload = {
      annotationId: props.annotationId ?? null,
      finalCorners: corners.value,
      finalRotation: rotation.value,
      timeSpentSeconds: timeSpentSeconds.value,
      displayImageBase64: toBase64(displayCanvas, 'jpeg', 0.9),
      archiveImageBase64: toBase64(archiveCanvas, 'jpeg', 0.9),
      autoDetection: autoDetection.value,
    };

    emit('complete', payload);
  } catch (error) {
    console.error('Processing failed:', error);
  } finally {
    isProcessing.value = false;
  }
};

const handleReject = (reasonId: number | string, notes: string | null) => {
  const payload: RejectPayload = {
    annotationId: props.annotationId ?? null,
    rejectionReasonId: reasonId,
    notes,
    timeSpentSeconds: timeSpentSeconds.value,
  };
  emit('reject', payload);
  showRejectModal.value = false;
};

// The `cancel` event is declared in defineEmits, but no control in this
// template invokes this handler — so the event can never fire. Tracked in
// QUEUE.md. Deleting the handler would leave a declared public event with no
// emitter at all, which is worse, so it stays until the gap is designed away.
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const handleCancel = () => {
  emit('cancel');
};

// Watch container size
const updateContainerSize = () => {
  if (containerRef.value && imageLoaded.value) {
    fitToContainer();
  }
};

// Lifecycle
onMounted(async () => {
  loadImage();

  // Watch for container resize
  if (containerRef.value) {
    const resizeObserver = new ResizeObserver(updateContainerSize);
    resizeObserver.observe(containerRef.value);
  }

  // If no suggested corners, run auto-detection after image loads
  if (!props.suggestedCorners) {
    try {
      await loadOpenCV();
    } catch (error) {
      console.error('Failed to load OpenCV.js:', error);
    }
  }
});

// Watch for image load to run auto-detection
watch(imageLoaded, async (loaded) => {
  if (loaded && !props.suggestedCorners && opencvLoaded.value) {
    await runAutoDetection();
  }
});

watch(opencvLoaded, async (loaded) => {
  if (loaded && imageLoaded.value && !props.suggestedCorners && !autoDetection.value) {
    await runAutoDetection();
  }
});

// Watch zoom changes
watch(zoom, updateStageSize);
</script>

<template>
  <div class="four-corners-annotator">
    <!-- Loading state -->
    <div v-if="opencvLoading || !imageLoaded" class="four-corners-loading">
      <div class="four-corners-spinner" />
      <span class="four-corners-loading-text">
        {{ opencvLoading ? 'Loading OpenCV.js...' : 'Loading image...' }}
      </span>
    </div>

    <!-- Main content -->
    <template v-else>
      <!-- Canvas area -->
      <div ref="containerRef" class="four-corners-canvas-container">
        <v-stage :config="stageConfig">
          <v-layer>
            <!-- Document image -->
            <v-image :config="imageConfig" />

            <!-- Quadrilateral overlay -->
            <QuadrilateralOverlay :corners="scaledCorners" />

            <!-- Corner handles -->
            <CornerHandle
              v-for="key in (['topLeft', 'topRight', 'bottomRight', 'bottomLeft'] as CornerKey[])"
              :key="key"
              :corner-key="key"
              :position="scaledCorners[key]"
              @drag-move="handleCornerDrag"
            />
          </v-layer>
        </v-stage>

        <!-- Detection status badge -->
        <div v-if="autoDetection" class="four-corners-detection-status">
          <span>Auto-detected</span>
          <span
            class="four-corners-confidence-badge"
            :class="{
              'four-corners-confidence-high': autoDetection.confidence >= 0.7,
              'four-corners-confidence-low': autoDetection.confidence < 0.5,
            }"
          >
            {{ Math.round(autoDetection.confidence * 100) }}%
          </span>
        </div>

        <!-- Rotation suggestion banner -->
        <div v-if="rotationSuggestion !== null" class="four-corners-rotation-suggestion">
          <span>Suggested rotation: {{ rotationSuggestion }}°</span>
          <button
            type="button"
            class="four-corners-suggestion-btn four-corners-suggestion-apply"
            @click="applyRotationSuggestion"
          >
            Apply
          </button>
          <button
            type="button"
            class="four-corners-suggestion-btn four-corners-suggestion-dismiss"
            @click="dismissRotationSuggestion"
          >
            Dismiss
          </button>
        </div>

        <!-- Detecting overlay -->
        <div v-if="isDetecting" class="four-corners-detecting-overlay">
          <div class="four-corners-spinner" />
          <span>Detecting corners...</span>
        </div>
      </div>

      <!-- Controls panel -->
      <div class="four-corners-controls">
        <ZoomControls
          :zoom="zoom"
          @update:zoom="handleZoomChange"
          @fit="fitToContainer"
        />

        <RotationControls
          :rotation="rotation"
          :disabled="isProcessing"
          @rotate="rotate"
        />

        <ActionButtons
          :can-process="canProcess"
          :is-processing="isProcessing"
          :has-changes="hasChanges"
          @reject="showRejectModal = true"
          @preview="generatePreview"
          @accept="handleComplete"
          @reset="reset"
        />
      </div>
    </template>

    <!-- Reject Modal -->
    <RejectModal
      :show="showRejectModal"
      :reasons="rejectionReasons"
      @close="showRejectModal = false"
      @reject="handleReject"
    />

    <!-- Preview Modal -->
    <PreviewModal
      :show="showPreviewModal"
      :canvas="previewCanvas"
      @close="showPreviewModal = false"
    />
  </div>
</template>

<style scoped>
.four-corners-annotator {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 500px;
  background: #f5f5f5;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.four-corners-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 400px;
  gap: 16px;
}

.four-corners-loading-text {
  color: #6c757d;
  font-size: 14px;
}

.four-corners-canvas-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #333;
  position: relative;
  overflow: auto;
  min-height: 400px;
}

.four-corners-detection-status {
  position: absolute;
  top: 12px;
  left: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 4px;
  font-size: 13px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.four-corners-detecting-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: rgba(0, 0, 0, 0.5);
  color: white;
}

.four-corners-rotation-suggestion {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: rgba(255, 193, 7, 0.95);
  border-radius: 4px;
  font-size: 13px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  color: #333;
}

.four-corners-suggestion-btn {
  padding: 4px 8px;
  font-size: 12px;
  font-weight: 500;
  border-radius: 3px;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}

.four-corners-suggestion-apply {
  background: #28a745;
  color: white;
}

.four-corners-suggestion-apply:hover {
  background: #218838;
}

.four-corners-suggestion-dismiss {
  background: transparent;
  color: #333;
  border: 1px solid #666;
}

.four-corners-suggestion-dismiss:hover {
  background: rgba(0, 0, 0, 0.1);
}

.four-corners-controls {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 16px;
  background: #fff;
  border-top: 1px solid #dee2e6;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .four-corners-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
}
</style>
