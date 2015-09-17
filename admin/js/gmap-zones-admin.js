jQuery(document).ready(function ($) {
    /**
     * All of the code for your admin-specific JavaScript source
     * should reside in this file.
     *
     * Note that this assume you're going to use jQuery, so it prepares
     * the $ function reference to be used within the scope of this
     * function.
     *
     * From here, you're able to define handlers for when the DOM is
     * ready:
     *
     * $(function() {
	 *
	 * });
     *
     * Or when the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and so on.
     *
     * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
     * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
     * be doing this, we should try to minimize doing that in our own work.
     */

    // Code for docs demos
    function createColorpickers() {
        // Api demo
        var bodyStyle = $('body')[0].style;
        $('#demo_apidemo').colorpicker({
            color: bodyStyle.backgroundColor
        }).on('changeColor', function (ev) {
            bodyStyle.backgroundColor = ev.color.toHex();
        });

        // Horizontal mode
        $('#demo_forceformat').colorpicker({
            format: 'rgba', // force this format
            horizontal: true
        });

        $('.demo-auto').colorpicker();

        // Disabled / enabled triggers
        $(".disable-button").click(function (e) {
            e.preventDefault();
            $("#demo_endis").colorpicker('disable');
        });

        $(".enable-button").click(function (e) {
            e.preventDefault();
            $("#demo_endis").colorpicker('enable');
        });
    }

    createColorpickers();

    // Create / destroy instances
    $('.demo-destroy').click(function (e) {
        e.preventDefault();
        $('.demo').colorpicker('destroy');
        $(".disable-button, .enable-button").off('click');
    });

    $('.demo-create').click(function (e) {
        e.preventDefault();
        createColorpickers();
    });

});
