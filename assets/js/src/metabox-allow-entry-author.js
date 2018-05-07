/*global jQuery:false */
/*global console:false */
/*global document:false */
/*global ajaxurl:false */
(function($){
    jQuery(document).ready( function($) {
        $('#screen-options-wrap .cs-allow-author input[type=checkbox]').on( 'change', function() {
            var data = {
                'action': 'custom_sidebars_allow_author',
                '_wpnonce': $('#custom_sidebars_allow_author').val(),
                'value': this.checked
            };
            $.post( ajaxurl, data );
        });
    });
})(jQuery);
