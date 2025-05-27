<?php

function getDirectoryList($basePath) {
    $directories = [];

    if (is_dir($basePath)) {
        $batchDirs = scandir($basePath);
        foreach ($batchDirs as $batchDir) {
            if ($batchDir != '.' && $batchDir != '..' && is_dir($basePath . '/' . $batchDir)) {
                $deptDirs = scandir($basePath . '/' . $batchDir);
                foreach ($deptDirs as $deptDir) {
                    if ($deptDir != '.' && $deptDir != '..' && strpos($deptDir, 'dept_') === 0) {
                        $departmentName = str_replace('dept_', '', $deptDir);
                        $courseDirs = scandir($basePath . '/' . $batchDir . '/' . $deptDir);
                        $courses = [];
                        foreach ($courseDirs as $courseDir) {
                            if ($courseDir != '.' && $courseDir != '..' && is_dir($basePath . '/' . $batchDir . '/' . $deptDir . '/' . $courseDir)) {
                                $pdfCount = count(glob($basePath . '/' . $batchDir . '/' . $deptDir . '/' . $courseDir . '/*.pdf'));
                                $pdfCount -= 1;
                                $courses[] = [
                                    'name' => $courseDir,
                                    'pdfCount' => $pdfCount
                                ];
                            }
                        }
                        $directories[] = [
                            'batch' => str_replace('batch_', '', $batchDir),
                            'department' => $departmentName,
                            'courses' => $courses
                        ];
                    }
                }
            }
        }
    }

    return $directories;
}

$feedbackDir = 'course_feedback_students';
$directories = getDirectoryList($feedbackDir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Feedback</title>
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
            height: 60px;
            background: #2e3458;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            z-index: 1000;
        }

        .top-header img {
            width: 40px;
            margin-right: 10px;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: 220px;
            height: calc(100% - 60px);
            background: #2e3458;
            padding: 20px;
            color: white;
        }

        .main-content {
            margin-left: 240px;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px;
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background: #2980b9;
            color: white;
            font-weight: bold;
            border: none;
            margin-top: 20px;
            cursor: pointer;
        }

        button:hover {
            background: #1f6692;
        }

        .btn-download-all {
            display: inline-block;
            margin-top: 20px;
            background: #27ae60;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
        }

        .btn-download-all:hover {
            background: #219150;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }
        }
        body, html {
    height: 100%;
    margin: 0;
    padding: 0;
}

.main-content {
    display: flex;
    justify-content: center;  
    align-items: center;      
    height: 100vh;           
}

.form-container {
    background-color: #f9f9f9;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
    </style>
</head>
<body>

<header class="top-header">
    <img src="images/bauet-logo2.png" alt="Logo">
    <h1>Bangladesh Army University of Engineering & Technology</h1>
</header>

<div class="sidebar">
    <h3>Admin Menu</h3>
   
</div>

<div class="main-content">
    <div class="form-container">
        <h2>Select Batch, Department, and Course</h2>
        <form action="download_zip.php" method="GET">
            <label for="batch">Select Batch:</label>
            <select name="batch" id="batch" required>
                <option value="">-- Select a Batch --</option>
                <?php
                    $batchList = array_unique(array_column($directories, 'batch'));
                    foreach ($batchList as $batch) {
                        echo "<option value=\"$batch\">$batch</option>";
                    }
                ?>
            </select>

            <label for="department">Select Department:</label>
            <select name="department" id="department" required>
                <option value="">-- Select a Department --</option>
            </select>

            <label for="course">Select Course:</label>
            <select name="course" id="course" required>
                <option value="">-- Select a Course --</option>
            </select>

            <button type="submit">Download Feedback (ZIP)</button>
        </form>

        <a href="download_all_feedback.php" class="btn-download-all">Download All Feedback (ZIP)</a>
    </div>
</div>

<script>
    var directories = <?php echo json_encode($directories); ?>;

    document.getElementById('batch').addEventListener('change', function () {
        var batchSelected = this.value;
        var departmentSelect = document.getElementById('department');
        var courseSelect = document.getElementById('course');
        departmentSelect.innerHTML = '<option value="">-- Select a Department --</option>';
        courseSelect.innerHTML = '<option value="">-- Select a Course --</option>';

        if (batchSelected) {
            var departments = directories.filter(function (dir) {
                return dir.batch === batchSelected;
            });

            departments.forEach(function (dir) {
                var option = document.createElement('option');
                option.value = dir.department;
                option.textContent = dir.department;
                departmentSelect.appendChild(option);
            });
        }
    });

    document.getElementById('department').addEventListener('change', function () {
        var batchSelected = document.getElementById('batch').value;
        var departmentSelected = this.value;
        var courseSelect = document.getElementById('course');
        courseSelect.innerHTML = '<option value="">-- Select a Course --</option>';

        if (batchSelected && departmentSelected) {
            var courses = directories.find(function (dir) {
                return dir.batch === batchSelected && dir.department === departmentSelected;
            }).courses;

            courses.forEach(function (course) {
                var option = document.createElement('option');
                option.value = course.name;
                option.textContent = course.name + " (" + course.pdfCount  + " Feedbacks)";
                courseSelect.appendChild(option);
            });
        }
    });
</script>

</body>
</html>
