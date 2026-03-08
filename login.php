<?php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST['login_input']);
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $error = "Please enter both email/username and password.";
    } else {
        try {
            // Allow login with either email or username
            $stmt = $conn->prepare("SELECT user_id, username, email, password FROM users WHERE email = :input OR username = :input");
            $stmt->bindParam(':input', $login_input);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    // Password is correct
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];

                    header("Location: index.php"); // Redirect to home/dashboard
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No user found with that email or username.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-error {
            background: rgba(255, 0, 0, 0.2);
            color: #ff7675;
            border: 1px solid #ff7675;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <!-- Background Overlay -->
        <div class="auth-box">
            <h2>Welcome Back</h2>
            <?php if ($error): ?>
                <div class="alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <input type="text" name="login_input" placeholder="Email or Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="auth-btn">Enter Sanctuary</button>
            </form>
            <span class="auth-link">
                New here? <a href="signup.php">Join the frequency</a>
            </span>
            <span class="auth-link">
                <a href="index.php">Back to Home</a>
            </span>
        </div>
    </div>

</body>

</html>