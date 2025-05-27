<?php
if (isset($_GET['batch']) && isset($_GET['department'])) {
    $batch = $_GET['batch'];
    $department = $_GET['department'];


    $mainFeedbackDir = 'course_feedback_students/batch_' . $batch . '/dept_' . $department;
    

    $zipFileName = "feedback_batch_{$batch}_dept_{$department}.zip";
    $zipFilePath = __DIR__ . '/' . $zipFileName;  // Ensure the correct path

    if (!is_dir($mainFeedbackDir)) {
        echo "The specified batch/department directory does not exist: $mainFeedbackDir";
        exit;
    }

    $zip = new ZipArchive();

    
    if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
    
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($mainFeedbackDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

       
        foreach ($files as $name => $file) {
        
            if (!$file->isDir() && strtolower($file->getExtension()) === 'pdf') {
                $filePath = $file->getRealPath();
               
                $relativePath = str_replace($mainFeedbackDir . '/', '', $filePath);

                
                $zip->addFile($filePath, $relativePath);
            }
        }

        if ($zip->close()) {
            
            if (!file_exists($zipFilePath)) {
                echo "ZIP file was not created successfully.";
                exit;
            }

            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($zipFilePath));

            
            readfile($zipFilePath);

           
            unlink($zipFilePath); 
            exit();
        } else {
            echo "Error creating the zip archive.";
        }
    } else {
        echo "Error opening the zip archive.";
    }
} else {
    echo "Invalid parameters. Batch and department must be selected.";
}
?>
