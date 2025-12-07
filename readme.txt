=== Fast TOC ===
Contributors: michielve
Tags: toc, table of contents
Requires at least: 5
Tested up to: 6.9
Requires PHP: 5.4.0
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display a table of contents

== Description ==

This plugin displays a table of contents for the selected post types. See [the documentation](https://blog.michielvaneerd.nl/fast-toc/) for more information and an example.

= Features =

* Display table of contents
* Set default settings (see Settings / Reading section)
* Specify what headers are ignored with CSS selectors
* Collapsible or not
* Specify the list style type
* Change default position with a shortcode

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/fast-toc` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Reading screen to set some default settings.

== Changelog ==

= 20251207 =
* Make it work with Wordpress 6.9

= 20221230 =
* Highlight current item.

= 20221229 =
* Flex display for up arrow.

= 20221228 =
* Added selector TOC to place the TOC to a specific CSS selector.

= 20200526 =
* String replace global

= 20200519 =
* When scrolling down, remove scroll to top 

= 20200518 =
* Add scroll to top link.

= 20200517 =
* Add root selector.
* Don't overwrite ID of header if header already has one.

= 20200126 =
* Nicer id attribute values

= 20200119 =
* Shortcode [fast-toc] and the_content filter.

= 20200116 =
* Many settings have been added (collapsible, numbers, etc.).

= 20200111 =
* Select post types, minimum headers and headers to ignore

= 20200105 =
* First release
