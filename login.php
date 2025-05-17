<?php
// Start the session
session_start();

// Database credentials
$servername = "localhost";
$username = "root"; // Your database username
$password = "Nischay@2004"; // Your database password - consider using environment variables for sensitive data
$dbname = "user_db"; // Your database name

// Create connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    // Log error instead of dying directly to user in production
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later."); // User-friendly message
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    // Using prepared statements is more secure against SQL injection than mysqli_real_escape_string
    $email = $_POST['email']; // We will use this in a prepared statement

    // Prepare statement to fetch user
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    if ($stmt === false) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        die("An error occurred. Please try again later.");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password (md5 is not secure, consider password_hash() and password_verify())
        if (md5($_POST['password']) === $user['password']) {
            // Store email in session
            $_SESSION['email'] = $email;
            $_SESSION['loggedin'] = true; // Optional: set a general logged-in flag

            // Redirect to home page after successful login
            // Path is correct as home.php is in the same directory
            header("Location: home.php");
            exit(); // Stop further execution after redirect
        } else {
            // Invalid password
            // For security, use a generic message for both invalid email and password
            echo "Invalid email or password.";
        }
    } else {
        // No user found with that email
        echo "Invalid email or password.";
    }
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
