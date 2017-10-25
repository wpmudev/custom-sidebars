/*global jQuery:false */
/*global console:false */
/*global document:false */
/*global ajaxurl:false */

/**
 * Handle "Custom sidebars configuration is allowed for:" option on
 * widgets screen options.
 */
(function($){
    jQuery(document).ready( function($) {
        $('#screen-options-wrap .cs-roles input[type=checkbox]').on( 'change', function() {
            var data = {
                'action': 'custom_sidebars_metabox_roles',
                '_wpnonce': $('#custom_sidebars_metabox_roles').val(),
                'fields': {}
            };
            $('#screen-options-wrap .cs-roles input[type=checkbox]').each( function() {
                data.fields[$(this).val()] = this.checked;
            });
            $.post( ajaxurl, data );
        });
    });
})(jQuery);
