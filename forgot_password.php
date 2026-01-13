<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only include PHPMailer if the files exist to prevent a crash
$phpMailerPath = 'PHPMailer/src/';
if (file_exists($phpMailerPath . 'PHPMailer.php')) {
    require $phpMailerPath . 'Exception.php';
    require $phpMailerPath . 'PHPMailer.php';
    require $phpMailerPath . 'SMTP.php';
}

require_once 'dbconnect.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $email = trim($_POST['username']);
    
    // 1. Check if user exists
    $stmt = $connection->prepare("SELECT id FROM trainees WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        
        // 2. Store token in DB
        $updateStmt = $connection->prepare("UPDATE trainees SET reset_token = ? WHERE username = ?");
        $updateStmt->bind_param("ss", $token, $email);
        
        if ($updateStmt->execute()) {
            // 3. Send Email
            if (!file_exists($phpMailerPath . 'PHPMailer.php')) {
                $message = "Error: PHPMailer folder not found. Please upload PHPMailer files.";
                $message_type = "error";
            } else {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'briankiilu843@gmail.com'; // CHANGE THIS
                    $mail->Password   = 'ihhj kelt mjlo jxgb';   // CHANGE THIS (App Password)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('your-email@gmail.com', 'MNP Portal Support');
                    $mail->addAddress($email);

                    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body    = "Hello, <br><br>Click the link below to reset your password:<br><br><a href='$resetLink'>$resetLink</a>";

                    $mail->send();
                    $message = "Success! A reset link has been sent to your email.";
                    $message_type = "success";
                } catch (Exception $e) {
                    $message = "Mail could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    $message_type = "error";
                }
            }
        }
    } else {
        $message = "No account found with that email address.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | MNP CLP Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --mnp-blue: #007bff; --mnp-red: #dc3545; --light-gray-bg: #f8f9fa; }
        body { font-family: Arial, sans-serif; background-color: var(--light-gray-bg); margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .reset-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center; }
        .reset-box h2 { color: #333; margin-bottom: 10px; }
        .reset-box p { color: #666; font-size: 0.9em; margin-bottom: 25px; }
        .input-group { display: flex; align-items: center; border: 1px solid #ced4da; border-radius: 4px; margin-bottom: 20px; padding: 8px 12px; }
        .input-group i { color: var(--mnp-blue); margin-right: 10px; }
        input { border: none; outline: none; width: 100%; font-size: 14px; }
        .btn-reset { background-color: var(--mnp-blue); color: white; border: none; width: 100%; padding: 12px; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        .btn-reset:hover { background-color: #0056b3; }
        .back-link { display: block; margin-top: 20px; color: var(--mnp-blue); text-decoration: none; font-size: 0.9em; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; }
        .error { background-color: #f8d7da; color: #721c24; }
        .success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="reset-box">
        <h2>Reset Password</h2>
        <p>Enter your email or phone number and we'll send you instructions to reset your password.</p>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Email address or Phone #" required>
            </div>
            <button type="submit" class="btn-reset">Send Reset Link</button>
        </form>

        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </div>
</body>
</html>