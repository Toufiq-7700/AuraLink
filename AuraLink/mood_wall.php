<?php
session_start();
require_once 'db_connect.php';

// Handle POST Request (New Aura)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $start = $_POST['start_color'];
    $end = $_POST['end_color'];
    $emoji = $_POST['emoji'];
    $tone = $_POST['tone'];
    $pulse = $_POST['pulse_intensity']; // This comes as 1, 2, 3, 4

    // Map pulse int to class
    $pulseMap = [
        '1' => 'pulse-slow',
        '2' => 'pulse-medium',
        '3' => 'pulse-fast',
        '4' => 'pulse-hyper'
    ];
    $pulseClass = isset($pulseMap[$pulse]) ? $pulseMap[$pulse] : 'pulse-medium';

    try {
        $stmt = $conn->prepare("INSERT INTO mood_wall (user_id, start_color, end_color, emoji, pulse_intensity, element_tone) VALUES (:uid, :start, :end, :emoji, :pulse, :tone)");
        $stmt->bindParam(':uid', $_SESSION['user_id']);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->bindParam(':emoji', $emoji);
        $stmt->bindParam(':pulse', $pulseClass);
        $stmt->bindParam(':tone', $tone);
        $stmt->execute();

        // Redirect to avoid resubmission
        header("Location: mood_wall.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error saving aura: " . $e->getMessage();
    }
}

// Fetch all Auras
$dbAuras = [];
try {
    $stmt = $conn->query("SELECT * FROM mood_wall ORDER BY created_at DESC");
    $dbAuras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error or just show empty
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Wall | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="style.css">

    <style>
        /* Cyber-Zen Custom Styles for Mood Wall */
        :root {
            --bg-dark: #0D0D0D;
            --glass-panel: rgba(255, 255, 255, 0.05);
            --neon-purple: #b026ff;
            --neon-cyan: #00f3ff;
            --border-white: rgba(255, 255, 255, 0.1);
        }

        body {
            background-color: var(--bg-dark);
            background-image:
                radial-gradient(circle at 10% 20%, rgba(176, 38, 255, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(0, 243, 255, 0.1) 0%, transparent 40%);
        }

        .wall-container {
            max-width: 1200px;
            margin: 100px auto;
            padding: 20px;
        }

        /* --- Aura Creator Section --- */
        .aura-creator {
            background: var(--glass-panel);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-white);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 50px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 150px;
        }

        .control-group label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        input[type="color"] {
            -webkit-appearance: none;
            border: none;
            width: 50px;
            height: 50px;
            cursor: pointer;
            background: none;
        }

        input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        input[type="color"]::-webkit-color-swatch {
            border: 2px solid var(--border-white);
            border-radius: 50%;
        }

        select,
        input[type="range"] {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-white);
            color: white;
            padding: 10px;
            border-radius: 8px;
            font-family: var(--font-main);
            outline: none;
        }

        .release-btn {
            background: linear-gradient(90deg, var(--neon-purple), var(--neon-cyan));
            border: none;
            padding: 15px 40px;
            color: #fff;
            font-weight: 700;
            border-radius: 30px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(176, 38, 255, 0.4);
            margin-top: auto;
        }

        .release-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 243, 255, 0.6);
        }

        .release-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(1);
        }

        /* --- Aura Wall Grid --- */
        .aura-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 30px;
        }

        .aura-card {
            aspect-ratio: 1 / 1;
            border-radius: 20px;
            /* Soft rectangle/Orb-like */
            position: relative;
            cursor: pointer;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .aura-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.2);
            z-index: 10;
        }

        .aura-emoji {
            font-size: 4rem;
            z-index: 2;
            filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.3));
        }

        .aura-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 15px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: white;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            text-align: center;
        }

        .aura-card:hover .aura-info {
            opacity: 1;
        }

        /* --- Animations --- */
        @keyframes breathe {
            0% {
                transform: scale(1);
                opacity: 0.9;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
                box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.3);
            }

            100% {
                transform: scale(1);
                opacity: 0.9;
            }
        }

        /* Pulse Intensity Classes */
        .pulse-slow {
            animation: breathe 6s infinite ease-in-out;
        }

        .pulse-medium {
            animation: breathe 4s infinite ease-in-out;
        }

        .pulse-fast {
            animation: breathe 2s infinite ease-in-out;
        }

        .pulse-hyper {
            animation: breathe 1s infinite ease-in-out;
        }
    </style>
</head>

<body>

    <!-- Reuse Navbar -->
    <nav class="navbar">
        <div class="logo">AuraLink</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="mood_wall.php" class="active" style="color:var(--secondary-color)">Mood Wall</a>
            <a href="index.php#vision-canvas">Vision Canvas</a>
            <a href="index.php#sync-board">Sync Board</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="nav-btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn login-btn">Login</a>
                <a href="signup.php" class="nav-btn signup-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="wall-container">
        <!-- Aura Creator -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($error)): ?>
                <div
                    style="background: rgba(255, 0, 0, 0.2); color: #ff7675; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form class="aura-creator" method="POST" action="mood_wall.php">
                <div class="control-group">
                    <label>Start Color</label>
                    <input type="color" name="start_color" value="#6c5ce7">
                </div>
                <div class="control-group">
                    <label>End Color</label>
                    <input type="color" name="end_color" value="#00cec9">
                </div>
                <div class="control-group">
                    <label>Vibe Emoji</label>
                    <select name="emoji">
                        <option value="✨">✨ Sparkle</option>
                        <option value="🌊">🌊 Wave</option>
                        <option value="🔥">🔥 Fire</option>
                        <option value="☁️">☁️ Cloud</option>
                        <option value="💜">💜 Heart</option>
                        <option value="🍃">🍃 Leaf</option>
                    </select>
                </div>
                <div class="control-group">
                    <label>Tone</label>
                    <select name="tone">
                        <option value="Bell">Bell</option>
                        <option value="Wave">Wave</option>
                        <option value="Wind">Wind</option>
                        <option value="Pulse">Pulse</option>
                    </select>
                </div>
                <div class="control-group">
                    <label>Pulse Intensity</label>
                    <input type="range" name="pulse_intensity" min="1" max="4" value="2" title="1=Slow, 4=Hyper">
                </div>
                <button type="submit" class="release-btn">Release to Wall</button>
            </form>
        <?php else: ?>
            <div class="aura-creator" style="justify-content:center; text-align:center;">
                <p style="color:var(--text-muted); font-size:1.2rem;">Login to release your aura to the wall.</p>
                <a href="login.php" class="release-btn" style="text-decoration:none; margin-left: 20px;">Login Here</a>
            </div>
        <?php endif; ?>

        <!-- Aura Wall Display -->
        <h2 style="margin-bottom: 20px; text-shadow: 0 0 10px rgba(255,255,255,0.3);">Global Frequency</h2>
        <div class="aura-grid" id="auraGrid">
            <!-- Cards injected by JS -->
        </div>
    </div>

    <script>
        // Data from PHP
        const dbAuras = <?php echo json_encode($dbAuras); ?>;

        const grid = document.getElementById('auraGrid');

        // Render Function
        function renderAuras() {
            grid.innerHTML = '';

            if (dbAuras.length === 0) {
                grid.innerHTML = '<p style="color: grey; grid-column: 1/-1; text-align:center;">The wall is silent. Be the first to speak.</p>';
                return;
            }

            dbAuras.forEach(aura => {
                const card = document.createElement('div');
                // Handle schema diffs: PHP sends 'start_color', 'end_color', 'emoji', 'pulse_intensity' (class), 'element_tone'
                card.className = `aura-card ${aura.pulse_intensity}`;
                card.style.background = `linear-gradient(135deg, ${aura.start_color}, ${aura.end_color})`;

                card.innerHTML = `
                    <div class="aura-emoji">${aura.emoji}</div>
                    <div class="aura-info">Tone: ${aura.element_tone}</div>
                `;

                grid.appendChild(card);
            });
        }

        // Initialize Wall
        renderAuras();

    </script>
</body>

</html>