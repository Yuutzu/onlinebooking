<?php
/**
 * CSRF Token Protection Class
 * Provides methods to generate, validate, and manage CSRF tokens for form submissions
 */

class CSRFToken
{

    const TOKEN_NAME = '_csrf_token';
    const TOKEN_LIFETIME = 3600; // 1 hour in seconds

    /**
     * Generate a new CSRF token
     * @return string Generated token
     */
    public static function generate()
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = [
                'token' => bin2hex(random_bytes(32)),
                'created' => time()
            ];
        }

        // Regenerate if token is expired
        if (time() - $_SESSION[self::TOKEN_NAME]['created'] > self::TOKEN_LIFETIME) {
            $_SESSION[self::TOKEN_NAME] = [
                'token' => bin2hex(random_bytes(32)),
                'created' => time()
            ];
        }

        return $_SESSION[self::TOKEN_NAME]['token'];
    }

    /**
     * Validate CSRF token from request
     * @param string $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate($token)
    {
        // Check if token exists in session
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        // Check if token matches
        $sessionToken = $_SESSION[self::TOKEN_NAME]['token'];
        if (!hash_equals($sessionToken, $token)) {
            return false;
        }

        // Check if token is expired
        if (time() - $_SESSION[self::TOKEN_NAME]['created'] > self::TOKEN_LIFETIME) {
            unset($_SESSION[self::TOKEN_NAME]);
            return false;
        }

        return true;
    }

    /**
     * Get token field HTML for form inclusion
     * @return string HTML input field with CSRF token
     */
    public static function field()
    {
        $token = self::generate();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Verify CSRF token from POST request
     * Outputs error and exits if invalid
     * @return void
     */
    public static function verifyOrDie()
    {
        $token = $_POST[self::TOKEN_NAME] ?? '';

        if (empty($token) || !self::validate($token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token validation failed']));
        }
    }

    /**
     * Get token name for reference
     * @return string Token field name
     */
    public static function getTokenName()
    {
        return self::TOKEN_NAME;
    }
}
?>