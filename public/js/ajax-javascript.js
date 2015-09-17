/**
 * Created by robwilde on 13/04/2015.
 */
jQuery.ajax({
    url: ajaxurl,
    data: {
        action: 'get_postcode'
    },
    type: 'GET'
});
