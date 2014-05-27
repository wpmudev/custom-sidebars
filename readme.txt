=== Plugin Name ===
Contributors: WPMUDEV, marquex
Donate link: http://marquex.es/donate
Tags: custom sidebars, widgets, sidebars, custom, sidebar, widget, personalize
Requires at least: 3.3
Tested up to: 3.8.2
Stable tag: trunk

Allows to create your own widgetized areas and custom sidebars, and select what sidebars to use for each post or page.
 
== Description ==

Sometimes it is necessary to show different elements on the sidebars for some posts or pages. The themes nowadays give you some areas to put the widgets, but those areas are common for all the posts that are using the same template. NOTE: **You need to use a theme that accepts widgets to make this plugin work.** 

Custom Sidebars allows you to create all the widgetized areas you need, your own custom sidebars, configure them adding widgets, and replace the default sidebars on the posts or pages you want in just few clicks.

= Important Update: WPMU DEV has taken over the maintenance and support for Custom Sidebars =

With the backing of [WPMU DEV's professional WordPress team](http://premium.wpmudev.org/) you can expect faster support, bug-fixes, and new features. And the donate button won't disturb you anymore!

= Customize every widget area =

You can also set new default sidebars for a group of posts or pages easily, keeping the chance of changing them individually.:

*	Sidebars for all the posts that belong to a category.
*	Sidebars for all the posts that belong to a post-type.
*	Sidebars for archives (by category, post-type, author, tag).
*	Sidebars for the main blog page.
*	Sidebars for search results.

= Translations =

Translations are welcome! We will write your name down here if you donate your translation work. Thanks very much to:

*	English - Javi Marquez (http://arqex.com)
*	Spanish - Javi Marquez (http://arqex.com)
*	German - [Markus Vocke, Professionelles Webdesign](http://www.web-funk.de)
*	Dutch - Herman Boswijk
*	Italian - [David Pesarin](http://davidpesarin.wordpress.com) 
*	French - [Aldabra](http://www.unamourdeuxgeeks.com)
*	Hebrew - [Dvir](http://foxy.co.il/blog/)

= About Us =

WPMU DEV is a premium supplier of quality WordPress plugins and themes. For premium support with any WordPress related issues you can join us here: [http://premium.wpmudev.org/join/](http://premium.wpmudev.org/join/)

Don't forget to stay up to date on everything WordPress from the Internet's number one resource: [http://wpmu.org](http://wpmu.org)

== Installation ==

There are two ways of installing the plugin:

**From the [WordPress plugins page](http://wordpress.org/extend/plugins/)**

1. Download the plugin
2. Upload the `custom-sidebars` folder to your `/wp-content/plugins/` directory.
3. Active the plugin in the plugin menu panel in your administration area.

**From inside your WordPress installation, in the plugin section.**

1. Search for custom sidebars plugin
2. Download it and then active it.

Once, you have the plugin activated, you will find a new option called 'Custom Sidebars' in your Appearance menu. There you will be able to create and manage your own sidebars.

You can find some simple tutorials on the [Custom sidebars plugin web page](http://marquex.es/541/custom-sidebars-plugin-v0-8)

== Frequently Asked Questions ==

= How do begin to work with the plugin? =

Here there is an old [video tutorial](http://vimeo.com/18746218) about how to set up your first sidebars.

= Where do I set my sidebars up? =

You have a sidebar box when editing a entry. Also you can define default sidebars for different posts and archives.

= Why do I get a message 'There are no replaceable sidebars selected'?  =

You can create all the sidebars you want, but you need some sidebars of your theme to be replaced by the ones that you have created. You have to select which sidebars from your theme are suitable to be replaced in the Custom Sidebars settings page and you will have them available to switch.

= Everything is working properly on Admin area, but the site is not displayin the sidebars. Why? =

 You probably are using a theme that don’t load dynamic sidebars properly or don’t use the wp_head() function in its header. The plugin replace the sidebars inside that function, and many others plugins hook there, so it is [more than recommended to use it](http://josephscott.org/archives/2009/04/wordpress-theme-authors-dont-forget-the-wp_head-function/).

= It appears that only an Admin can choose to add a sidebar. How can Editors (or any other role) edit customs sidebars? =

Any user that can switch themes, can create sidebars. Switch_themes is the capability needed to manage widgets, so if you can’t edit widgets you can’t create custom sidebars. There are some plugins to give capabilities to the roles, so you can make your author be able to create the sidebars. Try [User role editor](http://wordpress.org/extend/plugins/user-role-editor/)

= Does it have custom taxonomies support? =

Sidebars for custom taxonomies are not working by the moment, it’s hard to build an interface.

= Can I use the plugin in commercial projects? =

Custom Sidebars has the same license as Wordpress, so you can use it wherever you want for free. Nevertheless, donations are welcome.

= I like the plugin, but what can I do if my website is based in a WP version older than 3.3 =

If you are running a earlier version of Wordpress download Custom Sidebars 0.8.2.

== Screenshots ==

1. screenshot-1.png The plugin options page. Placed in the appearance menu, you can create, edit or delete sidebars there, set the replaceable sidebars and reset the sidebars data. 
2. screenshot-2.png The new sidebars created by the plugin, can be customized in the Widgets menu.
3. screenshot-3.png A new box is added to the post and page edit forms, where you can set your custom sidebars up.
4. screenshot-4.png Default sidebars page, here you will be able to assign sidebars to all the post that belongs to a category or a post-type. Also author, tags and main blog pages sidebars can be defined here.
5. screenshot-5.png The sidebar sb1 has replace the sidebar footer 1 in the front-end.

== Changelog ==
= 1.5 =
*		Added: Custom sidebars now works with buddypress pages.

= 1.4 =
*		Fixed: Individual post sidebar selection when default sidebars for single posts are defined
*		Fixed: Category sidebars sorting
*		Added: WP 3.8 new admin design (MP6) support

= 1.3.1 = 
*		Fixed: Absolute paths that leaded to the outdated browser error
*		Fixed: Stripped slashes for the pre/post widget/title fields

= 1.3 =
*		Fixed: A lot of warnings with the PHP debug mode on
*		Improved: Styles to make them compatible with WP 3.6
*		Fixed: Creation of sidebars from the custom sidebars option 
*		Fixed: Missing loading icons in the admin area
*		Removed: Donate banner. Thanks to the ones that have be supporting Custom Sidebar so far.

= 1.2 =
*       Fixed: Searches with no results shows default sidebar.
*		Added: RTL support (thanks to Dvir http://foxy.co.il/blog/)
*		Improved: Minor enhancements in the interface to adapt it to wp3.
*		Added: French and Hebrew translations
*		Fixed: Slashes are added to the attributes of before and after title/widget

= 1.1 =
*       Fixed: Where lightbox not showing for everybody (Thanks to Robert Utnehmer)
*       Added: Default sidebar for search results pages
*       Added: Default sidebar for date archives
*	Added: Default sidebar for Uncategorized posts

= 1.0 = 
*       Fixed: Special characters make sidebars undeletable
*       Added: Child/parent pages support
*       Improved interface to handle hundreds of sidebars easily
*       Added: Ajax support for creating an editing sidebars from the widget page
*       Added: Italian translation

= 0.8.2 =
* 	Fixed: Problems with spanish translation
*	Added: Dutch and German language files
* 	Fixed: Some css issues with WP3.3

= 0.8.1 =
*	Fixed: You can assign sidebars to your pages again.

= 0.8 =
*	Fixed: Category hierarchy is now handled properly by the custom sidebars plugin.
*	Added: Sidebars can be set for every custom post type post individually.
*	Improved the way it replace the sidebars.
*	Improved some text and messages in the back-end.

= 0.7.1 =
* 	Fixed: Now the plugin works with themes like Thesis that don't use the the_header hook. Changed the hook where execute the replacement code to wp_head.
*	Fixed: When a second sidebar is replaced with the originally first sidebar, it is replaced by the first sidebar replacement instead. 

= 0.7 =
*	Fixed: Bulk and Quick editing posts and pages reset their custom sidebars.
*	Changed capability needed to switch_themes, and improved capability management.

= 0.6 =

*	New interface, more user friendly
*	Added the possibility of customize the main blog page sidebars
*	Added the sidebars by category, so now you can personalize all the post that belongs to a category easily in a hierarchycal way
*	Added the possibility of customize the authors page sidebars
*	Added the possibility of customize the tags page sidebars
*	Added, now it is possible to edit the sidebars names, as well as the pre-widget, post-widget, pre-title, post-title for a sidebar.
*	Added the possibility of customize the sidebars of posts list by category or post-type.


= 0.5 =

*	Fixed a bug that didn't allow to create new bars when every previous bars were deleted.
*	Fixed a bug introduced in v0.4 that did not allow to assign bars per post-types properly
*	Added an option to remove all the Custom Sidebars data from the database easily.

= 0.4 =

*	Empty sidebars will now be shown as empty, instead of displaying the theme's default sidebar.

= 0.3 =

*	PHP 4 Compatible (Thanks to Kay Larmer)
*	Fixed a bug introduced in v0.2 that did not allow to save the replaceable bars options

= 0.2 =

*	Improved security by adding wp_nonces to the forms.
*	Added the pt-widget post type to the ignored post types.
*	Improved i18n files.
*	Fixed screenshots for documentation.

= 0.1 =

*	Initial release

== Upgrade Notice ==

= 1.0 =
*Caution:* Version 1.0 needs Wordpress 3.3 to work. If you are running an earlier version *do not upgrade*.

= 0.7.1 =
Now custom sidebars works with Thesis theme and some minor bugs have been solved.

= 0.7 =
This version fix a bug of v0.6 and before that reset the custom sidebars of posts and pages when they are quick edited or bulk edited, so upgrade is recommended.
This version also changes the capability for managing custom sidebars to 'switch_themes' the one that allows to see the appearance menu in the admin page. I think the plugin is more coherent this way, but anyway it is easy to modify under plugin edit.

= 0.6 =
This version adds several options for customize the sidebars by categories and replace the default blog page sidebars. Now it's possible to edit sidebar properties. Also fixes some minor bugs.



== About Us ==
WPMU DEV is a premium supplier of quality WordPress plugins and themes. For premium support with any WordPress related issues you can join us here:
<a href="http://premium.wpmudev.org/join/">http://premium.wpmudev.org/join/</a>

Don't forget to stay up to date on everything WordPress from the Internet's number one resource:
<a href="http://wpmu.org/">http://wpmu.org</a>

Hey, one more thing... we hope you <a href="http://profiles.wordpress.org/WPMUDEV/">enjoy our free offerings</a> as much as we've loved making them for you!

== Contact and Credits ==

Originally written by [Javier Marquez](http://marquex.es/) (e-mail 'javi' at 'marquex dot es').

Custom Sidebars uses the great jQuery plugin [Tiny Scrollbar](http://www.baijs.nl/tinyscrollbar/) by Maarten Baijs.