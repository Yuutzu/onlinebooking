<?php
session_start();
include('../admin/config/config.php');
include('../admin/config/checklogin.php');
include('../admin/inc/email_2fa_helper.php');
require('../admin/inc/alert.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

// Ensure user is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

// Fetch site settings
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();

// Fetch client 2FA status
$query = "SELECT client_name, client_email, two_fa_enabled FROM clients WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_object();
$stmt->close();

// Handle 2FA toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        // Send test code before enabling
        $testCode = generateEmailCode();

        if (sendEmail2FACode($client->client_email, $client->client_name, $testCode)) {
            // Store temporary code for verification
            store2FACode($mysqli, $client_id, $testCode);
            $_SESSION['2fa_setup_pending'] = true;
            $success = "A test verification code has been sent to your email. Please enter it below to enable 2FA.";
        } else {
            $error = "Failed to send test code. Please check your email address and try again.";
        }
    } elseif (isset($_POST['verify_and_enable'])) {
        $inputCode = trim($_POST['verification_code']);

        // Get stored code
        $query = "SELECT two_fa_code, two_fa_expiry FROM clients WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_object();
        $stmt->close();

        if (verifyEmail2FACode($inputCode, $data->two_fa_code, $data->two_fa_expiry)) {
            // Enable 2FA
            $query = "UPDATE clients SET two_fa_enabled = 1, two_fa_code = NULL, two_fa_expiry = NULL WHERE id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $client_id);
            $stmt->execute();
            $stmt->close();

            unset($_SESSION['2fa_setup_pending']);
            alert('success', 'Two-Factor Authentication has been enabled successfully!');
            header("Location: email_2fa_settings.php");
            exit();
        } else {
            $error = "Invalid or expired verification code. Please try again.";
        }
    } elseif (isset($_POST['disable_2fa'])) {
        // Disable 2FA
        $query = "UPDATE clients SET two_fa_enabled = 0, two_fa_code = NULL, two_fa_expiry = NULL WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $client_id);
        $stmt->execute();
        $stmt->close();

        alert('success', 'Two-Factor Authentication has been disabled.');
        header("Location: email_2fa_settings.php");
        exit();
    } elseif (isset($_POST['send_test_code'])) {
        // Send test code
        $testCode = generateEmailCode();

        if (store2FACode($mysqli, $client_id, $testCode) && sendEmail2FACode($client->client_email, $client->client_name, $testCode)) {
            $success = "Test code sent successfully! Check your email.";
        } else {
            $error = "Failed to send test code.";
        }
    }

    // Refresh client data
    $query = "SELECT client_name, client_email, two_fa_enabled FROM clients WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_object();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Settings</title>

    <!-- Favicon -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/png"
            href="../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
    <?php endif; ?>

    <?php require('./inc/links.php'); ?>
    <style>
        /* Page-specific tweaks to complement common.css */
        * {
            box-sizing: border-box;
        }

        .client-main-content {
            padding: 150px 0 20px 0;
            min-height: calc(100vh - 100px);
        }

        .twofa-container {
            width: 100%;
            padding: 0 15px;
            margin: 0 auto;
        }

        .twofa-card {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background-color: #ffffff;
            margin-bottom: 25px;
        }

        .twofa-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .twofa-card .card-body {
            padding: 40px;
        }

        .twofa-page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--mainColor, #4a1c1d);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .twofa-header-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .twofa-description {
            font-size: 16px;
            color: #6c757d;
            line-height: 1.6;
            margin-top: 10px;
        }

        .twofa-status-section {
            background-color: transparent;
            padding: 0;
            border-radius: 0;
            margin: 0 0 25px 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .twofa-status-badge {
            font-size: 13px;
            padding: 6px 14px;
            font-weight: 600;
            border-radius: 20px;
            display: inline-block;
            vertical-align: middle;
        }

        .twofa-helper-box {
            background-color: transparent;
            border-radius: 0;
            border-left: none;
            padding: 0;
            margin: 25px 0;
        }

        .twofa-helper-box p {
            margin-bottom: 12px;
            font-size: 15px;
            line-height: 1.6;
        }

        .twofa-actions-inline {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .twofa-actions-inline button,
        .twofa-actions-inline a {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-primary-twofa {
            background-color: var(--mainColor, #4a1c1d);
            color: white;
            border: none;
        }

        .btn-primary-twofa:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .verification-box {
            background-color: transparent;
            border-radius: 0;
            padding: 0;
            margin: 25px 0;
        }

        .verification-box h6 {
            font-size: 18px;
            font-weight: 700;
            color: var(--mainColor, #4a1c1d);
            margin-bottom: 20px;
        }

        .verification-code-input {
            font-size: 18px;
            letter-spacing: 6px;
            font-weight: 600;
            text-align: center;
            padding: 12px;
        }

        .how-it-works {
            background-color: transparent;
            border-radius: 0;
            padding: 0;
            margin: 25px 0;
        }

        .how-it-works p {
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .faq-section {
            margin-top: 30px;
        }

        .faq-section .card-body {
            padding: 40px;
        }

        .faq-section h6 {
            font-size: 20px;
            font-weight: 700;
            color: var(--mainColor, #4a1c1d);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--mainColor, #4a1c1d);
            padding-bottom: 10px;
        }

        .faq-item {
            margin-bottom: 20px;
            padding: 0;
            background-color: transparent;
            border-radius: 0;
        }

        .faq-item strong {
            color: var(--mainColor, #4a1c1d);
            font-size: 15px;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .back-link a {
            color: var(--mainColor, #4a1c1d);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Tablet (768px and up) */
        @media (min-width: 768px) {
            .twofa-container {
                max-width: 800px;
                padding: 0 20px;
            }

            .twofa-page-title {
                font-size: 32px;
            }

            .client-main-content {
                padding: 120px 15px 30px 15px;
            }

            .twofa-card .card-body {
                padding: 50px;
            }

            .faq-section .card-body {
                padding: 50px;
            }
        }

        /* Medium devices (992px and up) */
        @media (min-width: 992px) {
            .twofa-container {
                max-width: 850px;
            }

            .twofa-page-title {
                font-size: 36px;
            }

            .client-main-content {
                padding: 120px 20px 40px 20px;
            }

            .twofa-card .card-body {
                padding: 50px;
            }

            .faq-section .card-body {
                padding: 50px;
            }

            .twofa-actions-inline {
                gap: 15px;
            }
        }

        /* Large devices (1200px and up) */
        @media (min-width: 1200px) {
            .twofa-container {
                max-width: 900px;
            }

            .client-main-content {
                padding: 120px 20px 50px 20px;
            }
        }

        /* Small devices (480px and below) */
        @media (max-width: 480px) {
            .twofa-page-title {
                font-size: 22px;
                margin-bottom: 8px;
            }

            .twofa-description {
                font-size: 14px;
            }

            .twofa-card .card-body {
                padding: 25px;
            }

            .twofa-status-section {
                padding: 0;
                margin: 0 0 20px 0;
            }

            .twofa-helper-box,
            .verification-box,
            .how-it-works {
                padding: 0;
                margin: 20px 0;
            }

            .twofa-actions-inline {
                flex-direction: column;
                gap: 10px;
                margin-top: 20px;
            }

            .twofa-actions-inline button,
            .twofa-actions-inline a {
                width: 100%;
                padding: 12px;
                text-align: center;
            }

            .verification-code-input {
                font-size: 16px;
                letter-spacing: 4px;
                padding: 10px;
            }

            .verification-box h6,
            .faq-section h6 {
                font-size: 16px;
            }

            .faq-item {
                padding: 0;
                margin-bottom: 15px;
            }

            .back-link a {
                font-size: 13px;
            }

            .client-main-content {
                padding: 100px 10px 15px 10px;
            }
        }

        /* Medium tablets (481px to 767px) */
        @media (min-width: 481px) and (max-width: 767px) {
            .twofa-page-title {
                font-size: 26px;
            }

            .twofa-container {
                max-width: 95%;
                padding: 0 15px;
            }

            .twofa-card .card-body {
                padding: 50px;
            }

            .faq-section .card-body {
                padding: 35px;
            }

            .twofa-actions-inline {
                gap: 12px;
            }

            .twofa-actions-inline button,
            .twofa-actions-inline a {
                flex: 1;
                min-width: 140px;
            }
        }

        /* Landscape mode adjustments */
        @media (max-height: 600px) and (orientation: landscape) {
            .client-main-content {
                padding: 20px 15px;
            }

            .twofa-page-title {
                margin-bottom: 5px;
            }

            .twofa-description {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>


    <?php require('./inc/nav.php'); ?>

    <br>
    <br>



    <main class="client-main-content">

        <div class="twofa-container">
            <div class="card card-register border-0 twofa-card">
                <div class="card-body position-static">

                    <div class="mb-4 twofa-header-section">
                        <h1 class="twofa-page-title">Two-Factor Authentication</h1>
                        <p class="twofa-description">
                            Add an extra layer of security to your account with email verification codes.
                        </p>
                    </div>

                    <div class="twofa-status-section">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <span class="someText"><strong style="font-size: 16px;">Current Status:</strong></span>
                            <?php if ($client->two_fa_enabled): ?>
                                <span class="badge bg-success twofa-status-badge">✓ Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary twofa-status-badge">○ Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger someText"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success someText"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <?php if ($client->two_fa_enabled): ?>
                        <!-- 2FA is enabled -->
                        <div class="twofa-helper-box">
                            <p class="someText mb-2"><strong style="font-size: 16px; color: var(--mainColor, #4a1c1d);">✓
                                    2FA is Active</strong></p>
                            <p class="someText mb-0">
                                Verification codes will be sent to:<br>
                                <strong
                                    style="color: var(--mainColor, #4a1c1d);"><?php echo htmlspecialchars($client->client_email); ?></strong>
                            </p>
                        </div>

                        <form method="POST" class="twofa-actions-inline">
                            <button type="submit" name="send_test_code"
                                class="btn btn-outline-primary btn-primary-twofa someText">
                                📧 Send Test Code
                            </button>
                            <button type="submit" name="disable_2fa" class="btn btn-danger someText"
                                onclick="return confirm('Are you sure you want to disable Two-Factor Authentication?')">
                                🔓 Disable 2FA
                            </button>
                        </form>

                    <?php else: ?>
                        <!-- 2FA is disabled -->
                        <?php if (isset($_SESSION['2fa_setup_pending'])): ?>
                            <!-- Verification step -->
                            <div class="verification-box">
                                <h6>🔐 Verify Your Email</h6>
                                <p class="someText mb-3">
                                    Enter the 6-digit code sent to<br>
                                    <strong><?php echo htmlspecialchars($client->client_email); ?></strong>
                                </p>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label someText fw-600 mb-2">Verification Code</label>
                                        <input type="text" name="verification_code"
                                            class="form-control verification-code-input someText shadow-none"
                                            placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus
                                            inputmode="numeric">
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" name="verify_and_enable"
                                            class="btn btn-success btnAddCategory someText flex-grow-1">
                                            ✓ Verify & Enable 2FA
                                        </button>
                                        <a href="email_2fa_settings.php" class="btn btn-outline-secondary someText">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <!-- Enable 2FA -->
                            <div class="how-it-works">
                                <p class="someText mb-3 fw-bold" style="font-size: 16px; color: var(--mainColor, #4a1c1d);">How
                                    it works:</p>
                                <p class="someText">📌 Step 1: Click "Enable 2FA" below</p>
                                <p class="someText">📧 Step 2: We'll send a test code to your email</p>
                                <p class="someText">✓ Step 3: Enter the code to activate 2FA</p>
                                <p class="someText mb-0">🔐 Step 4: You'll need a code each time you log in</p>
                            </div>

                            <form method="POST">
                                <button type="submit" name="enable_2fa"
                                    class="btn btn-primary btnAddCategory someText w-100 fw-600"
                                    style="padding: 12px; font-size: 16px;">
                                    🔒 Enable Two-Factor Authentication
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>

            <!-- FAQ Section -->
            <div class="card card-register border-0 twofa-card faq-section">
                <div class="card-body">
                    <h6>❓ Frequently Asked Questions</h6>

                    <div class="faq-item">
                        <p class="someText mb-2"><strong>Why should I enable 2FA?</strong></p>
                        <p class="someText" style="color: #6c757d; margin-bottom: 0;">
                            2FA adds extra security. Even if someone knows your password, they can't access your account
                            without the email code.
                        </p>
                    </div>

                    <div class="faq-item">
                        <p class="someText mb-2"><strong>What if I don't receive the email?</strong></p>
                        <p class="someText" style="color: #6c757d; margin-bottom: 0;">
                            Check your spam/junk folder. Verify that
                            <strong><?php echo htmlspecialchars($client->client_email); ?></strong>
                            is correct in your profile.
                        </p>
                    </div>

                    <div class="faq-item mb-0">
                        <p class="someText mb-2"><strong>Can I disable 2FA later?</strong></p>
                        <p class="someText" style="color: #6c757d; margin-bottom: 0;">
                            Yes, you can disable 2FA anytime from this page.
                        </p>
                    </div>
                </div>
            </div>

            <div class="back-link">
                <a href="profile.php">← Back to Profile</a>
            </div>
        </div>

    </main>

    <script>
        // Only allow numbers in verification code input
        document.querySelectorAll('input[name="verification_code"]').forEach(input => {
            input.addEventListener('keypress', function (e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Auto-focus to next field or submit on complete input
            input.addEventListener('input', function (e) {
                if (this.value.length === 6) {
                    // Visual feedback that code is complete
                    this.classList.add('is-valid');
                }
            });
        });

        // Responsive adjustments for different screen sizes
        function adjustLayout() {
            const container = document.querySelector('.twofa-container');
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                container.style.padding = '0 10px';
            }
        }

        // Run on load and resize
        window.addEventListener('load', adjustLayout);
        window.addEventListener('resize', adjustLayout);
    </script>
</body>

</html>