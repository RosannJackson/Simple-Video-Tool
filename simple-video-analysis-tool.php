<?php
/*
Plugin Name: Simple Video Analysis Tool
Description: Easy video analysis and management
Version: 1.0
*/

// Prevent direct access
if (!defined('ABSPATH')) exit;

class SimpleVideoAnalysisTool {
    public function __construct() {
        // Hook into WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('video_tool', array($this, 'display_video_tool'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function enqueue_scripts() {
        // Enqueue necessary styles
        wp_enqueue_style(
            'video-tool-style',
            plugins_url('css/video-tool.css', __FILE__),
            array(),
            '1.0'
        );

        // Enqueue necessary scripts
        wp_enqueue_script(
            'video-tool-script',
            plugins_url('js/video-tool.js', __FILE__),
            array('jquery'),
            '1.0',
            true
        );

        // Pass variables to JavaScript
        wp_localize_script(
            'video-tool-script',
            'videoToolAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('video-tool-nonce')
            )
        );
    }

    public function display_video_tool() {
        ob_start();
        ?>
        <div class="video-analysis-tool">
            <!-- Upload Form -->
            <div class="upload-section">
                <h3>Upload Video</h3>
                <form id="video-upload-form" method="post" enctype="multipart/form-data">
                    <input type="file" name="video_file" accept="video/*" required>
                    <button type="submit" class="upload-button">Upload Video</button>
                </form>
            </div>

            <!-- YouTube URL Form -->
            <div class="youtube-section">
                <h3>Or Add YouTube URL</h3>
                <form id="youtube-form">
                    <input type="url" name="youtube_url" placeholder="Paste YouTube URL here">
                    <button type="submit">Add Video</button>
                </form>
            </div>

            <!-- Video List -->
            <div id="video-list">
                <!-- Videos will be displayed here via JavaScript -->
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function add_admin_menu() {
        add_menu_page(
            'Video Tool Settings',
            'Video Tool',
            'manage_options',
            'video-tool-settings',
            array($this, 'settings_page'),
            'dashicons-video-alt3'
        );
    }

    public function register_settings() {
        register_setting('video-tool-settings', 'video_tool_options');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Video Tool Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('video-tool-settings');
                do_settings_sections('video-tool-settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th>Maximum Upload Size</th>
                        <td>
                            <input type="number" name="video_tool_options[max_size]" 
                                   value="<?php echo esc_attr(get_option('video_tool_options')['max_size'] ?? '500'); ?>">
                            <p class="description">Maximum video size in MB</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the plugin
$simple_video_tool = new SimpleVideoAnalysisTool();

// Handle AJAX upload
add_action('wp_ajax_upload_video', 'handle_video_upload');
function handle_video_upload() {
    check_ajax_referer('video-tool-nonce', 'nonce');
    
    if (!current_user_can('upload_files')) {
        wp_send_json_error('Permission denied');
    }

    $upload = wp_handle_upload($_FILES['video'], array('test_form' => false));
    
    if ($upload && !isset($upload['error'])) {
        wp_send_json_success($upload);
    } else {
        wp_send_json_error($upload['error']);
    }
}
