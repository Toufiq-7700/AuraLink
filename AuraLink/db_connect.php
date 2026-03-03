<<<<<<< HEAD
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auralink_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In a real production environment, you wouldn't die with the error message
    // but for local development it's helpful.
    die("Connection failed: " . $e->getMessage());
}
=======
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auralink_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In a real production environment, you wouldn't die with the error message
    // but for local development it's helpful.
    die("Connection failed: " . $e->getMessage());
}
>>>>>>> f82c3ed (Updated AuraLink project)
?>