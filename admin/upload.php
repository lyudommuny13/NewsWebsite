<?php
session_start();
require_once '../config/db_connect.php';
require_once '../admin_check.php';
checkAdmin();

// Handle image upload
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $response = array();

    // Check for errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $file['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Verify file extension
        if (in_array($filetype, $allowed)) {
            // Create upload directory if it doesn't exist
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Create unique filename
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $response = array(
                    'location' => '../uploads/' . $new_filename // URL of the uploaded image
                );
                echo json_encode($response);
                exit;
            }
        }
    }

    // If we get here, something went wrong
    http_response_code(500);
    echo json_encode(array('error' => 'Upload failed'));
    exit;
}

// If no file was uploaded
http_response_code(400);
echo json_encode(array('error' => 'No file uploaded'));
exit;
?> 