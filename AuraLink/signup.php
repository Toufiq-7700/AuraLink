<<<<<<< HEAD
<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Simple validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                $error = "Email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Something went wrong.";
                }
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
    <title>Sign Up | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="style.css">
    <style>
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
        }

        .alert-error {
            background: rgba(255, 0, 0, 0.2);
            color: #ff7675;
            border: 1px solid #ff7675;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.2);
            color: #55efc4;
            border: 1px solid #55efc4;
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Join the Pulse</h2>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <form action="signup.php" method="POST">
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Create Password" required>
                </div>
                <button type="submit" class="auth-btn">Start Your Journey</button>
            </form>
            <span class="auth-link">
                Already synced? <a href="login.php">Login here</a>
            </span>
            <span class="auth-link">
                <a href="index.php">Back to Home</a>
            </span>
        </div>
    </div>

</body>

=======
<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Simple validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                $error = "Email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Something went wrong.";
                }
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
    <title>Sign Up | AuraLink</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="style.css">
    <style>
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
        }

        .alert-error {
            background: rgba(255, 0, 0, 0.2);
            color: #ff7675;
            border: 1px solid #ff7675;
        }

        .alert-success {
            background: rgba(0, 255, 0, 0.2);
            color: #55efc4;
            border: 1px solid #55efc4;
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Join the Pulse</h2>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <form action="signup.php" method="POST">
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Create Password" required>
                </div>
                <button type="submit" class="auth-btn">Start Your Journey</button>
            </form>
            <span class="auth-link">
                Already synced? <a href="login.php">Login here</a>
            </span>
            <span class="auth-link">
                <a href="index.php">Back to Home</a>
            </span>
        </div>
    </div>

</body>

>>>>>>> f82c3ed (Updated AuraLink project)
</html>