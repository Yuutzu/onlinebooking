<?php
/**
 * Email-based 2FA Helper Functions
 * Simple two-factor authentication using email codes
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generate a 6-digit verification code
 * @return string
 */
function generateEmailCode()
{
    return sprintf("%06d", mt_rand(100000, 999999));
}

/**
 * Send 2FA code via email
 * @param string $email Recipient email
 * @param string $name Recipient name
 * @param string $code 2FA code
 * @return bool
 */
function sendEmail2FACode($email, $name, $code)
{
    try {
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luxehavenmariott@gmail.com';
        $mail->Password = 'nufq zebo yjow cobb';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email settings
        $mail->setFrom('luxehavenmariott@gmail.com', 'Luxe Haven Hotel');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Luxe Haven Team - Login Verification Code';

        // Email body (match OTP email design & color palette)
        $mail->Body = "
        <html>
        <head>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    color: #333333;
                    background-color: #f0eeeb;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                h1, h2, h3 {
                    color: #4a1c1d;
                }
                p {
                    font-size: 16px;
                    line-height: 1.5;
                    color: #555555;
                }
                b {
                    color: #4a1c1d;
                }
                .footer {
                    font-size: 12px;
                    color: #888888;
                    text-align: center;
                }
                .footer i {
                    font-style: italic;
                }
                .code-box {
                    margin: 12px 0 18px;
                }
                .code {
                    font-weight: bold;
                    font-size: 22px;
                    letter-spacing: 4px;
                    color: #d9534f;
                    background-color: #f8f8f8;
                    padding: 10px 16px;
                    border-radius: 4px;
                    display: inline-block;
                }
                .highlight {
                    color: #4a1c1d;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Hello {$name},</h2>

                <p>You are attempting to log in to your <b>Luxe Haven Hotel</b> account.</p>

                <p>Your login verification code is:</p>

                <div class='code-box'>
                    <span class='code'>{$code}</span>
                </div>

                <p>This code is valid for the next <span class='highlight'>10 minutes</span>. For your security, please do not share this code with anyone.</p>

                <p>If you did not request this code, you may safely ignore this email and consider changing your password.</p>

                <br>

                <p>Sincerely,</p>
                <p><b>LUXE HAVEN HOTEL MANAGEMENT</b></p>

                <br>
                <div class='footer'>
                    <p>***<i>This is an auto-generated email. DO NOT REPLY.</i>***</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Hello {$name},\n\nYour verification code is: {$code}\n\nThis code will expire in 10 minutes.\n\nIf you did not request this code, please ignore this email.\n\n- Luxe Haven Hotel";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("2FA Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Verify 2FA code
 * @param string $inputCode User's input code
 * @param string $storedCode Code stored in database
 * @param string $expiry Expiry timestamp
 * @return bool
 */
function verifyEmail2FACode($inputCode, $storedCode, $expiry)
{
    // Clean input
    $inputCode = trim($inputCode);
    $storedCode = trim($storedCode);

    // Check if code matches
    if ($inputCode !== $storedCode) {
        return false;
    }

    // Check if code has expired
    $currentTime = new DateTime();
    $expiryTime = new DateTime($expiry);

    if ($currentTime > $expiryTime) {
        return false;
    }

    return true;
}

/**
 * Store 2FA code in database
 * @param object $mysqli Database connection
 * @param int $userId User ID
 * @param string $code 2FA code
 * @return bool
 */
function store2FACode($mysqli, $userId, $code)
{
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $query = "UPDATE clients SET two_fa_code = ?, two_fa_expiry = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssi', $code, $expiry, $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Clear 2FA code from database
 * @param object $mysqli Database connection
 * @param int $userId User ID
 * @return bool
 */
function clear2FACode($mysqli, $userId)
{
    $query = "UPDATE clients SET two_fa_code = NULL, two_fa_expiry = NULL WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
?>