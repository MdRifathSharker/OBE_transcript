<!-- filepath: d:\XAMPP\htdocs\Project Website\admin_add_courses.php -->
<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "registration_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch = $_POST['batch'];
    $dept = $_POST['dept'];
    $courses = array_filter($_POST['course']);
    $table_name = "course_" . $batch . "_" . $dept;
    $create_table_query = "CREATE TABLE IF NOT EXISTS `$table_name` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(50) NOT NULL
    )";
    $conn->query($create_table_query);


    $clear_table_query = "TRUNCATE TABLE `$table_name`";
    $conn->query($clear_table_query);


    foreach ($courses as $course) {
        $stmt = $conn->prepare("INSERT INTO `$table_name` (course_code) VALUES (?)");
        $stmt->bind_param("s", $course);
        $stmt->execute();
    }

    echo "<script>alert('Courses added successfully for Batch $batch and Department $dept!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Courses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
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
            background: #2e3458;
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
            margin-top: 4px;
            margin-left: 4px;
            width: 220px;
            background: #2e3458;
            color: white;
            padding: 20px;
            height: 90vh;
            position: fixed;
            left: 0;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar ul li {
            font-weight: bold;
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #34495e;
        }

        .sidebar ul li.active {
            background: #1f6692b9;
            color: white;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s ease-in-out;
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #2980b9;
            outline: none;
        }

        .submit-btn {
            background: #2980b9;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #1f6692;
        }
    </style>
</head>

<body>
    <header class="top-header">
        <img src="images/bauet-logo2.png" alt="BAUET Logo">
        <h1>Bangladesh Army University of Engineering & Technology</h1>
    </header>

    <div class="sidebar">
        <h1><u>Admin Dashboard</u></h1>
        <ul>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h2 class="text-center">Add Courses</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="batch">Batch Number</label>
                    <input type="text" id="batch" name="batch" placeholder="Enter Batch Number" required>
                </div>
                <div class="form-group">
                    <label for="dept">Department</label>
                    <input type="text" id="dept" name="dept" placeholder="Enter Department" required>
                </div>
                <?php for ($i = 1; $i <= 14; $i++): ?>
                    <div class="form-group">
                        <label for="course<?= $i ?>">Course Code <?= $i ?></label>
                        <input type="text" id="course<?= $i ?>" name="course[]" placeholder="Enter Course Code">
                    </div>
                <?php endfor; ?>
                <button type="submit" class="submit-btn">Save Courses</button>
            </form>
        </div>
    </div>
</body>

</html>