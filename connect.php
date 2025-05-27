<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

$servername = "localhost";
$username = "root";
$password = "";
$database = "registration_db";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateRandomPassword($length = 6) {
    $characters = '1234567890';
    $password = '';
    $charLength = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $charLength)];
    }
    return $password;
}

function sendPasswordEmail($email, $firstname, $generatedPassword) {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'obe0015@gmail.com';
        $mail->Password   = 'sontqonlznvgbzes'; // App password
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('obe0015@gmail.com', 'BAUET Registration');
        $mail->addAddress($email, $firstname);

        $mail->isHTML(true);
        $mail->Subject = 'Your BAUET Account Password';
        $mail->Body    = "Dear <b>$firstname</b>,<br><br>Your account has been verified.<br>Your password is: <b>$generatedPassword</b><br><br>Please log in and change it soon.<br><br>Thanks,<br>BAUET";
        $mail->AltBody = "Dear $firstname, Your account has been verified. Your password is: $generatedPassword. Please log in and change it soon. Thanks, BAUET";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}

$student_id = trim($_POST['id']);
$email = trim($_POST['email']);

if (empty($student_id) || empty($email)) {
    die("ID and Email are required.");
}

$sql_check = "SELECT * FROM users WHERE id = ? AND email = ? AND password IS NULL";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ss", $student_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $firstname = $row['firstname'];

    $generatedPassword = generateRandomPassword();
    $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);

    $sql_update = "UPDATE users SET password = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ss", $hashedPassword, $student_id);

    if ($stmt_update->execute()) {
        if (sendPasswordEmail($email, $firstname, $generatedPassword)) {
            echo "<script>alert('Registration successful! Password sent to your email.'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Registration successful, but email failed to send.'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Failed to update password.');</script>";
    }
    $stmt_update->close();
} else {
    echo "<script>alert('No matching user found or already registered. Please contact admin.'); window.location.href='registrationform.html';</script>";
}

$stmt->close();
$conn->close();
?>
