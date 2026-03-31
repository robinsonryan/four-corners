import { describe, it, expect } from 'vitest';
import { cornersToBackend, cornersFromBackend } from '../Types';
import type { Corners } from '../Types';

describe('Type Utilities', () => {
  describe('cornersToBackend', () => {
    it('converts camelCase corners to snake_case', () => {
      const corners: Corners = {
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      };

      const result = cornersToBackend(corners);

      expect(result).toEqual({
        top_left: { x: 100, y: 100 },
        top_right: { x: 500, y: 100 },
        bottom_right: { x: 500, y: 400 },
        bottom_left: { x: 100, y: 400 },
      });
    });

    it('preserves coordinate values exactly', () => {
      const corners: Corners = {
        topLeft: { x: 123.456, y: 789.012 },
        topRight: { x: 0, y: 0 },
        bottomRight: { x: -100, y: -200 },
        bottomLeft: { x: 999999, y: 888888 },
      };

      const result = cornersToBackend(corners);

      expect(result.top_left.x).toBe(123.456);
      expect(result.top_left.y).toBe(789.012);
      expect(result.top_right).toEqual({ x: 0, y: 0 });
      expect(result.bottom_right).toEqual({ x: -100, y: -200 });
    });
  });

  describe('cornersFromBackend', () => {
    it('converts snake_case corners to camelCase', () => {
      const backendCorners = {
        top_left: { x: 100, y: 100 },
        top_right: { x: 500, y: 100 },
        bottom_right: { x: 500, y: 400 },
        bottom_left: { x: 100, y: 400 },
      };

      const result = cornersFromBackend(backendCorners);

      expect(result).toEqual({
        topLeft: { x: 100, y: 100 },
        topRight: { x: 500, y: 100 },
        bottomRight: { x: 500, y: 400 },
        bottomLeft: { x: 100, y: 400 },
      });
    });

    it('round-trips correctly', () => {
      const originalCorners: Corners = {
        topLeft: { x: 50, y: 75 },
        topRight: { x: 450, y: 80 },
        bottomRight: { x: 455, y: 380 },
        bottomLeft: { x: 55, y: 375 },
      };

      const backendFormat = cornersToBackend(originalCorners);
      const restored = cornersFromBackend(backendFormat);

      expect(restored).toEqual(originalCorners);
    });
  });
});
