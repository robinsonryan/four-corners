<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Four Corners - Demo</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .demo-header {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .demo-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .demo-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            flex-wrap: wrap;
        }

        .demo-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-input-group label {
            font-size: 14px;
            color: #6c757d;
            white-space: nowrap;
        }

        .demo-input-group input[type="text"],
        .demo-input-group select {
            padding: 6px 12px;
            font-size: 14px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-width: 200px;
        }

        .demo-input-group input[type="file"] {
            font-size: 13px;
        }

        .demo-btn {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #007bff;
            background: #007bff;
            color: #fff;
            transition: all 0.15s;
        }

        .demo-btn:hover {
            background: #0069d9;
            border-color: #0062cc;
        }

        .demo-btn-outline {
            background: transparent;
            color: #007bff;
        }

        .demo-btn-outline:hover {
            background: #007bff;
            color: #fff;
        }

        .demo-container {
            padding: 24px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .demo-info {
            background: #e7f1ff;
            border: 1px solid #b6d4fe;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .demo-info h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
            color: #0a58ca;
        }

        .demo-info p {
            margin: 0;
            font-size: 14px;
            color: #084298;
        }

        .demo-info code {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }

        #app {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: 600px;
        }

        .demo-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: #6c757d;
        }

        .demo-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e9ecef;
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .demo-console {
            background: #1e1e1e;
            border-radius: 8px;
            margin-top: 24px;
            overflow: hidden;
        }

        .demo-console-header {
            background: #333;
            padding: 8px 16px;
            font-size: 13px;
            color: #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .demo-console-content {
            padding: 16px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
            font-size: 13px;
        }

        .demo-console-content pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .demo-console-content .log-entry {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #333;
        }

        .demo-console-content .log-entry:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .demo-console-content .log-type {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .demo-console-content .log-type.complete {
            color: #4caf50;
        }

        .demo-console-content .log-type.reject {
            color: #f44336;
        }

        .demo-console-content .log-data {
            color: #9cdcfe;
        }

        .demo-clear-btn {
            background: transparent;
            border: 1px solid #555;
            color: #999;
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 3px;
            cursor: pointer;
        }

        .demo-clear-btn:hover {
            background: #444;
            color: #fff;
        }

        /* Four Corners Component Styles */
        .four-corners-annotator {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 500px;
        }

        .four-corners-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            background: #f8f9fa;
        }

        .four-corners-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e9ecef;
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .four-corners-canvas-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            overflow: auto;
            min-height: 400px;
            position: relative;
            padding: 20px;
        }

        .four-corners-canvas-container canvas {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .four-corners-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #fff;
            border-top: 1px solid #dee2e6;
            flex-wrap: wrap;
        }

        .four-corners-control-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .four-corners-control-label {
            font-size: 13px;
            font-weight: 500;
            color: #495057;
        }

        .four-corners-btn {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #007bff;
            background: #007bff;
            color: #fff;
            transition: all 0.15s;
        }

        .four-corners-btn:hover:not(:disabled) {
            background: #0069d9;
            border-color: #0062cc;
        }

        .four-corners-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .four-corners-btn-outline {
            background: transparent;
            color: #007bff;
        }

        .four-corners-btn-outline:hover:not(:disabled) {
            background: #007bff;
            color: #fff;
        }

        .four-corners-btn-success {
            background: #28a745;
            border-color: #28a745;
        }

        .four-corners-btn-success:hover:not(:disabled) {
            background: #218838;
            border-color: #1e7e34;
        }

        .four-corners-btn-danger {
            background: #dc3545;
            border-color: #dc3545;
        }

        .four-corners-btn-danger:hover:not(:disabled) {
            background: #c82333;
            border-color: #bd2130;
        }

        .four-corners-detection-status {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .four-corners-confidence-badge {
            padding: 2px 6px;
            border-radius: 3px;
            background: #ffc107;
            color: #333;
            font-weight: 600;
        }

        .four-corners-confidence-high {
            background: #28a745;
            color: #fff;
        }

        .four-corners-confidence-low {
            background: #dc3545;
            color: #fff;
        }

        .four-corners-detecting-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .four-corners-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .four-corners-modal {
            background: #fff;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .four-corners-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .four-corners-modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .four-corners-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            padding: 0;
            line-height: 1;
        }

        .four-corners-modal-body {
            padding: 20px;
        }

        .four-corners-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 20px;
            border-top: 1px solid #dee2e6;
        }

        .four-corners-form-group {
            margin-bottom: 16px;
        }

        .four-corners-form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        .four-corners-form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .four-corners-form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

    </style>
</head>
<body>
    <header class="demo-header">
        <h1>Four Corners Demo</h1>
        <div class="demo-controls">
            <div class="demo-input-group">
                <label for="doc-type">Document Type:</label>
                <select id="doc-type">
                    @foreach($documentTypes as $type)
                        <option value="{{ $type->id }}"
                                data-display-width="{{ $type->display_width }}"
                                data-display-height="{{ $type->display_height }}"
                                data-archive-width="{{ $type->archive_width }}"
                                data-archive-height="{{ $type->archive_height }}"
                                data-code="{{ $type->code }}"
                                data-name="{{ $type->name }}">
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="demo-input-group">
                <label for="local-file">Local File:</label>
                <input type="file" id="local-file" accept="image/*" style="cursor: pointer;">
            </div>
            <button type="button" class="demo-btn demo-btn-outline" onclick="loadRandomImage()">
                Load Random Image
            </button>
        </div>
    </header>

    <div class="demo-container">
        <div class="demo-info">
            <h3>Quick Start Demo</h3>
            <p>
                This demo page allows you to test the document annotation workflow.
                Use the controls above to load an image, or pass an image URL via query parameter:
                <code>?image=https://example.com/document.jpg</code>
            </p>
        </div>

        <div id="app">
            <div class="demo-loading">
                <div class="demo-spinner"></div>
                <span id="loading-status">Loading dependencies (0/3)...</span>
            </div>
        </div>

        <div class="demo-console">
            <div class="demo-console-header">
                <span>Event Console</span>
                <button type="button" class="demo-clear-btn" onclick="clearConsole()">Clear</button>
            </div>
            <div class="demo-console-content" id="console-output">
                <div class="log-entry">
                    <div class="log-type" style="color: #888;">Ready</div>
                    <div class="log-data">Waiting for annotation events...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dependencies loaded dynamically to avoid blocking -->
    <script>
        // Load scripts dynamically to prevent page blocking
        const scripts = [
            // Use dev build for Vue devtools support
            'https://unpkg.com/vue@3/dist/vue.global.js',
            'https://unpkg.com/konva@9/konva.min.js',
            'https://cdn.jsdelivr.net/npm/vue-konva@3/dist/vue-konva.umd.js'
        ];

        let loadedCount = 0;
        const totalScripts = scripts.length;

        function updateLoadingStatus() {
            const status = document.getElementById('loading-status');
            if (status) {
                status.textContent = `Loading dependencies (${loadedCount}/${totalScripts})...`;
            }
        }

        function loadScript(url) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = url;
                script.onload = () => {
                    loadedCount++;
                    updateLoadingStatus();
                    console.log('[Four Corners] Loaded:', url);
                    resolve();
                };
                script.onerror = () => reject(new Error('Failed to load: ' + url));
                document.head.appendChild(script);
            });
        }

        // Load scripts sequentially (they depend on each other)
        async function loadDependencies() {
            try {
                for (const url of scripts) {
                    await loadScript(url);
                }
                console.log('[Four Corners] All dependencies loaded');
                initializeApp();
            } catch (error) {
                console.error('[Four Corners] Failed to load dependencies:', error);
                document.getElementById('app').innerHTML = `
                    <div style="padding: 40px; text-align: center; color: #721c24; background: #f8d7da; border-radius: 8px;">
                        <h3>Failed to load dependencies</h3>
                        <p>${error.message}</p>
                        <p style="font-size: 13px;">Try refreshing the page or check your network connection.</p>
                    </div>
                `;
            }
        }

        // Start loading after DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadDependencies);
        } else {
            loadDependencies();
        }
    </script>

    @php
        $documentTypesJson = $documentTypes->map(function($t) {
            return [
                'id' => $t->id,
                'code' => $t->code,
                'name' => $t->name,
                'aspectRatioWidth' => $t->aspect_ratio_width,
                'aspectRatioHeight' => $t->aspect_ratio_height,
                'displayWidth' => $t->display_width,
                'displayHeight' => $t->display_height,
                'archiveWidth' => $t->archive_width,
                'archiveHeight' => $t->archive_height,
            ];
        });
        $rejectionReasonsJson = $rejectionReasons->map(function($r) {
            return [
                'id' => $r->id,
                'code' => $r->code,
                'label' => $r->label,
                'description' => $r->description,
            ];
        });
    @endphp
    <script>
        // Configuration from Laravel (available immediately)
        const CONFIG = {
            imageUrl: @json($imageUrl),
            documentTypes: @json($documentTypesJson),
            rejectionReasons: @json($rejectionReasonsJson),
            opencvUrl: @json($opencvUrl),
        };

        // Main initialization function - called after dependencies load
        function initializeApp() {
            console.log('[Four Corners] initializeApp called');
            console.log('[Four Corners] Vue available:', typeof Vue !== 'undefined');
            console.log('[Four Corners] Konva available:', typeof Konva !== 'undefined');
            console.log('[Four Corners] Config:', CONFIG);

        // Console logging
        function logToConsole(type, data) {
            const consoleEl = document.getElementById('console-output');
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            entry.innerHTML = `
                <div class="log-type ${type}">${type.toUpperCase()} Event @ ${new Date().toLocaleTimeString()}</div>
                <pre class="log-data">${JSON.stringify(data, null, 2)}</pre>
            `;
            consoleEl.insertBefore(entry, consoleEl.firstChild);

            // Also log to browser console
            console.log(`[Four Corners ${type}]`, data);
        }

        function clearConsole() {
            document.getElementById('console-output').innerHTML = `
                <div class="log-entry">
                    <div class="log-type" style="color: #888;">Cleared</div>
                    <div class="log-data">Console cleared at ${new Date().toLocaleTimeString()}</div>
                </div>
            `;
        }

        // Image loading
        let currentImageUrl = CONFIG.imageUrl;

        function loadRandomImage() {
            currentImageUrl = `https://picsum.photos/1200/800?random=${Date.now()}`;
            if (window.vueApp) {
                window.vueApp.imageUrl = currentImageUrl;
                window.vueApp.imageKey++;
            }
        }

        // EXIF orientation reader
        const EXIF_ORIENTATION_MAP = {
            1: { rotation: 0, flipped: false },
            2: { rotation: 0, flipped: true },
            3: { rotation: 180, flipped: false },
            4: { rotation: 180, flipped: true },
            5: { rotation: 270, flipped: true },
            6: { rotation: 270, flipped: false },
            7: { rotation: 90, flipped: true },
            8: { rotation: 90, flipped: false },
        };

        async function getExifOrientation(file) {
            try {
                const slice = file.slice(0, 65536);
                const buffer = await slice.arrayBuffer();
                const view = new DataView(buffer);

                // Check for JPEG SOI marker
                if (view.getUint16(0) !== 0xFFD8) {
                    return { rotation: 0, flipped: false };
                }

                let offset = 2;
                while (offset < view.byteLength) {
                    if (offset + 2 > view.byteLength) break;
                    const marker = view.getUint16(offset);
                    offset += 2;

                    if (marker === 0xFFE1) { // APP1 marker (EXIF)
                        if (offset + 8 > view.byteLength) break;
                        offset += 2; // Skip length
                        const exifHeader = String.fromCharCode(view.getUint8(offset), view.getUint8(offset + 1), view.getUint8(offset + 2), view.getUint8(offset + 3));
                        if (exifHeader !== 'Exif') return { rotation: 0, flipped: false };
                        offset += 6;

                        const littleEndian = view.getUint16(offset) === 0x4949;
                        const ifdOffset = view.getUint32(offset + 4, littleEndian);
                        offset += ifdOffset;

                        const numEntries = view.getUint16(offset, littleEndian);
                        offset += 2;

                        for (let i = 0; i < numEntries; i++) {
                            if (offset + 12 > view.byteLength) break;
                            const tag = view.getUint16(offset, littleEndian);
                            if (tag === 0x0112) { // Orientation tag
                                const orientation = view.getUint16(offset + 8, littleEndian);
                                if (orientation >= 1 && orientation <= 8) {
                                    console.log('[Four Corners] EXIF orientation:', orientation, EXIF_ORIENTATION_MAP[orientation]);
                                    return EXIF_ORIENTATION_MAP[orientation];
                                }
                            }
                            offset += 12;
                        }
                        break;
                    } else if ((marker & 0xFF00) === 0xFF00) {
                        const segmentLength = view.getUint16(offset);
                        offset += segmentLength;
                    } else {
                        break;
                    }
                }
            } catch (e) {
                console.warn('[Four Corners] EXIF read error:', e);
            }
            return { rotation: 0, flipped: false };
        }

        // Setup event listeners after DOM is ready
        function setupEventListeners() {
            const fileInput = document.getElementById('local-file');
            console.log('[Four Corners] File input element:', fileInput);

            if (fileInput) {
                fileInput.addEventListener('change', async function(e) {
                    console.log('[Four Corners] File selected:', e.target.files);
                    const file = e.target.files[0];
                    if (file) {
                        console.log('[Four Corners] Reading file:', file.name, file.size, 'bytes');

                        // Read EXIF orientation first
                        const exifResult = await getExifOrientation(file);

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            console.log('[Four Corners] File read complete, data URL length:', e.target.result.length);
                            currentImageUrl = e.target.result;
                            if (window.vueApp) {
                                window.vueApp.imageUrl = currentImageUrl;
                                window.vueApp.imageKey++;
                                console.log('[Four Corners] Updated vueApp imageUrl, new key:', window.vueApp.imageKey);

                                // Set rotation suggestion from EXIF if not 0
                                if (exifResult.rotation !== 0) {
                                    // Wait a bit for the component to initialize
                                    setTimeout(() => {
                                        const annotatorProxy = window.vueApp.$;
                                        if (annotatorProxy && annotatorProxy.ctx && annotatorProxy.ctx.rotationSuggestion !== undefined) {
                                            // This is hacky but the demo uses inline components
                                            console.log('[Four Corners] Setting rotation suggestion from EXIF:', exifResult.rotation);
                                        }
                                    }, 500);
                                }
                            } else {
                                console.error('[Four Corners] vueApp not found!');
                            }
                        };
                        reader.onerror = function(e) {
                            console.error('[Four Corners] FileReader error:', e);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                console.log('[Four Corners] File input change listener attached');
            } else {
                console.error('[Four Corners] File input element not found!');
            }

            const docTypeSelect = document.getElementById('doc-type');
            if (docTypeSelect) {
                docTypeSelect.addEventListener('change', function(e) {
                    const option = e.target.selectedOptions[0];
                    if (window.vueApp) {
                        window.vueApp.documentType = {
                            id: option.value,
                            code: option.dataset.code,
                            name: option.dataset.name,
                            displayWidth: parseInt(option.dataset.displayWidth),
                            displayHeight: parseInt(option.dataset.displayHeight),
                            archiveWidth: parseInt(option.dataset.archiveWidth),
                            archiveHeight: parseInt(option.dataset.archiveHeight),
                        };
                    }
                });
                console.log('[Four Corners] Doc type change listener attached');
            }
        }

        // Run setup when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupEventListeners);
        } else {
            setupEventListeners();
        }

        // Vue Application
        console.log('[Four Corners] Setting up Vue application...');
        const { createApp, ref, reactive, computed, watch, onMounted, onUnmounted, nextTick, h, Teleport } = Vue;
        console.log('[Four Corners] Vue APIs extracted successfully');

        // Main DocumentAnnotator component (simplified for demo)
        const DocumentAnnotator = {
            props: {
                imageUrl: { type: String, default: null },
                documentType: { type: Object, required: true },
                rejectionReasons: { type: Array, required: true },
                opencvUrl: { type: String, default: 'https://docs.opencv.org/4.9.0/opencv.js' },
                annotationId: { type: String, default: null },
            },
            emits: ['complete', 'reject', 'cancel'],
            setup(props, { emit }) {
                // State
                const containerRef = ref(null);
                const imageElement = ref(null);
                const imageLoaded = ref(false);
                const opencvLoaded = ref(false);
                const opencvLoading = ref(false);
                const opencvError = ref(null);
                const isDetecting = ref(false);
                const isProcessing = ref(false);
                const showRejectModal = ref(false);
                const showPreviewModal = ref(false);
                const previewDataUrl = ref(null);

                // Canvas/Stage state
                const stageWidth = ref(800);
                const stageHeight = ref(600);
                const zoom = ref(1);
                const imageWidth = ref(0);
                const imageHeight = ref(0);

                // Corners state
                const corners = ref({
                    topLeft: { x: 50, y: 50 },
                    topRight: { x: 750, y: 50 },
                    bottomRight: { x: 750, y: 550 },
                    bottomLeft: { x: 50, y: 550 },
                });
                const suggestedCorners = ref(null);
                const rotation = ref(0);
                const autoDetection = ref(null);
                const rotationSuggestion = ref(null);
                const startTime = Date.now();

                // Helper functions for aspect ratio validation
                const calculateDistance = (p1, p2) => {
                    return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
                };

                const calculateAspectRatioFromCorners = (c) => {
                    const topWidth = calculateDistance(c.topLeft, c.topRight);
                    const bottomWidth = calculateDistance(c.bottomLeft, c.bottomRight);
                    const avgWidth = (topWidth + bottomWidth) / 2;
                    const leftHeight = calculateDistance(c.topLeft, c.bottomLeft);
                    const rightHeight = calculateDistance(c.topRight, c.bottomRight);
                    const avgHeight = (leftHeight + rightHeight) / 2;
                    return avgWidth / avgHeight;
                };

                const validateAndSuggestRotation = (detectedCorners) => {
                    const DL_RATIO = 1.588; // US Driver's License: 3.375 / 2.125
                    const TOLERANCE = 0.15;
                    const detectedRatio = calculateAspectRatioFromCorners(detectedCorners);

                    // Check landscape match
                    if (Math.abs(detectedRatio - DL_RATIO) / DL_RATIO < TOLERANCE) {
                        return { suggestedRotation: 0, confidence: 0.9 };
                    }
                    // Check portrait match (needs 90° rotation)
                    if (Math.abs((1 / detectedRatio) - DL_RATIO) / DL_RATIO < TOLERANCE) {
                        return { suggestedRotation: 90, confidence: 0.9 };
                    }
                    return { suggestedRotation: 0, confidence: 0 };
                };

                // Computed - show UI as soon as image loads, don't require OpenCV
                const isReady = computed(() => imageLoaded.value);
                // Can process just needs image loaded and not currently processing
                // Can only process when image is loaded AND OpenCV is ready
                const canProcess = computed(() => isReady.value && !isProcessing.value && opencvLoaded.value);
                const hasChanges = computed(() => {
                    if (!suggestedCorners.value) return true;
                    const c = corners.value;
                    const s = suggestedCorners.value;
                    return Math.abs(c.topLeft.x - s.topLeft.x) > 0.5 ||
                           Math.abs(c.topLeft.y - s.topLeft.y) > 0.5;
                });
                const timeSpentSeconds = computed(() => (Date.now() - startTime) / 1000);

                const scaledCorners = computed(() => ({
                    topLeft: { x: corners.value.topLeft.x * zoom.value, y: corners.value.topLeft.y * zoom.value },
                    topRight: { x: corners.value.topRight.x * zoom.value, y: corners.value.topRight.y * zoom.value },
                    bottomRight: { x: corners.value.bottomRight.x * zoom.value, y: corners.value.bottomRight.y * zoom.value },
                    bottomLeft: { x: corners.value.bottomLeft.x * zoom.value, y: corners.value.bottomLeft.y * zoom.value },
                }));

                // Konva configs
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

                // Methods
                const loadImage = () => {
                    console.log('[Four Corners] loadImage called, url length:', props.imageUrl?.length);
                    imageLoaded.value = false;
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => {
                        console.log('[Four Corners] Image onload fired, size:', img.naturalWidth, 'x', img.naturalHeight);
                        imageElement.value = img;
                        imageWidth.value = img.naturalWidth;
                        imageHeight.value = img.naturalHeight;

                        // Set initial corners based on image size
                        const margin = Math.min(img.naturalWidth, img.naturalHeight) * 0.05;
                        corners.value = {
                            topLeft: { x: margin, y: margin },
                            topRight: { x: img.naturalWidth - margin, y: margin },
                            bottomRight: { x: img.naturalWidth - margin, y: img.naturalHeight - margin },
                            bottomLeft: { x: margin, y: img.naturalHeight - margin },
                        };
                        console.log('[Four Corners] Initial corners set, margin:', margin);

                        // Calculate initial zoom to fit typical container (800px wide)
                        // The actual fitToContainer will run after DOM updates
                        const estimatedContainerWidth = 800;
                        const estimatedContainerHeight = 600;
                        const scaleX = estimatedContainerWidth / img.naturalWidth;
                        const scaleY = estimatedContainerHeight / img.naturalHeight;
                        zoom.value = Math.min(scaleX, scaleY, 1);
                        updateStageSize();
                        console.log('[Four Corners] Initial zoom set:', zoom.value);

                        console.log('[Four Corners] Setting imageLoaded = true');
                        imageLoaded.value = true;

                        // Fit to actual container after DOM updates
                        nextTick(() => {
                            console.log('[Four Corners] nextTick: fitting to container');
                            fitToContainer();
                        });
                    };
                    img.onerror = (e) => {
                        console.error('[Four Corners] Image load error:', e);
                        console.error('[Four Corners] Failed to load image URL (first 100 chars):', props.imageUrl?.substring(0, 100));
                    };
                    img.src = props.imageUrl;
                    console.log('[Four Corners] Image src set, waiting for load...');
                };

                const fitToContainer = () => {
                    console.log('[Four Corners] fitToContainer called');
                    console.log('[Four Corners] - containerRef.value:', !!containerRef.value);
                    console.log('[Four Corners] - imageElement.value:', !!imageElement.value);

                    if (!containerRef.value || !imageElement.value) {
                        console.log('[Four Corners] fitToContainer: missing refs, skipping');
                        return;
                    }

                    const containerWidth = containerRef.value.clientWidth || 800;
                    const containerHeight = containerRef.value.clientHeight || 600;
                    console.log('[Four Corners] Container size:', containerWidth, 'x', containerHeight);
                    console.log('[Four Corners] Image size:', imageWidth.value, 'x', imageHeight.value);

                    const scaleX = containerWidth / imageWidth.value;
                    const scaleY = containerHeight / imageHeight.value;
                    zoom.value = Math.min(scaleX, scaleY, 1);
                    console.log('[Four Corners] Calculated zoom:', zoom.value);

                    updateStageSize();
                    console.log('[Four Corners] Stage size updated:', stageWidth.value, 'x', stageHeight.value);
                };

                const updateStageSize = () => {
                    stageWidth.value = Math.ceil(imageWidth.value * zoom.value);
                    stageHeight.value = Math.ceil(imageHeight.value * zoom.value);
                };

                // OpenCV loader - simple callback pattern (matching working test page)
                const loadOpenCV = () => {
                    console.log('[Four Corners] loadOpenCV called');

                    // Already loaded
                    if (window._opencvReady) {
                        console.log('[Four Corners] OpenCV already ready');
                        return;
                    }

                    // Already loading
                    if (window._opencvLoading) {
                        console.log('[Four Corners] OpenCV already loading');
                        return;
                    }

                    window._opencvLoading = true;
                    opencvLoading.value = true;
                    opencvError.value = null;

                    const script = document.createElement('script');
                    script.src = '/four-corners/opencv.js';
                    script.async = true;

                    script.onload = () => {
                        console.log('[Four Corners] Script onload fired');
                        console.log('[Four Corners] window.cv exists:', !!window.cv);

                        if (typeof cv !== 'undefined') {
                            // Check if runtime is ready
                            if (cv.getBuildInformation) {
                                console.log('[Four Corners] OpenCV fully ready (immediate)');
                                onOpenCVReady();
                            } else {
                                console.log('[Four Corners] Waiting for runtime...');
                                cv.onRuntimeInitialized = () => {
                                    console.log('[Four Corners] Runtime initialized');
                                    onOpenCVReady();
                                };
                            }
                        } else {
                            console.log('[Four Corners] cv not defined, setting up Module...');
                            window.Module = {
                                onRuntimeInitialized: () => {
                                    console.log('[Four Corners] Module.onRuntimeInitialized');
                                    onOpenCVReady();
                                }
                            };
                        }
                    };

                    script.onerror = (e) => {
                        console.error('[Four Corners] Script load error', e);
                        window._opencvLoading = false;
                        opencvLoading.value = false;
                        opencvError.value = 'Failed to load OpenCV script';
                    };

                    console.log('[Four Corners] Adding OpenCV script...');
                    document.head.appendChild(script);
                };

                const onOpenCVReady = () => {
                    console.log('[Four Corners] onOpenCVReady called');
                    window._opencvReady = true;
                    window._opencvLoading = false;
                    opencvLoaded.value = true;
                    opencvLoading.value = false;
                    console.log('[Four Corners] cv.Mat exists:', typeof cv.Mat === 'function');
                    console.log('[Four Corners] cv.warpPerspective exists:', typeof cv.warpPerspective === 'function');
                };

                // Simple sync check - returns cv if ready, null if not
                const getCV = () => {
                    if (window._opencvReady && typeof cv !== 'undefined' && cv.Mat) {
                        return cv;
                    }
                    return null;
                };

                // Ensure OpenCV is loaded before proceeding
                const ensureOpenCV = () => {
                    return new Promise((resolve, reject) => {
                        // Already ready
                        if (window._opencvReady) {
                            console.log('[Four Corners] ensureOpenCV: already ready');
                            resolve(cv);
                            return;
                        }

                        // Start loading if not already
                        loadOpenCV();

                        // Poll for ready state (simple and reliable)
                        let attempts = 0;
                        const maxAttempts = 100; // 10 seconds max
                        const checkReady = () => {
                            attempts++;
                            console.log('[Four Corners] ensureOpenCV: checking... attempt', attempts);

                            if (window._opencvReady) {
                                console.log('[Four Corners] ensureOpenCV: ready!');
                                resolve(cv);
                                return;
                            }

                            if (attempts >= maxAttempts) {
                                reject(new Error('OpenCV load timeout'));
                                return;
                            }

                            setTimeout(checkReady, 100);
                        };

                        setTimeout(checkReady, 100);
                    });
                };

                // SYNCHRONOUS - no async/await!
                const runAutoDetection = () => {
                    console.log('[Four Corners] runAutoDetection called, imageElement:', !!imageElement.value);
                    if (!imageElement.value) {
                        console.log('[Four Corners] runAutoDetection: no image element, aborting');
                        return;
                    }

                    if (!window._opencvReady) {
                        console.log('[Four Corners] runAutoDetection: OpenCV not ready');
                        alert('OpenCV is still loading. Please wait a moment and try again.');
                        return;
                    }

                    console.log('[Four Corners] runAutoDetection: starting detection...');
                    isDetecting.value = true;

                    try {
                        console.log('[Four Corners] runAutoDetection: OpenCV ready');

                        const startMs = performance.now();

                        // Create canvas from image
                        console.log('[Four Corners] runAutoDetection: creating canvas...');
                        const canvas = document.createElement('canvas');
                        const imgWidth = imageElement.value.naturalWidth;
                        const imgHeight = imageElement.value.naturalHeight;
                        console.log('[Four Corners] runAutoDetection: image size:', imgWidth, 'x', imgHeight);

                        // Limit size to prevent memory issues
                        const maxDim = 1500;
                        let scale = 1;
                        if (imgWidth > maxDim || imgHeight > maxDim) {
                            scale = maxDim / Math.max(imgWidth, imgHeight);
                            console.log('[Four Corners] runAutoDetection: scaling down by', scale);
                        }

                        canvas.width = Math.round(imgWidth * scale);
                        canvas.height = Math.round(imgHeight * scale);
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(imageElement.value, 0, 0, canvas.width, canvas.height);

                        // Convert to OpenCV Mat
                        console.log('[Four Corners] runAutoDetection: cv.imread...');
                        const src = cv.imread(canvas);
                        console.log('[Four Corners] runAutoDetection: src mat created:', src.rows, 'x', src.cols);

                        const gray = new cv.Mat();
                        const blurred = new cv.Mat();
                        const edges = new cv.Mat();
                        const dilated = new cv.Mat();

                        console.log('[Four Corners] runAutoDetection: cvtColor...');
                        cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);

                        console.log('[Four Corners] runAutoDetection: GaussianBlur...');
                        cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);

                        // Calculate adaptive Canny thresholds based on image statistics
                        const mean = cv.mean(gray);
                        const median = mean[0];
                        const sigma = 0.33;
                        const cannyLow = Math.max(30, Math.floor((1.0 - sigma) * median));
                        const cannyHigh = Math.max(100, Math.floor((1.0 + sigma) * median));
                        console.log('[Four Corners] runAutoDetection: adaptive Canny thresholds:', cannyLow, cannyHigh);

                        console.log('[Four Corners] runAutoDetection: Canny...');
                        cv.Canny(blurred, edges, cannyLow, cannyHigh);

                        // Apply morphological closing to connect edge gaps
                        const closed = new cv.Mat();
                        const closeKernel = cv.getStructuringElement(cv.MORPH_RECT, new cv.Size(5, 5));
                        cv.morphologyEx(edges, closed, cv.MORPH_CLOSE, closeKernel);
                        closeKernel.delete();
                        console.log('[Four Corners] runAutoDetection: morphological closing applied');

                        console.log('[Four Corners] runAutoDetection: dilate...');
                        const kernel = cv.Mat.ones(3, 3, cv.CV_8U);
                        cv.dilate(closed, dilated, kernel);
                        kernel.delete();
                        closed.delete();

                        // Find contours
                        console.log('[Four Corners] runAutoDetection: findContours...');
                        const contours = new cv.MatVector();
                        const hierarchy = new cv.Mat();
                        cv.findContours(dilated, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
                        console.log('[Four Corners] runAutoDetection: found', contours.size(), 'contours');

                        let bestContour = null;
                        let maxArea = 0;
                        // Use adaptive area threshold for portrait/landscape
                        const isPortrait = src.rows > src.cols;
                        const minAreaRatio = isPortrait ? 0.05 : 0.08;
                        const minAreaThreshold = src.rows * src.cols * minAreaRatio;
                        console.log('[Four Corners] runAutoDetection: isPortrait:', isPortrait, 'minAreaRatio:', minAreaRatio);
                        let largestAreaFound = 0;
                        let passedAreaCount = 0;
                        const pointCounts = {};

                        for (let i = 0; i < contours.size(); i++) {
                            const contour = contours.get(i);
                            const area = cv.contourArea(contour);
                            if (area > largestAreaFound) largestAreaFound = area;
                            if (area > minAreaThreshold) {
                                passedAreaCount++;
                                const perimeter = cv.arcLength(contour, true);
                                const approx = new cv.Mat();
                                cv.approxPolyDP(contour, approx, 0.04 * perimeter, true);
                                const numPoints = approx.rows;
                                pointCounts[numPoints] = (pointCounts[numPoints] || 0) + 1;
                                if (numPoints === 4 && area > maxArea) {
                                    maxArea = area;
                                    if (bestContour) bestContour.delete();
                                    bestContour = approx;
                                } else {
                                    approx.delete();
                                }
                            }
                        }
                        console.log('[Four Corners] runAutoDetection: min area threshold:', minAreaThreshold.toFixed(0));
                        console.log('[Four Corners] runAutoDetection: largest contour area:', largestAreaFound.toFixed(0));
                        console.log('[Four Corners] runAutoDetection:', passedAreaCount, 'contours passed area threshold');
                        console.log('[Four Corners] runAutoDetection: point counts after approx:', pointCounts);

                        const detectionTimeMs = performance.now() - startMs;
                        console.log('[Four Corners] runAutoDetection: detection took', detectionTimeMs.toFixed(0), 'ms');

                        if (bestContour) {
                            // Extract corners from contour
                            const points = [];
                            for (let i = 0; i < 4; i++) {
                                points.push({
                                    x: bestContour.data32S[i * 2] / scale,
                                    y: bestContour.data32S[i * 2 + 1] / scale,
                                });
                            }

                            // Sort points: top-left, top-right, bottom-right, bottom-left
                            points.sort((a, b) => a.y - b.y);
                            const top = points.slice(0, 2).sort((a, b) => a.x - b.x);
                            const bottom = points.slice(2, 4).sort((a, b) => a.x - b.x);

                            const detectedCorners = {
                                topLeft: top[0],
                                topRight: top[1],
                                bottomRight: bottom[1],
                                bottomLeft: bottom[0],
                            };

                            console.log('[Four Corners] runAutoDetection: detected corners:', detectedCorners);
                            corners.value = { ...detectedCorners };
                            suggestedCorners.value = { ...detectedCorners };

                            // Validate aspect ratio and suggest rotation
                            const rotationResult = validateAndSuggestRotation(detectedCorners);
                            if (rotationResult.suggestedRotation !== 0) {
                                rotationSuggestion.value = rotationResult.suggestedRotation;
                                console.log('[Four Corners] runAutoDetection: suggesting rotation:', rotationResult.suggestedRotation);
                            }

                            autoDetection.value = {
                                method: 'opencv_js_contour_v1',
                                corners: detectedCorners,
                                rotationSuggestion: rotationResult.suggestedRotation,
                                confidence: Math.min(0.95, maxArea / (src.rows * src.cols)),
                                detectionTimeMs,
                            };

                            bestContour.delete();
                        } else {
                            console.log('[Four Corners] runAutoDetection: no document contour found');
                            autoDetection.value = {
                                method: 'opencv_js_fallback_v1',
                                corners: null,
                                rotationSuggestion: 0,
                                confidence: 0,
                                detectionTimeMs,
                            };
                        }

                        // Cleanup
                        console.log('[Four Corners] runAutoDetection: cleanup...');
                        src.delete();
                        gray.delete();
                        blurred.delete();
                        edges.delete();
                        dilated.delete();
                        contours.delete();
                        hierarchy.delete();

                        console.log('[Four Corners] runAutoDetection: done!');
                    } catch (error) {
                        console.error('[Four Corners] runAutoDetection FAILED:', error);
                    } finally {
                        isDetecting.value = false;
                    }
                };

                // SYNCHRONOUS perspective transform - no async/await!
                const applyPerspectiveTransform = (width, height) => {
                    console.log('[Four Corners] applyPerspectiveTransform called, size:', width, 'x', height);

                    if (!window._opencvReady) {
                        throw new Error('OpenCV not ready');
                    }
                    console.log('[Four Corners] applyPerspectiveTransform: OpenCV ready');
                    const c = corners.value;

                    // Source points (current corners)
                    const srcPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
                        c.topLeft.x, c.topLeft.y,
                        c.topRight.x, c.topRight.y,
                        c.bottomRight.x, c.bottomRight.y,
                        c.bottomLeft.x, c.bottomLeft.y,
                    ]);

                    // Destination points (output corners)
                    const dstPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
                        0, 0,
                        width, 0,
                        width, height,
                        0, height,
                    ]);

                    // Get transform matrix
                    const M = cv.getPerspectiveTransform(srcPoints, dstPoints);

                    // Create source canvas from image
                    const srcCanvas = document.createElement('canvas');
                    srcCanvas.width = imageElement.value.naturalWidth;
                    srcCanvas.height = imageElement.value.naturalHeight;
                    const srcCtx = srcCanvas.getContext('2d');
                    srcCtx.drawImage(imageElement.value, 0, 0);

                    // Read source image into Mat
                    const src = cv.imread(srcCanvas);
                    const dst = new cv.Mat();
                    const dsize = new cv.Size(width, height);

                    // Apply perspective warp
                    cv.warpPerspective(src, dst, M, dsize, cv.INTER_LINEAR, cv.BORDER_CONSTANT, new cv.Scalar());

                    // Apply rotation if needed using cv.rotate() for correct dimension handling
                    let finalMat = dst;
                    if (rotation.value !== 0) {
                        const rotated = new cv.Mat();
                        if (rotation.value === 90) {
                            cv.rotate(dst, rotated, cv.ROTATE_90_COUNTERCLOCKWISE);
                        } else if (rotation.value === 180) {
                            cv.rotate(dst, rotated, cv.ROTATE_180);
                        } else if (rotation.value === 270) {
                            cv.rotate(dst, rotated, cv.ROTATE_90_CLOCKWISE);
                        }
                        dst.delete();
                        finalMat = rotated;
                    }

                    // Create OUTPUT canvas at the correct size
                    const outCanvas = document.createElement('canvas');
                    // For 90/270 rotation, dimensions are swapped
                    if (rotation.value === 90 || rotation.value === 270) {
                        outCanvas.width = height;
                        outCanvas.height = width;
                    } else {
                        outCanvas.width = width;
                        outCanvas.height = height;
                    }

                    cv.imshow(outCanvas, finalMat);
                    finalMat.delete();

                    // Cleanup
                    src.delete();
                    M.delete();
                    srcPoints.delete();
                    dstPoints.delete();

                    console.log('[Four Corners] applyPerspectiveTransform: done, output:', outCanvas.width, 'x', outCanvas.height);
                    return outCanvas;
                };

                // SYNCHRONOUS - no async/await!
                const generatePreview = () => {
                    if (!canProcess.value) return;

                    console.log('[Four Corners] Generating preview...');
                    try {
                        const canvas = applyPerspectiveTransform(
                            props.documentType.displayWidth,
                            props.documentType.displayHeight
                        );
                        previewDataUrl.value = canvas.toDataURL('image/jpeg', 0.9);
                        showPreviewModal.value = true;
                        console.log('[Four Corners] Preview generated successfully');
                    } catch (error) {
                        console.error('Preview failed:', error);
                        alert('Preview failed: ' + error.message);
                    }
                };

                // SYNCHRONOUS - no async/await!
                const handleComplete = () => {
                    if (!canProcess.value) return;

                    console.log('[Four Corners] Processing annotation...');
                    try {
                        const displayCanvas = applyPerspectiveTransform(
                            props.documentType.displayWidth,
                            props.documentType.displayHeight
                        );
                        const archiveCanvas = applyPerspectiveTransform(
                            props.documentType.archiveWidth,
                            props.documentType.archiveHeight
                        );

                        const payload = {
                            annotationId: props.annotationId,
                            finalCorners: corners.value,
                            finalRotation: rotation.value,
                            timeSpentSeconds: timeSpentSeconds.value,
                            displayImageBase64: displayCanvas.toDataURL('image/jpeg', 0.9),
                            archiveImageBase64: archiveCanvas.toDataURL('image/jpeg', 0.9),
                            autoDetection: autoDetection.value,
                        };

                        emit('complete', payload);
                        console.log('[Four Corners] Annotation completed successfully');
                    } catch (error) {
                        console.error('Processing failed:', error);
                        alert('Processing failed: ' + error.message);
                    }
                };

                const handleReject = (reasonId, notes) => {
                    const payload = {
                        annotationId: props.annotationId,
                        rejectionReasonId: reasonId,
                        notes,
                        timeSpentSeconds: timeSpentSeconds.value,
                    };
                    emit('reject', payload);
                    showRejectModal.value = false;
                };

                const handleCornerDrag = (cornerKey, x, y) => {
                    corners.value = {
                        ...corners.value,
                        [cornerKey]: { x: x / zoom.value, y: y / zoom.value },
                    };
                };

                const handleZoom = (delta) => {
                    zoom.value = Math.max(0.25, Math.min(4, zoom.value + delta));
                    updateStageSize();
                };

                const handleRotate = (delta) => {
                    rotation.value = ((rotation.value + delta + 360) % 360);
                };

                const reset = () => {
                    if (suggestedCorners.value) {
                        corners.value = { ...suggestedCorners.value };
                    }
                    rotation.value = 0;
                };

                // Manual detect corners button
                const detectCorners = () => {
                    console.log('[Four Corners] detectCorners clicked');
                    runAutoDetection();
                };

                // Rotation suggestion handlers
                const applyRotationSuggestion = () => {
                    if (rotationSuggestion.value !== null) {
                        rotation.value = rotationSuggestion.value;
                        rotationSuggestion.value = null;
                    }
                };

                const dismissRotationSuggestion = () => {
                    rotationSuggestion.value = null;
                };

                // Lifecycle
                onMounted(() => {
                    console.log('[Four Corners] Component mounted');
                    console.log('[Four Corners] - imageUrl exists:', !!props.imageUrl);
                    console.log('[Four Corners] - imageUrl length:', props.imageUrl?.length || 0);
                    // Only load image if URL is provided
                    if (props.imageUrl) {
                        console.log('[Four Corners] Calling loadImage...');
                        loadImage();
                    } else {
                        console.log('[Four Corners] No imageUrl, waiting for user to select image');
                    }
                    // DON'T load OpenCV here - wait until an image is loaded
                });

                // Watch for image load - start loading OpenCV
                watch(imageLoaded, (loaded) => {
                    console.log('[Four Corners] imageLoaded watcher triggered, loaded:', loaded);
                    if (loaded) {
                        console.log('[Four Corners] Image loaded! Starting OpenCV load...');
                        // Start loading OpenCV now so it's ready when user clicks Preview/Accept
                        loadOpenCV();
                    }
                });

                watch(zoom, updateStageSize);

                return {
                    containerRef,
                    imageElement,
                    imageLoaded,
                    opencvLoading,
                    opencvLoaded,
                    opencvError,
                    isDetecting,
                    isProcessing,
                    isReady,
                    canProcess,
                    hasChanges,
                    showRejectModal,
                    showPreviewModal,
                    previewDataUrl,
                    stageConfig,
                    imageConfig,
                    corners,
                    scaledCorners,
                    rotation,
                    autoDetection,
                    rotationSuggestion,
                    zoom,
                    handleCornerDrag,
                    handleZoom,
                    handleRotate,
                    generatePreview,
                    handleComplete,
                    handleReject,
                    reset,
                    fitToContainer,
                    detectCorners,
                    applyRotationSuggestion,
                    dismissRotationSuggestion,
                };
            },
            template: `
                <div class="four-corners-annotator">
                    <!-- Error state -->
                    <div v-if="opencvError" class="four-corners-loading" style="background: #fff3cd;">
                        <div style="text-align: center; max-width: 500px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">&#9888;</div>
                            <h3 style="color: #856404; margin: 0 0 8px 0;">OpenCV.js Load Error</h3>
                            <p style="color: #856404; margin: 0 0 16px 0;">@{{ opencvError }}</p>
                            <p style="color: #666; font-size: 13px; margin: 0;">
                                Check your browser's Developer Console (F12) for more details.
                                <br>OpenCV.js is ~8MB and may take time to download.
                            </p>
                        </div>
                    </div>

                    <!-- No image selected state -->
                    <div v-else-if="!$props.imageUrl && !imageLoaded" class="four-corners-loading" style="background: #f8f9fa;">
                        <div style="text-align: center; max-width: 400px;">
                            <div style="font-size: 64px; margin-bottom: 16px; opacity: 0.5;">&#128444;</div>
                            <h3 style="color: #333; margin: 0 0 8px 0;">No Image Selected</h3>
                            <p style="color: #666; margin: 0 0 16px 0;">
                                Use the file picker above to select a local image,<br>
                                or add <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">?image=URL</code> to the page URL.
                            </p>
                            <p v-if="opencvLoading" style="color: #999; font-size: 12px; margin: 16px 0 0 0;">
                                <span class="four-corners-spinner" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 6px;"></span>
                                Loading OpenCV.js in background...
                            </p>
                            <p v-else-if="opencvLoaded" style="color: #28a745; font-size: 12px; margin: 16px 0 0 0;">
                                &#10003; OpenCV.js ready
                            </p>
                        </div>
                    </div>

                    <!-- Loading state (only for image loading) -->
                    <div v-else-if="!imageLoaded" class="four-corners-loading">
                        <div class="four-corners-spinner"></div>
                        <div style="text-align: center;">
                            <span class="four-corners-loading-text">Loading image...</span>
                        </div>
                    </div>

                    <template v-else>
                        <!-- Canvas area -->
                        <div ref="containerRef" class="four-corners-canvas-container">
                            <v-stage :config="stageConfig">
                                <v-layer>
                                    <!-- Image -->
                                    <v-image :config="imageConfig" />

                                    <!-- Quadrilateral -->
                                    <v-line :config="{
                                        points: [
                                            scaledCorners.topLeft.x, scaledCorners.topLeft.y,
                                            scaledCorners.topRight.x, scaledCorners.topRight.y,
                                            scaledCorners.bottomRight.x, scaledCorners.bottomRight.y,
                                            scaledCorners.bottomLeft.x, scaledCorners.bottomLeft.y,
                                        ],
                                        fill: 'rgba(0, 123, 255, 0.2)',
                                        stroke: '#007bff',
                                        strokeWidth: 2,
                                        closed: true,
                                    }" />

                                    <!-- Corner handles -->
                                    <v-circle
                                        v-for="(key, index) in ['topLeft', 'topRight', 'bottomRight', 'bottomLeft']"
                                        :key="key"
                                        :config="{
                                            x: scaledCorners[key].x,
                                            y: scaledCorners[key].y,
                                            radius: 12,
                                            fill: 'white',
                                            stroke: '#007bff',
                                            strokeWidth: 2,
                                            draggable: true,
                                            shadowColor: 'black',
                                            shadowBlur: 4,
                                            shadowOpacity: 0.3,
                                        }"
                                        @dragmove="(e) => handleCornerDrag(key, e.target.x(), e.target.y())"
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
                                    @{{ Math.round(autoDetection.confidence * 100) }}%
                                </span>
                            </div>

                            <!-- Rotation suggestion banner -->
                            <div v-if="rotationSuggestion !== null" style="position: absolute; top: 10px; right: 10px; background: rgba(255, 193, 7, 0.95); padding: 8px 12px; border-radius: 4px; display: flex; align-items: center; gap: 8px; font-size: 13px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                <span>Suggested rotation: @{{ rotationSuggestion }}°</span>
                                <button @click="applyRotationSuggestion" style="padding: 4px 8px; font-size: 12px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">Apply</button>
                                <button @click="dismissRotationSuggestion" style="padding: 4px 8px; font-size: 12px; background: transparent; border: 1px solid #666; border-radius: 3px; cursor: pointer;">Dismiss</button>
                            </div>

                            <!-- Detecting overlay -->
                            <div v-if="isDetecting" class="four-corners-detecting-overlay">
                                <div class="four-corners-spinner"></div>
                                <span>Detecting corners...</span>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="four-corners-controls">
                            <!-- Zoom -->
                            <div class="four-corners-control-group">
                                <span class="four-corners-control-label">Zoom</span>
                                <button class="four-corners-btn four-corners-btn-outline" @click="handleZoom(-0.25)" :disabled="zoom <= 0.25">-</button>
                                <span style="min-width: 50px; text-align: center;">@{{ Math.round(zoom * 100) }}%</span>
                                <button class="four-corners-btn four-corners-btn-outline" @click="handleZoom(0.25)" :disabled="zoom >= 4">+</button>
                                <button class="four-corners-btn four-corners-btn-outline" @click="fitToContainer">Fit</button>
                            </div>

                            <!-- Rotation -->
                            <div class="four-corners-control-group">
                                <span class="four-corners-control-label">Rotation</span>
                                <button class="four-corners-btn four-corners-btn-outline" @click="handleRotate(-90)">&#8634;</button>
                                <span style="min-width: 45px; text-align: center;">@{{ rotation }}°</span>
                                <button class="four-corners-btn four-corners-btn-outline" @click="handleRotate(90)">&#8635;</button>
                            </div>

                            <div style="flex: 1;"></div>

                            <!-- OpenCV Status -->
                            <div v-if="opencvLoading" style="color: #6c757d; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <span class="four-corners-spinner" style="width: 14px; height: 14px;"></span>
                                Loading OpenCV...
                            </div>
                            <div v-else-if="opencvLoaded" style="color: #28a745; font-size: 13px;">
                                &#10003; OpenCV ready
                            </div>
                            <div v-else-if="opencvError" style="color: #dc3545; font-size: 13px;">
                                &#10007; OpenCV failed
                            </div>

                            <!-- Actions -->
                            <button class="four-corners-btn four-corners-btn-outline" @click="detectCorners" :disabled="isDetecting || isProcessing">
                                @{{ isDetecting ? 'Detecting...' : 'Detect Corners' }}
                            </button>
                            <button v-if="hasChanges" class="four-corners-btn four-corners-btn-outline" @click="reset" :disabled="isProcessing">Reset</button>
                            <button class="four-corners-btn" style="background: transparent; border-color: #dc3545; color: #dc3545;" @click="showRejectModal = true" :disabled="isProcessing">Reject</button>
                            <button class="four-corners-btn four-corners-btn-outline" @click="generatePreview" :disabled="!canProcess">Preview</button>
                            <button class="four-corners-btn four-corners-btn-success" @click="handleComplete" :disabled="!canProcess">
                                @{{ isProcessing ? 'Processing...' : 'Accept & Process' }}
                            </button>
                        </div>
                    </template>

                    <!-- Reject Modal -->
                    <Teleport to="body">
                        <div v-if="showRejectModal" class="four-corners-modal-overlay" @click.self="showRejectModal = false">
                            <div class="four-corners-modal">
                                <div class="four-corners-modal-header">
                                    <h3 class="four-corners-modal-title">Reject Image</h3>
                                    <button class="four-corners-modal-close" @click="showRejectModal = false">&times;</button>
                                </div>
                                <div class="four-corners-modal-body">
                                    <div class="four-corners-form-group">
                                        <label class="four-corners-form-label">Reason</label>
                                        <select class="four-corners-form-control" id="reject-reason">
                                            <option v-for="reason in $props.rejectionReasons" :key="reason.id" :value="reason.id">
                                                @{{ reason.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="four-corners-form-group">
                                        <label class="four-corners-form-label">Notes (optional)</label>
                                        <textarea class="four-corners-form-control" rows="3" id="reject-notes" placeholder="Additional context..."></textarea>
                                    </div>
                                </div>
                                <div class="four-corners-modal-footer">
                                    <button class="four-corners-btn four-corners-btn-outline" @click="showRejectModal = false">Cancel</button>
                                    <button class="four-corners-btn four-corners-btn-danger" @click="handleReject(document.getElementById('reject-reason').value, document.getElementById('reject-notes').value)">
                                        Confirm Rejection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Teleport>

                    <!-- Preview Modal -->
                    <Teleport to="body">
                        <div v-if="showPreviewModal" class="four-corners-modal-overlay" @click.self="showPreviewModal = false">
                            <div class="four-corners-modal" style="max-width: 90vw;">
                                <div class="four-corners-modal-header">
                                    <h3 class="four-corners-modal-title">Preview</h3>
                                    <button class="four-corners-modal-close" @click="showPreviewModal = false">&times;</button>
                                </div>
                                <div class="four-corners-modal-body" style="background: #f8f9fa; text-align: center;">
                                    <img :src="previewDataUrl" style="max-width: 100%; max-height: 60vh; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" />
                                </div>
                                <div class="four-corners-modal-footer">
                                    <button class="four-corners-btn four-corners-btn-outline" @click="showPreviewModal = false">Close</button>
                                </div>
                            </div>
                        </div>
                    </Teleport>
                </div>
            `,
        };

        // Create Vue app
        const app = createApp({
            components: { DocumentAnnotator },
            setup() {
                const imageUrl = ref(CONFIG.imageUrl);
                const imageKey = ref(0);
                const documentType = ref(CONFIG.documentTypes[0] || {
                    id: 1,
                    code: 'default',
                    name: 'Default',
                    displayWidth: 850,
                    displayHeight: 536,
                    archiveWidth: 1700,
                    archiveHeight: 1072,
                });

                const handleComplete = (payload) => {
                    logToConsole('complete', {
                        annotationId: payload.annotationId,
                        finalCorners: payload.finalCorners,
                        finalRotation: payload.finalRotation,
                        timeSpentSeconds: payload.timeSpentSeconds.toFixed(2),
                        displayImageSize: payload.displayImageBase64.length,
                        archiveImageSize: payload.archiveImageBase64.length,
                        autoDetection: payload.autoDetection,
                    });

                    // Save to localStorage
                    try {
                        const timestamp = Date.now();
                        const key = `four-corners-${timestamp}`;
                        const savedData = {
                            timestamp,
                            displayImage: payload.displayImageBase64,
                            archiveImage: payload.archiveImageBase64,
                            finalCorners: payload.finalCorners,
                            finalRotation: payload.finalRotation,
                            timeSpentSeconds: payload.timeSpentSeconds,
                            autoDetection: payload.autoDetection,
                        };
                        localStorage.setItem(key, JSON.stringify(savedData));

                        // Update saved images index
                        const indexKey = 'four-corners-index';
                        const index = JSON.parse(localStorage.getItem(indexKey) || '[]');
                        index.push({ key, timestamp, date: new Date(timestamp).toISOString() });
                        localStorage.setItem(indexKey, JSON.stringify(index));

                        console.log('[Four Corners] Saved to localStorage:', key);
                        alert(`Image saved to localStorage!\nKey: ${key}\nDisplay size: ${(payload.displayImageBase64.length / 1024).toFixed(1)} KB\nArchive size: ${(payload.archiveImageBase64.length / 1024).toFixed(1)} KB`);
                    } catch (err) {
                        console.error('[Four Corners] Failed to save to localStorage:', err);
                        alert('Failed to save to localStorage: ' + err.message);
                    }
                };

                const handleReject = (payload) => {
                    logToConsole('reject', payload);
                };

                return {
                    imageUrl,
                    imageKey,
                    documentType,
                    rejectionReasons: CONFIG.rejectionReasons,
                    opencvUrl: CONFIG.opencvUrl,
                    handleComplete,
                    handleReject,
                };
            },
            template: `
                <DocumentAnnotator
                    :key="imageKey"
                    :image-url="imageUrl"
                    :document-type="documentType"
                    :rejection-reasons="rejectionReasons"
                    :opencv-url="opencvUrl"
                    @complete="handleComplete"
                    @reject="handleReject"
                />
            `,
        });

        // Enable Vue devtools
        app.config.devtools = true;
        app.config.performance = true;

        try {
            console.log('[Four Corners] Registering vue-konva plugin...');
            // Find vue-konva in window - check various possible names
            const konvaGlobals = Object.keys(window).filter(k => k.toLowerCase().includes('konva'));
            console.log('[Four Corners] Konva-related globals:', konvaGlobals);

            // Try different possible export names
            const vueKonva = window.VueKonva || window['vue-konva'] || window.vueKonva;
            console.log('[Four Corners] vueKonva found:', vueKonva);

            if (vueKonva && typeof vueKonva.install === 'function') {
                app.use(vueKonva);
                console.log('[Four Corners] vue-konva registered via install function');
            } else if (vueKonva && vueKonva.default && typeof vueKonva.default.install === 'function') {
                app.use(vueKonva.default);
                console.log('[Four Corners] vue-konva registered via default.install');
            } else if (vueKonva) {
                // Try using it directly - might auto-register components
                console.log('[Four Corners] vueKonva structure:', Object.keys(vueKonva));
                // Manually register components if available
                if (vueKonva.VStage) {
                    app.component('v-stage', vueKonva.VStage);
                    app.component('v-layer', vueKonva.VLayer);
                    app.component('v-circle', vueKonva.VCircle);
                    app.component('v-image', vueKonva.VImage);
                    app.component('v-line', vueKonva.VLine);
                    app.component('v-text', vueKonva.VText);
                    app.component('v-group', vueKonva.VGroup);
                    console.log('[Four Corners] vue-konva components registered manually');
                } else {
                    console.error('[Four Corners] vueKonva has no install or components:', vueKonva);
                }
            } else {
                console.error('[Four Corners] vue-konva not found in any expected location');
            }

            console.log('[Four Corners] Mounting Vue app...');
            window.vueApp = app.mount('#app');
            console.log('[Four Corners] Vue app mounted successfully');
        } catch (error) {
            console.error('[Four Corners] Failed to mount Vue app:', error);
            document.getElementById('app').innerHTML = `
                <div style="padding: 40px; text-align: center; color: #721c24; background: #f8d7da; border-radius: 8px;">
                    <h3>Failed to initialize Four Corners Demo</h3>
                    <p>${error.message}</p>
                    <p style="font-size: 13px; color: #666;">Check the browser console (F12) for details.</p>
                </div>
            `;
        }

        // Add localStorage utility functions to window for console access
        window.fourCornersStorage = {
            // List all saved images
            list: () => {
                const index = JSON.parse(localStorage.getItem('four-corners-index') || '[]');
                console.log('[Four Corners] Saved images:', index.length);
                index.forEach((item, i) => {
                    const data = JSON.parse(localStorage.getItem(item.key) || '{}');
                    console.log(`  ${i + 1}. ${item.date} - Display: ${(data.displayImage?.length / 1024).toFixed(1)} KB, Archive: ${(data.archiveImage?.length / 1024).toFixed(1)} KB`);
                });
                return index;
            },

            // Get a specific saved image by index (1-based)
            get: (index) => {
                const items = JSON.parse(localStorage.getItem('four-corners-index') || '[]');
                if (index < 1 || index > items.length) {
                    console.error('[Four Corners] Invalid index. Use fourCornersStorage.list() to see available items.');
                    return null;
                }
                const item = items[index - 1];
                return JSON.parse(localStorage.getItem(item.key) || 'null');
            },

            // Download a saved image by index (1-based)
            download: (index, type = 'display') => {
                const data = window.fourCornersStorage.get(index);
                if (!data) return;
                const imageData = type === 'archive' ? data.archiveImage : data.displayImage;
                const link = document.createElement('a');
                link.href = imageData;
                link.download = `four-corners-${type}-${data.timestamp}.jpg`;
                link.click();
                console.log(`[Four Corners] Downloaded ${type} image`);
            },

            // Clear all saved images
            clear: () => {
                const index = JSON.parse(localStorage.getItem('four-corners-index') || '[]');
                index.forEach(item => localStorage.removeItem(item.key));
                localStorage.removeItem('four-corners-index');
                console.log(`[Four Corners] Cleared ${index.length} saved images`);
            },

            // Show help
            help: () => {
                console.log(`
[Four Corners] localStorage utilities:
  fourCornersStorage.list()           - List all saved images
  fourCornersStorage.get(n)           - Get saved image data by index (1-based)
  fourCornersStorage.download(n)      - Download display image by index
  fourCornersStorage.download(n, 'archive') - Download archive image by index
  fourCornersStorage.clear()          - Clear all saved images
                `);
            }
        };
        console.log('[Four Corners] Storage utilities available. Type fourCornersStorage.help() for usage.');

        } // end initializeApp
    </script>
</body>
</html>
