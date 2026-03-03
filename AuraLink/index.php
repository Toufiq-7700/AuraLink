<<<<<<< HEAD
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraLink | Beyond Words</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">AuraLink</div>
        <div class="nav-links">
            <a href="mood_wall.php">Mood Wall</a>
            <a href="#vision-canvas">Vision Canvas</a>
            <a href="sync_board.php">Sync Board</a>
            <a href="#about">About</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user">Welcome,
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="logout.php" class="nav-btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn login-btn">Login</a>
                <a href="signup.php" class="nav-btn signup-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1 class="glow-text">Beyond words, into the pulse.</h1>
            <p class="subtitle">Express, evolve, and sync in a world of pure feeling.</p>
            <a href="#features" class="cta-button">Enter the Sanctuary</a>
        </div>
        <div class="hero-bg-overlay"></div>
    </header>

    <!-- Feature Cards -->
    <section id="features" class="features">
        <div class="container">
            <!-- Card 1: Mood Wall -->
            <div class="feature-card" id="mood-wall">
                <div class="feature-icon"><i class="fa-solid fa-wave-square"></i></div>
                <h3>The Mood Wall</h3>
                <span class="tagline">Drop a Vibe.</span>
                <p>Post your silence to the global frequency using colors and pulses.</p>
                <button class="card-btn" onclick="window.location.href='mood_wall.php'">Enter the Wall</button>
            </div>

            <!-- Card 2: Vision Canvas -->
            <div class="feature-card" id="vision-canvas">
                <div class="feature-icon"><i class="fa-solid fa-paintbrush"></i></div>
                <h3>The Vision Canvas</h3>
                <span class="tagline">Sketch to Soul.</span>
                <p>Watch AI translate your simple lines into a visual masterpiece.</p>
                <button class="card-btn">Start Drawing</button>
            </div>

            <!-- Card 3: Sync Board -->
            <div class="feature-card" id="sync-board">
                <div class="feature-icon"><i class="fa-solid fa-circle-nodes"></i></div>
                <h3>The Sync Board</h3>
                <span class="tagline">Deep Match.</span>
                <p>Two minds, one color—test your intuition and find your frequency.</p>
                <button class="card-btn" onclick="window.location.href='sync_board.php'">Find a Partner</button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2>Why AuraLink?</h2>
            <div class="about-text">
                <p>In a world full of noise, we've forgotten how to understand one another in silence. AuraLink is a
                    digital experiment designed to strip away the distraction of language. We believe that a color can
                    say more than a paragraph, and a shared rhythm can connect two hearts better than a thousand words.
                </p>
                <p class="highlight">Born for the Speak Without Words Challenge.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-left">
            &copy; 2024 AuraLink Team
        </div>
        <div class="footer-middle">
            Built for the Speak Without Words Challenge.
        </div>
        <div class="footer-right">
            <a href="#"><i class="fa-brands fa-github"></i></a>
            <a href="#"><i class="fa-brands fa-twitter"></i></a>
            <a href="#"><i class="fa-brands fa-discord"></i></a>
        </div>
    </footer>


    <!-- Particle Canvas -->
    <canvas id="bgCanvas"></canvas>

    <script>
        // Constellation Effect
        const canvas = document.getElementById('bgCanvas');
        const ctx = canvas.getContext('2d');
        let width, height;

        // Resize
        function resize() {
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width;
            canvas.height = height;
        }
        window.addEventListener('resize', resize);
        resize();

        // Particles
        const particles = [];
        const particleCount = 60; // Adjust for density
        const connectionDist = 150;

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5; // Slow movement
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2 + 1;
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                // Bounce
                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;
            }

            draw() {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        // Init
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        // Animation Loop
        function animate() {
            ctx.clearRect(0, 0, width, height);

            // Update & Draw Particles
            particles.forEach(p => {
                p.update();
                p.draw();
            });

            // Draw Connections
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < connectionDist) {
                        ctx.strokeStyle = `rgba(180, 180, 255, ${0.15 * (1 - dist / connectionDist)})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>

=======
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraLink | Beyond Words</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">AuraLink</div>
        <div class="nav-links">
            <a href="mood_wall.php">Mood Wall</a>
            <a href="vision_canvas.php">Vision Canvas</a>
            <a href="sync_board.php">Sync Board</a>
            <a href="#about">About</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user">Welcome,
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="logout.php" class="nav-btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn login-btn">Login</a>
                <a href="signup.php" class="nav-btn signup-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1 class="glow-text">Beyond words, into the pulse.</h1>
            <p class="subtitle">Express, evolve, and sync in a world of pure feeling.</p>
            <a href="#features" class="cta-button">Enter the Sanctuary</a>
        </div>
        <div class="hero-bg-overlay"></div>
    </header>

    <!-- Feature Cards -->
    <section id="features" class="features">
        <div class="container">
            <!-- Card 1: Mood Wall -->
            <div class="feature-card" id="mood-wall">
                <div class="feature-icon"><i class="fa-solid fa-wave-square"></i></div>
                <h3>The Mood Wall</h3>
                <span class="tagline">Drop a Vibe.</span>
                <p>Post your silence to the global frequency using colors and pulses.</p>
                <button class="card-btn" onclick="window.location.href='mood_wall.php'">Enter the Wall</button>
            </div>

            <!-- Card 2: Vision Canvas -->
            <div class="feature-card" id="vision-canvas">
                <div class="feature-icon"><i class="fa-solid fa-paintbrush"></i></div>
                <h3>The Vision Canvas</h3>
                <span class="tagline">Sketch to Soul.</span>
                <p>Watch AI translate your simple lines into a visual masterpiece.</p>
                <button class="card-btn" onclick="window.location.href='vision_canvas.php'">Start Drawing</button>
            </div>

            <!-- Card 3: Sync Board -->
            <div class="feature-card" id="sync-board">
                <div class="feature-icon"><i class="fa-solid fa-circle-nodes"></i></div>
                <h3>The Sync Board</h3>
                <span class="tagline">Deep Match.</span>
                <p>Two minds, one color—test your intuition and find your frequency.</p>
                <button class="card-btn" onclick="window.location.href='sync_board.php'">Find a Partner</button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2>Why AuraLink?</h2>
            <div class="about-text">
                <p>In a world full of noise, we've forgotten how to understand one another in silence. AuraLink is a
                    digital experiment designed to strip away the distraction of language. We believe that a color can
                    say more than a paragraph, and a shared rhythm can connect two hearts better than a thousand words.
                </p>
                <p class="highlight">Born for the Speak Without Words Challenge.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-left">
            &copy; 2024 AuraLink Team
        </div>
        <div class="footer-middle">
            Built for the Speak Without Words Challenge.
        </div>
        <div class="footer-right">
            <a href="#"><i class="fa-brands fa-github"></i></a>
            <a href="#"><i class="fa-brands fa-twitter"></i></a>
            <a href="#"><i class="fa-brands fa-discord"></i></a>
        </div>
    </footer>


    <!-- Particle Canvas -->
    <canvas id="bgCanvas"></canvas>

    <script>
        // Constellation Effect
        const canvas = document.getElementById('bgCanvas');
        const ctx = canvas.getContext('2d');
        let width, height;

        // Resize
        function resize() {
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width;
            canvas.height = height;
        }
        window.addEventListener('resize', resize);
        resize();

        // Particles
        const particles = [];
        const particleCount = 60; // Adjust for density
        const connectionDist = 150;

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5; // Slow movement
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2 + 1;
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                // Bounce
                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;
            }

            draw() {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        // Init
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        // Animation Loop
        function animate() {
            ctx.clearRect(0, 0, width, height);

            // Update & Draw Particles
            particles.forEach(p => {
                p.update();
                p.draw();
            });

            // Draw Connections
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < connectionDist) {
                        ctx.strokeStyle = `rgba(180, 180, 255, ${0.15 * (1 - dist / connectionDist)})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>

>>>>>>> f82c3ed (Updated AuraLink project)
</html>