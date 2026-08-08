/**
 * Minimal typings for the OpenCV.js surface this package actually uses.
 *
 * `resources/js/vendor/opencv.js` is a 9.8 MB emscripten build that ships no
 * types, which is why the corner-detection and perspective-transform composables
 * were written against `any`. This declares only the ~30 members we call — not
 * the whole library — so the interop is type-checked without pretending to
 * describe OpenCV in full.
 *
 * If you call a new `cv.*` member, add it here rather than reaching for `any`.
 */

/** An OpenCV matrix handle. Must be explicitly `delete()`d — emscripten memory. */
export interface CvMat {
    readonly rows: number;
    readonly cols: number;
    /** Signed 32-bit view of the matrix data, used to read contour points. */
    readonly data32S: Int32Array;
    size(): CvSize;
    delete(): void;
}

export interface CvSize {
    readonly width: number;
    readonly height: number;
}

/** A vector of matrices, as returned into by `findContours`. */
export interface CvMatVector {
    size(): number;
    get(index: number): CvMat;
    delete(): void;
}

/** `cv.mean()` returns a 4-element scalar (one per channel). */
export type CvScalar = readonly number[];

export interface OpenCV {
    /** Set by the emscripten runtime once the WASM module is ready. */
    onRuntimeInitialized?: () => void;

    Mat: {
        new (): CvMat;
        /** Static factory: a rows×cols matrix filled with ones. */
        ones(rows: number, cols: number, type: number): CvMat;
    };
    MatVector: new () => CvMatVector;
    Size: new (width: number, height: number) => CvSize;

    // Constants. Typed as number rather than enumerated — their values are
    // OpenCV's business, and we only ever pass them straight back in.
    readonly CV_32FC2: number;
    readonly CV_8U: number;
    readonly COLOR_RGBA2GRAY: number;
    readonly MORPH_RECT: number;
    readonly MORPH_CLOSE: number;
    readonly RETR_EXTERNAL: number;
    readonly CHAIN_APPROX_SIMPLE: number;
    readonly ROTATE_90_CLOCKWISE: number;
    readonly ROTATE_90_COUNTERCLOCKWISE: number;
    readonly ROTATE_180: number;

    imread(source: HTMLImageElement | HTMLCanvasElement): CvMat;
    imshow(canvas: HTMLCanvasElement | string, mat: CvMat): void;
    matFromArray(rows: number, cols: number, type: number, data: number[]): CvMat;

    mean(src: CvMat): CvScalar;
    cvtColor(src: CvMat, dst: CvMat, code: number): void;
    GaussianBlur(src: CvMat, dst: CvMat, ksize: CvSize, sigmaX: number): void;
    Canny(src: CvMat, dst: CvMat, threshold1: number, threshold2: number): void;
    dilate(src: CvMat, dst: CvMat, kernel: CvMat): void;
    morphologyEx(src: CvMat, dst: CvMat, op: number, kernel: CvMat): void;
    getStructuringElement(shape: number, ksize: CvSize): CvMat;

    findContours(
        image: CvMat,
        contours: CvMatVector,
        hierarchy: CvMat,
        mode: number,
        method: number,
    ): void;
    contourArea(contour: CvMat): number;
    arcLength(curve: CvMat, closed: boolean): number;
    approxPolyDP(curve: CvMat, approx: CvMat, epsilon: number, closed: boolean): void;
    isContourConvex(contour: CvMat): boolean;

    getPerspectiveTransform(src: CvMat, dst: CvMat): CvMat;
    warpPerspective(src: CvMat, dst: CvMat, m: CvMat, dsize: CvSize): void;
    rotate(src: CvMat, dst: CvMat, rotateCode: number): void;
}
