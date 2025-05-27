<?php
// Path to your feedback directory
$feedbackDir = "course_feedback_students";

// Array to store departments for each batch
$departments = [];

// Check if the feedback directory exists
if (is_dir($feedbackDir)) {
    // Open the directory and loop through its contents
    $dir = opendir($feedbackDir);
    while (($folder = readdir($dir)) !== false) {
        // Look for directories that start with 'batch_' (batch directories)
        if (strpos($folder, 'batch_') === 0) {
            // Now, look for the dept_* directories inside each batch
            $batchDir = $feedbackDir . '/' . $folder;
            if (is_dir($batchDir)) {
                // Scan for departments (directories starting with 'dept_')
                $batchDepartments = []; // Temporary array to hold departments for this batch
                $subDir = opendir($batchDir);
                while (($subFolder = readdir($subDir)) !== false) {
                    // Check if the directory starts with 'dept_'
                    if (strpos($subFolder, 'dept_') === 0) {
                        // Extract department name (e.g., "CSE" from "dept_CSE")
                        $departmentName = str_replace('dept_', '', $subFolder);
                        // Add department to the array for this batch
                        $batchDepartments[] = $departmentName;
                    }
                }
                closedir($subDir);

                // Store the departments for the current batch
                if (!empty($batchDepartments)) {
                    $departments[$folder] = $batchDepartments;
                }
            }
        }
    }
    closedir($dir);
}

// Return the department list as a JSON response for use in the front-end
echo json_encode($departments);
?>
