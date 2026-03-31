import { ref, type Ref } from 'vue';
import type { AutoDetection, Corners, Point, Rotation, RotationSuggestion } from '../Types';

interface UseCornerDetectionReturn {
  isDetecting: Ref<boolean>;
  detectionResult: Ref<AutoDetection | null>;
  detect: (imageElement: HTMLImageElement | HTMLCanvasElement, cv: any) => Promise<AutoDetection>;
  validateAndSuggestRotation: (corners: Corners) => RotationSuggestion;
}

function calculateAdaptiveThresholds(gray: any, cv: any): { low: number; high: number } {
  const mean = cv.mean(gray);
  const median = mean[0]; // Approximate using mean

  // Otsu-inspired adaptive thresholds
  const sigma = 0.33;
  const low = Math.max(10, Math.floor((1.0 - sigma) * median));
  const high = Math.min(255, Math.floor((1.0 + sigma) * median));

  return { low: Math.max(low, 30), high: Math.max(high, 100) };
}

function calculateDistance(p1: Point, p2: Point): number {
  return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
}

function calculateAspectRatioFromCorners(corners: Corners): number {
  // Calculate width as average of top and bottom edges
  const topWidth = calculateDistance(corners.topLeft, corners.topRight);
  const bottomWidth = calculateDistance(corners.bottomLeft, corners.bottomRight);
  const avgWidth = (topWidth + bottomWidth) / 2;

  // Calculate height as average of left and right edges
  const leftHeight = calculateDistance(corners.topLeft, corners.bottomLeft);
  const rightHeight = calculateDistance(corners.topRight, corners.bottomRight);
  const avgHeight = (leftHeight + rightHeight) / 2;

  return avgWidth / avgHeight;
}

/**
 * Validate detected corners against expected driver's license aspect ratio
 * and suggest rotation if needed.
 * US Driver's License: 3.375" x 2.125" = 1.588:1 aspect ratio
 */
function validateAndSuggestRotation(corners: Corners): RotationSuggestion {
  const DL_RATIO = 1.588; // 3.375 / 2.125
  const TOLERANCE = 0.15; // 15% tolerance

  const detectedRatio = calculateAspectRatioFromCorners(corners);

  // Check landscape match (ratio close to 1.588)
  const landscapeDiff = Math.abs(detectedRatio - DL_RATIO) / DL_RATIO;
  if (landscapeDiff < TOLERANCE) {
    return { suggestedRotation: 0, confidence: 0.9 - landscapeDiff, detectedAspectRatio: detectedRatio };
  }

  // Check portrait match (inverse ratio close to 1.588, needs 90 rotation)
  const inverseRatio = 1 / detectedRatio;
  const portraitDiff = Math.abs(inverseRatio - DL_RATIO) / DL_RATIO;
  if (portraitDiff < TOLERANCE) {
    return { suggestedRotation: 90, confidence: 0.9 - portraitDiff, detectedAspectRatio: detectedRatio };
  }

  // No good match found
  return { suggestedRotation: 0, confidence: 0, detectedAspectRatio: detectedRatio };
}

function orderPoints(points: Point[]): Corners {
  // Sort points by sum (top-left has smallest sum, bottom-right has largest)
  const sorted = [...points].sort((a, b) => (a.x + a.y) - (b.x + b.y));
  const topLeft = sorted[0];
  const bottomRight = sorted[3];

  // For the remaining two, sort by difference (top-right has largest diff, bottom-left has smallest)
  const remaining = [sorted[1], sorted[2]].sort((a, b) => (b.x - b.y) - (a.x - a.y));
  const topRight = remaining[0];
  const bottomLeft = remaining[1];

  return { topLeft, topRight, bottomRight, bottomLeft };
}

// Multi-pass detection parameters - progressively relaxed for difficult images
interface DetectionPass {
  cannyLow: number;
  cannyHigh: number;
  minAreaRatio: number;
  epsilon: number;
  useMorphClose: boolean;
}

const DETECTION_PASSES: DetectionPass[] = [
  { cannyLow: 50, cannyHigh: 150, minAreaRatio: 0.05, epsilon: 0.02, useMorphClose: false },
  { cannyLow: 30, cannyHigh: 100, minAreaRatio: 0.03, epsilon: 0.03, useMorphClose: true },
  { cannyLow: 20, cannyHigh: 80, minAreaRatio: 0.02, epsilon: 0.04, useMorphClose: true },
];

export function useCornerDetection(): UseCornerDetectionReturn {
  const isDetecting = ref(false);
  const detectionResult = ref<AutoDetection | null>(null);

  /**
   * Single-pass detection with given parameters
   */
  const detectWithParams = (
    blurred: any,
    cv: any,
    width: number,
    height: number,
    imageArea: number,
    pass: DetectionPass
  ): { quad: Point[] | null; area: number } => {
    let edges: any = null;
    let closed: any = null;
    let dilated: any = null;
    let contours: any = null;
    let hierarchy: any = null;

    try {
      // Canny edge detection
      edges = new cv.Mat();
      cv.Canny(blurred, edges, pass.cannyLow, pass.cannyHigh);

      // Apply morphological closing if enabled (connects edge gaps)
      let edgesForContours = edges;
      if (pass.useMorphClose) {
        closed = new cv.Mat();
        const closeKernel = cv.getStructuringElement(cv.MORPH_RECT, new cv.Size(5, 5));
        cv.morphologyEx(edges, closed, cv.MORPH_CLOSE, closeKernel);
        closeKernel.delete();
        edgesForContours = closed;
      }

      // Dilate to close small gaps
      dilated = new cv.Mat();
      const kernel = cv.Mat.ones(3, 3, cv.CV_8U);
      cv.dilate(edgesForContours, dilated, kernel);
      kernel.delete();

      // Find contours
      contours = new cv.MatVector();
      hierarchy = new cv.Mat();
      cv.findContours(dilated, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

      let bestQuad: Point[] | null = null;
      let bestArea = 0;

      // Adjust minimum area ratio for portrait images
      const isPortrait = height > width;
      const effectiveMinAreaRatio = isPortrait ? pass.minAreaRatio * 0.8 : pass.minAreaRatio;

      // Find largest quadrilateral
      for (let i = 0; i < contours.size(); i++) {
        const contour = contours.get(i);
        const area = cv.contourArea(contour);

        if (area < imageArea * effectiveMinAreaRatio) {
          contour.delete();
          continue;
        }

        // Approximate polygon with pass-specific epsilon
        const peri = cv.arcLength(contour, true);
        const approx = new cv.Mat();
        cv.approxPolyDP(contour, approx, pass.epsilon * peri, true);

        // Check if it's a quadrilateral
        if (approx.rows === 4) {
          if (cv.isContourConvex(approx)) {
            if (area > bestArea) {
              bestArea = area;
              bestQuad = [];
              for (let j = 0; j < 4; j++) {
                bestQuad.push({
                  x: approx.data32S[j * 2],
                  y: approx.data32S[j * 2 + 1],
                });
              }
            }
          }
        }

        approx.delete();
        contour.delete();
      }

      return { quad: bestQuad, area: bestArea };
    } finally {
      if (edges) edges.delete();
      if (closed) closed.delete();
      if (dilated) dilated.delete();
      if (contours) contours.delete();
      if (hierarchy) hierarchy.delete();
    }
  };

  const detect = async (
    imageElement: HTMLImageElement | HTMLCanvasElement,
    cv: any
  ): Promise<AutoDetection> => {
    isDetecting.value = true;
    const startTime = performance.now();

    let src: any = null;
    let gray: any = null;
    let blurred: any = null;

    try {
      // Read image
      src = cv.imread(imageElement);
      const width = src.cols;
      const height = src.rows;
      const imageArea = width * height;

      // Convert to grayscale
      gray = new cv.Mat();
      cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);

      // Apply Gaussian blur
      blurred = new cv.Mat();
      const ksize = new cv.Size(5, 5);
      cv.GaussianBlur(gray, blurred, ksize, 0);

      // Try adaptive thresholds first
      const adaptiveThresholds = calculateAdaptiveThresholds(gray, cv);

      let bestQuad: Point[] | null = null;
      let bestArea = 0;

      // Multi-pass detection: try each pass until we find a good result
      for (let passIndex = 0; passIndex < DETECTION_PASSES.length; passIndex++) {
        const pass = { ...DETECTION_PASSES[passIndex] };

        // For first pass, use adaptive thresholds if they're better
        if (passIndex === 0) {
          pass.cannyLow = Math.min(pass.cannyLow, adaptiveThresholds.low);
          pass.cannyHigh = Math.max(pass.cannyHigh, adaptiveThresholds.high);
        }

        const result = detectWithParams(blurred, cv, width, height, imageArea, pass);

        if (result.quad && result.area > bestArea) {
          bestQuad = result.quad;
          bestArea = result.area;

          // If we found a quad covering > 20% of image, it's probably good enough
          if (bestArea > imageArea * 0.2) {
            break;
          }
        }
      }

      const detectionTimeMs = Math.round(performance.now() - startTime);

      if (bestQuad) {
        const orderedCorners = orderPoints(bestQuad);
        const confidence = Math.min(bestArea / imageArea, 1);
        const rotationResult = validateAndSuggestRotation(orderedCorners);

        detectionResult.value = {
          method: 'opencv_js_contour_v1',
          corners: orderedCorners,
          rotationSuggestion: rotationResult.suggestedRotation,
          confidence: Math.round(confidence * 100) / 100,
          detectionTimeMs,
        };
      } else {
        // Fallback: use image edges with margin
        const margin = Math.min(width, height) * 0.05;
        detectionResult.value = {
          method: 'opencv_js_fallback_v1',
          corners: {
            topLeft: { x: margin, y: margin },
            topRight: { x: width - margin, y: margin },
            bottomRight: { x: width - margin, y: height - margin },
            bottomLeft: { x: margin, y: height - margin },
          },
          rotationSuggestion: 0,
          confidence: 0,
          detectionTimeMs,
        };
      }

      return detectionResult.value;
    } finally {
      // Clean up OpenCV mats
      if (src) src.delete();
      if (gray) gray.delete();
      if (blurred) blurred.delete();

      isDetecting.value = false;
    }
  };

  return {
    isDetecting,
    detectionResult,
    detect,
    validateAndSuggestRotation,
  };
}
