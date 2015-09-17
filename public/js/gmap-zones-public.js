jQuery(document).ready(function ($) {

    var $popup_modal = $('.popup-modal');
    var $body = $('body');

    // grab the add to cart click on the archive page
    $body.on('added_to_cart', function (e, data) {
        if ($('#gmapz_modal').length && $.cookie('chef_poa_cookie') == null) {
            $.magnificPopup.open({
                closeOnContentClick: false,
                closeOnBgClick: false,
                closeBtnInside: false,
                items: {
                    src: '#gmapz_modal'
                },
                type: 'inline'
            });
        }
        resizeMap();
    });

    // hover over the add-to-cart button on the single item page
    var $single_cart_btn = $('.single_add_to_cart_button');
    $single_cart_btn.on('hover', function ( ) {
        if ($('#gmapz_modal').length && $.cookie('chef_poa_cookie') == null) {
            $.magnificPopup.open({
                closeOnContentClick: false,
                closeOnBgClick: false,
                closeBtnInside: false,
                items: {
                    src: '#gmapz_modal'
                },
                type: 'inline'
            });
        }
        resizeMap();
    });

    $(document).on('click', '.popup-modal-dismiss', function (e) {
        e.preventDefault();
        $.magnificPopup.close();
    });

    function resizeBootstrapMap() {
        var mapParentWidth = $('#modal_body').width();
        var gMapID = $('#map');
        gMapID.width(mapParentWidth);
        gMapID.height(3 * mapParentWidth / 4);
        google.maps.event.trigger(gMapID, 'resize');
        console.log(mapParentWidth);
    }

    // resize the map whenever the window resizes
    $(window).resize(resizeBootstrapMap);

});
