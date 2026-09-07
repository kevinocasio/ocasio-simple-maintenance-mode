=== Ocasio Simple Maintenance Mode ===
Contributors: ocas
Tags: maintenance mode, coming soon, under construction, splash page, 503
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A clean, modern maintenance mode splash page. Take your site offline for visitors with one click while admins browse normally.

== Description ==

Need to update your theme, redesign pages, or troubleshoot without confusing visitors?

Ocasio Simple Maintenance Mode gives you a clean, professional splash page with a single toggle switch.

Whenever you turn it on, visitors and search engines receive a proper HTTP 503 status code so your search rankings stay completely safe. Meanwhile, logged-in administrators browse and work on the live site normally.

It features custom headline and description controls, WordPress Media Library logo uploads, and an instant live preview link so you can check your splash page without logging out.

== Installation ==

1. Upload the `ocasio-simple-maintenance-mode` folder to your `/wp-content/plugins/` directory, or install it directly through your WordPress admin screen.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Ocasio Plugins -> Maintenance** in your admin sidebar to turn on maintenance mode and set your text.

== Frequently Asked Questions ==

= Can administrators still browse the site when maintenance mode is active? =
Yes. Anyone logged in with administrator permissions can browse, edit, and test the site normally.

= Does this hurt my Google search rankings? =
No. It sends a standard HTTP 503 Service Unavailable header with a retry-after directive, telling search engines your site is only down temporarily.

= How can I preview the splash page without logging out? =
Click the **Live Preview** button on your settings page to view the exact splash screen in a new tab.

= Can I add my own logo? =
Yes. You can choose any image from your WordPress Media Library or paste an image URL.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Added 1-click maintenance mode toggle with 503 search engine headers.
* Added custom headline, message, and media library logo support.
* Added 1-click live preview for logged-in administrators.
* Integrated into the unified Ocasio Plugins suite dashboard.
