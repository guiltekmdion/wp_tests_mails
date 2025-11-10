<?php
/**
 * Plugin Name: WP Domain Security Checker
 * Plugin URI: https://github.com/guiltekmdion/wp_tests_mails
 * Description: Plugin permettant de tester la sécurité d'un nom de domaine (DNS et politiques email) via un shortcode
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wp-domain-security-checker
 * License: GPL v2 or later
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes
define('WPDSC_VERSION', '1.0.0');
define('WPDSC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPDSC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Inclure les fichiers nécessaires
require_once WPDSC_PLUGIN_DIR . 'includes/class-database.php';
require_once WPDSC_PLUGIN_DIR . 'includes/class-dns-checker.php';
require_once WPDSC_PLUGIN_DIR . 'includes/class-email-checker.php';
require_once WPDSC_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once WPDSC_PLUGIN_DIR . 'includes/class-admin.php';
require_once WPDSC_PLUGIN_DIR . 'includes/class-email-notifications.php';

/**
 * Activation du plugin
 */
function wpdsc_activate() {
    $database = new WPDSC_Database();
    $database->create_tables();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wpdsc_activate');

/**
 * Désactivation du plugin
 */
function wpdsc_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wpdsc_deactivate');

/**
 * Initialisation du plugin
 */
function wpdsc_init() {
    // Initialiser le shortcode
    $shortcode = new WPDSC_Shortcode();
    
    // Initialiser l'interface admin
    if (is_admin()) {
        $admin = new WPDSC_Admin();
    }
    
    // Charger les styles et scripts
    add_action('wp_enqueue_scripts', 'wpdsc_enqueue_scripts');
    add_action('admin_enqueue_scripts', 'wpdsc_admin_enqueue_scripts');
}
add_action('plugins_loaded', 'wpdsc_init');

/**
 * Charger les styles et scripts frontend
 */
function wpdsc_enqueue_scripts() {
    wp_enqueue_style('wpdsc-style', WPDSC_PLUGIN_URL . 'assets/css/style.css', array(), WPDSC_VERSION);
    wp_enqueue_script('wpdsc-script', WPDSC_PLUGIN_URL . 'assets/js/script.js', array('jquery'), WPDSC_VERSION, true);
    
    wp_localize_script('wpdsc-script', 'wpdsc_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpdsc_nonce')
    ));
}

/**
 * Charger les styles et scripts admin
 */
function wpdsc_admin_enqueue_scripts($hook) {
    if (strpos($hook, 'domain-security-checker') === false) {
        return;
    }
    
    wp_enqueue_style('wpdsc-admin-style', WPDSC_PLUGIN_URL . 'assets/css/admin-style.css', array(), WPDSC_VERSION);
    wp_enqueue_script('wpdsc-admin-script', WPDSC_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), WPDSC_VERSION, true);
}
