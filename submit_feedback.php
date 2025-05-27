<?php

require('fpdf.php');

$host = "localhost";
$username = "root";
$password = "";
$database = "registration_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $studentName = $conn->real_escape_string($_POST['student_name']);
    $studentID = $conn->real_escape_string($_POST['student_id']);
    $session = $conn->real_escape_string($_POST['session']);
    $batch = $conn->real_escape_string($_POST['batch']);
    $course = $conn->real_escape_string($_POST['course']);
    $department = $conn->real_escape_string($_POST['department']);
    $objectivesRating = $conn->real_escape_string($_POST['objectives']);
    $contentRating = $conn->real_escape_string($_POST['content']);
    $methodsRating = $conn->real_escape_string($_POST['methods']);
    $amethodsRating = $conn->real_escape_string($_POST['amethods']);
    $ikmethodsRating = $conn->real_escape_string($_POST['ikmethods']);
    $icmethodsRating = $conn->real_escape_string($_POST['icmethods']);
    $lrmethodsRating = $conn->real_escape_string($_POST['lrmethods']);
    $smethodsRating = $conn->real_escape_string($_POST['smethods']);
    $fmethodsRating = $conn->real_escape_string($_POST['fmethods']);
    $omethodsRating = $conn->real_escape_string($_POST['omethods']);
    $comments = $conn->real_escape_string($_POST['comments']);
    $signature = $conn->real_escape_string($_POST['signature']);
    $feedbackDate = $conn->real_escape_string($_POST['date']);

   
    $tableName = "course_feedback_batch_" . preg_replace("/[^a-zA-Z0-9_]/", "", $batch);

    $createTableSQL = "CREATE TABLE IF NOT EXISTS $tableName (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(255) NOT NULL,
        student_id VARCHAR(50) NOT NULL,
        session VARCHAR(50),
        batch VARCHAR(50) NOT NULL,
        department VARCHAR(100),
        course_code_title VARCHAR(255) NOT NULL,
        objectives_rating INT,
        content_rating INT,
        teaching_methods_rating INT,
        assessment_methods_rating INT,
        instructor_knowledge_rating INT,
        instructor_communication_rating INT,
        learning_resources_rating INT,
        student_engagement_rating INT,
        feedback_support_rating INT,
        overall_satisfaction_rating INT,
        comments TEXT,
        signature VARCHAR(255),
        feedback_date DATE,
        submission_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        pdf_path VARCHAR(255)
    )";

    if ($conn->query($createTableSQL) === TRUE) {
      
        $insertSQL = "INSERT INTO $tableName (
            student_name, student_id, session, batch, department, course_code_title,
            objectives_rating, content_rating, teaching_methods_rating,
            assessment_methods_rating, instructor_knowledge_rating,
            instructor_communication_rating, learning_resources_rating,
            student_engagement_rating, feedback_support_rating,
            overall_satisfaction_rating, comments, signature, feedback_date
        ) VALUES (
            '$studentName', '$studentID', '$session', '$batch', '$department', '$course',
            '$objectivesRating', '$contentRating', '$methodsRating',
            '$amethodsRating', '$ikmethodsRating', '$icmethodsRating',
            '$lrmethodsRating', '$smethodsRating', '$fmethodsRating',
            '$omethodsRating', '$comments', '$signature', '$feedbackDate'
        )";

        if ($conn->query($insertSQL) === TRUE) {
            $feedbackID = $conn->insert_id;

            
            $sanitizedCourse = preg_replace("/[^a-zA-Z0-9_]/", "_", $course);
            $sanitizedStudentID = preg_replace("/[^a-zA-Z0-9_]/", "", $studentID);

            
            $pdfFileName = "feedback_" . $sanitizedStudentID . "_" . $sanitizedCourse . ".pdf";

            
            $pdfStorageDir = __DIR__ . "/course_feedback_students/batch_" . preg_replace("/[^a-zA-Z0-9_]/", "", $batch) . "/dept_" . preg_replace("/[^a-zA-Z0-9_]/", "", $department) . "/";

        
            if (!is_dir($pdfStorageDir)) {
                mkdir($pdfStorageDir, 0755, true);
            }

            $pdfFilePath = $pdfStorageDir . $pdfFileName;

           
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 12);

            $pdf->Cell(0, 10, 'Course Feedback', 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->Cell(40, 10, 'Student Name:', 0);
            $pdf->Cell(0, 10, $studentName, 0, 1);

            $pdf->Cell(40, 10, 'Student ID:', 0);
            $pdf->Cell(0, 10, $studentID, 0, 1);

            $pdf->Cell(40, 10, 'Session:', 0);
            $pdf->Cell(0, 10, $session, 0, 1);

            $pdf->Cell(40, 10, 'Batch:', 0);
            $pdf->Cell(0, 10, $batch, 0, 1);

            $pdf->Cell(40, 10, 'Department:', 0);
            $pdf->Cell(0, 10, $department, 0, 1);

            $pdf->Cell(40, 10, 'Course:', 0);
            $pdf->Cell(0, 10, $course, 0, 1);
            $pdf->Ln(5);

            $pdf->Cell(60, 10, 'Rating (1-5)', 1, 0, 'C');
            $pdf->Cell(0, 10, '', 0, 1); 

            $pdf->Cell(60, 10, 'Course Objectives:', 1);
            $pdf->Cell(0, 10, $objectivesRating, 0, 1);

            $pdf->Cell(60, 10, 'Course Content:', 1);
            $pdf->Cell(0, 10, $contentRating, 0, 1);

            $pdf->Cell(60, 10, 'Teaching Methods:', 1);
            $pdf->Cell(0, 10, $methodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Assessment Methods:', 1);
            $pdf->Cell(0, 10, $amethodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Instructor Knowledge:', 1);
            $pdf->Cell(0, 10, $ikmethodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Instructor Communication:', 1);
            $pdf->Cell(0, 10, $icmethodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Learning Resources:', 1);
            $pdf->Cell(0, 10, $lrmethodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Student Engagement:', 1);
            $pdf->Cell(0, 10, $smethodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Feedback & Support:', 1);
            $pdf->Cell(0, 10, $fmethodsRating, 0, 1);

            $pdf->Cell(60, 10, 'Overall Satisfaction:', 1);
            $pdf->Cell(0, 10, $omethodsRating, 0, 1);
            $pdf->Ln(5);

            $pdf->MultiCell(0, 10, 'Comments: ' . $comments, 0, 1);
            $pdf->Ln(5);

            $pdf->Cell(40, 10, 'Signature:', 0);
            $pdf->Cell(0, 10, $signature, 0, 1);

            $pdf->Cell(40, 10, 'Date:', 0);
            $pdf->Cell(0, 10, $feedbackDate, 0, 1);

            $pdf->Output('F', $pdfFilePath);

            
            $updateSQL = "UPDATE $tableName SET pdf_path = '$pdfFilePath' WHERE id = $feedbackID";
            if ($conn->query($updateSQL) === TRUE) {
                echo "Feedback submitted and saved as PDF successfully.";
                header("Location: studentdash.html?feedback=success");
                exit();
            } else {
                echo "Error updating PDF path: " . $conn->error;
            }
        } else {
            echo "Error saving feedback data: " . $conn->error;
        }
    } else {
        echo "Error creating feedback table: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>