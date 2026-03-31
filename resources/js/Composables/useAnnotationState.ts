import { ref, computed, type Ref, type ComputedRef } from 'vue';
import type { Corners, Point, Rotation, AutoDetection, CornerKey } from '../Types';

interface UseAnnotationStateReturn {
  // State
  corners: Ref<Corners>;
  suggestedCorners: Ref<Corners | null>;
  rotation: Ref<Rotation>;
  suggestedRotation: Ref<Rotation>;
  autoDetection: Ref<AutoDetection | null>;
  timeSpentSeconds: ComputedRef<number>;

  // Computed
  hasCornerChanges: ComputedRef<boolean>;
  hasRotationChanges: ComputedRef<boolean>;
  hasChanges: ComputedRef<boolean>;

  // Methods
  updateCorner: (key: CornerKey, point: Point) => void;
  setRotation: (value: Rotation) => void;
  rotate: (delta: 90 | -90 | 180) => void;
  reset: () => void;
  setAutoDetection: (detection: AutoDetection) => void;
}

function getDefaultCorners(width = 1000, height = 700): Corners {
  const margin = 50;
  return {
    topLeft: { x: margin, y: margin },
    topRight: { x: width - margin, y: margin },
    bottomRight: { x: width - margin, y: height - margin },
    bottomLeft: { x: margin, y: height - margin },
  };
}

function cornersEqual(a: Corners, b: Corners): boolean {
  const tolerance = 0.5; // Sub-pixel tolerance
  return (
    Math.abs(a.topLeft.x - b.topLeft.x) < tolerance &&
    Math.abs(a.topLeft.y - b.topLeft.y) < tolerance &&
    Math.abs(a.topRight.x - b.topRight.x) < tolerance &&
    Math.abs(a.topRight.y - b.topRight.y) < tolerance &&
    Math.abs(a.bottomRight.x - b.bottomRight.x) < tolerance &&
    Math.abs(a.bottomRight.y - b.bottomRight.y) < tolerance &&
    Math.abs(a.bottomLeft.x - b.bottomLeft.x) < tolerance &&
    Math.abs(a.bottomLeft.y - b.bottomLeft.y) < tolerance
  );
}

export function useAnnotationState(
  initialCorners?: Corners,
  initialRotation?: Rotation
): UseAnnotationStateReturn {
  // Corner state
  const corners = ref<Corners>(initialCorners ?? getDefaultCorners());
  const suggestedCorners = ref<Corners | null>(initialCorners ?? null);

  // Rotation state
  const rotation = ref<Rotation>(initialRotation ?? 0);
  const suggestedRotation = ref<Rotation>(initialRotation ?? 0);

  // Auto-detection
  const autoDetection = ref<AutoDetection | null>(null);

  // Timing
  const startTime = ref<number>(Date.now());
  const timeSpentSeconds = computed(() => (Date.now() - startTime.value) / 1000);

  // Change tracking
  const hasCornerChanges = computed(() => {
    if (!suggestedCorners.value) return true;
    return !cornersEqual(corners.value, suggestedCorners.value);
  });

  const hasRotationChanges = computed(() => {
    return rotation.value !== suggestedRotation.value;
  });

  const hasChanges = computed(() => hasCornerChanges.value || hasRotationChanges.value);

  // Methods
  const updateCorner = (key: CornerKey, point: Point) => {
    corners.value = { ...corners.value, [key]: point };
  };

  const setRotation = (value: Rotation) => {
    rotation.value = value;
  };

  const rotate = (delta: 90 | -90 | 180) => {
    const newRotation = ((rotation.value + delta + 360) % 360) as Rotation;
    rotation.value = newRotation;
  };

  const reset = () => {
    if (suggestedCorners.value) {
      corners.value = { ...suggestedCorners.value };
    }
    rotation.value = suggestedRotation.value;
  };

  const setAutoDetection = (detection: AutoDetection) => {
    autoDetection.value = detection;
    if (detection.corners) {
      suggestedCorners.value = detection.corners;
      corners.value = { ...detection.corners };
    }
    suggestedRotation.value = detection.rotationSuggestion;
    rotation.value = detection.rotationSuggestion;
  };

  return {
    // State
    corners,
    suggestedCorners,
    rotation,
    suggestedRotation,
    autoDetection,
    timeSpentSeconds,

    // Computed
    hasCornerChanges,
    hasRotationChanges,
    hasChanges,

    // Methods
    updateCorner,
    setRotation,
    rotate,
    reset,
    setAutoDetection,
  };
}
