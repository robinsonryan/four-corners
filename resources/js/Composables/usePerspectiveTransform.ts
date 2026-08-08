import type { Corners, Rotation } from '../Types';
import type { CvMat, OpenCV } from "../Types/opencv";

interface UsePerspectiveTransformReturn {
  transform: (
    imageElement: HTMLImageElement | HTMLCanvasElement,
    corners: Corners,
    outputWidth: number,
    outputHeight: number,
    rotation: Rotation,
    cv: OpenCV
  ) => Promise<HTMLCanvasElement>;
  toBase64: (
    canvas: HTMLCanvasElement,
    format?: 'jpeg' | 'png',
    quality?: number
  ) => string;
}

export function usePerspectiveTransform(): UsePerspectiveTransformReturn {
  const transform = async (
    imageElement: HTMLImageElement | HTMLCanvasElement,
    corners: Corners,
    outputWidth: number,
    outputHeight: number,
    rotation: Rotation,
    cv: OpenCV
  ): Promise<HTMLCanvasElement> => {
    let src: CvMat | null = null;
    let dst: CvMat | null = null;
    let rotated: CvMat | null = null;
    let srcTri: CvMat | null = null;
    let dstTri: CvMat | null = null;
    let M: CvMat | null = null;

    try {
      // Read source image
      src = cv.imread(imageElement);

      // Define source points (the corners selected by user)
      srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
        corners.topLeft.x, corners.topLeft.y,
        corners.topRight.x, corners.topRight.y,
        corners.bottomRight.x, corners.bottomRight.y,
        corners.bottomLeft.x, corners.bottomLeft.y,
      ]);

      // Define destination points (the output rectangle)
      dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
        0, 0,
        outputWidth, 0,
        outputWidth, outputHeight,
        0, outputHeight,
      ]);

      // Get perspective transform matrix
      M = cv.getPerspectiveTransform(srcTri, dstTri);

      // Apply perspective transformation
      dst = new cv.Mat();
      const dsize = new cv.Size(outputWidth, outputHeight);
      cv.warpPerspective(src, dst, M, dsize);

      // Apply rotation if needed using cv.rotate() for correct dimension handling
      if (rotation !== 0) {
        rotated = new cv.Mat();

        if (rotation === 90) {
          cv.rotate(dst, rotated, cv.ROTATE_90_COUNTERCLOCKWISE);
        } else if (rotation === 180) {
          cv.rotate(dst, rotated, cv.ROTATE_180);
        } else if (rotation === 270) {
          cv.rotate(dst, rotated, cv.ROTATE_90_CLOCKWISE);
        }
      }

      // Create output canvas
      const canvas = document.createElement('canvas');

      // Adjust canvas size for 90/270 degree rotations
      if (rotation === 90 || rotation === 270) {
        canvas.width = outputHeight;
        canvas.height = outputWidth;
      } else {
        canvas.width = outputWidth;
        canvas.height = outputHeight;
      }

      // Write result to canvas
      cv.imshow(canvas, rotated || dst);

      return canvas;
    } finally {
      // Clean up OpenCV mats
      if (src) src.delete();
      if (dst) dst.delete();
      if (rotated) rotated.delete();
      if (srcTri) srcTri.delete();
      if (dstTri) dstTri.delete();
      if (M) M.delete();
    }
  };

  const toBase64 = (
    canvas: HTMLCanvasElement,
    format: 'jpeg' | 'png' = 'jpeg',
    quality = 0.9
  ): string => {
    const mimeType = format === 'jpeg' ? 'image/jpeg' : 'image/png';
    return canvas.toDataURL(mimeType, quality);
  };

  return {
    transform,
    toBase64,
  };
}
