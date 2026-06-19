<?php
if (isset($_GET['file'])) {
    $file = urldecode($_GET['file']);
    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file);
    header('Content-Type: ' . $mimeType);
    header("Content-Disposition: attachment; filename=employeefile.pdf");
    header("Content-Length: " . filesize($file));
    echo file_get_contents($file);
    exit();
} else {
    http_response_code(400);
    exit();
}