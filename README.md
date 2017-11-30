=== Plugin Name ===

Plugin Name: GK Post Previewer
Contributors: mcfarhat
Donate link:
Tags: wordpress, custom post, post, video, social media
Requires at least: 4.3
Tested up to: 4.9
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

=== Short Summary ===

The GK Post Previewer is a wordpress plugin the purpose of which is to allow creating a preview of any post grabbed from different social media sites, including video and image links.


== Description ==

The plugin aims at adding support from within wordpress, to a new type of posts being social media posts, whether from facebook, instagram,... and allowing the inclusion of those specific posts inside your wordpress installation. 

Particularly of interest are posts that have image and video content, to allow automated fetching of the image and video links, as well as rendering and display of relevant content within your wordpress site.

Upon installation, a new menu item is created, Post Previews, which allows the creation of relevant post types.

When accessing this screen, you can paste any link for any post grabbed from different social media, or any other site for that purpose. Once you do that, you can click on the load button, through which the plugin then scraps the url, and fetches the proper relevant video and image links, as well as title and subtitle of the post, and feeds them into the relevant fields.

You would also have the capability at the moment for uploading a different image in place of the one found via scraping the data. This can be accomplished via using the Change Media button.

Once done, saving the post would add it to the list of current post previews.

In order to display those posts to the front end, you need to insert the shortcode [post_preview_posts_sc limit=10] which would allow the display of all your posts up to the defined count on the front end.

You alternatively have the option to utilize the widget option as well to add it to your screen from the widgets screen. Widget is called "Post Previews Widget" and also allows setting the proper post limit.

If you would like some custom work done, or have an idea for a plugin you're ready to fund, check our site at www.greateck.com or contact us at info@greateck.com

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/GK-Post-Previewer` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. That's it! Now you can drop any posts into the newly created post type under "Post Previewer" menu item, and view them on any page using the shortcode [post_preview_posts_sc limit=10]

== Screenshots ==
1. Screenshot showing the Post Previewer Link in the Left Menu tab <a href="https://www.dropbox.com/s/4gzai71ptqgqxkc/menu_link.png?dl=0">https://www.dropbox.com/s/4gzai71ptqgqxkc/menu_link.png?dl=0</a>
2. Screenshot showing the add post screen <a href="https://www.dropbox.com/s/ybaweje9uj4jdbq/post_preview_entry.png?dl=0">https://www.dropbox.com/s/ybaweje9uj4jdbq/post_preview_entry.png?dl=0</a>
3. Screenshot showing the Widget for post previewer <a href="https://www.dropbox.com/s/n63gp63cqeo152u/post_preview_widget.png?dl=0">https://www.dropbox.com/s/n63gp63cqeo152u/post_preview_widget.png?dl=0</a>

== Changelog ==

= 0.2.0 =
* Current Version *

= 0.1.0 =
* Initial Version *
