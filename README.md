=== WP Admin Captcha ===
Contributors: yourname
Tags: captcha, recaptcha, security, login, admin
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple and lightweight plugin that adds Google reCAPTCHA v2 to the WordPress login screen to protect against brute-force attacks and bots.

== Description ==

WP Admin Captcha is a straightforward security plugin designed to protect your WordPress login page (`wp-login.php`). It integrates Google reCAPTCHA v2 (the "I'm not a robot" checkbox) directly into the login form. 

By verifying the captcha response with Google's servers before authenticating the user, it effectively blocks automated bots and brute-force login attempts without adding unnecessary bloat to your website.

Key Features:
* Lightweight code with minimal impact on site performance.
* Simple settings page to input Site Key and Secret Key.
* Safely skips verification if keys are not configured (prevents locking yourself out).
* Uses native WordPress HTTP API for secure external requests.

== Installation ==

1. Upload the `wp-admin-captcha` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings > WP Admin Captcha**.
4. Log in to the [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin).
5. Create a new reCAPTCHA v2 (Checkbox) property for your domain.
6. Copy the **Site Key** and **Secret Key** and paste them into the plugin's settings page.
7. Save changes.
8. Log out and visit your login page to see the captcha in action.

== Frequently Asked Questions ==

= Does it support reCAPTCHA v3? =
* No, this plugin is specifically designed for reCAPTCHA v2 (the interactive checkbox).

= What happens if I input the wrong keys? =
* If you input the wrong keys, the captcha will fail to load or verify. If you get locked out of your site, simply connect to your server via FTP, navigate to `/wp-content/plugins/`, and rename or delete the `wp-admin-captcha` folder to deactivate the plugin and regain access.

== Changelog ==

= 1.0.0 =
* Initial release. Added settings page, login form integration, and authentication filter.
