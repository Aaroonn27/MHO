<?php
/**
 * Image Helper Functions
 * Functions to help with database-stored images
 */

/**
 * Get base64 encoded image data URL from database blob
 * 
 * @param mixed $image_data The binary image data from database
 * @param string $image_type The MIME type (e.g., 'image/jpeg')
 * @return string|null Returns data URL or null if no image
 */
function get_image_data_url($image_data, $image_type) {
    if (empty($image_data) || empty($image_type)) {
        return null;
    }
    
    return 'data:' . htmlspecialchars($image_type) . ';base64,' . base64_encode($image_data);
}

/**
 * Display an announcement image
 * 
 * @param array $announcement The announcement array with image_data and image_type
 * @param string $class Optional CSS class for the img tag
 * @param string $alt Optional alt text
 */
function display_announcement_image($announcement, $class = '', $alt = 'Announcement Image') {
    if (!empty($announcement['image_data']) && !empty($announcement['image_type'])) {
        $data_url = get_image_data_url($announcement['image_data'], $announcement['image_type']);
        echo '<img src="' . $data_url . '" alt="' . htmlspecialchars($alt) . '" class="' . htmlspecialchars($class) . '">';
    }
}

/**
 * Check if announcement has an image
 * 
 * @param array $announcement The announcement array
 * @return bool
 */
function has_announcement_image($announcement) {
    return !empty($announcement['image_data']) && !empty($announcement['image_type']);
}
?>