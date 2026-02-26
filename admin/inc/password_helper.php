<?php
/**
 * Password Security Helper Functions
 * - Password hashing with Argon2ID
 * - Secure password reset token generation
 * - Password history tracking
 */

/**
 * Hash a password securely using Argon2ID
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,
        'threads' => 3
    ]);
}

/**
 * Verify a password against a hash
 * @param string $password Plain text password
 * @param string $hash Password hash
 * @return bool True if password matches hash
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Check if a password needs rehashing (upgrades hash algorithm if needed)
 * @param string $hash Password hash
 * @return bool True if hash should be upgraded
 */
function passwordNeedsRehash($hash)
{
    return password_needs_rehash($hash, PASSWORD_ARGON2ID);
}

/**
 * Generate a secure password reset token
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @return string|bool The reset token on success, false on failure
 */
function generatePasswordResetToken($mysqli, $userId)
{
    try {
        // Generate cryptographically secure random token (32 bytes)
        $token = bin2hex(random_bytes(32));

        // Hash the token for storage
        $tokenHash = hash('sha256', $token);

        // Set expiry to 1 hour from now
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token hash in database
        $query = "UPDATE clients SET password_reset_token = ?, password_reset_expiry = ?, password_reset_used = 0 WHERE id = ?";
        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssi', $tokenHash, $expiry, $userId);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? $token : false;
    } catch (Exception $e) {
        error_log("Password reset token generation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Verify a password reset token
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param string $token Plain text token from URL
 * @return bool True if token is valid and not expired
 */
function verifyPasswordResetToken($mysqli, $userId, $token)
{
    try {
        // Hash the provided token
        $tokenHash = hash('sha256', $token);

        // Check if token exists, matches, not expired, and not used
        $query = "SELECT id FROM clients 
                  WHERE id = ? 
                  AND password_reset_token = ? 
                  AND password_reset_expiry > NOW() 
                  AND password_reset_used = 0 
                  LIMIT 1";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('is', $userId, $tokenHash);
        $stmt->execute();
        $stmt->store_result();

        $isValid = $stmt->num_rows > 0;
        $stmt->close();

        return $isValid;
    } catch (Exception $e) {
        error_log("Password reset token verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Complete password reset - update password and invalidate token
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param string $newPassword New plain text password
 * @param string $token Reset token for verification
 * @return bool True on success
 */
function completePasswordReset($mysqli, $userId, $newPassword, $token)
{
    try {
        // Verify token first
        if (!verifyPasswordResetToken($mysqli, $userId, $newPassword)) {
            return false;
        }

        // Hash the new password
        $hashedPassword = hashPassword($newPassword);

        // Get old password for history
        $query = "SELECT client_password FROM clients WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($oldPassword);
        $stmt->fetch();
        $stmt->close();

        // Start transaction
        $mysqli->begin_transaction();

        try {
            // Update password and invalidate token
            $query = "UPDATE clients SET client_password = ?, password_reset_token = NULL, password_reset_expiry = NULL, password_reset_used = 1, password_changed_at = NOW() WHERE id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('si', $hashedPassword, $userId);
            $stmt->execute();
            $stmt->close();

            // Add to password history
            $query = "INSERT INTO password_history (client_id, old_password_hash, changed_by, reason) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $changedBy = "self";
            $reason = "password_reset";
            $stmt->bind_param('isss', $userId, $oldPassword, $changedBy, $reason);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $mysqli->commit();

            return true;
        } catch (Exception $e) {
            $mysqli->rollback();
            error_log("Password reset error: " . $e->getMessage());
            return false;
        }
    } catch (Exception $e) {
        error_log("Password reset completion error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update password with history tracking
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param string $currentPassword Current password for verification
 * @param string $newPassword New password
 * @return array ['success' => bool, 'message' => string]
 */
function updatePassword($mysqli, $userId, $currentPassword, $newPassword)
{
    try {
        // Get current password hash
        $query = "SELECT client_password FROM clients WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($storedHash);
        $stmt->fetch();
        $stmt->close();

        if (!$storedHash) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Verify current password
        if (!verifyPassword($currentPassword, $storedHash)) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Hash new password
        $newHash = hashPassword($newPassword);

        // Start transaction
        $mysqli->begin_transaction();

        try {
            // Update password
            $query = "UPDATE clients SET client_password = ?, password_changed_at = NOW() WHERE id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('si', $newHash, $userId);
            $stmt->execute();
            $stmt->close();

            // Add to password history
            $query = "INSERT INTO password_history (client_id, old_password_hash, changed_by, reason) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $changedBy = "self";
            $reason = "user_update";
            $stmt->bind_param('isss', $userId, $storedHash, $changedBy, $reason);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $mysqli->commit();

            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (Exception $e) {
            $mysqli->rollback();
            error_log("Password update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating password'];
        }
    } catch (Exception $e) {
        error_log("Password update error: " . $e->getMessage());
        return ['success' => false, 'message' => 'System error'];
    }
}

/**
 * Invalidate all password reset tokens for a user
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @return bool True on success
 */
function invalidateResetTokens($mysqli, $userId)
{
    $query = "UPDATE clients SET password_reset_token = NULL, password_reset_expiry = NULL, password_reset_used = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Migrate plain text password to hash
 * Use this for one-time migration of existing passwords
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param string $plainPassword Plain text password
 * @return bool True on success
 */
function migratePasswordHash($mysqli, $userId, $plainPassword)
{
    $hashedPassword = hashPassword($plainPassword);

    $query = "UPDATE clients SET client_password = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('si', $hashedPassword, $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

?>