=== Primary Category for Posts and Custom Post Types ===
Contributors: sumitpore
Donate link: https://sumitpore.in
Tags: comments, spam
Requires at least: 4.5
Tested up to: 5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to set a Primary Category for posts!

== Description ==

Please go to `Settings -> Primary Cat for Posts` to set primary categories.

After selecting a particular category as a primary category, you will see radio buttons to select a term on post edit page of post type of that respective category.

On post edit page, if you save the post w/o selecting primary category, it will throw an error that primary category is not selected.

If you want to fetch posts associated with the primary category, then you can call the `PCP_Public_Posts_Listing::get_posts` method in your template.

Also, you can use the shortcode `pcp_list_posts` to fetch the list. You can pass parameters to that
shortcode in following format

[pcp_list_posts post_type='<name of post type>' taxonomy='<slug of primary category taxonomy>' term='<taxonomy term you are interested in>']

Templates available in `templates` directory can be extended in the theme. To do that, create 
'pcp-templates' folder in the theme and copy template in that folder. Make sure you follow the 
directory structure similar to the plugin in pcp-templates folder.