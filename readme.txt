=== WordPress to Buffer ===
Contributors: flamingtarball, andy7629
Plugin URI: http://marksteadman.com/vault/buffer/
Author URI: http://marksteadman.com/
Tags: buffer, bufferapp, twitter, facebook, linkedin
Requires at least: 2.5.0
Tested up to: 3.3.2
Stable tag: 0.8

Publish your posts to Twitter, Facebook and LinkedIn via your Buffer account.

== Description ==

With this plugin, you can easily add your WordPress posts to Facebook, Twitter
or LinkedIn, via your [bufferapp.com](http://bufferapp.com) account.

* Easy to setup
* Choose which profiles to post to
* Posts are optimised for each social network
* Media attached to posts, to provide better-looking Facebook updates
* Truncated post content is used where there's no title

== Installation ==

Just download the plugin from the WordPress directory or install it directly from
the WordPress admin dashboard.

To install the plugin from the wordpress.org plugin directory:

1. Download `post-to-buffer.zip`,
2. Unzip it and upload the `post-to-buffer` folder to the `/wp-content/plugins/`
directory
3. Activate the plugin through the 'Plugins' menu in WordPress,

To configure the plugin, go to 'Settings' > 'Buffer' and follow the instructions.
You'll need to create an app within bufferapp.com, but the plugin will guide you
through this process.

== Changelog ==

= 8.8 =
* Fixed warning in meta box

= 0.7 =
* Allowed profiles to be selected or unselected by default
* Replaced cURL with the WordPress HTTP API

= 0.6 =
* Fixed incorrect nonce field

= 0.5 =
* Fixed issue listing profiles the first time Buffer is connected

= 0.4 =
* Improved disconnection from Buffer

= 0.3 =
* Ability to disconnect from Buffer
* User-interface corrections

= 0.2 =
* Added posting to Buffer by default, from XML-RPC (ie: the WordPress mobile apps)
* Added buffering for scheduled posts (they are added to the buffer once published)

= 0.1 =
* Basic functionality implemented