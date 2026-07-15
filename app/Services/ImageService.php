<?php

/**
 * Image Service
 *
 * Handles image upload, processing, and deletion.
 *
 * @package ProyectoBase
 * @subpackage App\Services
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Services;

class ImageService
{
    /**
     * Upload directory path
     * @var string
     */
    private $upload_dir;

    /**
     * Allowed MIME types (server-verified via finfo)
     * @var array
     */
    private $allowed_types;

    /**
     * Allowed file extensions (whitelist)
     * @var array
     */
    private $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Maximum allowed file size in bytes (5 MB)
     * @var int
     */
    private $max_size = 5242880;

    /**
     * @param string $upload_dir  Absolute path to the upload directory
     */
    public function __construct($upload_dir)
    {
        $this->upload_dir   = $upload_dir;
        $this->allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];

        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    /**
     * Processes an uploaded image file.
     *
     * @param array $image  $_FILES entry
     * @return string|false  Filename on success, false on failure
     */
    public function processImage($image)
    {
        if (!$image || $image['error'] != 0) {
            return false;
        }

        // Server-side MIME detection — never trust $_FILES['type'] (client-controlled)
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);

        if (!in_array($mimeType, $this->allowed_types, true)) {
            return false;
        }

        if ($image['size'] > $this->max_size) {
            return false;
        }

        $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_extensions, true)) {
            return false;
        }

        $nameWithoutExt  = pathinfo($image['name'], PATHINFO_FILENAME);
        $nameWithoutExt  = preg_replace('/[^a-zA-Z0-9_-]/', '', $nameWithoutExt);
        $filename        = uniqid() . '_' . $nameWithoutExt . '.' . $extension;
        $destination     = $this->upload_dir . $filename;

        if (move_uploaded_file($image['tmp_name'], $destination)) {
            return $filename;
        }

        return false;
    }

    /**
     * Deletes an image file. The default image is never deleted.
     *
     * @param string $filename
     * @return bool
     */
    public function deleteImage($filename)
    {
        if (!$filename || $filename === 'user_default.jpg') {
            return true;
        }

        $fullPath = $this->upload_dir . $filename;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return true; // Already gone — treat as success
    }

    /**
     * Resizes an image in-place while maintaining aspect ratio.
     *
     * @param string $imagePath  Absolute path to the image
     * @param int    $width      Target width in pixels
     * @param int    $height     Target height in pixels
     * @return bool
     */
    public function resizeImage($imagePath, $width = 200, $height = 200)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $info = getimagesize($imagePath);
        if (!$info) {
            return false;
        }

        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $src = imagecreatefromwebp($imagePath);
                break;
            default:
                return false;
        }

        $origWidth  = imagesx($src);
        $origHeight = imagesy($src);
        $ratio      = $origWidth / $origHeight;

        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $resized = imagecreatetruecolor($width, $height);

        if ($info[2] === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        $result = false;
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($resized, $imagePath, 90);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($resized, $imagePath, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($resized, $imagePath);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($resized, $imagePath, 90);
                break;
        }

        imagedestroy($src);
        imagedestroy($resized);

        return $result;
    }
}
