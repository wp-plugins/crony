=== Crony Cronjob Manager ===
Contributors: sc0ttkclark
Donate link: http://www.scottkclark.com/
Tags: cron, wp_cron, cronjob, cron job, automatic
Requires at least: 2.9
Tested up to: 3.0
Stable tag: 0.1

THIS IS A BETA VERSION - Currently in development - Create and Manage Cronjobs in WP by running Scripts, Functions, and/or PHP code. This plugin utilizes the wp_cron API.

== Description ==

**THIS IS A BETA VERSION - Currently in development**

**OFFICIAL SUPPORT** - Crony Cronjob Manager - Support Forums: http://www.scottkclark.com/forums/crony-cronjob-manager/

Create and Manage Cronjobs in WP by running Scripts, Functions, and/or PHP code. This plugin utilizes the wp_cron API.

All you do is install the plugin, schedule your Scripts / Functions / PHP code to run at a specific interval, and live your life!

== Frequently Asked Questions ==

**What does wp_cron() do?**

As you receive visitors on your site, WordPress checks your database to see if anything is scheduled to run. If you have a wp_cron() function scheduled ever 12 hours, then the very first visitor 12+ hours from the last scheduled run of that function will trigger the function to run in the background. The Cronjob (or Cron Job) sends a request to run cron through HTTP request that doesn't halt page loading for the visitor.

**How is wp_cron() different from Server configured Cronjobs?**

Cronjobs configured on a server run on their intervals automatically, while wp_cron() runs only after being triggered from a visitor to your site.

== Changelog ==

= 0.1 =
* First official release to the public as a plugin

== Upgrade Notice ==

= 0.1 =
You aren't using the real plugin, upgrade and you enjoy what you originally downloaded this for!

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Official Support ==

Crony Cronjob Manager - Support Forums: http://www.scottkclark.com/forums/crony-cronjob-manager/

== About the Plugin Author ==

Scott Kingsley Clark from SKC Development -- Scott specializes in WordPress and Pods CMS Framework development using PHP, MySQL, and AJAX. Scott is also a developer on the Pods CMS Framework plugin

== Features ==

= Administration =
* Create and Manage Cronjobs
* Admin.Class.php - A class for plugins to manage data using the WordPress UI appearance

= API =
* Add a job via the Crony API through other plugins

== Roadmap ==

= 0.2 =
* Still deciding