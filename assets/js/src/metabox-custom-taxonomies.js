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
        $('#screen-options-wrap .cs-custom-taxonomies input[type=checkbox]').on( 'change', function() {
            var data = {
                'action': 'custom_sidebars_metabox_custom_taxonomies',
                '_wpnonce': $('#custom_sidebars_custom_taxonomies').val(),
                'fields': {}
            };
            $('#screen-options-wrap .cs-custom-taxonomies input[type=checkbox]').each( function() {
                data.fields[$(this).val()] = this.checked;
            });
            $.post( ajaxurl, data );
        });
    });
})(jQuery);
