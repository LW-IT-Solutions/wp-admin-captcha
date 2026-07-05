<?php
/**
 * Plugin Name: WP Admin Captcha
 * Description: A simple plugin that adds Google reCAPTCHA v2 to the WordPress login screen to prevent brute-force attacks.
 * Version: 1.0.0
 * Author: LW IT Solutions - LukasWojcik.com
 * Text Domain: wp-admin-captcha
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// 1. Add Settings Menu
add_action( 'admin_menu', 'wp_admin_captcha_menu' );
function wp_admin_captcha_menu() {
    add_options_page( 
        'WP Admin Captcha Settings', 
        'WP Admin Captcha', 
        'manage_options', 
        'wp-admin-captcha', 
        'wp_admin_captcha_options_page' 
    );
}

// 2. Register Settings
add_action( 'admin_init', 'wp_admin_captcha_settings' );
function wp_admin_captcha_settings() {
    register_setting( 'wp_admin_captcha_group', 'wp_admin_captcha_site_key', 'sanitize_text_field' );
    register_setting( 'wp_admin_captcha_group', 'wp_admin_captcha_secret_key', 'sanitize_text_field' );
}

// 3. Settings Page HTML
function wp_admin_captcha_options_page() {
    ?>
    <div class="wrap">
        <h1>WP Admin Captcha Settings</h1>
        <p>Please enter your Google reCAPTCHA v2 keys below. You can get them from the <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin Console</a>.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'wp_admin_captcha_group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Site Key</th>
                    <td><input type="text" name="wp_admin_captcha_site_key" value="<?php echo esc_attr( get_option('wp_admin_captcha_site_key') ); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Secret Key</th>
                    <td><input type="text" name="wp_admin_captcha_secret_key" value="<?php echo esc_attr( get_option('wp_admin_captcha_secret_key') ); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// 4. Enqueue reCAPTCHA script on login page
add_action( 'login_enqueue_scripts', 'wp_admin_captcha_enqueue_scripts' );
function wp_admin_captcha_enqueue_scripts() {
    $site_key = get_option( 'wp_admin_captcha_site_key' );
    if ( $site_key ) {
        wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
    }
}

// 5. Display reCAPTCHA widget in the login form
add_action( 'login_form', 'wp_admin_captcha_display_widget' );
function wp_admin_captcha_display_widget() {
    $site_key = get_option( 'wp_admin_captcha_site_key' );
    if ( $site_key ) {
        echo '<div class="g-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '" style="margin-bottom: 15px;"></div>';
    }
}

// 6. Verify reCAPTCHA during authentication
add_filter( 'wp_authenticate_user', 'wp_admin_captcha_verify', 10, 2 );
function wp_admin_captcha_verify( $user, $password ) {
    // If there is already an error (e.g., wrong password), return it
    if ( is_wp_error( $user ) ) {
        return $user;
    }

    $secret_key = get_option( 'wp_admin_captcha_secret_key' );
    
    // If no secret key is saved, skip verification so we don't lock out the admin
    if ( empty( $secret_key ) ) {
        return $user;
    }

    // Check if the captcha was checked
    if ( ! isset( $_POST['g-recaptcha-response'] ) || empty( $_POST['g-recaptcha-response'] ) ) {
        return new WP_Error( 'empty_captcha', '<strong>ERROR</strong>: Please check the reCAPTCHA box.' );
    }

    $response = sanitize_text_field( $_POST['g-recaptcha-response'] );
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    
    // Send POST request to Google
    $request = wp_remote_post( $verify_url, array(
        'body' => array(
            'secret'   => $secret_key,
            'response' => $response,
            'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : ''
        )
    ) );

    // Handle connection errors
    if ( is_wp_error( $request ) ) {
        return new WP_Error( 'captcha_api_error', '<strong>ERROR</strong>: Unable to reach Google reCAPTCHA API.' );
    }

    $body = wp_remote_retrieve_body( $request );
    $result = json_decode( $body );

    // If Google says the captcha is invalid, block the login
    if ( ! $result || ! $result->success ) {
        return new WP_Error( 'invalid_captcha', '<strong>ERROR</strong>: reCAPTCHA verification failed. Please try again.' );
    }

    // Everything is fine, return the user object to proceed with login
    return $user;
}
