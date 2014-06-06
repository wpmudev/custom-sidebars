<?php
/**
 * Set default-replacements for the theme sidebars.
 */
?>

<div class="themes-php csb">
<div class="wrap">

<?php include( 'part-tabs.php' ); ?>

<?php $cs_is_defaults = true; //This make disappear sidebar selection link in the defaults page.?>

<div id="defaultsidebarspage">

<form action="themes.php?page=customsidebars&p=defaults" method="post">

<div id="poststuff" class="defaultsdetailscontainer">
<h2><?php _e('Default sidebars for single entries', CSB_LANG ); ?></h2>
<div id ="defaultsforposts" class="stuffbox">
<p><?php _e('These replacements will be applied to every entry post that matches a certain post type or category.', CSB_LANG ); ?></p>
<p><?php _e('The sidebars by categories work in a hierarchycal way, if a post belongs to a parent and a child category it will show the child category sidebars if they are defined, otherwise it will show the parent ones. If no category sidebar for post are defined, the post will show the post post-type sidebar. If none of those sidebars are defined, the theme default sidebar is shown.', CSB_LANG ); ?></p>

<div class="cscolright">

<?php /***************************************
category_posts_{$id_category}_{$id_modifiable} : Posts by category
*********************************************/?>

<?php include CSB_VIEWS_DIR . 'defaults/single_category.php' ?>

</div>

<div class="cscolleft">

<?php include CSB_VIEWS_DIR . 'defaults/single_posttype.php' ?>

</div>

<p class="submit"><input type="submit" class="button-primary" name="update-defaults-posts" value="<?php _e('Save Changes', CSB_LANG ); ?>" /></p>
</div>


<h2><?php _e('Default sidebars for archives', CSB_LANG ); ?></h2>
<div id ="defaultsforpages" class="stuffbox">
<p><?php _e('You can define specific sidebars for the different Wordpress archive pages. Sidebars for archives pages work in the same hierarchycal way than the one for single posts.', CSB_LANG ); ?></p>

<div class="cscolright">


<?php include CSB_VIEWS_DIR . 'defaults/archive_category.php' ?>
<?php include CSB_VIEWS_DIR . 'defaults/archive_tag.php' ?>
<?php include CSB_VIEWS_DIR . 'defaults/archive_search.php' ?>

</div>

<div class="cscolleft">

<?php include CSB_VIEWS_DIR . 'defaults/archive_blog.php' ?>
<?php include CSB_VIEWS_DIR . 'defaults/archive_posttype.php' ?>
<?php include CSB_VIEWS_DIR . 'defaults/archive_date.php' ?>
<?php include CSB_VIEWS_DIR . 'defaults/archive_author.php' ?>

</div>

<p class="submit"><input type="submit" class="button-primary" name="update-defaults-pages" value="<?php _e('Save Changes', CSB_LANG ); ?>" /></p>
</div>

</div>

</form>

</div>

<?php include( 'part-footer.php' ); ?>

</div>
</div>