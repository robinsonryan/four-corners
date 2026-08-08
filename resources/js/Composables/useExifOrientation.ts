import type { ExifOrientation, ExifOrientationResult } from '../Types';

/**
 * EXIF Orientation mapping:
 * 1: Normal (0 rotation)
 * 2: Flipped horizontal
 * 3: Rotated 180
 * 4: Flipped vertical
 * 5: Rotated 90 CW + flipped horizontal
 * 6: Rotated 90 CW (270 CCW)
 * 7: Rotated 90 CCW + flipped horizontal
 * 8: Rotated 90 CCW (270 CW)
 */
const EXIF_ORIENTATION_MAP: Record<ExifOrientation, ExifOrientationResult> = {
  1: { rotation: 0, flipped: false },
  2: { rotation: 0, flipped: true },
  3: { rotation: 180, flipped: false },
  4: { rotation: 180, flipped: true },
  5: { rotation: 270, flipped: true },
  6: { rotation: 270, flipped: false },
  7: { rotation: 90, flipped: true },
  8: { rotation: 90, flipped: false },
};

interface UseExifOrientationReturn {
  getOrientation: (file: File) => Promise<ExifOrientationResult>;
  getOrientationFromArrayBuffer: (buffer: ArrayBuffer) => ExifOrientationResult;
}

export function useExifOrientation(): UseExifOrientationReturn {
  /**
   * Read EXIF orientation from the first 64KB of a file
   */
  const getOrientation = async (file: File): Promise<ExifOrientationResult> => {
    // Read first 64KB which should contain EXIF header
    const slice = file.slice(0, 65536);
    const buffer = await slice.arrayBuffer();
    return getOrientationFromArrayBuffer(buffer);
  };

  /**
   * Parse EXIF orientation from ArrayBuffer
   */
  const getOrientationFromArrayBuffer = (buffer: ArrayBuffer): ExifOrientationResult => {
    const view = new DataView(buffer);

    // Check for JPEG SOI marker
    if (view.getUint16(0) !== 0xFFD8) {
      return { rotation: 0, flipped: false };
    }

    let offset = 2;
    const length = view.byteLength;

    while (offset < length) {
      if (offset + 2 > length) break;

      const marker = view.getUint16(offset);
      offset += 2;

      // APP1 marker (EXIF)
      if (marker === 0xFFE1) {
        if (offset + 2 > length) break;
        // Skip the APP1 segment length field; only the offset advance matters.
        offset += 2;

        // Check for "Exif\0\0" header
        if (offset + 6 > length) break;
        const exifHeader = String.fromCharCode(
          view.getUint8(offset),
          view.getUint8(offset + 1),
          view.getUint8(offset + 2),
          view.getUint8(offset + 3)
        );

        if (exifHeader !== 'Exif') {
          return { rotation: 0, flipped: false };
        }

        offset += 6; // Skip "Exif\0\0"

        // TIFF header
        if (offset + 8 > length) break;
        const tiffStart = offset;
        const endian = view.getUint16(offset);
        const littleEndian = endian === 0x4949; // 'II' = little endian, 'MM' = big endian

        // Check TIFF magic number (42)
        if (view.getUint16(offset + 2, littleEndian) !== 0x002A) {
          return { rotation: 0, flipped: false };
        }

        // Get offset to first IFD
        const ifdOffset = view.getUint32(offset + 4, littleEndian);
        offset = tiffStart + ifdOffset;

        if (offset + 2 > length) break;
        const numEntries = view.getUint16(offset, littleEndian);
        offset += 2;

        // Scan IFD entries for orientation tag (0x0112)
        for (let i = 0; i < numEntries; i++) {
          if (offset + 12 > length) break;

          const tag = view.getUint16(offset, littleEndian);

          if (tag === 0x0112) {
            // Orientation tag found
            const orientation = view.getUint16(offset + 8, littleEndian) as ExifOrientation;

            if (orientation >= 1 && orientation <= 8) {
              return EXIF_ORIENTATION_MAP[orientation];
            }
            return { rotation: 0, flipped: false };
          }

          offset += 12;
        }

        // Orientation tag not found
        return { rotation: 0, flipped: false };
      } else if ((marker & 0xFF00) === 0xFF00) {
        // Other marker, skip it
        if (offset + 2 > length) break;
        const segmentLength = view.getUint16(offset);
        offset += segmentLength;
      } else {
        // Invalid marker, stop
        break;
      }
    }

    return { rotation: 0, flipped: false };
  };

  return {
    getOrientation,
    getOrientationFromArrayBuffer,
  };
}
