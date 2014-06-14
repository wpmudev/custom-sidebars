<?php
/**
 * Displays the tab-navigation in all custom-sidebar sections.
 */

$csb = CustomSidebars::instance();
$active = $csb->current_page();
?>

<h2 class="nav-tab-wrapper">
<a class="nav-tab <?php if ( 'conf' == $active ) : ?>nav-tab-active<?php endif; ?>" href="themes.php?page=customsidebars"><?php _e( 'Custom Sidebars', CSB_LANG ); ?></a>
<a class="nav-tab <?php if ( 'defaults' == $active ) : ?>nav-tab-active<?php endif; ?>" href="themes.php?page=customsidebars&p=defaults"><?php _e( 'Default Sidebars', CSB_LANG ); ?></a>

<?php
/**
 * Allow other extensions to add tab-items to the custom sidebar page.
 *
 * @since  1.6
 * @param  string $active The currently active tab.
 */
do_action( 'cs_additional_tabs', $active );

// Legacy version of the hook - before 1.6
do_action( 'cs_additionalTabs' );
?>

<?php if ( 'edit' == $active ) : ?><a class="nav-tab nav-tab-active" href="#"><?php _e( 'Edit Sidebar', CSB_LANG ); ?></a><?php endif; ?>

</h2>
<?php $csb->message(); ?>
