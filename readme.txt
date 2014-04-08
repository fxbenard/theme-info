=== Theme Info ===
Contributors: johnbillion, sivel, fxbenard
Tags: theme, info, data, utility, developer, meta, tool
Requires at least: 3.4
Tested up to: 3.6
Stable tag: trunk

Provides a simple way of displaying up-to-date information about specific WordPress Theme Directory hosted themes in your blog posts and pages.

== Description ==

This plugin provides a simple way of displaying up-to-date information about specific themes hosted on the WordPress Theme Directory in your blog posts and pages. It is intended for plugin authors who want to display details of their own themes from the WP Theme Directory on their blog and want those details to remain up to date. It's also useful for bloggers who may blog about themes and would like the details in their blog posts to remain up to date.

This plugin uses WordPress shortcodes so it's ridiculously easy to include any information about a particular theme in your post or page.

= Er, what? =

You want to blog about a particular theme on your blog and include various details of it in your blog post (eg. the number of downloads or the last updated date). You could manually type this information into your post but this means that in a few days/weeks/months' time the information will be out of date.

This plugin allows you to use shortcodes in your blog posts and pages which fetches this information right from the WordPress Theme Directory, therefore ensuring the information always remains up to date.

= Here's an example =

This plugin uses WordPress shortcodes so it's ridiculously easy to include any information about a particular theme in your post or page.

> This theme has been downloaded `[theme downloaded]` times!

This will produce the following content in your blog post:

> This theme has been downloaded 1,650 times!

The download count will remain current without you having to touch your blog post again.

== Installation ==

You can install this plugin directly from your WordPress dashboard:

 1. Go to the *Plugins* menu and click *Add New*.
 2. Search for *Theme Info*.
 3. Click *Install Now* next to the Theme Info plugin.
 4. Activate the plugin.
 5. Now read the usage guidelines below.

Alternatively, see the guide to [Manually Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Usage =

1. Write a new blog post or page, or open an existing post or page for editing.
2. In the 'Theme Info' box on that screen, type the slug of the theme (see [screenshot #1](http://wordpress.org/plugins/theme-info/screenshots/)). The theme slug is the last part of the URL of the theme's page on wordpress.org.
3. Add a shortcode to your blog entry like this: `[theme version]` and save the post. (That's the word 'theme' and not the slug of the theme by the way).
4. Take a look at your post and the version number of the theme will be displayed.

For a complete list of all the shortcodes you can use, see the [FAQ page](http://wordpress.org/plugins/theme-info/faq/).

== Frequently Asked Questions ==

= Is this plugin for me? =

This plugin is only going to be of use to you if:

1. You are a theme author and you want a ridiculously easy way to include up to date information about any of your themes in your blog posts or pages.
2. You are the author of a blog that highlights themes of interest and you want to ensure that information in your posts remains up to date.

= Which attributes of a plugin can I display? =

The majority of the available shortcodes can be seen from the post writing screen. Just click the '[show]' link in the 'Theme Info' box.

Please see http://lud.icro.us/wordpress-plugin-info/ for a complete list of all the available shortcodes. There are a few additional (less useful) shortcodes listed there.

Shortcodes which display a formatted hyperlink can have their default link text overridden by adding a 'text' parameter. For example: `[theme homepage text='Homepage']` will display a link to the theme homepage with the link text 'Homepage'.

= Can I display plugin info outside of my blog posts? =

Yes! You can use the `theme_info()` function anywhere in your template. The function takes two parameters, the slug of your theme and the attribute you'd like to display. The following example will display the last updated date for the Twenty Fourteen theme:

`<?php theme_info( 'twentyfourteen', 'updated' ); ?>`

You can also get the info rather than printing it out using the `wp_get_theme()` function:

`<?php $updated = wp_get_theme( 'twentyfourteen', 'updated' ); ?>`

= The geek stuff =

The theme information is collected from wp.org each time you save your post or page. It is updated hourly using WordPress' cron system and uses the Plugin API available in WordPress 2.7 or later. The theme data is stored as an associative array in a custom field called 'theme-info', and the theme slug you enter is saved as a custom field called 'theme'. For supergeeks, this means you can also access the plugin data using `get_post_meta()`, but I'll let you figure that out for yourself.

== Screenshots ==

1. Adding a theme to a post. Remember to use the slug and not the name, as using the name isn't 100% reliable.

== Upgrade Notice ==

= 0.1 =
* Initial release.

== Changelog ==

= 0.1 =
* Initial release.
