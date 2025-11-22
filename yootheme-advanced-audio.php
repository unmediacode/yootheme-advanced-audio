<?php
/**
 * Plugin Name: YOOtheme Advanced Audio Player
 * Plugin URI: https://github.com/unmediacode/yootheme-advanced-audio
 * Description: Professional audio player for YOOtheme Pro with Howler.js, multiple layouts, and dynamic content support. Developed by Miguel Taboada.
 * Version: 1.0.1
 * Author: Miguel Taboada
 * Author URI: https://github.com/unmediacode
 * License: GPL v2 or later
 * Text Domain: yootheme-advanced-audio
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('YTAA_VERSION', '1.0.1');
define('YTAA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YTAA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Register YOOtheme element
add_action('after_setup_theme', function () {
    // Check if YOOtheme Pro is active
    if (!class_exists('YOOtheme\Application')) {
        return;
    }

    // Get YOOtheme application instance
    $app = \YOOtheme\Application::getInstance();

    // Register element directory
    $app->extend(\YOOtheme\Builder::class, function ($builder) {
        $builder->addTypePath(YTAA_PLUGIN_DIR . 'element/*/element.json');
        $builder->addTypePath(YTAA_PLUGIN_DIR . 'element/element.json');
    });
}, 20);

// Enqueue assets
add_action('wp_enqueue_scripts', function () {
    $css_version = YTAA_VERSION . '.' . filemtime(YTAA_PLUGIN_DIR . 'assets/css/player.css');
    $js_version = YTAA_VERSION . '.' . filemtime(YTAA_PLUGIN_DIR . 'assets/js/player.js');

    wp_enqueue_style(
        'ytaa-player',
        YTAA_PLUGIN_URL . 'assets/css/player.css',
        [],
        $css_version
    );

    // Wavesurfer.js for audio and waveform
    wp_enqueue_script(
        'wavesurfer',
        'https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.min.js',
        [],
        '7.0.0',
        true
    );

    // Color Thief for adaptive colors
    wp_enqueue_script(
        'color-thief',
        'https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.min.js',
        [],
        '2.3.0',
        true
    );

    wp_enqueue_script(
        'ytaa-player',
        YTAA_PLUGIN_URL . 'assets/js/player.js',
        ['wavesurfer', 'color-thief'],
        $js_version,
        true
    );
});

// Admin notice if YOOtheme Pro is not active
add_action('admin_notices', function () {
    if (!class_exists('YOOtheme\Application')) {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>YOOtheme Advanced Audio Player</strong> requires YOOtheme Pro to be installed and activated.';
        echo '</p></div>';
    }
});

// Updater
if (is_admin()) {
    require_once YTAA_PLUGIN_DIR . 'includes/Updater.php';
    new \YOOtheme\AdvancedAudio\Updater(
        'yootheme-advanced-audio',
        YTAA_VERSION,
        'unmediacode',
        'yootheme-advanced-audio'
    );
}
