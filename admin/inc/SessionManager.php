<?php
/**
 * Secure Session Manager Class
 * Provides secure session handling with regeneration, timeout, and security checks
 */

class SessionManager
{

    const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    const SESSION_REGENERATE_INTERVAL = 600; // 10 minutes

    /**
     * Initialize secure session
     */
    public static function init()
    {
        // Set secure session configuration
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1'); // Only on HTTPS in production
        ini_set('session.cookie_samesite', 'Strict');

        // Set session timeout
        ini_set('session.gc_maxlifetime', self::SESSION_TIMEOUT);

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Validate user session
     * @return bool True if session is valid, false otherwise
     */
    public static function validate()
    {
        // Check if session has required fields
        if (!isset($_SESSION['session_started'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
                self::destroy();
                return false;
            }
        }

        // Check IP address consistency (optional but recommended)
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== self::getClientIP()) {
                // IP changed - possible session hijacking
                self::destroy();
                return false;
            }
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();

        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > self::SESSION_REGENERATE_INTERVAL) {
            self::regenerate();
        }

        return true;
    }

    /**
     * Create new user session
     * @param int $user_id User ID
     * @param string $username Username
     * @param array $additional_data Additional session data
     */
    public static function create($user_id, $username, $additional_data = [])
    {
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Initialize session data
        $_SESSION['session_started'] = time();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regeneration'] = time();
        $_SESSION['ip_address'] = self::getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Add any additional data
        foreach ($additional_data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * Destroy session completely
     */
    public static function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();

            // Clear session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
        }
    }

    /**
     * Get client IP address
     * @return string Client IP
     */
    private static function getClientIP()
    {
        // Handle various proxy scenarios
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
    }

    /**
     * Check if user is logged in
     * @return bool True if user is logged in
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && self::validate();
    }

    /**
     * Get remaining session time in seconds
     * @return int Remaining time
     */
    public static function getRemainingTime()
    {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }

        $elapsed = time() - $_SESSION['last_activity'];
        return max(0, self::SESSION_TIMEOUT - $elapsed);
    }
}
?>