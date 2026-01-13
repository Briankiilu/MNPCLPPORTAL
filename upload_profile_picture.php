<?php
session_start();
require_once 'dbconnect.php'; 


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    
    header("Location: index.php"); 
    exit();
}

$username = $_SESSION['username'];

$target_dir = "../uploads/profile_pictures/"; 
$uploadOk = 1;


if (!is_dir($target_dir)) {
    
    if (!mkdir($target_dir, 0777, true)) {
        $_SESSION['upload_message'] = "Error: Failed to create upload directory.";
        header("Location: student_dashboard.php");
        exit();
    }
}


if (isset($_FILES["profile_image"])) {
    $file = $_FILES["profile_image"];

    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    $max_file_size = 500000; 

    
    $new_file_name = $username . '_' . time() . '.' . $imageFileType;
    
    $target_file = $target_dir . $new_file_name;

    
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $_SESSION['upload_message'] = "File is not an image.";
        $uploadOk = 0;
    }
    
    
    if ($file["size"] > $max_file_size) {
        $_SESSION['upload_message'] = "Sorry, your file is too large (max 500KB).";
        $uploadOk = 0;
    }

   
    if(!in_array($imageFileType, $allowed_extensions)) {
        $_SESSION['upload_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    
    if ($uploadOk == 0) {
        
    } else {
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            
           
            if (isset($connection) && $connection) {
               
                $safe_path = mysqli_real_escape_string($connection, $target_file); 
                $safe_username = mysqli_real_escape_string($connection, $username);
                
                
                $update_query = "UPDATE trainees SET profile_pic_path = '$safe_path' WHERE username = '$safe_username'";
                
                if (mysqli_query($connection, $update_query)) {
                    
                    $_SESSION['profile_pic'] = $target_file; 
                    $_SESSION['upload_message'] = "The file ". htmlspecialchars(basename($file["name"])). " has been uploaded and your profile updated.";
                } else {
                    $_SESSION['upload_message'] = "Error updating database: " . mysqli_error($connection);
                }
                
            } else {
                $_SESSION['upload_message'] = "Database connection error.";
            }

        } else {
            $_SESSION['upload_message'] = "Sorry, there was an error uploading your file.";
        }
    }
} else {
    
    $_SESSION['upload_message'] = "No file selected.";
}


header("Location: student_dashboard.php");
exit();
?>