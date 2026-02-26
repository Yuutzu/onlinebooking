<?php
/**
 * Secure File Upload Handler Class
 * Provides comprehensive validation for file uploads including MIME type checking,
 * file extension validation, size limits, and secure filename generation
 */

class FileUploadHandler
{

    // Allowed MIME types for different file categories
    const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => ['jpg', 'jpeg', 'jpe'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'image/bmp' => ['bmp']
    ];

    // Maximum file sizes (in bytes)
    const MAX_FILE_SIZE = 5242880; // 5MB
    const MAX_IMAGE_SIZE = 2097152; // 2MB

    // Upload directory
    private $uploadDir;
    private $allowedMimes;
    private $maxFileSize;

    /**
     * Constructor
     * @param string $uploadDir Directory to store uploaded files
     * @param array $allowedMimes Optional custom allowed MIME types
     * @param int $maxFileSize Optional custom max file size
     */
    public function __construct($uploadDir = '../dist/img/', $allowedMimes = null, $maxFileSize = null)
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->allowedMimes = $allowedMimes ?? self::ALLOWED_IMAGE_MIMES;
        $this->maxFileSize = $maxFileSize ?? self::MAX_IMAGE_SIZE;

        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
            // Create .htaccess to prevent PHP execution in upload directory
            $this->createHtaccess();
        }
    }

    /**
     * Create .htaccess file to prevent script execution
     */
    private function createHtaccess()
    {
        $htaccess = $this->uploadDir . '.htaccess';
        if (!file_exists($htaccess)) {
            $content = "# Prevent execution of scripts\n";
            $content .= "<FilesMatch \"\\.php$\">\n";
            $content .= "    Deny from all\n";
            $content .= "</FilesMatch>\n";
            $content .= "# Also block common executable extensions\n";
            $content .= "<FilesMatch \"\\.exe$|.sh$|.bat$|.com$|.phtml$|.shtml$\">\n";
            $content .= "    Deny from all\n";
            $content .= "</FilesMatch>\n";
            file_put_contents($htaccess, $content);
            chmod($htaccess, 0644);
        }
    }

    /**
     * Validate file upload
     * @param array $file $_FILES array element
     * @param string $category Optional category for size limits
     * @return array ['success' => bool, 'message' => string, 'filename' => string]
     */
    public function validate($file, $category = 'image')
    {
        // Check if file was uploaded with no errors
        if (!isset($file['error']) || is_array($file['error'])) {
            return [
                'success' => false,
                'message' => 'Invalid file upload'
            ];
        }

        // Check upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($file['error']);
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }

        // Check if file is empty
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'No valid file uploaded'
            ];
        }

        // Check file size
        if ($file['size'] <= 0 || $file['size'] > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 2);
            return [
                'success' => false,
                'message' => "File size must not exceed {$maxSizeMB}MB"
            ];
        }

        // Get file extension from original name
        $originalName = basename($file['name']);
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate file extension
        $extensionValid = false;
        foreach ($this->allowedMimes as $mime => $extensions) {
            if (in_array($fileExtension, $extensions)) {
                $extensionValid = true;
                break;
            }
        }

        if (!$extensionValid) {
            $allowedExts = implode(', ', array_merge(...array_values($this->allowedMimes)));
            return [
                'success' => false,
                'message' => "File extension not allowed. Allowed: {$allowedExts}"
            ];
        }

        // Verify actual MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Check if detected MIME type is allowed
        if (!array_key_exists($detectedMime, $this->allowedMimes)) {
            return [
                'success' => false,
                'message' => 'File content does not match allowed file types'
            ];
        }

        // Verify that the file extension matches the detected MIME type
        $allowedExtensions = $this->allowedMimes[$detectedMime];
        if (!in_array($fileExtension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'File extension does not match file content'
            ];
        }

        // Generate secure filename
        $secureFilename = $this->generateSecureFilename($fileExtension);

        return [
            'success' => true,
            'message' => 'File validation passed',
            'filename' => $secureFilename,
            'mime_type' => $detectedMime
        ];
    }

    /**
     * Upload and save file
     * @param array $file $_FILES array element
     * @param string $category Optional category for size limits
     * @return array ['success' => bool, 'message' => string, 'filename' => string, 'path' => string]
     */
    public function upload($file, $category = 'image')
    {
        // Validate file
        $validation = $this->validate($file, $category);

        if (!$validation['success']) {
            return $validation;
        }

        $secureFilename = $validation['filename'];
        $targetPath = $this->uploadDir . $secureFilename;

        // Move uploaded file to target directory
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => false,
                'message' => 'Failed to save uploaded file'
            ];
        }

        // Set strict file permissions
        chmod($targetPath, 0644);

        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'filename' => $secureFilename,
            'path' => $targetPath
        ];
    }

    /**
     * Generate cryptographically secure filename
     * @param string $extension Original file extension
     * @return string Secure filename
     */
    private function generateSecureFilename($extension)
    {
        // Generate random filename using cryptographically secure random bytes
        $randomName = bin2hex(random_bytes(16));
        $timestamp = time();
        $filename = $timestamp . '_' . $randomName . '.' . strtolower($extension);

        return $filename;
    }

    /**
     * Get human-readable upload error message
     * @param int $errorCode Upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File size exceeds php.ini limit',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Temporary directory not found',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Delete uploaded file
     * @param string $filename Filename to delete
     * @return bool Success status
     */
    public function deleteFile($filename)
    {
        // Prevent path traversal attacks
        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return false;
        }

        $filePath = $this->uploadDir . basename($filename);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
?>