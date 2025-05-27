<?php
// Helper function to get batch and department directories dynamically
function getDirectoryList($basePath) {
    $directories = [];

    // Loop through each directory in the base path
    if (is_dir($basePath)) {
        $batchDirs = scandir($basePath);
        foreach ($batchDirs as $batchDir) {
            if ($batchDir != '.' && $batchDir != '..' && is_dir($basePath . '/' . $batchDir)) {
                // For each batch, get departments
                $deptDirs = scandir($basePath . '/' . $batchDir);
                foreach ($deptDirs as $deptDir) {
                    if ($deptDir != '.' && $deptDir != '..' && is_dir($basePath . '/' . $batchDir . '/' . $deptDir)) {
                        // Only consider directories that start with "dept_"
                        if (strpos($deptDir, 'dept_') === 0) {
                            // Remove the 'dept_' prefix
                            $departmentName = str_replace('dept_', '', $deptDir);
                            $directories[] = [
                                'batch' => $batchDir,
                                'department' => $departmentName // Store clean department name
                            ];
                        }
                    }
                }
            }
        }
    }

    return $directories;
}

// Path to the feedback folder
$feedbackDir = 'course_feedback_students'; // Replace with the actual path if needed

// Get list of available batches and departments
$directories = getDirectoryList($feedbackDir);

// Return the batch and department list as a JSON response for use in the front-end
echo json_encode($directories);
?>
