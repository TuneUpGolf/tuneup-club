<?php
// Simple PHP video streaming test
// Save this as video-test.php in a web-accessible directory

// Set the path to your video file - change this to your actual path
$videoPath = 'safari_compatible.mp4';
$filename = basename($videoPath);

// Basic error checking
if (!file_exists($videoPath)) {
    header('HTTP/1.0 404 Not Found');
    echo 'Video file not found';
    exit;
}

// Get file size and mime type
$fileSize = filesize($videoPath);
$mimeType = 'video/mp4';

// Detect Safari browser
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isSafari = strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false;

// Turn off all output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Set content download type
header('Content-Type: ' . $mimeType);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Accept-Ranges: bytes');
header('X-Content-Type-Options: nosniff');

// Special Safari handling
if ($isSafari) {
    // Safari-specific headers that might help
    header('Content-Length: ' . $fileSize);

    // Force a 200 OK for the initial request
    http_response_code(200);

    // Set additional Safari-friendly headers
    header('Connection: Keep-Alive');
    header('X-Playback-Session-Id: ' . uniqid());

    // For Safari, we'll skip all range handling and just send the complete file
    // This works better for Safari in many cases
    readfile($videoPath);
    exit;
}

// For non-Safari browsers, use standard range request handling
$range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : '';

if (!empty($range)) {
    // Extract the byte range
    list(, $range) = explode('=', $range, 2);

    if (preg_match('/(\d*)-(\d*)/', $range, $matches)) {
        $start = empty($matches[1]) ? 0 : intval($matches[1]);
        $end = empty($matches[2]) ? $fileSize - 1 : intval($matches[2]);

        // Validate range
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        // Calculate length
        $length = $end - $start + 1;

        // Set partial content headers
        header('HTTP/1.1 206 Partial Content');
        header('Content-Length: ' . $length);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);

        // Output the range
        $fp = fopen($videoPath, 'rb');
        fseek($fp, $start);
        $sent = 0;

        while (!feof($fp) && $sent < $length) {
            $buffer = fread($fp, min(1024 * 16, $length - $sent));
            echo $buffer;
            $sent += strlen($buffer);
            flush();
        }

        fclose($fp);
        exit;
    }
}

// No range or unrecognized range - send entire file
header('Content-Length: ' . $fileSize);
readfile($videoPath);
exit;
