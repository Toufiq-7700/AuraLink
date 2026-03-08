<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vision Canvas | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="style.css">
    <style>
        .canvas-container {
            max-width: 1000px;
            margin: 100px auto 50px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .tools-panel {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 30px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .tool-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tool-group label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
        }

        input[type="color"] {
            -webkit-appearance: none;
            border: none;
            width: 40px;
            height: 40px;
            cursor: pointer;
            background: none;
            border-radius: 50%;
        }

        input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        input[type="color"]::-webkit-color-swatch {
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        input[type="range"] {
            width: 100px;
            accent-color: var(--primary-color);
        }

        #drawingCanvas {
            background-color: #ffffff;
            border-radius: 10px;
            cursor: crosshair;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .btn-action {
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
        }

        .btn-clear {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-clear:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-save {
            background: linear-gradient(90deg, #00b894, #00cec9);
            box-shadow: 0 0 15px rgba(0, 206, 201, 0.4);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(0, 206, 201, 0.6);
        }

        .btn-ai {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            box-shadow: var(--glow-primary);
        }

        .btn-ai:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(108, 92, 231, 0.8);
        }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .page-header p {
            color: var(--text-muted);
        }

        /* AI Notification Overlay */
        #aiOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(5, 5, 16, 0.9);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }

        #aiOverlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .loader {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">AuraLink</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="mood_wall.php">Mood Wall</a>
            <a href="vision_canvas.php" class="active" style="color:var(--secondary-color)">Vision Canvas</a>
            <a href="sync_board.php">Sync Board</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user">Welcome,
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="logout.php" class="nav-btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn login-btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="canvas-container">
        <div class="page-header">
            <h1>Vision Canvas</h1>
            <p>Sketch your thoughts. Let the AI transform them into pure emotion.</p>
        </div>

        <div class="tools-panel">
            <div class="tool-group">
                <label><i class="fa-solid fa-palette"></i> Color:</label>
                <input type="color" id="brushColor" value="#000000">
            </div>
            <div class="tool-group">
                <label><i class="fa-solid fa-pen-nib"></i> Brush Size:</label>
                <input type="range" id="brushSize" min="1" max="50" value="5">
            </div>
        </div>

        <!-- HTML5 Canvas -->
        <canvas id="drawingCanvas" width="800" height="500"></canvas>

        <div class="action-buttons">
            <button class="btn-action btn-clear" id="clearBtn"><i class="fa-solid fa-trash-can"></i> Clear</button>
            <button class="btn-action btn-ai" id="aiBtn"><i class="fa-solid fa-wand-magic-sparkles"></i> AI
                Enhance</button>
            <button class="btn-action btn-save" id="saveBtn"><i class="fa-solid fa-download"></i> Save to PC</button>
        </div>
    </div>

    <!-- AI Overlay -->
    <div id="aiOverlay">
        <div class="loader"></div>
        <h2 id="aiStatusText">Calling neural network...</h2>
        <p style="color:var(--text-muted); margin-top:10px;">Analyzing sketch patterns and generating emotional overlay.
        </p>
    </div>

    <script>
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');

        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Setup initial background to white (so saving jpeg/png looks good instead of transparent black)
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // UI Elements
        const colorPicker = document.getElementById('brushColor');
        const sizePicker = document.getElementById('brushSize');
        const clearBtn = document.getElementById('clearBtn');
        const saveBtn = document.getElementById('saveBtn');
        const aiBtn = document.getElementById('aiBtn');
        const overlay = document.getElementById('aiOverlay');
        const aiStatusText = document.getElementById('aiStatusText');

        // Drawing Event Listeners
        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            [lastX, lastY] = [e.offsetX, e.offsetY];

            // Draw a dot on click
            ctx.beginPath();
            ctx.fillStyle = colorPicker.value;
            ctx.arc(lastX, lastY, sizePicker.value / 2, 0, Math.PI * 2);
            ctx.fill();
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.strokeStyle = colorPicker.value;
            ctx.lineWidth = sizePicker.value;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
            [lastX, lastY] = [e.offsetX, e.offsetY];
        });

        canvas.addEventListener('mouseup', () => isDrawing = false);
        canvas.addEventListener('mouseout', () => isDrawing = false);

        // Clear Canvas
        clearBtn.addEventListener('click', () => {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        });

        // Save to PC
        saveBtn.addEventListener('click', () => {
            const dataURL = canvas.toDataURL('image/png');
            const a = document.createElement('a');
            a.href = dataURL;
            a.download = 'AuraLink_Vision_' + Date.now() + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });

        // Mock API AI Enhance Call
        aiBtn.addEventListener('click', () => {
            overlay.classList.add('active');
            aiStatusText.innerText = "Calling AI API...";

            // Step 1: Simulate network delay for API
            setTimeout(() => {
                aiStatusText.innerText = "Interpreting soul sketch...";

                // Step 2: Simulate another delay for processing
                setTimeout(() => {
                    aiStatusText.innerText = "Applying aesthetic aura...";

                    // Modify canvas to look "enhanced"
                    enhanceCanvas();

                    setTimeout(() => {
                        overlay.classList.remove('active');
                    }, 500);

                }, 1500);
            }, 1000);
        });

        // Fake AI Filter logic
        function enhanceCanvas() {
            // We just apply some global composite operations to make it look "magical"
            const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            // Convert drawing to a silhouette
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.putImageData(imgData, 0, 0);

            // Add some "aura" gradients over the top
            ctx.globalCompositeOperation = 'multiply';
            const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
            gradient.addColorStop(0, 'rgba(157, 0, 255, 0.3)'); // primary
            gradient.addColorStop(1, 'rgba(0, 236, 255, 0.3)'); // secondary

            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Add some noise or particles
            ctx.globalCompositeOperation = 'screen';
            for (let i = 0; i < 300; i++) {
                ctx.beginPath();
                ctx.fillStyle = `rgba(255, 255, 255, ${Math.random() * 0.5})`;
                ctx.arc(Math.random() * canvas.width, Math.random() * canvas.height, Math.random() * 3, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.globalCompositeOperation = 'source-over'; // Reset
        }
    </script>
</body>

</html>