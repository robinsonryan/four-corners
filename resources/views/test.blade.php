<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenCV.js Test - Resize & Perspective Transform</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1, h2 {
            color: #333;
        }
        .section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .controls label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .controls input[type="file"] {
            margin-bottom: 16px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        button.success {
            background: #28a745;
        }
        button.success:hover {
            background: #218838;
        }
        .canvas-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .canvas-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .canvas-box h3 {
            margin-top: 0;
            color: #666;
            font-size: 14px;
        }
        canvas {
            border: 1px solid #ddd;
            max-width: 100%;
            background: #fff;
        }
        #status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        #status.loading {
            background: #fff3cd;
            color: #856404;
        }
        #status.ready {
            background: #d4edda;
            color: #155724;
        }
        #status.error {
            background: #f8d7da;
            color: #721c24;
        }
        #log {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .corner-inputs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        .corner-input {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }
        .corner-input input {
            width: 60px;
            padding: 4px;
        }
        .corner-input label {
            min-width: 80px;
            margin: 0;
        }
        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>OpenCV.js Test Page</h1>
    <p>Test resize and perspective transform operations to verify OpenCV.js works correctly.</p>

    <div id="status" class="loading">Loading OpenCV.js...</div>

    <!-- Image Selection -->
    <div class="section">
        <h2>1. Select Image</h2>
        <label for="fileInput">Choose an image file:</label>
        <input type="file" id="fileInput" accept="image/*" disabled>
        <p id="inputInfo" class="help-text">No image loaded</p>
    </div>

    <!-- Test 1: Simple Resize -->
    <div class="section">
        <h2>2. Test: Simple Resize</h2>
        <p class="help-text">Basic cv.resize() operation - if this works, OpenCV is functioning.</p>
        <div>
            <label>Resize to:</label>
            <input type="number" id="resizeWidth" value="300" style="width: 80px;"> x
            <input type="number" id="resizeHeight" value="200" style="width: 80px;"> pixels
        </div>
        <div style="margin-top: 10px;">
            <button id="resizeBtn" disabled>Test Resize</button>
        </div>
        <div class="canvas-container">
            <div class="canvas-box">
                <h3>Input</h3>
                <canvas id="canvasInput" width="300" height="150"></canvas>
            </div>
            <div class="canvas-box">
                <h3>Resize Output</h3>
                <canvas id="canvasResize" width="300" height="150"></canvas>
                <p id="resizeInfo" class="help-text">No output yet</p>
            </div>
        </div>
    </div>

    <!-- Test 2: Perspective Transform -->
    <div class="section">
        <h2>3. Test: Perspective Transform</h2>
        <p class="help-text">cv.getPerspectiveTransform() + cv.warpPerspective() - the operation used for document cropping.</p>

        <div>
            <label>Output size:</label>
            <input type="number" id="perspWidth" value="400" style="width: 80px;"> x
            <input type="number" id="perspHeight" value="300" style="width: 80px;"> pixels
        </div>

        <div class="corner-inputs">
            <div class="corner-input">
                <label>Top-Left:</label>
                <input type="number" id="tlX" value="50"> ,
                <input type="number" id="tlY" value="50">
            </div>
            <div class="corner-input">
                <label>Top-Right:</label>
                <input type="number" id="trX" value="350"> ,
                <input type="number" id="trY" value="60">
            </div>
            <div class="corner-input">
                <label>Bottom-Left:</label>
                <input type="number" id="blX" value="40"> ,
                <input type="number" id="blY" value="280">
            </div>
            <div class="corner-input">
                <label>Bottom-Right:</label>
                <input type="number" id="brX" value="360"> ,
                <input type="number" id="brY" value="290">
            </div>
        </div>
        <p class="help-text">Corners define the quadrilateral to extract from the source image. Click "Set Default Corners" after loading an image to use sensible defaults.</p>

        <div style="margin-top: 10px;">
            <button id="defaultCornersBtn" disabled>Set Default Corners</button>
            <button id="perspectiveBtn" class="success" disabled>Test Perspective Transform</button>
        </div>

        <div class="canvas-container">
            <div class="canvas-box">
                <h3>Perspective Output</h3>
                <canvas id="canvasPerspective" width="400" height="300"></canvas>
                <p id="perspInfo" class="help-text">No output yet</p>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="section">
        <button id="clearBtn" disabled>Clear All</button>
    </div>

    <!-- Log -->
    <div class="section">
        <h2>Debug Log</h2>
        <div id="log"></div>
    </div>

    <script>
        // Simple logging
        const logEl = document.getElementById('log');
        function log(msg) {
            const time = new Date().toLocaleTimeString();
            logEl.textContent += `[${time}] ${msg}\n`;
            logEl.scrollTop = logEl.scrollHeight;
            console.log(msg);
        }

        // Status display
        const statusEl = document.getElementById('status');
        function setStatus(msg, type) {
            statusEl.textContent = msg;
            statusEl.className = type;
        }

        // Elements
        const fileInput = document.getElementById('fileInput');
        const resizeBtn = document.getElementById('resizeBtn');
        const perspectiveBtn = document.getElementById('perspectiveBtn');
        const defaultCornersBtn = document.getElementById('defaultCornersBtn');
        const clearBtn = document.getElementById('clearBtn');

        const canvasInput = document.getElementById('canvasInput');
        const canvasResize = document.getElementById('canvasResize');
        const canvasPerspective = document.getElementById('canvasPerspective');

        let imageLoaded = false;
        let loadedImage = null;

        // Load OpenCV.js
        log('Starting OpenCV.js load...');

        function onOpenCvReady() {
            log('cv object available, checking if runtime is ready...');

            if (cv.getBuildInformation) {
                log('OpenCV.js is fully ready!');
                onOpenCvFullyReady();
            } else {
                log('Waiting for runtime initialization...');
                cv.onRuntimeInitialized = () => {
                    log('Runtime initialized callback fired');
                    onOpenCvFullyReady();
                };
            }
        }

        function onOpenCvFullyReady() {
            setStatus('OpenCV.js ready! Select an image to test.', 'ready');
            fileInput.disabled = false;
            clearBtn.disabled = false;

            log('cv.Mat exists: ' + (typeof cv.Mat === 'function'));
            log('cv.imread exists: ' + (typeof cv.imread === 'function'));
            log('cv.resize exists: ' + (typeof cv.resize === 'function'));
            log('cv.getPerspectiveTransform exists: ' + (typeof cv.getPerspectiveTransform === 'function'));
            log('cv.warpPerspective exists: ' + (typeof cv.warpPerspective === 'function'));
        }

        // Handle image file selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            log('Loading image: ' + file.name);

            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    loadedImage = img;

                    // Draw image to input canvas
                    canvasInput.width = img.width;
                    canvasInput.height = img.height;
                    const ctx = canvasInput.getContext('2d');
                    ctx.drawImage(img, 0, 0);

                    document.getElementById('inputInfo').textContent = `Loaded: ${img.width} x ${img.height} pixels`;
                    imageLoaded = true;
                    resizeBtn.disabled = false;
                    perspectiveBtn.disabled = false;
                    defaultCornersBtn.disabled = false;

                    log('Image loaded: ' + img.width + 'x' + img.height);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        // Set default corners based on image size
        defaultCornersBtn.addEventListener('click', function() {
            if (!loadedImage) return;

            const w = loadedImage.width;
            const h = loadedImage.height;
            const margin = Math.min(w, h) * 0.1;

            document.getElementById('tlX').value = Math.round(margin);
            document.getElementById('tlY').value = Math.round(margin);
            document.getElementById('trX').value = Math.round(w - margin);
            document.getElementById('trY').value = Math.round(margin);
            document.getElementById('blX').value = Math.round(margin);
            document.getElementById('blY').value = Math.round(h - margin);
            document.getElementById('brX').value = Math.round(w - margin);
            document.getElementById('brY').value = Math.round(h - margin);

            log('Set default corners with 10% margin');
        });

        // Handle resize button click
        resizeBtn.addEventListener('click', function() {
            if (!imageLoaded) {
                log('Error: No image loaded');
                return;
            }

            log('=== Starting RESIZE test ===');

            try {
                const width = parseInt(document.getElementById('resizeWidth').value) || 300;
                const height = parseInt(document.getElementById('resizeHeight').value) || 200;

                log('Reading image from input canvas...');
                let src = cv.imread(canvasInput);
                log('Source Mat: ' + src.rows + 'x' + src.cols + ', type: ' + src.type());

                let dst = new cv.Mat();
                let dsize = new cv.Size(width, height);

                log('Calling cv.resize to ' + width + 'x' + height + '...');
                cv.resize(src, dst, dsize, 0, 0, cv.INTER_AREA);
                log('Resize complete! Output: ' + dst.rows + 'x' + dst.cols);

                // IMPORTANT: Set canvas size BEFORE cv.imshow
                canvasResize.width = width;
                canvasResize.height = height;

                log('Writing to output canvas...');
                cv.imshow(canvasResize, dst);

                document.getElementById('resizeInfo').textContent = `Output: ${width} x ${height} pixels`;

                src.delete();
                dst.delete();
                log('=== RESIZE test SUCCESS ===');

            } catch (err) {
                log('ERROR: ' + err.message);
                console.error(err);
                setStatus('Error: ' + err.message, 'error');
            }
        });

        // Handle perspective transform button click
        perspectiveBtn.addEventListener('click', function() {
            if (!imageLoaded) {
                log('Error: No image loaded');
                return;
            }

            log('=== Starting PERSPECTIVE TRANSFORM test ===');

            try {
                // Get corner coordinates
                const tlX = parseFloat(document.getElementById('tlX').value);
                const tlY = parseFloat(document.getElementById('tlY').value);
                const trX = parseFloat(document.getElementById('trX').value);
                const trY = parseFloat(document.getElementById('trY').value);
                const blX = parseFloat(document.getElementById('blX').value);
                const blY = parseFloat(document.getElementById('blY').value);
                const brX = parseFloat(document.getElementById('brX').value);
                const brY = parseFloat(document.getElementById('brY').value);

                const outWidth = parseInt(document.getElementById('perspWidth').value) || 400;
                const outHeight = parseInt(document.getElementById('perspHeight').value) || 300;

                log('Source corners: TL(' + tlX + ',' + tlY + ') TR(' + trX + ',' + trY + ') BL(' + blX + ',' + blY + ') BR(' + brX + ',' + brY + ')');
                log('Output size: ' + outWidth + 'x' + outHeight);

                // Read source image
                log('Reading source image...');
                let src = cv.imread(canvasInput);
                log('Source Mat: ' + src.rows + 'x' + src.cols);

                // Create source points array (the quadrilateral corners)
                log('Creating source points...');
                let srcPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
                    tlX, tlY,
                    trX, trY,
                    brX, brY,
                    blX, blY
                ]);

                // Create destination points (rectangle corners)
                log('Creating destination points...');
                let dstPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
                    0, 0,
                    outWidth, 0,
                    outWidth, outHeight,
                    0, outHeight
                ]);

                // Get perspective transform matrix
                log('Computing perspective transform matrix...');
                let M = cv.getPerspectiveTransform(srcPoints, dstPoints);
                log('Transform matrix computed');

                // Apply perspective warp
                log('Applying warpPerspective...');
                let dst = new cv.Mat();
                let dsize = new cv.Size(outWidth, outHeight);
                cv.warpPerspective(src, dst, M, dsize, cv.INTER_LINEAR, cv.BORDER_CONSTANT, new cv.Scalar());
                log('warpPerspective complete! Output: ' + dst.rows + 'x' + dst.cols);

                // CRITICAL: Set output canvas size BEFORE cv.imshow
                // (Setting canvas dimensions AFTER cv.imshow would clear the canvas!)
                canvasPerspective.width = outWidth;
                canvasPerspective.height = outHeight;

                // Display result
                log('Writing to output canvas...');
                cv.imshow(canvasPerspective, dst);

                document.getElementById('perspInfo').textContent = `Output: ${outWidth} x ${outHeight} pixels`;

                // Clean up all Mat objects
                log('Cleaning up...');
                src.delete();
                dst.delete();
                M.delete();
                srcPoints.delete();
                dstPoints.delete();

                log('=== PERSPECTIVE TRANSFORM test SUCCESS ===');

            } catch (err) {
                log('ERROR: ' + err.message);
                console.error(err);
                setStatus('Error: ' + err.message, 'error');
            }
        });

        // Handle clear button
        clearBtn.addEventListener('click', function() {
            [canvasInput, canvasResize, canvasPerspective].forEach(canvas => {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });
            canvasInput.width = 300;
            canvasInput.height = 150;
            canvasResize.width = 300;
            canvasResize.height = 150;
            canvasPerspective.width = 400;
            canvasPerspective.height = 300;

            document.getElementById('inputInfo').textContent = 'No image loaded';
            document.getElementById('resizeInfo').textContent = 'No output yet';
            document.getElementById('perspInfo').textContent = 'No output yet';

            imageLoaded = false;
            loadedImage = null;
            resizeBtn.disabled = true;
            perspectiveBtn.disabled = true;
            defaultCornersBtn.disabled = true;
            fileInput.value = '';

            log('Cleared all canvases');
        });

        // Load OpenCV.js script
        const script = document.createElement('script');
        script.src = '/four-corners/opencv.js';
        script.async = true;

        script.onload = function() {
            log('OpenCV.js script loaded, checking cv object...');

            if (typeof cv !== 'undefined') {
                onOpenCvReady();
            } else {
                log('cv not defined yet, setting up Module callback...');
                window.Module = {
                    onRuntimeInitialized: function() {
                        log('Module.onRuntimeInitialized called');
                        onOpenCvReady();
                    }
                };
            }
        };

        script.onerror = function() {
            log('ERROR: Failed to load OpenCV.js');
            setStatus('Failed to load OpenCV.js', 'error');
        };

        document.body.appendChild(script);
    </script>
</body>
</html>
