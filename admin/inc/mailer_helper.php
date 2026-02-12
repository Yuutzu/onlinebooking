<?php
/**
 * PHPMailer Helper
 * Provides pre-configured PHPMailer instances using environment variables
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require_once dirname(__DIR__, 2) . '/PHPMailer/PHPMailer/src/Exception.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/PHPMailer/src/SMTP.php';

// Load environment configuration
require_once __DIR__ . '/../config/env.php';

/**
 * Get a configured PHPMailer instance
 * 
 * @param string|null $fromName Optional custom from name
 * @return PHPMailer
 */
function getMailer($fromName = null)
{
    $mail = new PHPMailer(true);

    // SMTP Configuration from environment
    $mail->isSMTP();
    $mail->Host = env('SMTP_HOST', 'smtp.gmail.com');
    $mail->SMTPAuth = true;
    $mail->Username = env('SMTP_USERNAME');
    $mail->Password = env('SMTP_PASSWORD');
    $mail->Port = (int) env('SMTP_PORT', 587);

    // Set encryption
    $smtpSecure = env('SMTP_SECURE', 'tls');
    if ($smtpSecure === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($smtpSecure === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }

    // Set from address
    $fromEmail = env('SMTP_FROM_EMAIL', env('SMTP_USERNAME'));
    $fromNameDefault = env('SMTP_FROM_NAME', 'Luxe Haven Hotel PH Team');
    $mail->setFrom($fromEmail, $fromName ?? $fromNameDefault);

    // Set HTML as default
    $mail->isHTML(true);

    return $mail;
}

/**
 * Send an email using the configured mailer
 * 
 * @param string $toEmail Recipient email
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @param string|null $fromName Optional custom from name
 * @return bool
 * @throws Exception
 */
function sendMail($toEmail, $toName, $subject, $body, $fromName = null)
{
    $mail = getMailer($fromName);
    $mail->addAddress($toEmail, $toName);
    $mail->Subject = $subject;
    $mail->Body = $body;

    return $mail->send();
}
