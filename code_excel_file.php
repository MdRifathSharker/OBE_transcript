<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost";
$username = "root";
$password = "";
$database = "registration_db";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['save_excel_data'])) {
    $headerRowIndex = (int)$_POST['header_row'];
    $batch = $_POST['batch_no'];
    $file = $_FILES['import_file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $headerRowIndexZeroBased = $headerRowIndex - 1;
    $headers = $rows[$headerRowIndexZeroBased];
    $idCol = array_search('ID No', $headers);
    $nameCol = array_search('Name', $headers);
    $emailCol = array_search('E-mail', $headers);
    $deptCol = array_search('Department', $headers);
    $designationCol = array_search('Designation', $headers);

    if ($idCol === false || $nameCol === false || $emailCol === false) {
        die("Column headers not found. Make sure header row contains: ID No, Name, E-mail.");
    }

    $message = "";

    for ($i = $headerRowIndexZeroBased + 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $student_id = $conn->real_escape_string(trim($row[$idCol]));
        $firstname = $conn->real_escape_string(trim($row[$nameCol]));
        $email = $conn->real_escape_string(trim($row[$emailCol]));
        $department = $deptCol !== false ? $conn->real_escape_string(trim($row[$deptCol])) : '';
        $designation = $designationCol !== false ? $conn->real_escape_string(trim($row[$designationCol])) : '';

        if (!empty($student_id) && !empty($firstname) && !empty($email)) {
            $sql_check = "SELECT * FROM users WHERE id = '$student_id'";
            $result_check = $conn->query($sql_check);
            if ($result_check->num_rows == 0) {
                $sql = "INSERT INTO users (id, firstname, email, batch, department, designation) 
                        VALUES ('$student_id', '$firstname', '$email', '$batch', '$department', '$designation')";
                if (!$conn->query($sql)) {
                    $message .= "Error inserting user with ID: $student_id - " . $conn->error . "\\n";
                }
            } else {
                $message .= "User with ID: $student_id already exists. Skipped.\\n";
            }
        } else {
            $message .= "Missing required data in row $i. Skipped.\\n";
        }
    }

    echo "<script>alert('Upload completed.\\n$message'); window.location.href='admindash.html';</script>";
} else {
    echo "Form not submitted.";
}
$conn->close();
?>
