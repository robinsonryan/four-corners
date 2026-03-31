// Point coordinates
export interface Point {
  x: number;
  y: number;
}

// Four corners of a document
export interface Corners {
  topLeft: Point;
  topRight: Point;
  bottomRight: Point;
  bottomLeft: Point;
}

// Corner key type
export type CornerKey = keyof Corners;

// Auto-detection result from OpenCV.js
export interface AutoDetection {
  method: 'opencv_js_contour_v1' | 'opencv_js_fallback_v1' | 'ml_model_v1' | 'manual';
  corners: Corners | null;
  rotationSuggestion: Rotation;
  confidence: number;
  detectionTimeMs: number;
}

// Rotation values
export type Rotation = 0 | 90 | 180 | 270;

// EXIF orientation values (1-8)
export type ExifOrientation = 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8;

// EXIF orientation result
export interface ExifOrientationResult {
  rotation: Rotation;
  flipped: boolean;
}

// Rotation suggestion from aspect ratio validation
export interface RotationSuggestion {
  suggestedRotation: Rotation;
  confidence: number;
  detectedAspectRatio: number;
}

// Document type from backend
export interface DocumentType {
  id: number | string;
  code: string;
  name: string;
  aspectRatioWidth: number;
  aspectRatioHeight: number;
  displayWidth: number;
  displayHeight: number;
  archiveWidth: number;
  archiveHeight: number;
}

// Rejection reason from backend
export interface RejectionReason {
  id: number | string;
  code: string;
  label: string;
  description: string | null;
}

// Annotation status
export type AnnotationStatus = 'pending' | 'processing' | 'processed' | 'rejected';

// Annotation record
export interface Annotation {
  id: string;
  originalImagePath: string;
  originalWidth: number;
  originalHeight: number;
  documentTypeId: number | string;
  status: AnnotationStatus;
  autoDetection: AutoDetection | null;
  finalCorners: Corners | null;
  finalRotation: Rotation;
  acceptedWithoutChanges: boolean | null;
  cornerAdjustments: AdjustmentMetrics | null;
  rotationWasCorrect: boolean | null;
  displayImagePath: string | null;
  archiveImagePath: string | null;
  rejectionReasonId: number | string | null;
  rejectionNotes: string | null;
  annotatedBy: number | null;
  annotatedAt: string | null;
  timeSpentSeconds: number | null;
}

// Adjustment metrics
export interface AdjustmentMetrics {
  topLeft: number;
  topRight: number;
  bottomRight: number;
  bottomLeft: number;
  total: number;
  mean: number;
}

// Component props
export interface DocumentAnnotatorProps {
  imageUrl: string;
  documentType: DocumentType;
  annotationId?: string;
  suggestedCorners?: Corners;
  suggestedRotation?: Rotation;
  autoDetectionConfidence?: number;
  autoDetectionMethod?: AutoDetection['method'];
  rejectionReasons: RejectionReason[];
}

// Component emits
export interface CompletePayload {
  annotationId: string | null;
  finalCorners: Corners;
  finalRotation: Rotation;
  timeSpentSeconds: number;
  displayImageBase64: string;
  archiveImageBase64: string;
  autoDetection: AutoDetection | null;
}

export interface RejectPayload {
  annotationId: string | null;
  rejectionReasonId: number | string;
  notes: string | null;
  timeSpentSeconds: number;
}

// Package configuration
export interface FourCornersConfig {
  documentTypes: DocumentType[];
  rejectionReasons: RejectionReason[];
  opencvUrl: string;
  output: {
    jpegQuality: number;
    format: 'jpeg' | 'png';
  };
}

// Utility functions for coordinate conversion
export function cornersToBackend(corners: Corners): {
  top_left: { x: number; y: number };
  top_right: { x: number; y: number };
  bottom_right: { x: number; y: number };
  bottom_left: { x: number; y: number };
} {
  return {
    top_left: corners.topLeft,
    top_right: corners.topRight,
    bottom_right: corners.bottomRight,
    bottom_left: corners.bottomLeft,
  };
}

export function cornersFromBackend(corners: {
  top_left: { x: number; y: number };
  top_right: { x: number; y: number };
  bottom_right: { x: number; y: number };
  bottom_left: { x: number; y: number };
}): Corners {
  return {
    topLeft: corners.top_left,
    topRight: corners.top_right,
    bottomRight: corners.bottom_right,
    bottomLeft: corners.bottom_left,
  };
}
