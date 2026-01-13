<?php

session_start();


require_once 'dbconnect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);

   
    if (empty($username)) {
        $_SESSION['login_error'] = "Email address or Phone # is required.";
        header("Location: index.php");
        exit();
    }

   
    
    
    $stmt = $connection->prepare("SELECT user_role FROM trainees WHERE username = ?");
    
    if (!$stmt) {
       
        $_SESSION['login_error'] = "Database error: Could not prepare statement.";
        header("Location: index.php");
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        
        $user_data = $result->fetch_assoc();
        
        
        $_SESSION['pending_login_user'] = $username;
        $_SESSION['user_role_type'] = $user_data['user_role'];

        
        header("Location: password_entry.php");
        exit();
    } else {
        
        $_SESSION['login_error'] = "User not found. Please check your details.";
        header("Location: index.php");
        exit();
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MNP CLP Portal - Sign In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <style>
    :root {
    --mnp-blue: #007bff; 
    --mnp-red: #dc3545; 
    --gray-text: #6c757d;
    --light-gray-bg: #f8f9fa;
    --box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
}

body {
    font-family: Arial, sans-serif;
    background-image: url('background-image-mnp.jpg'); 
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    margin: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}


.header {
    position: absolute;
    top: 20px;
    right: 20px;
}

.home-button {
    background-color: var(--mnp-red);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.home-button:hover {
    background-color: #c82333;
}


.login-box {
    display: flex;
    background-color: white;
    border-radius: 8px;
    box-shadow: var(--box-shadow);
    overflow: hidden; 
    max-width: 900px;
    width: 120%;
}


.login-image-pane {
    flex: 1; 
    position: relative;
    min-width: 0px; 
}

.login-image-pane img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    filter: brightness(105%) contrast(102%);
}


.login-form-pane {
    flex: 1; 
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.mnp-logo {
    width: 120px; 
    height: auto;
    margin-bottom: 10px;
}

.portal-subtitle {
    color: red;
    font-size: 1.1em;
    margin-bottom: 30px;
}


form {
    width: 100%;
    max-width: 300px; 
    text-align: left;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
    font-size: 0.9em;
}

.input-group {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: 4px;
    margin-bottom: 20px;
    padding: 5px 10px;
}

.input-group .icon {
    color: var(--gray-text);
    margin-right: 10px;
}

.input-group input[type="text"] {
    border: none;
    flex-grow: 1;
    padding: 10px 0;
    outline: none;
    font-size: 1em;
}

.next-button {
    width: 100%;
    background-color: var(--mnp-blue);
    color: white;
    padding: 12px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.1em;
    font-weight: bold;
    transition: background-color 0.3s;
}

.next-button:hover {
    background-color: #0056b3;
}

.form-links {
    margin-top: 20px;
    font-size: 0.9em;
}

.form-links a {
    color: var(--mnp-blue);
    text-decoration: none;
    transition: color 0.3s;
}

.form-links a:hover {
    color: #0056b3;
    text-decoration: underline;
}


@media (max-width: 768px) {
    .login-box {
        flex-direction: column;
        max-width: 400px;
    }

    .login-image-pane {
        display: none; 
    }

    .login-form-pane {
        padding: 30px;
    }
}
    
    </style>
    <div class="header">
        <button class="home-button" onclick="window.location.href='/'">
            <i class="fas fa-home"></i> Home
        </button>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-image-pane">
                <img src="images/download.jpeg" alt="Industry Collaboration Handshake">
            </div>

            <div class="login-form-pane">
                <div class="logo-section">
                    <img src="images/logo.png" alt="MNP Logo" class="mnp-logo">
                    <h2>The Meru National Polytechnic</h2>
                    <p class="portal-subtitle">Trainees & Trainers Portal - Sign In</p>
                </div>

                <?php
                if (isset($_SESSION['login_error'])) {
                    echo '<p style="color: #dc3545; font-weight: bold; margin-bottom: 10px;">' . htmlspecialchars($_SESSION['login_error']) . '</p>';
                    unset($_SESSION['login_error']); 
                }
                ?>
                
                <form id="loginForm" action="index.php" method="POST">
                    <label for="username">Email address or Phone #</label>
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="username" name="username" placeholder="Enter Your Email Address*" required>
                    </div>

                    <button type="submit" class="next-button">Next</button>
                </form>

                <div class="form-links">
    <a href="forgot_password.php">Forgot your password?</a>
 </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const usernameField = document.getElementById('username');
            
        
            if (usernameField.value.trim() === '') {
                alert('Please enter your Email address or Phone #.');
                usernameField.focus();
                event.preventDefault(); 
            }
        });
    </script>
</body>
</html>