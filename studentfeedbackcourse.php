<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_department = $_SESSION['user_department'];
$user_batch = $_SESSION['user_batch'];

$host = "localhost";
$username = "root";
$password = "";
$database = "registration_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch courses for the student's batch and department
$courses = [];
$tableName = "course_" . $user_batch . "_" . $user_department;
$checkTableQuery = "SHOW TABLES LIKE '$tableName'";
$result = $conn->query($checkTableQuery);

if ($result->num_rows > 0) {
    $fetchCoursesQuery = "SELECT course_code FROM `$tableName`";
    $courseResult = $conn->query($fetchCoursesQuery);

    if ($courseResult->num_rows > 0) {
        while ($row = $courseResult->fetch_assoc()) {
            $courses[] = $row['course_code'];
        }
    } else {
        echo "<script>alert('No courses found for your batch and department.');</script>";
    }
} else {
    echo "<script>alert('No course table found for your batch and department.');</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentName = $conn->real_escape_string($_POST['student_name']);
    $studentID = $conn->real_escape_string($_POST['student_id']);
    $session = $conn->real_escape_string($_POST['session']);
    $batch = $conn->real_escape_string($_POST['batch']);
    $department = $conn->real_escape_string($_POST['department']);
    $course = $conn->real_escape_string($_POST['course']);
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

    // Table 1: course_feedback_<student_batch>_<student_department>
    $table1 = "course_feedback_" . $batch . "_" . $department;
    $createTable1SQL = "CREATE TABLE IF NOT EXISTS `$table1` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(255) NOT NULL,
        student_id VARCHAR(50) NOT NULL,
        session VARCHAR(50),
        batch VARCHAR(50) NOT NULL,
        department VARCHAR(50) NOT NULL,
        course_code VARCHAR(50) NOT NULL,
        objectives_rating INT NOT NULL,
        content_rating INT NOT NULL,
        methods_rating INT NOT NULL,
        amethods_rating INT NOT NULL,
        ikmethods_rating INT NOT NULL,
        icmethods_rating INT NOT NULL,
        lrmethods_rating INT NOT NULL,
        smethods_rating INT NOT NULL,
        fmethods_rating INT NOT NULL,
        omethods_rating INT NOT NULL,
        comments TEXT,
        signature VARCHAR(255),
        feedback_date DATE,
        pdf_path VARCHAR(255)
    )";
    $conn->query($createTable1SQL);

    // Check if feedback already exists for the student and course
    $checkFeedbackSQL = "SELECT id FROM `$table1` WHERE student_id = ? AND course_code = ?";
    $stmtCheck = $conn->prepare($checkFeedbackSQL);
    $stmtCheck->bind_param("ss", $studentID, $course);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "<script>alert('Feedback for this course has already been submitted.');</script>";
        header("Location: studentdash.html?feedback=exists");
        exit();
    }

    // Insert feedback into the database
    $insertTable1SQL = "INSERT INTO `$table1` (
        student_name, student_id, session, batch, department, course_code,
        objectives_rating, content_rating, methods_rating, amethods_rating,
        ikmethods_rating, icmethods_rating, lrmethods_rating, smethods_rating,
        fmethods_rating, omethods_rating, comments, signature, feedback_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt1 = $conn->prepare($insertTable1SQL);
    $stmt1->bind_param(
        "sssssssssssssssssss",
        $studentName, $studentID, $session, $batch, $department, $course,
        $objectivesRating, $contentRating, $methodsRating, $amethodsRating,
        $ikmethodsRating, $icmethodsRating, $lrmethodsRating, $smethodsRating,
        $fmethodsRating, $omethodsRating, $comments, $signature, $feedbackDate
    );
    $stmt1->execute();
    $feedbackID = $conn->insert_id;

    // Create directories for storing the PDF
    $sanitizedCourse = preg_replace("/[^a-zA-Z0-9_]/", "_", $course);
    $sanitizedBatch = preg_replace("/[^a-zA-Z0-9_]/", "", $batch);
    $sanitizedDepartment = preg_replace("/[^a-zA-Z0-9_]/", "", $department);

    $pdfStorageDir = __DIR__ . "/course_feedback_students/batch_" . $sanitizedBatch . "/dept_" . $sanitizedDepartment . "/course_" . $sanitizedCourse . "/";
    if (!is_dir($pdfStorageDir)) {
        mkdir($pdfStorageDir, 0755, true);
    }

    // Generate PDF file for individual feedback
    $pdfFileName = "feedback_" . $studentID . "_" . $sanitizedCourse . ".pdf";
    $pdfFilePath = $pdfStorageDir . $pdfFileName;

    require('fpdf.php');
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

    // Update the PDF path in the database
    $updateSQL = "UPDATE `$table1` SET pdf_path = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateSQL);
    $stmtUpdate->bind_param("si", $pdfFilePath, $feedbackID);
    $stmtUpdate->execute();

    // Calculate averages for the course
    $avgQuery = "SELECT 
        AVG(objectives_rating) AS avg_objectives,
        AVG(content_rating) AS avg_content,
        AVG(methods_rating) AS avg_methods,
        AVG(amethods_rating) AS avg_assessment_methods,
        AVG(ikmethods_rating) AS avg_instructor_knowledge,
        AVG(icmethods_rating) AS avg_instructor_communication,
        AVG(lrmethods_rating) AS avg_learning_resources,
        AVG(smethods_rating) AS avg_student_engagement,
        AVG(fmethods_rating) AS avg_feedback_support,
        AVG(omethods_rating) AS avg_overall_satisfaction
    FROM `$table1`
    WHERE course_code = ?";
    $stmtAvg = $conn->prepare($avgQuery);
    $stmtAvg->bind_param("s", $course);
    $stmtAvg->execute();
    $avgResult = $stmtAvg->get_result();

    if ($avgRow = $avgResult->fetch_assoc()) {
        // Generate PDF for averages
        $avgPdfFileName = "average_marks_feedback_" . $sanitizedCourse . ".pdf";
        $avgPdfFilePath = $pdfStorageDir . $avgPdfFileName;

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);

        $pdf->Cell(0, 10, 'Average Course Feedback', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->Cell(60, 10, 'Course Objectives:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_objectives'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Course Content:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_content'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Teaching Methods:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_methods'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Assessment Methods:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_assessment_methods'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Instructor Knowledge:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_instructor_knowledge'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Instructor Communication:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_instructor_communication'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Learning Resources:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_learning_resources'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Student Engagement:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_student_engagement'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Feedback & Support:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_feedback_support'], 2), 0, 1);

        $pdf->Cell(60, 10, 'Overall Satisfaction:', 1);
        $pdf->Cell(0, 10, number_format($avgRow['avg_overall_satisfaction'], 2), 0, 1);

        $pdf->Ln(10);

        // Add a horizontal bar chart for the averages on a new page
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Average Ratings Graph', 0, 1, 'C');
        $pdf->Ln(5);

        // Data for the graph
        $data = [
            'Course Objectives' => $avgRow['avg_objectives'],
            'Course Content' => $avgRow['avg_content'],
            'Teaching Methods' => $avgRow['avg_methods'],
            'Assessment Methods' => $avgRow['avg_assessment_methods'],
            'Instructor Knowledge' => $avgRow['avg_instructor_knowledge'],
            'Instructor Communication' => $avgRow['avg_instructor_communication'],
            'Learning Resources' => $avgRow['avg_learning_resources'],
            'Student Engagement' => $avgRow['avg_student_engagement'],
            'Feedback & Support' => $avgRow['avg_feedback_support'],
            'Overall Satisfaction' => $avgRow['avg_overall_satisfaction']
        ];

        // Draw the horizontal bar chart
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetDrawColor(50, 50, 100);
        $barHeight = 8; // Slimmer bar height
        $x = 20; // Starting X position
        $y = 30; // Starting Y position on the new page

        foreach ($data as $label => $value) {
            // Draw the label
            $pdf->SetXY($x, $y);
            $pdf->Cell(50, $barHeight, $label, 0, 0, 'L');

            // Draw the horizontal bar
            $pdf->SetFillColor(100, 150, 250);
            $pdf->Rect($x + 55, $y, $value * 15, $barHeight, 'F'); // Slimmer and smaller bar width

            // Draw the value at the end of the bar
            $pdf->SetXY($x + 55 + ($value * 15) + 5, $y);
            $pdf->Cell(10, $barHeight, number_format($value, 2), 0, 1, 'L');

            // Move to the next bar
            $y += $barHeight + 5; // Add spacing between bars
        }

        $pdf->Output('F', $avgPdfFilePath);
    }

    echo "<script>alert('Feedback submitted and saved as PDF successfully!');</script>";
    header("Location: studentdash.html?feedback=success");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Feedback Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f7fc;
            min-height: 100vh;
            padding-top: 70px;
        }
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px;
            background: rgb(0, 32, 84);
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .top-header img {
            width: 40px;
            margin-right: 10px;
        }
        .sidebar {
            width: 220px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
        }
        .sidebar img {
            width: 200px;
            margin-bottom: 20px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2980b9;
            color: white;
            padding-left: 15px;
            padding-right: 15px;
            padding-top: 5px;
            padding-bottom: 5px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .container {
            width: 80%;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .rating-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }
        textarea {
            width: 100%;
            height: 100px;
            box-sizing: border-box;
        }
        button {
            background-color: #08c4e5;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header class="top-header">
        <img src="images/BAUET-Logo.png" alt="BAUET Logo">
        <h1>Bangladesh Army University of Engineering & Technology</h1>
    </header>
    <div class="sidebar">
        <img src="images/bauet_logo.png" alt="BAUET Logo">
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h2>Course Feedback Form</h2>
        </div>
        <div class="container">
            <h2>Course Feedback (By Students)</h2>
            <form action="" method="post">
                <table>
                    <tr>
                        <td><strong>Student Name</strong></td>
                        <td><input type="text" name="student_name" style="width: 300px;" value="<?php echo htmlspecialchars($user_name); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Student ID</strong></td>
                        <td><input type="text" name="student_id" style="width: 300px;" value="<?php echo htmlspecialchars($user_id); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Session</strong></td>
                        <td><input type="text" name="session" style="width: 300px;" required></td>
                    </tr>
                    <tr>
                        <td><strong>Batch</strong></td>
                        <td><input type="text" name="batch" style="width: 300px;" value="<?php echo htmlspecialchars($user_batch); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Department</strong></td>
                        <td><input type="text" name="department" style="width: 300px;" value="<?php echo htmlspecialchars($user_department); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td><strong>Course Code & Title</strong></td>
                        <td>
                            <select name="course" required>
                                <option value="">Select a Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course); ?>"><?php echo htmlspecialchars($course); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <table>
                    <tr>
                        <th rowspan="2" style="width: 60%;">
                            <p class="p_1">Please rate the following dimensions of quality of graduates according to importance in recruitment using the following rating scale:</p>
                            <strong>4=Excellent, 3= Good, 2=Average, and 1=Poor</strong> <br>
                            <strong>Description</strong>
                        </th>
                        <th colspan="4">Rating Scale</th>
                    </tr>
                    <tr>
                        <th class="rating-header">Excellent (4)</th>
                        <th class="rating-header">Good (3)</th>
                        <th class="rating-header">Average (2)</th>
                        <th class="rating-header">Poor (1)</th>
                    </tr>
                    <tr>
                        <td><strong>Course Objectives:</strong> Clarity and relevance of course objectives.</td>
                        <td><input type="radio" name="objectives" value="4" required></td>
                        <td><input type="radio" name="objectives" value="3"></td>
                        <td><input type="radio" name="objectives" value="2"></td>
                        <td><input type="radio" name="objectives" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Course Content:</strong> Quality and relevance of the course content.</td>
                        <td><input type="radio" name="content" value="4" required></td>
                        <td><input type="radio" name="content" value="3"></td>
                        <td><input type="radio" name="content" value="2"></td>
                        <td><input type="radio" name="content" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Teaching Methods:</strong> Effectiveness of teaching methods used.</td>
                        <td><input type="radio" name="methods" value="4" required></td>
                        <td><input type="radio" name="methods" value="3"></td>
                        <td><input type="radio" name="methods" value="2"></td>
                        <td><input type="radio" name="methods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Assessment Methods:</strong> Fairness and variety of assessment methods (exams, quizzes, projects).</td>
                        <td><input type="radio" name="amethods" value="4" required></td>
                        <td><input type="radio" name="amethods" value="3"></td>
                        <td><input type="radio" name="amethods" value="2"></td>
                        <td><input type="radio" name="amethods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Instructor’s Knowledge:</strong> Instructor’s depth of knowledge in the subject.</td>
                        <td><input type="radio" name="ikmethods" value="4" required></td>
                        <td><input type="radio" name="ikmethods" value="3"></td>
                        <td><input type="radio" name="ikmethods" value="2"></td>
                        <td><input type="radio" name="ikmethods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Instructor’s Communication:</strong> Clarity and effectiveness in communication.</td>
                        <td><input type="radio" name="icmethods" value="4" required></td>
                        <td><input type="radio" name="icmethods" value="3"></td>
                        <td><input type="radio" name="icmethods" value="2"></td>
                        <td><input type="radio" name="icmethods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Learning Resources:</strong> Availability and usefulness of learning resources (books, online materials).</td>
                        <td><input type="radio" name="lrmethods" value="4" required></td>
                        <td><input type="radio" name="lrmethods" value="3"></td>
                        <td><input type="radio" name="lrmethods" value="2"></td>
                        <td><input type="radio" name="lrmethods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Student Engagement:</strong> Level of student engagement and interaction during the course.</td>
                        <td><input type="radio" name="smethods" value="4" required></td>
                        <td><input type="radio" name="smethods" value="3"></td>
                        <td><input type="radio" name="smethods" value="2"></td>
                        <td><input type="radio" name="smethods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Feedback and Support:</strong> Timeliness and usefulness of feedback and support provided.</td>
                        <td><input type="radio" name="fmethods" value="4" required></td>
                        <td><input type="radio" name="fmethods" value="3"></td>
                        <td><input type="radio" name="fmethods" value="2"></td>
                        <td><input type="radio" name="fmethods" value="1"></td>
                    </tr>
                    <tr>
                        <td><strong>Overall Satisfaction:</strong> Overall satisfaction with the course.</td>
                        <td><input type="radio" name="omethods" value="4" required></td>
                        <td><input type="radio" name="omethods" value="3"></td>
                        <td><input type="radio" name="omethods" value="2"></td>
                        <td><input type="radio" name="omethods" value="1"></td>
                    </tr>
                </table>
                <h3>Additional Comments</h3>
                <textarea name="comments"></textarea>
                <p><b>Signature & Date</b></p>
                <input type="text" name="signature">
                <input type="date" name="date">
                <button type="submit">Submit Feedback</button>
            </form>
        </div>
    </div>
</body>
</html>