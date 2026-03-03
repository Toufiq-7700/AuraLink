<<<<<<< HEAD
<?php
session_start();
require_once 'db_connect.php';

// Enforce Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle AJAX Request for Saving Match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_match') {
    header('Content-Type: application/json');

    $partnerName = $_POST['partner_name'] ?? 'Unknown Signal';

    try {
        $stmt = $conn->prepare("INSERT INTO sync_games (user_id, result_status, partner_name) VALUES (:uid, 'MATCH', :partner)");
        $stmt->bindParam(':uid', $_SESSION['user_id']);
        $stmt->bindParam(':partner', $partnerName);
        $stmt->execute();

        // Get new total
        $countStmt = $conn->query("SELECT COUNT(*) as total FROM sync_games WHERE result_status = 'MATCH'");
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        echo json_encode(['success' => true, 'new_total' => $total]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 1. Get Initial Global Count
$globalSyncs = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM sync_games WHERE result_status = 'MATCH'");
    $globalSyncs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
}

// 2. Fetch Random Partner (Simulated Real User)
$partnerName = "Mysterious Signal";
try {
    // Get random user that is NOT me
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id != :uid ORDER BY RAND() LIMIT 1");
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->execute();
    $randomUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($randomUser) {
        $partnerName = $randomUser['username'];
    }
} catch (Exception $e) {
}

// 3. Fetch Recent Matches for Current User
$recentMatches = [];
try {
    $stmt = $conn->prepare("SELECT partner_name, created_at FROM sync_games WHERE user_id = :uid AND result_status = 'MATCH' ORDER BY created_at DESC LIMIT 5");
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->execute();
    $recentMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Board | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --neon-red: #ff7675;
            --neon-green: #55efc4;
            --neon-blue: #74b9ff;
            --neon-gold: #ffeaa7;
            --bg-dark: #0D0D0D;
        }

        body {
            background-color: var(--bg-dark);
            overflow: hidden;
            /* Fix for game view */
        }

        /* Screen Layout */
        .sync-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 80px;
            /* Space for Navbar */
            position: relative;
        }

        /* Header Info */
        .game-header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), transparent);
            position: relative;
            z-index: 10;
        }

        .page-title {
            font-size: 1.5rem;
            color: var(--secondary-color);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .sub-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            letter-spacing: 1px;
            font-style: italic;
        }

        .global-counter {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--neon-gold);
            text-shadow: 0 0 10px rgba(255, 234, 167, 0.5);
            margin-top: 10px;
        }


        /* Play Area */
        .play-area {
            flex: 1;
            display: flex;
            position: relative;
            align-items: center;
            justify-content: center;
        }

        .divider {
            position: absolute;
            left: 50%;
            top: 10%;
            bottom: 20%;
            width: 2px;
            background: linear-gradient(to bottom, transparent, var(--secondary-color), transparent);
            box-shadow: 0 0 15px var(--secondary-color);
            transform: translateX(-50%);
            animation: pulse-divider 2s infinite ease-in-out;
        }

        @keyframes pulse-divider {

            0%,
            100% {
                opacity: 0.5;
                height: 70%;
            }

            50% {
                opacity: 1;
                height: 80%;
            }
        }

        .player-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 40px;
            height: 100%;
        }

        .side-label {
            font-size: 1.5rem;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        /* Cards Container */
        .cards-row {
            display: flex;
            gap: 30px;
            perspective: 1000px;
            /* For 3D Flip */
        }

        /* Card Styles */
        .card-container {
            width: 120px;
            height: 180px;
            position: relative;
            cursor: pointer;
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .card-container.flipped {
            transform: rotateY(180deg);
        }

        /* Locked-in state (Before flip) */
        .card-container.locked {
            transform: translateY(-10px);
        }

        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        /* Front (Face Down) */
        .card-front {
            background: rgba(13, 13, 13, 0.9);
            border-color: rgba(255, 255, 255, 0.5);
            transform: rotateY(0deg);
            /* Explicitly set */
        }

        .card-front::after {
            content: '?';
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.1);
            font-weight: 700;
        }

        /* Specific Neon Borders for Face Down */
        .card-red .card-front {
            border-color: var(--neon-red);
            box-shadow: 0 0 10px rgba(255, 118, 117, 0.2);
        }

        .card-green .card-front {
            border-color: var(--neon-green);
            box-shadow: 0 0 10px rgba(85, 239, 196, 0.2);
        }

        .card-blue .card-front {
            border-color: var(--neon-blue);
            box-shadow: 0 0 10px rgba(116, 185, 255, 0.2);
        }

        /* Highlight Selection */
        .card-container.selected .card-front {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.4);
            border-width: 3px;
        }

        /* Back (Revealed Color) */
        .card-back {
            transform: rotateY(180deg);
            font-size: 3rem;
            background: #fff;
            /* Fallback */
            color: #fff;
        }

        .card-red .card-back {
            background: var(--neon-red);
            box-shadow: 0 0 40px var(--neon-red);
        }

        .card-green .card-back {
            background: var(--neon-green);
            box-shadow: 0 0 40px var(--neon-green);
        }

        .card-blue .card-back {
            background: var(--neon-blue);
            box-shadow: 0 0 40px var(--neon-blue);
        }


        /* Ghost Cards (Partner Side) */
        .ghost-card {
            width: 120px;
            height: 180px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .ghost-card.active {
            border-style: solid;
            border-color: var(--secondary-color);
            background: rgba(0, 206, 201, 0.05);
            box-shadow: 0 0 15px rgba(0, 206, 201, 0.2);
            animation: pulse-ghost 1s infinite alternate;
        }

        /* Partner Card (After Choices Made) */
        .partner-real-card {
            display: none;
            /* Hidden until needed */
        }

        @keyframes pulse-ghost {
            from {
                transform: scale(1);
                opacity: 0.5;
            }

            to {
                transform: scale(1.02);
                opacity: 1;
            }
        }

        /* Feedback Zone */
        .feedback-zone {
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .status-msg {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .status-msg.show {
            opacity: 1;
            transform: translateY(0);
        }

        .success-text {
            color: var(--neon-gold);
            text-shadow: 0 0 15px var(--neon-gold);
        }

        .fail-text {
            color: var(--text-muted);
        }

        .btn-reset {
            padding: 10px 40px;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s;
            opacity: 0;
            pointer-events: none;
        }

        .btn-reset.visible {
            opacity: 1;
            pointer-events: all;
        }

        .btn-reset:hover {
            background: var(--secondary-color);
            color: #000;
            box-shadow: 0 0 20px var(--secondary-color);
        }

        /* Recent List */
        .recent-matches {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 250px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            z-index: 100;
        }

        .recent-title {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .match-item {
            font-size: 0.8rem;
            color: #ccc;
            margin-bottom: 5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 5px;
        }

        .match-item:last-child {
            border: none;
        }

        /* Shake Animation */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .cards-row.shake {
            animation: shake 0.5s;
        }

        /* Mobile */
        @media(max-width: 768px) {
            .play-area {
                flex-direction: column;
            }

            .divider {
                width: 80%;
                height: 1px;
                top: 50%;
                left: 10%;
                bottom: auto;
            }

            .recent-matches {
                display: none;
            }

            .card-container,
            .ghost-card {
                width: 80px;
                height: 120px;
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
            <a href="sync_board.php" class="active" style="color:var(--secondary-color)">Sync Board</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="nav-btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn login-btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="sync-container">
        <div class="game-header">
            <div class="page-title">Sync Board</div>
            <div class="sub-text">Two hearts, one choice. Can you find the frequency?</div>
            <div class="global-counter" id="globalCounter">Total Global Syncs:
                <?php echo number_format($globalSyncs); ?></div>
        </div>

        <div class="play-area">
            <!-- Player 1: Me -->
            <div class="player-side">
                <div class="side-label">My Choice</div>
                <div class="cards-row" id="myHand">
                    <!-- Red Card -->
                    <div class="card-container card-red" onclick="pickCard('red', this)">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back"><i class="fa-solid fa-fire"></i></div>
                    </div>
                    <!-- Green Card -->
                    <div class="card-container card-green" onclick="pickCard('green', this)">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back"><i class="fa-solid fa-leaf"></i></div>
                    </div>
                    <!-- Blue Card -->
                    <div class="card-container card-blue" onclick="pickCard('blue', this)">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back"><i class="fa-solid fa-water"></i></div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Player 2: Partner -->
            <div class="player-side">
                <div class="side-label" id="partnerNameDisplay">Partner's Pulse</div>
                <div class="cards-row" id="partnerHand">
                    <!-- Ghosts -->
                    <div class="ghost-card" id="ghost-1"><i class="fa-regular fa-square"></i></div>
                    <div class="ghost-card" id="ghost-2"><i class="fa-regular fa-square"></i></div>
                    <div class="ghost-card" id="ghost-3"><i class="fa-regular fa-square"></i></div>

                    <!-- Hidden Real Card (Revealed later) -->
                    <div class="card-container partner-real-card" id="partnerRealCard">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back" id="partnerIcon"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="feedback-zone">
            <div class="status-msg" id="statusText"></div>
            <button class="btn-reset" id="resetBtn" onclick="resetGame()">Try Again</button>
        </div>

        <!-- Recent Matches List -->
        <div class="recent-matches">
            <div class="recent-title">Recent Connections</div>
            <?php if (count($recentMatches) > 0): ?>
                <?php foreach ($recentMatches as $match): ?>
                    <div class="match-item">
                        Synced with <strong style="color:#fff"><?php echo htmlspecialchars($match['partner_name']); ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="match-item">No connections yet...</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let mySelection = null;
        let isLocked = false;
        const partnerName = "<?php echo htmlspecialchars($partnerName); ?>";

        function pickCard(color, element) {
            if (isLocked) return;
            isLocked = true;
            mySelection = color;

            // 1. Visual Lock-in (Highlight border, stay face down)
            const allCards = document.querySelectorAll('#myHand .card-container');
            allCards.forEach(c => c.style.opacity = '0.3'); // Dim others
            element.style.opacity = '1';
            element.classList.add('selected'); // Glow effect
            element.classList.add('locked'); // Slight lift

            // 2. Start Partner Sequence
            startPartnerSequence();
        }

        function startPartnerSequence() {
            // Update Text
            const statusBox = document.getElementById('statusText');
            statusBox.innerHTML = `<span style="color:#fff">Waiting for ${partnerName}...</span>`;
            statusBox.className = 'status-msg show';
            document.getElementById('partnerNameDisplay').innerText = partnerName;

            // Animate Ghosts
            const ghosts = document.querySelectorAll('.ghost-card');
            ghosts.forEach(g => g.classList.add('active'));

            // 3. Simulated Delay (2 seconds)
            setTimeout(() => {
                revealOutcome();
            }, 2000);
        }

        function revealOutcome() {
            // Determine Partner Choice (40% Match Chance)
            const colors = ['red', 'green', 'blue'];
            let partnerColor = '';

            const isMatch = Math.random() < 0.4;
            if (isMatch) {
                partnerColor = mySelection;
            } else {
                const others = colors.filter(c => c !== mySelection);
                partnerColor = others[Math.floor(Math.random() * others.length)];
            }

            // Hide Ghosts
            document.querySelectorAll('.ghost-card').forEach(g => g.style.display = 'none');

            // Setup Partner Real Card
            const partnerCard = document.getElementById('partnerRealCard');
            partnerCard.className = `card-container partner-real-card card-${partnerColor}`; // Set color class
            partnerCard.style.display = 'block'; // Show it

            // Set Partner Icon
            const icons = { 'red': 'fa-fire', 'green': 'fa-leaf', 'blue': 'fa-water' };
            document.getElementById('partnerIcon').innerHTML = `<i class="fa-solid ${icons[partnerColor]}"></i>`;

            // FLIP BOTH CARDS NOW
            setTimeout(() => {
                // Flip Mine
                document.querySelector('.card-container.selected').classList.add('flipped');
                // Flip Partner's
                partnerCard.classList.add('flipped');

                checkWin(partnerColor);
            }, 100);
        }

        function checkWin(partnerColor) {
            const statusBox = document.getElementById('statusText');
            const resetBtn = document.getElementById('resetBtn');

            if (mySelection === partnerColor) {
                // WIN
                statusBox.innerHTML = "<i class='fa-solid fa-heart'></i> Hearts in Sync. Connection Established.";
                statusBox.className = "status-msg show success-text";
                // Gold Pulse
                document.body.style.boxShadow = "inset 0 0 100px rgba(255, 234, 167, 0.3)";
                saveMatch(); // DB Save
            } else {
                // LOSE
                statusBox.innerHTML = "Finding the rhythm... Try again to find the match.";
                statusBox.className = "status-msg show fail-text";
                // Shake
                document.getElementById('myHand').classList.add('shake');
                document.getElementById('partnerHand').classList.add('shake');
                // Fade effect
                document.querySelector('.play-area').style.opacity = '0.7';
            }

            resetBtn.classList.add('visible');
        }

        function saveMatch() {
            const fd = new FormData();
            fd.append('action', 'save_match');
            fd.append('partner_name', partnerName);

            fetch('sync_board.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('globalCounter').innerText = "Total Global Syncs: " + Number(data.new_total).toLocaleString();
                    }
                });
        }

        function resetGame() {
            window.location.reload();
        }
    </script>
</body>

=======
<?php
session_start();
require_once 'db_connect.php';

// Enforce Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle AJAX Request for Saving Match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_match') {
    header('Content-Type: application/json');

    $partnerName = $_POST['partner_name'] ?? 'Unknown Signal';

    try {
        $stmt = $conn->prepare("INSERT INTO sync_games (user_id, result_status, partner_name) VALUES (:uid, 'MATCH', :partner)");
        $stmt->bindParam(':uid', $_SESSION['user_id']);
        $stmt->bindParam(':partner', $partnerName);
        $stmt->execute();

        // Get new total
        $countStmt = $conn->query("SELECT COUNT(*) as total FROM sync_games WHERE result_status = 'MATCH'");
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        echo json_encode(['success' => true, 'new_total' => $total]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 1. Get Initial Global Count
$globalSyncs = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM sync_games WHERE result_status = 'MATCH'");
    $globalSyncs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
}

// 2. Fetch Random Partner (Simulated Real User)
$partnerName = "Mysterious Signal";
try {
    // Get random user that is NOT me
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id != :uid ORDER BY RAND() LIMIT 1");
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->execute();
    $randomUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($randomUser) {
        $partnerName = $randomUser['username'];
    }
} catch (Exception $e) {
}

// 3. Fetch Recent Matches for Current User
$recentMatches = [];
try {
    $stmt = $conn->prepare("SELECT partner_name, created_at FROM sync_games WHERE user_id = :uid AND result_status = 'MATCH' ORDER BY created_at DESC LIMIT 5");
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->execute();
    $recentMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Board | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --neon-red: #ff7675;
            --neon-green: #55efc4;
            --neon-blue: #74b9ff;
            --neon-gold: #ffeaa7;
            --bg-dark: #0D0D0D;
        }

        body {
            background-color: var(--bg-dark);
            overflow: hidden;
            /* Fix for game view */
        }

        /* Screen Layout */
        .sync-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 80px;
            /* Space for Navbar */
            position: relative;
        }

        /* Header Info */
        .game-header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), transparent);
            position: relative;
            z-index: 10;
        }

        .page-title {
            font-size: 1.5rem;
            color: var(--secondary-color);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .sub-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            letter-spacing: 1px;
            font-style: italic;
        }

        .global-counter {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--neon-gold);
            text-shadow: 0 0 10px rgba(255, 234, 167, 0.5);
            margin-top: 10px;
        }


        /* Play Area */
        .play-area {
            flex: 1;
            display: flex;
            position: relative;
            align-items: center;
            justify-content: center;
        }

        .divider {
            position: absolute;
            left: 50%;
            top: 10%;
            bottom: 20%;
            width: 2px;
            background: linear-gradient(to bottom, transparent, var(--secondary-color), transparent);
            box-shadow: 0 0 15px var(--secondary-color);
            transform: translateX(-50%);
            animation: pulse-divider 2s infinite ease-in-out;
        }

        @keyframes pulse-divider {

            0%,
            100% {
                opacity: 0.5;
                height: 70%;
            }

            50% {
                opacity: 1;
                height: 80%;
            }
        }

        .player-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 40px;
            height: 100%;
        }

        .side-label {
            font-size: 1.5rem;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        /* Cards Container */
        .cards-row {
            display: flex;
            gap: 30px;
            perspective: 1000px;
            /* For 3D Flip */
        }

        /* Card Styles */
        .card-container {
            width: 120px;
            height: 180px;
            position: relative;
            cursor: pointer;
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .card-container.flipped {
            transform: rotateY(180deg);
        }

        /* Locked-in state (Before flip) */
        .card-container.locked {
            transform: translateY(-10px);
        }

        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        /* Front (Face Down) */
        .card-front {
            background: rgba(13, 13, 13, 0.9);
            border-color: rgba(255, 255, 255, 0.5);
            transform: rotateY(0deg);
            /* Explicitly set */
        }

        .card-front::after {
            content: '?';
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.1);
            font-weight: 700;
        }

        /* Specific Neon Borders for Face Down */
        .card-red .card-front {
            border-color: var(--neon-red);
            box-shadow: 0 0 10px rgba(255, 118, 117, 0.2);
        }

        .card-green .card-front {
            border-color: var(--neon-green);
            box-shadow: 0 0 10px rgba(85, 239, 196, 0.2);
        }

        .card-blue .card-front {
            border-color: var(--neon-blue);
            box-shadow: 0 0 10px rgba(116, 185, 255, 0.2);
        }

        /* Highlight Selection */
        .card-container.selected .card-front {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.4);
            border-width: 3px;
        }

        /* Back (Revealed Color) */
        .card-back {
            transform: rotateY(180deg);
            font-size: 3rem;
            background: #fff;
            /* Fallback */
            color: #fff;
        }

        .card-red .card-back {
            background: var(--neon-red);
            box-shadow: 0 0 40px var(--neon-red);
        }

        .card-green .card-back {
            background: var(--neon-green);
            box-shadow: 0 0 40px var(--neon-green);
        }

        .card-blue .card-back {
            background: var(--neon-blue);
            box-shadow: 0 0 40px var(--neon-blue);
        }


        /* Ghost Cards (Partner Side) */
        .ghost-card {
            width: 120px;
            height: 180px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .ghost-card.active {
            border-style: solid;
            border-color: var(--secondary-color);
            background: rgba(0, 206, 201, 0.05);
            box-shadow: 0 0 15px rgba(0, 206, 201, 0.2);
            animation: pulse-ghost 1s infinite alternate;
        }

        /* Partner Card (After Choices Made) */
        .partner-real-card {
            display: none;
            /* Hidden until needed */
        }

        @keyframes pulse-ghost {
            from {
                transform: scale(1);
                opacity: 0.5;
            }

            to {
                transform: scale(1.02);
                opacity: 1;
            }
        }

        /* Feedback Zone */
        .feedback-zone {
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .status-msg {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .status-msg.show {
            opacity: 1;
            transform: translateY(0);
        }

        .success-text {
            color: var(--neon-gold);
            text-shadow: 0 0 15px var(--neon-gold);
        }

        .fail-text {
            color: var(--text-muted);
        }

        .btn-reset {
            padding: 10px 40px;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s;
            opacity: 0;
            pointer-events: none;
        }

        .btn-reset.visible {
            opacity: 1;
            pointer-events: all;
        }

        .btn-reset:hover {
            background: var(--secondary-color);
            color: #000;
            box-shadow: 0 0 20px var(--secondary-color);
        }

        /* Recent List */
        .recent-matches {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 250px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            z-index: 100;
        }

        .recent-title {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .match-item {
            font-size: 0.8rem;
            color: #ccc;
            margin-bottom: 5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 5px;
        }

        .match-item:last-child {
            border: none;
        }

        /* Shake Animation */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .cards-row.shake {
            animation: shake 0.5s;
        }

        /* Mobile */
        @media(max-width: 768px) {
            .play-area {
                flex-direction: column;
            }

            .divider {
                width: 80%;
                height: 1px;
                top: 50%;
                left: 10%;
                bottom: auto;
            }

            .recent-matches {
                display: none;
            }

            .card-container,
            .ghost-card {
                width: 80px;
                height: 120px;
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
            <a href="sync_board.php" class="active" style="color:var(--secondary-color)">Sync Board</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="nav-btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn login-btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="sync-container">
        <div class="game-header">
            <div class="page-title">Sync Board</div>
            <div class="sub-text">Two hearts, one choice. Can you find the frequency?</div>
            <div class="global-counter" id="globalCounter">Total Global Syncs:
                <?php echo number_format($globalSyncs); ?></div>
        </div>

        <div class="play-area">
            <!-- Player 1: Me -->
            <div class="player-side">
                <div class="side-label">My Choice</div>
                <div class="cards-row" id="myHand">
                    <!-- Red Card -->
                    <div class="card-container card-red" onclick="pickCard('red', this)">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back"><i class="fa-solid fa-fire"></i></div>
                    </div>
                    <!-- Green Card -->
                    <div class="card-container card-green" onclick="pickCard('green', this)">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back"><i class="fa-solid fa-leaf"></i></div>
                    </div>
                    <!-- Blue Card -->
                    <div class="card-container card-blue" onclick="pickCard('blue', this)">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back"><i class="fa-solid fa-water"></i></div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Player 2: Partner -->
            <div class="player-side">
                <div class="side-label" id="partnerNameDisplay">Partner's Pulse</div>
                <div class="cards-row" id="partnerHand">
                    <!-- Ghosts -->
                    <div class="ghost-card" id="ghost-1"><i class="fa-regular fa-square"></i></div>
                    <div class="ghost-card" id="ghost-2"><i class="fa-regular fa-square"></i></div>
                    <div class="ghost-card" id="ghost-3"><i class="fa-regular fa-square"></i></div>

                    <!-- Hidden Real Card (Revealed later) -->
                    <div class="card-container partner-real-card" id="partnerRealCard">
                        <div class="card-face card-front"></div>
                        <div class="card-face card-back" id="partnerIcon"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="feedback-zone">
            <div class="status-msg" id="statusText"></div>
            <button class="btn-reset" id="resetBtn" onclick="resetGame()">Try Again</button>
        </div>

        <!-- Recent Matches List -->
        <div class="recent-matches">
            <div class="recent-title">Recent Connections</div>
            <?php if (count($recentMatches) > 0): ?>
                <?php foreach ($recentMatches as $match): ?>
                    <div class="match-item">
                        Synced with <strong style="color:#fff"><?php echo htmlspecialchars($match['partner_name']); ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="match-item">No connections yet...</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let mySelection = null;
        let isLocked = false;
        const partnerName = "<?php echo htmlspecialchars($partnerName); ?>";

        function pickCard(color, element) {
            if (isLocked) return;
            isLocked = true;
            mySelection = color;

            // 1. Visual Lock-in (Highlight border, stay face down)
            const allCards = document.querySelectorAll('#myHand .card-container');
            allCards.forEach(c => c.style.opacity = '0.3'); // Dim others
            element.style.opacity = '1';
            element.classList.add('selected'); // Glow effect
            element.classList.add('locked'); // Slight lift

            // 2. Start Partner Sequence
            startPartnerSequence();
        }

        function startPartnerSequence() {
            // Update Text
            const statusBox = document.getElementById('statusText');
            statusBox.innerHTML = `<span style="color:#fff">Waiting for ${partnerName}...</span>`;
            statusBox.className = 'status-msg show';
            document.getElementById('partnerNameDisplay').innerText = partnerName;

            // Animate Ghosts
            const ghosts = document.querySelectorAll('.ghost-card');
            ghosts.forEach(g => g.classList.add('active'));

            // 3. Simulated Delay (2 seconds)
            setTimeout(() => {
                revealOutcome();
            }, 2000);
        }

        function revealOutcome() {
            // Determine Partner Choice (40% Match Chance)
            const colors = ['red', 'green', 'blue'];
            let partnerColor = '';

            const isMatch = Math.random() < 0.4;
            if (isMatch) {
                partnerColor = mySelection;
            } else {
                const others = colors.filter(c => c !== mySelection);
                partnerColor = others[Math.floor(Math.random() * others.length)];
            }

            // Hide Ghosts
            document.querySelectorAll('.ghost-card').forEach(g => g.style.display = 'none');

            // Setup Partner Real Card
            const partnerCard = document.getElementById('partnerRealCard');
            partnerCard.className = `card-container partner-real-card card-${partnerColor}`; // Set color class
            partnerCard.style.display = 'block'; // Show it

            // Set Partner Icon
            const icons = { 'red': 'fa-fire', 'green': 'fa-leaf', 'blue': 'fa-water' };
            document.getElementById('partnerIcon').innerHTML = `<i class="fa-solid ${icons[partnerColor]}"></i>`;

            // FLIP BOTH CARDS NOW
            setTimeout(() => {
                // Flip Mine
                document.querySelector('.card-container.selected').classList.add('flipped');
                // Flip Partner's
                partnerCard.classList.add('flipped');

                checkWin(partnerColor);
            }, 100);
        }

        function checkWin(partnerColor) {
            const statusBox = document.getElementById('statusText');
            const resetBtn = document.getElementById('resetBtn');

            if (mySelection === partnerColor) {
                // WIN
                statusBox.innerHTML = "<i class='fa-solid fa-heart'></i> Hearts in Sync. Connection Established.";
                statusBox.className = "status-msg show success-text";
                // Gold Pulse
                document.body.style.boxShadow = "inset 0 0 100px rgba(255, 234, 167, 0.3)";
                saveMatch(); // DB Save
            } else {
                // LOSE
                statusBox.innerHTML = "Finding the rhythm... Try again to find the match.";
                statusBox.className = "status-msg show fail-text";
                // Shake
                document.getElementById('myHand').classList.add('shake');
                document.getElementById('partnerHand').classList.add('shake');
                // Fade effect
                document.querySelector('.play-area').style.opacity = '0.7';
            }

            resetBtn.classList.add('visible');
        }

        function saveMatch() {
            const fd = new FormData();
            fd.append('action', 'save_match');
            fd.append('partner_name', partnerName);

            fetch('sync_board.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('globalCounter').innerText = "Total Global Syncs: " + Number(data.new_total).toLocaleString();
                    }
                });
        }

        function resetGame() {
            window.location.reload();
        }
    </script>
</body>

>>>>>>> f82c3ed (Updated AuraLink project)
</html>