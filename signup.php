<?php
session_start();
$servername = "localhost";
$username = "root"; // Change as needed
$password = "Nischay@2004";     // Change as needed - consider environment variables
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password_plain = $_POST['password']; // Plain text password
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($email) || empty($password_plain) || empty($confirm_password)) {
        echo "All fields are required.";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Check if passwords match
    if ($password_plain !== $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    // Check if email already exists using prepared statement
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    if ($stmt === false) {
        error_log("Prepare failed (check email): (" . $conn->errno . ") " . $conn->error);
        die("An error occurred during registration. Please try again later.");
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email already registered!";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Insert new user into the database
    // IMPORTANT: MD5 is not secure for password hashing. Use password_hash().
    // $hashedPassword = password_hash($password_plain, PASSWORD_DEFAULT);
    $hashedPassword = md5($password_plain); // Keeping md5 as per original code for now

    $stmt_insert = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    if ($stmt_insert === false) {
        error_log("Prepare failed (insert user): (" . $conn->errno . ") " . $conn->error);
        die("An error occurred during registration. Please try again later.");
    }
    $stmt_insert->bind_param("ss", $email, $hashedPassword);

    if ($stmt_insert->execute()) {
        // echo "Registration successful!"; // Avoid echo before header
        // Redirect to login page
        // Path is correct as index.html is in the same directory
        header("Location: index.html?registration=success"); // Added a query param for feedback
        exit();
    } else {
        error_log("Error inserting user: " . $stmt_insert->error);
        echo "Error during registration. Please try again.";
    }
    $stmt_insert->close();
}

$conn->close();
?>
