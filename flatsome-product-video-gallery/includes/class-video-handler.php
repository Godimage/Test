<?php
/**
 * Video Handler Class
 * Handles video upload, processing, and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class FPVG_Video_Handler {
    
    public function __construct() {
        add_action('wp_ajax_fpvg_upload_video', array($this, 'handle_video_upload'));
        add_action('wp_ajax_fpvg_delete_video', array($this, 'handle_video_delete'));
        add_action('wp_ajax_fpvg_generate_thumbnail', array($this, 'generate_video_thumbnail'));
    }
    
    /**
     * Handle video upload via AJAX
     */
    public function handle_video_upload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'fpvg_upload_video')) {
            wp_die('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_products')) {
            wp_die('Insufficient permissions');
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['video_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['video_file'];
        
        // Validate file
        $validation = $this->validate_video_file($file);
        if (is_wp_error($validation)) {
            wp_send_json_error($validation->get_error_message());
        }
        
        // Upload file
        $upload_result = $this->upload_video_file($file);
        if (is_wp_error($upload_result)) {
            wp_send_json_error($upload_result->get_error_message());
        }
        
        wp_send_json_success($upload_result);
    }
    
    /**
     * Validate video file
     */
    private function validate_video_file($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'File upload failed');
        }
        
        // Check file size
        $max_size = get_option('fpvg_video_max_size', 50) * 1024 * 1024; // Convert MB to bytes
        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', sprintf('File size exceeds %dMB limit', $max_size / (1024 * 1024)));
        }
        
        // Check file type
        $allowed_types = get_option('fpvg_allowed_formats', array('mp4', 'webm', 'ogg'));
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            return new WP_Error('invalid_file_type', 'Invalid file type. Allowed: ' . implode(', ', $allowed_types));
        }
        
        // Validate MIME type
        $allowed_mimes = array(
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg'
        );
        
        $file_mime = wp_check_filetype($file['name']);
        if (!isset($allowed_mimes[$file_extension]) || $file_mime['type'] !== $allowed_mimes[$file_extension]) {
            return new WP_Error('invalid_mime_type', 'Invalid file MIME type');
        }
        
        return true;
    }
    
    /**
     * Upload video file
     */
    private function upload_video_file($file) {
        // Set up upload directory
        $upload_dir = wp_upload_dir();
        $video_dir = $upload_dir['basedir'] . '/product-videos';
        $video_url = $upload_dir['baseurl'] . '/product-videos';
        
        // Create directory if it doesn't exist
        if (!file_exists($video_dir)) {
            wp_mkdir_p($video_dir);
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('video_') . '.' . $file_extension;
        $file_path = $video_dir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            return new WP_Error('upload_failed', 'Failed to move uploaded file');
        }
        
        // Return file information
        return array(
            'filename' => $filename,
            'url' => $video_url . '/' . $filename,
            'path' => $file_path,
            'size' => filesize($file_path),
            'type' => $file_extension
        );
    }
    
    /**
     * Handle video deletion
     */
    public function handle_video_delete() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'fpvg_delete_video')) {
            wp_die('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_products')) {
            wp_die('Insufficient permissions');
        }
        
        $video_url = sanitize_url($_POST['video_url']);
        
        // Extract filename from URL
        $filename = basename($video_url);
        
        // Delete file
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/product-videos/' . $filename;
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        wp_send_json_success('Video deleted successfully');
    }
    
    /**
     * Generate video thumbnail (placeholder for future implementation)
     */
    public function generate_video_thumbnail() {
        // This would require FFmpeg or similar video processing library
        // For now, we'll use the browser's video element to capture a frame
        wp_send_json_success('Thumbnail generation not implemented yet');
    }
    
    /**
     * Get video metadata
     */
    public static function get_video_metadata($video_url) {
        // Basic metadata extraction
        $filename = basename($video_url);
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/product-videos/' . $filename;
        
        $metadata = array(
            'filename' => $filename,
            'url' => $video_url,
            'type' => $file_extension,
            'size' => file_exists($file_path) ? filesize($file_path) : 0
        );
        
        return $metadata;
    }
}

