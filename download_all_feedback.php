<?php
$mainFeedbackDir = 'course_feedback_students';

$zipFileName = "all_feedback.zip";
$zipFilePath = __DIR__ . "/" . $zipFileName;

$zip = new ZipArchive();

if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($mainFeedbackDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir() && strtolower($file->getExtension()) === 'pdf') {
            $filePath = $file->getRealPath();

        
            $relativePath = substr($filePath, strlen(realpath($mainFeedbackDir)) + 1);

            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    if (file_exists($zipFilePath)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        unlink($zipFilePath); 
        exit();
    } else {
        echo "Zip file was not created.";
    }
} else {
    echo "Failed to create zip archive.";
}
?>
