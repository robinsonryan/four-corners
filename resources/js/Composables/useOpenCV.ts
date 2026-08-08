import { ref, type Ref } from 'vue';
import type { OpenCV } from "../Types/opencv";

declare global {
  interface Window {
    cv: typeof cv;
  }
  const cv: OpenCV;
}

interface UseOpenCVReturn {
  isLoaded: Ref<boolean>;
  isLoading: Ref<boolean>;
  error: Ref<Error | null>;
  load: () => Promise<typeof cv>;
}

function loadScript(url: string): Promise<void> {
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = url;
    script.async = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load script: ${url}`));
    document.head.appendChild(script);
  });
}

function waitForCV(): Promise<typeof cv> {
  return new Promise((resolve, reject) => {
    const timeout = setTimeout(() => {
      reject(new Error('OpenCV.js load timeout'));
    }, 30000);

    const check = () => {
      if (window.cv && window.cv.Mat) {
        clearTimeout(timeout);
        resolve(window.cv);
      } else if (window.cv && window.cv.onRuntimeInitialized === undefined) {
        // cv is loaded but not initialized yet
        window.cv.onRuntimeInitialized = () => {
          clearTimeout(timeout);
          resolve(window.cv);
        };
      } else {
        setTimeout(check, 100);
      }
    };
    check();
  });
}

export function useOpenCV(opencvUrl: string): UseOpenCVReturn {
  const isLoaded = ref(false);
  const isLoading = ref(false);
  const error = ref<Error | null>(null);

  const load = async (): Promise<typeof cv> => {
    if (isLoaded.value && window.cv) {
      return window.cv;
    }

    if (isLoading.value) {
      // Wait for existing load
      return new Promise((resolve, reject) => {
        const check = setInterval(() => {
          if (isLoaded.value) {
            clearInterval(check);
            resolve(window.cv);
          }
          if (error.value) {
            clearInterval(check);
            reject(error.value);
          }
        }, 100);
      });
    }

    isLoading.value = true;

    try {
      // Load OpenCV.js script
      await loadScript(opencvUrl);

      // Wait for cv to be ready
      await waitForCV();

      isLoaded.value = true;
      return window.cv;
    } catch (e) {
      error.value = e as Error;
      throw e;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoaded,
    isLoading,
    error,
    load,
  };
}
