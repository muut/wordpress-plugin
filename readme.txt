=== Muut – Commenting and Forums Re-Imagined ===
Contributors: PaulHughes01, tipiirai, jannelehtinen
Tags: forum, commenting, realtime, discussion
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 3.0.1
License: MIT
License URI: https://github.com/moot/wordpress/blob/master/LICENSE.txt

== Description ==

Muut represents a complete re-imagination of what internet discussion forums and commenting should be like.

It's a modern, fast, highly scalable discussion platform that you can embed onto your WordPress website, and personalize with css to match the design of your site. For more information on why we do what we do, check out our [manifesto](https://muut.com/manifesto/).

Whether you’re setting up for the first time or have just updated to the new version of the plugin, you’re going to love what we’ve done to make implementing Muut in your WordPress website easy and flexible.

* Unified system for both forums and commenting. Same users and design
* Full featured forums makes your WordPress site conversational
* Flat or threaded commenting for small or big topics
* Skinnable style the discussion directly from the WordPress CSS editor
* Realtime. No page reloads – posts, replies, likes and users appear in realtime
* Focus on content. Text focused, uncluttered and linear user interface
* Single Sign-On. Use the WordPress login, users and avatars
* Spam filtering, email notifications and 20+ different language versions

= Optimized for SEO =

* Micro format optimized static content
* Static content served from your domain
* Custom S3 bucket support for Developer accounts
* Escaped fragment support for Google

You can find more information about Muut at our [website](https://muut.com) and read the full [plugin documentation](https://muut.com/docs/wordpress.html).

== Installation ==

1. From your website admin, visit _Plugins > Add New_
2. Search for _"Muut"_
3. Click _Install Now_
4. Activate the plugin
5. Visit the _Muut_ menu item on the Admin navigation
6. Set your forum settings
7. Create pages and posts integrating Muut by using the Muut panel added to the editor!

= Getting Help =
If you need help with any aspect of integrating with the plugin, feel free to check out the full [plugin documentation](https://muut.com/docs/wordpress.html), visit our [forum](https://muut.com/forum/#!/wordpress), or you can submit a support request here.

== Frequently Asked Questions ==

= Can I still use WordPress commenting if I only want to use the main forum functionality? =

Absolutely. You simply can deactivate the "Use Muut for post commenting" Muut setting in the Muut administration page. Alternatively, you can disable or enable Muut commenting on specific posts by enabling that global setting and then activating/deactivating "Use Muut for commenting" option on a post-by-post basis.

= Can I embed a Muut channel with content surrounding it, before and after? =

You can, although we are planning to make it easier in a future release. For now, the best way is to use the old shortcode ([muut path="/the/channel/path"]) in the content editor.

= Where can I get more help with the plugin? =

There are several great ways to learn more about and get help with the plugin. The first is our full [plugin documentation](https://muut.com/docs/wordpress.html); you can also ask for help on our [forum](https://muut.com/forum/#!/wordpress) or submit a support ticket right here on the WordPress plugin page.

== Screenshots ==

1. A Muut forum
2. Muut post commenting
3. Muut settings page
4. Muut post/page editor panel

== Upgrade Notice ==

= 3.0.1 =
The update to version 3.0.1 is a small update with a couple big fixes and better SEO and S3 bucket support.

= 3.0 =
The update to version 3.0 is a major update that enhances the plugin experience in a large way. The update _does_ support all of the earlier plugin functionality (such as the shortcodes), but we recommend updating when you have a little bit of time to experiment and ensure that everything continues to work as expected. We hope you enjoy the new version!

== Changelog ==

= 3.0.1 =
Features, UX, Improvements

* Added support for escaped fragments so that actual forum content is indexed right in pages by Google.
* Added S3 bucket support for serving content and SEO indexing for Developer accounts.
* Easy to upgrade to Developer account straight from Muut settings page.

Bug Fixes

* If Muut commenting is globally disabled, pages no longer default to being Channel embed pages on creation.
* Able to disable Muut commenting on specific pages and posts, even if "Use Muut commenting on posts with existing comments" is on.

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