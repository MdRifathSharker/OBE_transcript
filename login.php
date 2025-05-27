<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "registration_db";
$conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$passwordInput = $_POST['password'];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if (password_verify($passwordInput, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['firstname'];
        $_SESSION['user_department'] = $row['department'];
        $_SESSION['user_batch'] = $row['batch'];

        if ($row['designation'] === 'Admin') {
            header("Location: admindash.html");
        } elseif ($row['designation'] === 'Teacher') {
            header("Location: teacherdash.html");
        } elseif ($row['designation'] === 'Student') {
            header("Location: studentdash.html");
        } else {
            echo "<script>alert('Unknown designation. Please contact admin.'); window.location.href='login.html';</script>";
        }
        exit();
    } else {
        echo "<script>alert('Incorrect password!'); window.location.href='login.html';</script>";
    }
} else {
    echo "<script>alert('User ID not found!'); window.location.href='login.html';</script>";
}

$stmt->close();
$conn->close();
?>