=== evangelizo ===
Contributors: marverix
Author: Marek Sierociński
Author URI: https://github.com/marverix
Plugin URL: https://github.com/marverix/wp-plugin-evangelizo
Requires PHP: 5.6
Requires at least: 5.0
Tested up to: 5.6.2
Tags: content, evangelizo, ewangelia, gospel, readings, psalm, wordpress, shortcode
Stable tag: main
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0-standalone.html

Display today readings from the Evangelizo.org project.

== Description ==

The plugin uses the evangelizo.org project to display the gospel for today. Data is cached until the end of the day so as not to bombard the website with unnecessary queries. The cache works for each language separately.

At the end of the content, an appropriate note is added that the owner of the page does not claim any rights to the readings displayed. There is also a link encouraging you to financially support the project.

== Installation ==

= Shortcode =

Inserting content and configuration is done using the shortcode:

    [evangelizo ...]

= Parameters =

Currently supported parameters:

* `language` - The language code, in which readings should be fetched and displayed?

= Supported Languages =

* `AR` - العربية
* `DE` - Deutsch
* `GR` - Ελληνικά
* `AM` - English
* `SP` - Español
* `FR` - Français
* `GA` - Gaeilge
* `ARM` - հայերեն
* `IT` - Italiano
* `KR` - 한국어
* `MG` - Malagasy
* `NL` - Nederlands
* `PL` - Polski
* `CN` - 中文
* `PT` - Português
* `RU` - русский
* `TR` - Türkçe

= Example =

Fetch and display readings in Español:

    [evangelizo language="SP"]

== Changelog ==
= 2021-03-01 =
* First release
