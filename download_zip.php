<?php
if (isset($_GET['batch']) && isset($_GET['department'])) {
    $batch = $_GET['batch'];
    $department = $_GET['department'];

    
    $mainFeedbackDir = __DIR__ . "/course_feedback_students/batch_" . $batch . "/dept_" . $department;

    
    if (!is_dir($mainFeedbackDir)) {
        die("Directory does not exist: $mainFeedbackDir");
    }

    
    $zipFileName = "feedback_batch_" . $batch . "_dept_" . $department . ".zip";
    $zipFilePath = __DIR__ . "/" . $zipFileName;

    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($mainFeedbackDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($mainFeedbackDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        
        if (file_exists($zipFilePath)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
            header('Content-Length: ' . filesize($zipFilePath));
            readfile($zipFilePath);

            
            unlink($zipFilePath);
            exit();
        } else {
            die("ZIP file was not created.");
        }
    } else {
        die("Could not open ZIP file for writing.");
    }
} else {
    die("Batch and department not set.");
}