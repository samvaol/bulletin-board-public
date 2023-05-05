<?php
include "config.php";

if (isset($_POST["submit"])) {
    // Check if a nickname is provided
    $nickname = isset($_POST['nickname']) ? $_POST['nickname'] : null;
    // Check if content is empty or not, and error if empty
    $content = trim($_POST["content"]);
    if (empty($content)) {
        header("Location: index.php?error=empty");
        exit();
    }

    // Check if an image or video was uploaded
    if (isset($_FILES["media"]) && $_FILES["media"]["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["media"]["name"]);
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_image_extensions = array("jpg", "jpeg", "png", "gif");
        $allowed_video_extensions = array("mp4", "webm", "ogg");

        if (in_array($file_extension, $allowed_image_extensions)) {
            $file_type = "image";
        } elseif (in_array($file_extension, $allowed_video_extensions)) {
            $file_type = "video";
        } else {
            echo "Sorry, only JPG, JPEG, PNG, GIF, MP4, WEBM, and OGG files are allowed.";
            exit();
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            echo "Error uploading file. Please try again.";
            exit();
        }
    } else {
        $file_path = null;
        $file_type = null;
    }

    $created_at = date("Y-m-d H:i:s");
    
    // Get the user's IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Update the prepared statement and bind parameters accordingly
    $stmt = $conn->prepare("INSERT INTO posts (content, media_path, file_type, nickname, created_at, user_ip) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $content, $file_path, $file_type, $nickname, $created_at, $user_ip);
    $stmt->execute();

    $conn->close();
}

header("Location: index.php");
exit();
