<?php
session_start();
require_once 'dbconnect.php'; 


if (!isset($_SESSION['pending_login_user'])) {
    header("Location: index.php");
    exit();
}


$username = $_SESSION['pending_login_user']; 
$display_username = htmlspecialchars($username);
$role = $_SESSION['user_role_type'];

$login_error = '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $submitted_password = $_POST['password'];

   
    if (!empty($submitted_password)) {
        
        
        $_SESSION['logged_in'] = true;
        
        
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $role; 

        
        unset($_SESSION['pending_login_user']); 
        unset($_SESSION['user_role_type']);
        
        
        header("Location: student_dashboard.php");
        exit();

    } else {
       
        $_SESSION['login_error'] = "Invalid password. Please try again.";
        header("Location: password_entry.php"); 
        exit();
    }
}



if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); 
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MNP CLP Portal - Enter Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        
        :root {
            --mnp-blue: #007bff; 
            --mnp-red: #dc3545; 
            --gray-text: #6c757d;
            --light-gray-bg: #f8f9fa;
            --box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            --border-color: #ced4da;
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

        /* --- Login Box Container (reused for consistency) --- */
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
            min-width: 300px;
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
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .portal-subtitle {
            color: var(--gray-text);
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
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 20px;
            padding: 5px 10px;
        }
        
        .input-group:focus-within {
            border-color: var(--mnp-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }


        .input-group .icon {
            color: var(--gray-text);
            margin-right: 10px;
        }

        .input-group input[type="password"] {
            border: none;
            flex-grow: 1;
            padding: 10px 0;
            outline: none;
            font-size: 1em;
        }

        .password-toggle {
            cursor: pointer;
            color: var(--gray-text);
            padding: 0 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #333;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            accent-color: var(--mnp-blue); 
            transform: scale(1.1);
        }

        .checkbox-group a {
            color: var(--mnp-blue);
            text-decoration: none;
        }
        .checkbox-group a:hover {
            text-decoration: underline;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 15px; 
            margin-top: 25px;
        }

        .go-back-button, .sign-in-button {
            flex: 1; 
            padding: 12px 0;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.05em;
            font-weight: bold;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        .go-back-button {
            background-color: #e9ecef; 
            color: #495057;
            border: 1px solid var(--border-color);
        }

        .go-back-button:hover {
            background-color: #dae0e5;
            border-color: #c6c6c6;
        }

        .sign-in-button {
            background-color: var(--mnp-blue);
            color: white;
            border: none;
        }

        .sign-in-button:hover {
            background-color: #0056b3;
        }

        .form-links {
            margin-top: 30px;
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

        .error-message {
            color: #dc3545; 
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 0.95em;
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
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="home-button" onclick="window.location.href='/'">
            <i class="fas fa-home"></i> Home
        </button>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-image-pane">
                <img src="images/download.jpeg" alt="Industry Collaboration Handshake"> </div>

            <div class="login-form-pane">
                <div class="logo-section">
                    <img src="images/logo.png" alt="MNP Logo" class="mnp-logo"> <h2>The Meru National Polytechnic</h2>
                    <p class="portal-subtitle">Collaborations And Linkages Portal - Sign In</p>
                   
                </div>

                <?php
                if (!empty($login_error)) {
                    echo '<p class="error-message">' . htmlspecialchars($login_error) . '</p>';
                }
                ?>
                
                <form id="passwordForm" action="password_entry.php" method="POST">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter Your Password*" required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to <a href="terms_of_use.html">Terms of use</a></label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="remember_me" name="remember_me">
                        <label for="remember_me">Remember Me</label>
                    </div>

                    <div class="button-group">
                        <button type="button" class="go-back-button" onclick="window.location.href='index.php'">Go Back</button>
                        <button type="submit" class="sign-in-button">Sign In</button>
                    </div>
                </form>

                <div class="form-links">
                    <a href="forgot_password.html">Forgot your password?</a> |
                    <a href="privacy_policy.html">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
       
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
               
                this.classList.toggle('fa-eye-slash');
            });
        }

      
        document.getElementById('passwordForm').addEventListener('submit', function(event) {
            const passwordField = document.getElementById('password');
            const termsCheckbox = document.getElementById('terms');
            
            if (passwordField.value.trim() === '') {
                alert('Please enter your password.');
                passwordField.focus();
                event.preventDefault();
                return;
            }

            if (!termsCheckbox.checked) {
                alert('You must agree to the Terms of use to proceed.');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>