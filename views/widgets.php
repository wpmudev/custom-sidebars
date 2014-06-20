<?php
/**
 * Updates the default widgets page of the admin area.
 * There are some HTML to be added for having all the functionality, so we
 * include it at the begining of the page, and it's placed later via js.
 */
?>

<div id="cs-widgets-extra" class="csb">
    <div id="oldbrowsererror" class="message error">
        <?php _e( 'You are using an old browser that doesn\'t support draggin widgets to a recently created sidebar. Refresh the page to add widgets to this sidebar and think about to update your browser.',  CSB_LANG ); ?>
    </div>

    <div id="cs-title-options">
        <h2><?php _e( 'Sidebars', CSB_LANG ); ?></h2>
        <div id="cs-options" class="csb cs-options">
            <button type="button" class="button button-primary cs-action btn-create-sidebar">
                <i class="dashicons dashicons-plus-alt"></i>
                <?php _e( 'Create a new sidebar', CSB_LANG ); ?>
            </button>
            <?php
            /**
             * Other extensions use this hook to display additional buttons.
             *
             * @since  1.6
             */
            do_action( 'cs_widgets_additional_buttons' );
            ?>
            <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-feedback" title="" alt="" />
        </div>
    </div>

    <div class="cs-title-col1"><h3><?php _e( 'Custom Sidebars', CSB_LANG ); ?></h3></div>
    <div class="cs-title-col2"><h3><?php _e( 'Theme Sidebars', CSB_LANG ); ?></h3></div>

    <?php /*
    <div id="cs-new-sidebar" class="widgets-holder-wrap">
        <div class="sidebar-name">
            <div class="sidebar-name-arrow"><br></div>
            <h3><?php _e( 'New Sidebar', CSB_LANG ) ?><span><img src="<?php echo admin_url() ?>/images/wpspin_light.gif" class="ajax-feedback" title="" alt=""></span></h3>
        </div>
        <div class="_widgets-sortables" style="min-height: 50px; ">
            <div class="sidebar-form">
                <form action="themes.php?page=customsidebars" method="post">
                    <?php wp_nonce_field( 'cs-create-sidebar', '_create_nonce');?>
                    <div class="namediv">
                            <label for="sidebar_name"><?php _e('Name', CSB_LANG ); ?></label>
                            <input type="text" name="sidebar_name" size="30" tabindex="1" value="" class="sidebar_name" />
                            <p class="description"><?php _e('The name has to be unique.', CSB_LANG )?></p>
                    </div>

                    <div class="descriptiondiv">
                            <label for="sidebar_description"><?php echo _e('Description', CSB_LANG ); ?></label>
                            <input type="text" name="sidebar_description" size="30" tabindex="1" value="" class="sidebar_description" />
                    </div>
                    <p class="submit submit-sidebar">
                        <span><img src="<?php echo admin_url() ?>/images/wpspin_light.gif" class="ajax-feedback" title="" alt=""></span>
                        <input type="submit" class="button-primary cs-create-sidebar" name="cs-create-sidebar" value="<?php _e('Create Sidebar', CSB_LANG ); ?>" />
                    </p>
                </form>
            </div>
        </div>
    </div>
    */ ?>

    <div class="cs-custom-sidebar cs-toolbar cf">
        <a class="cs-tool delete-sidebar" href="themes.php?page=customsidebars&p=delete&id="><?php _e( 'Delete', CSB_LANG ); ?></a>
        <span class="cs-separator"> | </span>
        <a class="cs-tool edit-sidebar" href="themes.php?page=customsidebars&p=edit&id="><?php _e( 'Edit', CSB_LANG ); ?></a>
        <span class="cs-separator"> | </span>
        <a class="cs-tool where-sidebar thickbox" href="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=cs-ajax&cs_action=where&id=" title="<?php _e( 'Where do you want the sidebar?', CSB_LANG ); ?>"><?php _e( 'Where?', CSB_LANG ); ?></a>
        <span class="cs-separator"> | </span>
    </div>

    <div class="cs-theme-sidebar cs-toolbar cf">
        <label for="cs-replaceable" class="cs-tool">
            <input type="checkbox" id="" class="has-label" />
            <?php _e( 'Make Replaceable', CSB_LANG ); ?>
        </label>
        <span class="cs-separator"> | </span>
    </div>

    <div class="cs-cancel-edit-bar">
        <a class="cs-tool cs-advanced-edit" href="themes.php?page=customsidebars&p=edit&id=">
            <?php _e( 'Advanced Edit',  CSB_LANG ); ?>
        </a>
        <span class="cs-separator"> | </span>
        <a class="cs-tool cs-cancel-edit" href="#">
            <?php _e( 'Cancel',  CSB_LANG ) ?>
        </a>
    </div>

    <div id="cs-save">
        <?php _e( 'Save', CSB_LANG ); ?>
    </div>

    <span id="cs-confirm-delete">
        <?php _e( 'Are you sure that you want to delete the sidebar',  CSB_LANG ) ?>
    </span>

    <form id="cs-wpnonces">
        <?php wp_nonce_field( 'cs-delete-sidebar', '_delete_nonce', false ); ?>
        <?php wp_nonce_field( 'cs-edit-sidebar', '_edit_nonce', false ); ?>
    </form>
 </div>

<!--[if lt IE 8]>
<script type="text/javascript">
jQuery(function(){
    csSidebars.showMessage('<?php _e( 'You are using an old browser and some features of custom sidebars are not available. You will be notified when you try to use them. Did you ever think about updating your browser?',  CSB_LANG ) ?>');
});
</script>
<![endif]-->