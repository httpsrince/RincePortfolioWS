<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Database Connection Parameters
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "portfoliodb"; 

// 2. Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// 3. Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error, 0); 
    header("Location: index.html?status=error&msg=" . urlencode("Database connection failed.") . "#contact-section");
    exit();
}

// 4. Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = isset($_POST['full_name']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['full_name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['email'])) : '';
    $phoneNumber = isset($_POST['phone_number']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['phone_number'])) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['subject'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['message'])) : '';

    // Basic server-side validation 
    if (empty($fullName) || empty($email) || empty($message)) {
        header("Location: index.html?status=error&msg=" . urlencode("Please fill in all required fields (Full Name, Email, Message).") . "#contact-section");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.html?status=error&msg=" . urlencode("Invalid email format.") . "#contact-section");
        exit();
    }

    // 5. Prepare and execute the SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, phone_number, subject, message) VALUES (?, ?, ?, ?, ?)");

    if ($stmt === false) {
        // Handle prepare error
        error_log("Prepare statement failed: " . $conn->error, 0);
        header("Location: index.html?status=error&msg=" . urlencode("An internal error occurred preparing the statement.") . "#contact-section");
        exit();
    }

    $stmt->bind_param("sssss", $fullName, $email, $phoneNumber, $subject, $message);

    if ($stmt->execute()) {
        // Success
        header("Location: index.html?status=success#contact-section");
        exit();
    } else {
        // Error executing statement
        error_log("Execute statement failed: " . $stmt->error, 0); 
        header("Location: index.html?status=error&msg=" . urlencode("Failed to send message: " . $stmt->error) . "#contact-section");
        exit();
    }

    // Close the statement
    $stmt->close();

} else {
    // If someone tries to access this script directly without a POST request
    header("Location: index.html?status=error&msg=" . urlencode("Invalid request method. Please submit the form.") . "#contact-section");
    exit();
}

// 7. Close the database connection (only if it was successfully opened)
if ($conn) {
    $conn->close();
}
?>