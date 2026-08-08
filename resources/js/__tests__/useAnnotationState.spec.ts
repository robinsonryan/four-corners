import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useAnnotationState } from '../Composables/useAnnotationState';
import type { Corners, Rotation } from '../Types';

describe('useAnnotationState', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  describe('initialization', () => {
    it('uses default corners when none provided', () => {
      const { corners } = useAnnotationState();

      expect(corners.value.topLeft).toEqual({ x: 50, y: 50 });
      expect(corners.value.topRight).toEqual({ x: 950, y: 50 });
      expect(corners.value.bottomRight).toEqual({ x: 950, y: 650 });
      expect(corners.value.bottomLeft).toEqual({ x: 50, y: 650 });
    });

    it('uses provided initial corners', () => {
      const initialCorners: Corners = {
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      };

      const { corners, suggestedCorners } = useAnnotationState(initialCorners);

      expect(corners.value).toEqual(initialCorners);
      expect(suggestedCorners.value).toEqual(initialCorners);
    });

    it('uses default rotation of 0 when none provided', () => {
      const { rotation, suggestedRotation } = useAnnotationState();

      expect(rotation.value).toBe(0);
      expect(suggestedRotation.value).toBe(0);
    });

    it('uses provided initial rotation', () => {
      const { rotation, suggestedRotation } = useAnnotationState(undefined, 90);

      expect(rotation.value).toBe(90);
      expect(suggestedRotation.value).toBe(90);
    });
  });

  describe('updateCorner', () => {
    it('updates a single corner', () => {
      const { corners, updateCorner } = useAnnotationState();

      updateCorner('topLeft', { x: 200, y: 200 });

      expect(corners.value.topLeft).toEqual({ x: 200, y: 200 });
      // Other corners should remain unchanged
      expect(corners.value.topRight).toEqual({ x: 950, y: 50 });
    });

    it('can update all corners independently', () => {
      const { corners, updateCorner } = useAnnotationState();

      updateCorner('topLeft', { x: 10, y: 10 });
      updateCorner('topRight', { x: 990, y: 10 });
      updateCorner('bottomRight', { x: 990, y: 690 });
      updateCorner('bottomLeft', { x: 10, y: 690 });

      expect(corners.value).toEqual({
        topLeft: { x: 10, y: 10 },
        topRight: { x: 990, y: 10 },
        bottomRight: { x: 990, y: 690 },
        bottomLeft: { x: 10, y: 690 },
      });
    });
  });

  describe('rotation', () => {
    it('rotates clockwise by 90 degrees', () => {
      const { rotation, rotate } = useAnnotationState();

      rotate(90);
      expect(rotation.value).toBe(90);

      rotate(90);
      expect(rotation.value).toBe(180);

      rotate(90);
      expect(rotation.value).toBe(270);

      rotate(90);
      expect(rotation.value).toBe(0);
    });

    it('rotates counter-clockwise by 90 degrees', () => {
      const { rotation, rotate } = useAnnotationState();

      rotate(-90);
      expect(rotation.value).toBe(270);

      rotate(-90);
      expect(rotation.value).toBe(180);
    });

    it('rotates by 180 degrees', () => {
      const { rotation, rotate } = useAnnotationState();

      rotate(180);
      expect(rotation.value).toBe(180);

      rotate(180);
      expect(rotation.value).toBe(0);
    });

    it('setRotation sets specific rotation value', () => {
      const { rotation, setRotation } = useAnnotationState();

      setRotation(270);
      expect(rotation.value).toBe(270);
    });
  });

  describe('change tracking', () => {
    it('hasCornerChanges returns false when corners match suggested', () => {
      const initialCorners: Corners = {
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      };

      const { hasCornerChanges } = useAnnotationState(initialCorners);

      expect(hasCornerChanges.value).toBe(false);
    });

    it('hasCornerChanges returns true when corners differ', () => {
      const initialCorners: Corners = {
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      };

      const { hasCornerChanges, updateCorner } = useAnnotationState(initialCorners);

      updateCorner('topLeft', { x: 150, y: 150 });

      expect(hasCornerChanges.value).toBe(true);
    });

    it('hasRotationChanges returns false when rotation matches suggested', () => {
      const { hasRotationChanges } = useAnnotationState(undefined, 90);

      expect(hasRotationChanges.value).toBe(false);
    });

    it('hasRotationChanges returns true when rotation differs', () => {
      const { hasRotationChanges, rotate } = useAnnotationState(undefined, 0);

      rotate(90);

      expect(hasRotationChanges.value).toBe(true);
    });

    it('hasChanges combines corner and rotation changes', () => {
      const initialCorners: Corners = {
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      };

      const { hasChanges, updateCorner } = useAnnotationState(initialCorners, 0);

      expect(hasChanges.value).toBe(false);

      updateCorner('topLeft', { x: 150, y: 150 });
      expect(hasChanges.value).toBe(true);

      // Reset and check rotation
      const { hasChanges: hasChanges2, rotate: rotate2 } = useAnnotationState(initialCorners, 0);
      expect(hasChanges2.value).toBe(false);

      rotate2(90);
      expect(hasChanges2.value).toBe(true);
    });
  });

  describe('reset', () => {
    it('resets corners to suggested values', () => {
      const initialCorners: Corners = {
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      };

      const { corners, updateCorner, reset } = useAnnotationState(initialCorners);

      updateCorner('topLeft', { x: 200, y: 200 });
      expect(corners.value.topLeft).toEqual({ x: 200, y: 200 });

      reset();
      expect(corners.value.topLeft).toEqual({ x: 100, y: 100 });
    });

    it('resets rotation to suggested value', () => {
      const { rotation, rotate, reset } = useAnnotationState(undefined, 90);

      rotate(90);
      expect(rotation.value).toBe(180);

      reset();
      expect(rotation.value).toBe(90);
    });
  });

  describe('auto-detection', () => {
    it('setAutoDetection updates corners and rotation', () => {
      const { corners, rotation, autoDetection, setAutoDetection } = useAnnotationState();

      const detection = {
        method: 'opencv_js_contour_v1' as const,
        corners: {
          topLeft: { x: 50, y: 50 },
          topRight: { x: 800, y: 55 },
          bottomRight: { x: 795, y: 600 },
          bottomLeft: { x: 45, y: 605 },
        },
        rotationSuggestion: 0 as Rotation,
        confidence: 0.85,
        detectionTimeMs: 150,
      };

      setAutoDetection(detection);

      expect(autoDetection.value).toEqual(detection);
      expect(corners.value).toEqual(detection.corners);
      expect(rotation.value).toBe(0);
    });

    it('setAutoDetection updates suggested values', () => {
      const { suggestedCorners, suggestedRotation, setAutoDetection } = useAnnotationState();

      const detection = {
        method: 'opencv_js_contour_v1' as const,
        corners: {
          topLeft: { x: 50, y: 50 },
          topRight: { x: 800, y: 55 },
          bottomRight: { x: 795, y: 600 },
          bottomLeft: { x: 45, y: 605 },
        },
        rotationSuggestion: 90 as Rotation,
        confidence: 0.85,
        detectionTimeMs: 150,
      };

      setAutoDetection(detection);

      expect(suggestedCorners.value).toEqual(detection.corners);
      expect(suggestedRotation.value).toBe(90);
    });
  });

  describe('time tracking', () => {
    it('returns a computed ref for time spent', () => {
      vi.useRealTimers(); // Use real timers for this test

      const { timeSpentSeconds } = useAnnotationState();

      // Initially should be very close to 0 (just created)
      expect(timeSpentSeconds.value).toBeGreaterThanOrEqual(0);
      expect(timeSpentSeconds.value).toBeLessThan(1);

      // The computed property should be a ref
      expect(typeof timeSpentSeconds.value).toBe('number');
    });
  });
});
