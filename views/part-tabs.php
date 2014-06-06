<?php
/**
 * Displays the tab-navigation in all custom-sidebar sections.
 */

$active = 'conf';
$tabedit = FALSE;

switch ( @$_GET['p'] ) {
	case 'defaults':
	case 'edit':
	case 'export':
		$active = $_GET['p'];
		break;
}
?>

<h2 class="nav-tab-wrapper">
<a class="nav-tab <?php if ( 'conf' == $active ) : ?>nav-tab-active<?php endif; ?>" href="themes.php?page=customsidebars"><?php _e( 'Custom Sidebars', CSB_LANG ); ?></a>
<a class="nav-tab <?php if ( 'defaults' == $active ) : ?>nav-tab-active<?php endif; ?>" href="themes.php?page=customsidebars&p=defaults"><?php _e( 'Default Sidebars', CSB_LANG ); ?></a>
<a class="nav-tab <?php if ( 'export' == $active ) : ?>nav-tab-active<?php endif; ?>" href="themes.php?page=customsidebars&p=export"><?php _e( 'Export/Import', CSB_LANG ); ?></a>

<?php do_action( 'cs_additionalTabs' ); ?>

<?php if ( 'edit' == $active ) : ?><a class="nav-tab nav-tab-active" href="#"><?php _e( 'Edit Sidebar', CSB_LANG ); ?></a><?php endif; ?>

</h2>
<?php $this->message(); ?>
