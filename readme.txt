=== Muut ===
Contributors: PaulHughes01, tipiirai, jannelehtinen
Tags: forum, commenting, realtime, discussion
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 3.0
License: MIT
License URI: https://github.com/moot/wordpress/blob/master/LICENSE.txt

== Description ==

Whether you’re setting up for the first time or have just updated to the new version of the plugin, you’re going to love what we’ve done to make implementing Muut in your WordPress website easy and flexible.

* Unified system for both forums and commenting. Same users and design
* Full featured forums makes your WordPress site conversational
* Flat or threaded commenting for small or big topics
* Skinnable style the discussion directly from the WordPress CSS editor
* Realtime. No page reloads – posts, replies, likes and users appear in realtime
* Focus on content. Text focused, uncluttered and linear user interface
* Single Sign-On. Use the WordPress login, users and avatars
* Seach engine optimized. Improve your ranking with user generated content
* Spam filtering, email notifications and 20+ different language versions

You can find more information about Muut at our [website](https://muut.com) and read the full [plugin documentation](https://muut.com/docs/wordpress.html).

== Installation ==

In order to install the plugin, from your website administration visit *Plugins > Add New*, search for "Muut" and click *Install Now*. You can also download the zip here and upload to your website via the administration uploader or unpack it and upload the `muut` directory to your `wp-content/plugins/` directory. Once that is completed, activate the plugin and visit the *Muut* navigation section on the administration menu to the left. You can enter your forum name, or visit the Muut [setup](https://muut.com/setup/) page to create a new forum, and then enter that forum's name in the plugin.

Once that is complete, you can edit the full plugin settings and visit the post and page editors to implement the plugin with the Muut panel; if you do not see the Muut panel, make sure that it is enabled in the editor *Screen Options*. On the regular post editor, the panel will only show if the "Use Muut for post commenting" setting is enabled. On the page editor, the panel is always available for the various types of embeds available.

== Frequently Asked Questions ==

= Can I still use WordPress commenting if I only want to use the main forum functionality? =

Absolutely. You simply can deactivate the "Use Muut for post commenting" Muut setting in the Muut administration page. Alternatively, you can disable or enable Muut commenting on specific posts by enabling that global setting and then activating/deactivating "Use Muut for commenting" option on a post-by-post basis.

= Can I embed a Muut channel with content surrounding it, before and after? =

You can, although we are planning to make it easier in a future release. For now, the best way is to use the old shortcode ([muut path="/the/channel/path"]) in the content editor.

== Screenshots ==

1. A Muut forum
2. Muut post commenting
3. Muut settings page
4. Muut post/page editor panel

== Upgrade Notice ==

The update to version 3.0 is a major update that enhances the plugin experience in a large way. The update _does_ support all of the earlier plugin functionality (such as the shortcodes), but we recommend updating when you have a little bit of time to experiment and ensure that everything continues to work as expected. We hope you enjoy the new version!

== Changelog ==

= 3.0 =
* Added meta box / panel to the post editor (and page editor) that controls the Muut embeds for that post, including Muut commenting.
* Deprecated shortcodes in favor of the added meta box.
* Added some options for embedding functionality to the various embed types.
* For posts using Muut commenting, a comment count for that post is fetched from the Muut API.
* Allowing search engines to index Muut content at the site’s domain now built in for Apache (can be disabled).
* Tied Muut commenting tighter with WordPress’s default commenting so that many of the WordPress commenting settings (like allowing comments on future articles) directly affect Muut commenting.
* Ships with default templates for Commenting overrides and the main Forum Page. Both templates can be overridden in a muut directory in the active theme root.
* Added a bunch of helpful template tags so that websites can, for example, add extra customizations on their own page templates.
* Powered by many more actions and filters that can be used for extra customizations.
* Modified and standardized Muut terminology; what was called a “sub-forum” or “category” is now known as a Channel.
* Moved the administrative settings to its own admin menu item “Muut” (rather than a sub-menu item under Plugins).
* Adds a global constant ‘MUUT_VERSION’ that can be defined as a specific Muut version if an older version should be used (the plugin defaults to the latest version).