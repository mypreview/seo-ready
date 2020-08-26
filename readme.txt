=== SEO Ready ===
Contributors: mahdiyazdani, mypreview, gookaani
Tags: gutenberg, block-editor, meta description, meta title, seo, search engine optimization
Donate link: https://www.mypreview.one
Requires at least: 5.3
Tested up to: 5.5
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A lightweight SEO plugin to generate most commonly used meta tags, but designed for privacy, speed, and accessibility.

== Description ==
When it comes to SEO there are a lot of things you need to consider to keep your website up to date. This plugin aims to help you with the technical side of On-Page SEO and provides a graphical user interface to generate a set of most commonly used meta tags, in less time than it takes to brew a cup of coffee.

*Even though the term “tag” is not 100% correct in many places (some tags should correctly be called “elements” or “attributes”), people use this term because they tend to look for tags rather than the correct technical terms.*

== Installation ==
= Minimum Requirements =

* PHP version 7.2 or greater.
* MySQL version 5.6 or greater or MariaDB version 10.0 or greater.
* WordPress version 5.3 or greater.

= Automatic installation =

Automatic installation is the easiest option — WordPress will handle the file transfer, and you won’t need to leave your web browser. To do an automatic install of the plugin, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”
 
In the search field type “SEO Ready”, then click “Search Plugins.” Once you’ve found the plugin, you can view details about it such as the point release, rating, and description. Click “Install Now,” and WordPress will take it from there.

= Manual installation =

The manual installation method requires downloading the plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation "Manual plugin installation").

= Updating =

Automatic updates should work smoothly, but we still recommend you back up your site.

== Frequently Asked Questions ==
= Where do I find the SEO panel for a post or page? =
To find the SEO panel for a post/page, in the block editor screen, click the “Analytics” icon at the top right to open the SEO sidebar.

= Which post types are supported? =
Any (custom) post type that comes with the new block editor (Gutenberg) support would be able to have access to the SEO panel directly from the edit screen.

= Which meta tags are supported? =

* Title
* Keywords
* Description
* Canonical
* No-index
* No-follow

= I don’t see the SEO panel on my edit screen! Why is that? =
You likely need to enable the REST API for your post type before it will work. By default, the REST API parameter turned off when you register a custom post type. Therefore, you need to intentionally turn it ON in your code.

**Note**: The `show_in_rest` option must be set to `true`.

= How is data privacy (GDPR) being ensured? =
To ensure the plugin is as privacy focused as possible it:

* Does not track your usage of the plugin.
* Does not phone out. No data is shared with third parties.
* Does not add generator comments, or secret comments to your site’s HTML.

= How do I get help with the plugin? =
The easiest way to receive support is to “Create a new topic” by visiting Community Forums page [here](https://wordpress.org/support/plugin/seo-ready "SEO Ready Support Forum").

Make sure to check the “Notify me of follow-up replies via email” checkbox to receive notifications, as soon as a reply posted to your question or inquiry.

*Please note that this is an opensource 100% volunteer project, and it’s not unusual to get reply days or even weeks later.*

= Can I help in translating this plugin into a new language? =
The plugin is fully translation-ready and localized using the GNU framework, and translators are welcome to contribute to the plugin.

Here’s the the [WordPress translation website &#8594;](https://translate.wordpress.org/projects/wp-plugins/seo-ready "WordPress translation website")

= How do I contribute to this plugin? =
We welcome contributions in any form, and you can help reporting, testing, and detailing bugs.

Here’s the [GitHub development repository &#8594;](https://github.com/mypreview/seo-ready "GitHub development repository")

= Did you like the idea behind this plugin? =
Please share your experience by leaving this plugin [5 shining stars](https://wordpress.org/support/plugin/seo-ready/reviews/ "Rate SEO Ready 5 stars") if you like it, thanks!

= I need help customizing this plugin? =
Professional engineer and independent creative technologist in tech · over 6 years experience working from prototype to production · developing WordPress products, services, and eCommerce solutions.

[Available for hire &#8594;](https://mahdiyazdani.com "Mahdi Yazdani’s personal website")

== Screenshots ==
1. Plugin’s sidebar panel.

== Changelog ==
= 1.0.0 =
* Initial release.
