=== Custom Sidebars - Dynamic Widget Area Manager ===
Contributors: WPMUDEV, marquex, WPMUDEV-Support2, WPMUDEV-Support1, WPMUDEV-Support6, WPMUDEV-Support4, iworks
Tags: sidebar, widget, footer, custom, flexible layout, dynamic widgets, manage sidebars, replace widgets, custom widget area
Requires at least: 3.6
Tested up to: 4.9.4
Stable tag: 3.1.2

Flexible sidebars for custom widget configurations on every page, post and custom post type on your site.

== Description ==

<strong>Manage and replace sidebars and other widget areas on your site with Custom Sidebars, a flexible widget area manager.</strong>

Make custom sidebar configurations and be able to choose what widgets display on each page or post of your site.

= Display Different Sidebars on Pages and Posts =

Custom Sidebars allows you to dynamically display custom widget configurations on any page, post, category, post type, or archive page.

[youtube https://www.youtube.com/watch?v=7kgqwceGynA]

Custom Sidebars allows you to display custom widget configurations on any page, post, category, post type, or archive page.

★★★★★ <br>
"Custom Sidebars will go on my "essential plugins" list from now on. I am pleased by how easy it was to figure out and by how many options are available in the free version." - <a href="https://profiles.wordpress.org/monkeyhateclean">monkeyhateclean</a>

★★★★★ <br>
"This plugin does exactly what it says. It's light, integrates well into WordPress and gives you tons of possibilities." - <a href="https://profiles.wordpress.org/darknova11">DarkNova</a>

Every part of Custom Sidebars integrates seamlessly with the Widgets menu for simplicity and control. No confusing settings pages or added menu items, just simple core integration.

<blockquote><h4>A Simple Flexible Sidebar Manager</h4>
<br>
<strong>Custom Sidebars Includes:</strong>
<ul>
  <li>Unlimited custom widget configurations</li>
  <li>Set custom widgets for individual posts and pages, categories, post types, and archives</li>
  <li>Seamless integration with the WordPress Widgets menu</li>
  <li>Works with well-coded themes and doesn't slow down your site</li>
  <li>Set individual widget visibility – for guests, by user role, by post type, for special pages or categories</li>
  <li>Author specific sidebars – display a custom sidebar for each of your authors</li>
  <li>Clone and sync widget settings – quickly edit complex configurations</li>
  <li>Import and export custom sidebars – backup and share sidebars</li>
</ul>

Install Custom Sidebars and see for yourself why it's the most popular widget extension plugin available for WordPress with over 200,000 active installs.
<br>
</blockquote>

<h4>Custom Sidebars Is Fully-Loaded</h4>
<br>
If you run a design agency, or just want to get rid of the ads, checkout <a href="https://premium.wpmudev.org/project/custom-sidebars-pro/">Custom Sidebars Pro:</a>
<ul>
	<li>Ad free</li>
	<li><a href="https://premium.wpmudev.org/support/">24/7 support</a> for all things WordPress</li>
	<li><a href="http://premium.wpmudev.org/join/">Upfront themes, 100+ premium plugins and WPMU DEV services</a> – Including speed optimization, managed backups with 10GB cloud storage and security hardening</li>
</ul>
Get <a href="https://premium.wpmudev.org/project/custom-sidebars-pro/">Custom Sidebars</a> for targeted marketing, better widgets for each level of your membership site, or just to clean up bloated content on each page. Download and try it for free!

== Screenshots ==

1. Set custom sidebars for individual posts and pages or by category, post-type, or archive.
2. Create new sidebars without confusing settings.
3. Integrates with WordPress core Widgets menu.

== Installation ==

There are two ways of installing the plugin:

**From the [WordPress plugins page](http://wordpress.org/extend/plugins/)**

1. Download the plugin, extract the zip file.
2. Upload the `custom-sidebars` folder to your `/wp-content/plugins/` directory.
3. Active the plugin in the plugin menu panel in your administration area.

**From inside your WordPress installation, in the plugin section.**

1. Search for custom sidebars plugin.
2. Download it and then active it.

Once you have the plugin activated you will find all new features inside your "Widgets" screen! There you will be able to create and manage your own sidebars.

Find more usage information on the [Custom Sidebars Pro page](https://premium.wpmudev.org/project/custom-sidebars-pro/).

[youtube https://www.youtube.com/watch?v=q05O9OFEYHM]

== Frequently Asked Questions ==

= How do I begin working with this plugin? =

Please refer to <a href="https://premium.wpmudev.org/project/custom-sidebars-pro/#usage">the usage section of Custom Sidebars Pro</a>

= Why can't I see a widget menu? =

This plugin requires your theme to have widget areas enabled, if you don't have widget areas enabled you probably need to use a different theme that does!

= Where do I set my sidebars up? =

You have a sidebar box when editing a entry. Also you can define default sidebars for different posts and archives.

= Why do I get a message 'There are no replaceable sidebars selected'?  =

You can create all the sidebars you want, but you need some sidebars of your theme to be replaced by the ones that you have created. You have to select which sidebars from your theme are suitable to be replaced in the Custom Sidebars settings page and you will have them available to switch.

= Everything is working properly on Admin area, but the custom sidebars are not displayed on the site. Why? =

 You are probably using a theme that doesn't load dynamic sidebars properly or doesn't use the wp_head() function in its header. The plugin replaces the sidebars inside that function, and many other plugins hook there, so it is [more than recommended to use it](http://josephscott.org/archives/2009/04/wordpress-theme-authors-dont-forget-the-wp_head-function/).

= It appears that only an Admin can choose to add a sidebar. How can Editors (or any other role) edit customs sidebars? =

Any user that can switch themes, can create sidebars. Switch_themes is the capability needed to manage widgets, so if you can’t edit widgets you can’t create custom sidebars. There are some plugins to give capabilities to the roles, so you can make your author be able to create the sidebars. Try [User role editor](http://wordpress.org/extend/plugins/user-role-editor/)

= Can I use the plugin in commercial projects? =

Custom Sidebars has the same license as WordPress, so you can use it wherever you want for free. Yay!

= I like the plugin, but what can I do if my website is based in a WP version older than 3.3 =

If you are running a earlier version of WordPress download Custom Sidebars 0.8.2.

== Changelog ==

= 3.1.2 =
* Added integration with Polylang - now you can choose sidebar depend on Polylang language.
* Added integration with WPML - now you can choose sidebar depend on WPML language.
* Added check to avoid warnigs when user delete term which is assigned to "Sidebar Location".
* Updated "WPMU Dev code library" to version 3.0.5.

= 3.1.1 =
* Improved assets directories.
* Improved widgets on very small screens.
* Fixed bulk edit problem with resetting sidebars.
* Fixed few notices on import screen.

= 3.1.0 =
* Added a quick and a bulk edit to custom post types.
* Added nonce check for set location, import & export actions to avoid CSRF vulnerability.
* Fixed a problem with getting sidebars settings for nested pages with more than 2 levels.
* Fixed a problem with widget visibility on taxonomy archive page.
* Fixed a typo on Import/Export screen.

= 3.0.9 =
* Added ability to turn off "Custom Sidebars" for certain roles.
* Fixed a problem with a category, category archive, and post in category replacement.
* Fixed a problem with removed "Category Archive" from "For Archives" options.
* Fixed build in taxonomies problem on "Sidebar Location" edit window.

= 3.0.8.1 =
* Fixed CSRF vulnerability. Props for [qasuar](https://wordpress.org/support/users/qasuar/)

= 3.0.8 =
* Added bulk sidebars edit.
* Fixed category archive and entry in category replaceable.
* Visibility of "Custom Explain" link is limited only to front-end.

= 3.0.7.1 =
* Fixed visibility of Custom Explain - now it is visible only for administrators.

= 3.0.7 =
* Added ability to replace sidebars for category archive.
* Added ability to replace sidebars for custom taxonomy archive.
* Added ability to turn on Custom Sidebars Explain mode from Admin Bar.
* Fixed a replacement problem on 404 pages.
* Improved "Sidebar Location" popup - added a message when we do not have any replaceable sidebar.
* Refactored "Column Sidebars" on post list screen.

= 3.0.6 =
* Added width to "Custom Sidebars" column on entries list screen to avoid uncontrolled column width.
* Added ability to add new sidebar using the only keyboard, after you fill name just push enter to move to the description field. Hit enter on description field to add a new sidebar.
* Fixed "WPMUDEV Frash" message for pro version.

= 3.0.5 =
* Improved columns display on post list screen - now "Custom Sidebars" column is hidden by default.
* Prevent to load assets on front-end.
* Updated "WPMU Dev code library" to version 3.0.4.
* Fixed a CSS glitch on media library.
* Fixed a JavaScript conflicting with CiviCRM plugin.
* Fixed a problem with taxonomies.

= 3.0.4 =
* Improved "Create a custom sidebar to get started." box.
* Upgraded "WPMU Dev code library" to version 3.0.3.
* Fixed a problem with empty taxonomies, now we can see all taxonomies, including empty.
* Fixed fetching posts.
* Fixed a problem on the Customizer page - removed clone option.
* Fixed Widgets Screen for Right to Left languages.

= 3.0.3 =
* Removed WP Checkup banner.

= 3.0.2 =
* Fixed compatibility issue with uBlock Origin and AdBlock Plus.
* Fixed getting started box not appearing.

= 3.0.1.0 =
* Added the "Create custom sidebar to get started." box.
* Fixed a problem with the link to disable accessibility mode.
* Fixed UX problem with "plus" icon on visibility options.
* Show advertising for "WP Checkup" in the whole admin area.

= 3.0.0.1 =
* Fixed a problem with wp_enqueue_script which was called too early.
* Fixed a problem with advertising, which should stay close when you close it.

= 3.0.0.0 =
* Fixed a conflicting with other plugins.
* Improved functionality, free and pro versions have now the same functionality. The only difference is that the free version includes ads.

= 2.1.2.0 =
* Fixed a problem with empty selectors.
* Remove unnecessary HTTP header - it is only needed when we run cs-explains.
* Update Dash notice.

= 2.1.1.9 =
* Fixed export problem, when two or more widget has the same name.
* Fixed problem with import widgets created before Custom Sidebars plugin was installed.
* Fixed problem with sidebars on Front Page.

= 2.1.1.8 =
* Added support and widgets links on the plugins page.
* Fixed export problem, when two or more widget has the same name.
* Fixed few "Undefined index" warnings.
* Improved import preview screen.
* Improved RTL languages on widgets screen.

= 2.1.1.7 =
* Rollback last change with front page.

= 2.1.1.6 =
* Fixed a bug when we try to use to get property of non-object in "maybe_display_widget()".
* If front page is a page, then we have now the same rules like we have on on page.

= 2.1.1.5 =
* Added new filter "cs_replace_post_type" to filter post type inside function "determine_replacement()".

= 2.1.1.4 =
* Fixed problem with sorting and filtering.

= 2.1.1.3 =
* Added check if there the function "mb_strtolower".

= 2.1.1.2 =
* Sidebars are now sorted by name.

= 2.1.1.1 =
* Fixed a problem with unclickable items on widgets admin screen.

= 2.1.1.0 =
* Fixed undefined index in Sidebars Editor.

= 2.1.0.9 =
* Fixed issue with broken link icon for cloned widgets.

= 2.1.0.8 =
* Small improvements in the admin UI: Better scrolling, fix JavaScript errors and PHP notices.
* Update third party libraries.
* Small improvements in code.

= 2.1.0.4 =
* Fix missing text-domain in translation

= 2.1.0.3 =
* Fix incompatibility with PopUp plugin

= 2.1.0.2 =
* Close possible security hole (XSS vulnerability)

= 2.1.0.1 =
* Fix incompatibility with PopUp plugin

= 2.1.0.0 =
* Fixed: Sidebars could not be created on certain webserver setups.

= 2.0.9.9 =
* Fixed: Minified CSS files included now.

= 2.0.9.8 =
* Better: Add context-guide how to changes settings for static front-page.

= 2.0.9.7 =
* New: Assign a Sidebar to 404 pages via the Sidebar Location dialog.

= 2.0.9.6 =
* Fix: In some browsers the "Add sidebar" popup was partially hidden.
* Fix: Sometimes the dropdown list of a multiselect list stayed open.
* Fix: Plugin now correctly loads the .po files to translate to other languages.
* Some other small improvements.

= 2.0.9.4 =
* Fix: For some users the plugin was not loading anymore after updating to 2.0.9.1.

= 2.0.9.3 =
* Fix: Z-index issue in Dashboard where wide widgets where covered by the main-menu.
* Fix: Added compatibility for static front-page sidebars with version 2.0.9

= 2.0.9.2 =
* Fix: Sidebar Locations "Front Page" and "Post Index" now work correctly.

= 2.0.9.1 =
* Fix: Sidebars now support unicode-text in name/description.
* Minor: New debugging output that explains why a sidebar/widget is displayed.


= 2.0.9 =
* Fix: Fixed issue with WP sidebar chooser right after creating a new sidebar.
* Fix: Fixed various issues when flagging a sidebar s replaceable.
* Fix: Plugin will not load in accessibility mode but display a notice instead.
* Minor fix: Make code compatible with PHP 5.2.4
* Minor fix: Slight improvement of AJAX stability
* Minor fix: Plugin now requires capability "edit_theme_options"

= 2.0.8 =
* Fix: Fixed issue with settings not being saved correctly.

= 2.0.7 =
* Fix: Fixed issue with some people losing some sidebar settings after update.

= 2.0.6.1 =
* Minor fix: Use WordPress core functions to get URL to JavaScript files.
* Minor fix: Refactor function name to avoid misunderstandings.

= 2.0.5 =
* Fixed: Meta box in post editor did show missing sidebars (e.g. after switching the theme)
* Fixed: PHP warning about strict standards.

= 2.0.3 =
* Fixed: JavaScript errors on Windows servers are fixed.

= 2.0.2 =
* Fixed: Dashboard notification is now removed when clicking "dismiss"

= 2.0.1 =
* PHP 5.2 compatibility layer.

= 2.0 =
* Complete UI redesign!
* Many small bugfixes.

= 1.6 =
* Added: WordPress filter "cs_sidebar_params" is called before a custom sidebar is registered.
* Added: Add setting "CUSTOM_SIDEBAR_DISABLE_METABOXES" in wp-config.php to remove custom-sidebar meta boxes.

= 1.5 =
* Added: Custom sidebars now works with BuddyPress pages.

= 1.4 =
* Fixed: Individual post sidebar selection when default sidebars for single posts are defined
* Fixed: Category sidebars sorting
* Added: WP 3.8 new admin design (MP6) support

= 1.3.1 =
* Fixed: Absolute paths that leaded to the outdated browser error
* Fixed: Stripped slashes for the pre/post widget/title fields

= 1.3 =
* Fixed: A lot of warnings with the PHP debug mode on
* Improved: Styles to make them compatible with WP 3.6
* Fixed: Creation of sidebars from the custom sidebars option
* Fixed: Missing loading icons in the admin area
* Removed: Donate banner. Thanks to the ones that have be supporting Custom Sidebar so far.

= 1.2 =
* Fixed: Searches with no results shows default sidebar.
* Added: RTL support (thanks to Dvir http://foxy.co.il/blog/)
* Improved: Minor enhancements in the interface to adapt it to wp3.
* Added: French and Hebrew translations
* Fixed: Slashes are added to the attributes of before and after title/widget

= 1.1 =
* Fixed: Where lightbox not showing for everybody (Thanks to Robert Utnehmer)
* Added: Default sidebar for search results pages
* Added: Default sidebar for date archives
* Added: Default sidebar for Uncategorized posts

= 1.0 =
* Fixed: Special characters make sidebars undeletable
* Added: Child/parent pages support
* Improved interface to handle hundreds of sidebars easily
* Added: Ajax support for creating an editing sidebars from the widget page
* Added: Italian translation

= 0.8.2 =
* Fixed: Problems with Spanish translation
* Added: Dutch and German language files
* Fixed: Some CSS issues with WP3.3

= 0.8.1 =
* Fixed: You can assign sidebars to your pages again.

= 0.8 =
* Fixed: Category hierarchy is now handled properly by the custom sidebars plugin.
* Added: Sidebars can be set for every custom post type post individually.
* Improved the way it replace the sidebars.
* Improved some text and messages in the back-end.

= 0.7.1 =
* Fixed: Now the plugin works with themes like Thesis that don't use the the_header hook. Changed the hook where execute the replacement code to wp_head.
* Fixed: When a second sidebar is replaced with the originally first sidebar, it is replaced by the first sidebar replacement instead.

= 0.7 =
* Fixed: Bulk and Quick editing posts and pages reset their custom sidebars.
* Changed capability needed to switch_themes, and improved capability management.

= 0.6 =

* New interface, more user friendly
* Added the possibility of customize the main blog page sidebars
* Added the sidebars by category, so now you can personalize all the post that belongs to a category easily in a hierarchical way
* Added the possibility of customize the authors page sidebars
* Added the possibility of customize the tags page sidebars
* Added, now it is possible to edit the sidebars names, as well as the pre-widget, post-widget, pre-title, post-title for a sidebar.
* Added the possibility of customize the sidebars of posts list by category or post-type.


= 0.5 =

* Fixed a bug that didn't allow to create new bars when every previous bars were deleted.
* Fixed a bug introduced in v0.4 that did not allow to assign bars per post-types properly
* Added an option to remove all the Custom Sidebars data from the database easily.

= 0.4 =

* Empty sidebars will now be shown as empty, instead of displaying the theme's default sidebar.

= 0.3 =

* PHP 4 Compatible (Thanks to Kay Larmer)
* Fixed a bug introduced in v0.2 that did not allow to save the replaceable bars options

= 0.2 =

* Improved security by adding wp_nonces to the forms.
* Added the pt-widget post type to the ignored post types.
* Improved i18n files.
* Fixed screenshots for documentation.

= 0.1 =

* Initial release

== Upgrade Notice ==

= 1.0 =
*Caution:* Version 1.0 needs WordPress 3.3 to work. If you are running an earlier version *do not upgrade*.

= 0.7.1 =
Now custom sidebars works with Thesis theme and some minor bugs have been solved.

= 0.7 =
This version fix a bug of v0.6 and before that reset the custom sidebars of posts and pages when they are quick edited or bulk edited, so upgrade is recommended.
This version also changes the capability for managing custom sidebars to 'switch_themes' the one that allows to see the appearance menu in the admin page. I think the plugin is more coherent this way, but anyway it is easy to modify under plugin edit.

= 0.6 =
This version adds several options for customize the sidebars by categories and replace the default blog page sidebars. Now it's possible to edit sidebar properties. Also fixes some minor bugs.

== Contact and Credits ==

Custom sidebars is maintained and developed by <a href="http://premium.wpmudev.org">WPMU DEV</a>.

Original development completed by <a href="http://marquex.es/">Javier Marquez</a>

Custom Sidebars uses the great jQuery plugin [Tiny Scrollbar](http://www.baijs.nl/tinyscrollbar/) by Maarten Baijs.
