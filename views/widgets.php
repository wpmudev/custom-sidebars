<?php
/**
 * Updates the default widgets page of the admin area.
 * There are some HTML to be added for having all the functionality, so we
 * include it at the begining of the page, and it's placed later via js.
 */
?>

<div id="cs-widgets-extra">
    <div id="oldbrowsererror" class="message error"><?php _e('You are using an old browser that doesn\'t support draggin widgets to a recently created sidebar. Refresh the page to add widgets to this sidebar and think about to update your browser.',  CSB_LANG ); ?></div>
    <div id="cs-title-options">
        <h2><?php _e('Sidebars', CSB_LANG ) ?></h2>
        <div id="cs-options" class="cs-options">
            <span><img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-feedback" title="" alt=""></span><a href="themes.php?page=customsidebars" class="button create-sidebar-button"><?php _e('Create a new sidebar', CSB_LANG ) ?></a>
        </div>
    </div>
    <div id="cs-new-sidebar" class="widgets-holder-wrap">

        <div class="sidebar-name">
            <div class="sidebar-name-arrow"><br></div>
            <h3><?php _e('New Sidebar', CSB_LANG ) ?><span><img src="<?php echo admin_url() ?>/images/wpspin_light.gif" class="ajax-feedback" title="" alt=""></span></h3>
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
    <div class="cs-edit-sidebar"><a class="where-sidebar thickbox" href="<?php echo admin_url('admin-ajax.php'); ?>?action=cs-ajax&cs_action=where&id=" title="<?php _e('Where do you want the sidebar?', CSB_LANG ) ?>"><?php _e('Where?', CSB_LANG )?></a><span class="cs-edit-separator"> | </span><a class="edit-sidebar" href="themes.php?page=customsidebars&p=edit&id="><?php _e('Edit', CSB_LANG )?></a><span class="cs-edit-separator"> | </span><a class="delete-sidebar" href="themes.php?page=customsidebars&p=delete&id="><?php _e('Delete', CSB_LANG )?></a></div>
    <div class="cs-cancel-edit-bar"><a class="cs-advanced-edit" href="themes.php?page=customsidebars&p=edit&id="><?php _e('Advanced Edit',  CSB_LANG ) ?></a><span class="cs-edit-separator"> | </span><a class="cs-cancel-edit" href="#"><?php _e('Cancel',  CSB_LANG ) ?></a></div>
    <div id="cs-save"><?php echo _e('Save', CSB_LANG ); ?></div>
    <span id="cs-confirm-delete"><?php _e('Are you sure that you want to delete the sidebar',  CSB_LANG ) ?></span>
    <form id="cs-wpnonces">
        <?php wp_nonce_field( 'cs-delete-sidebar', '_delete_nonce', false ); ?>
        <?php wp_nonce_field( 'cs-edit-sidebar', '_edit_nonce', false ); ?>
    </form>
    <?php include( 'part-footer.php' ); ?>
 </div>

<!--[if lt IE 8]>
<script type="text/javascript">
jQuery(function(){
    csSidebars.showMessage('<?php _e( 'You are using an old browser and some features of custom sidebars are not available. You will be notified when you try to use them. Did you ever think about updating your browser?',  CSB_LANG ) ?>');
});
</script>
<![endif]-->