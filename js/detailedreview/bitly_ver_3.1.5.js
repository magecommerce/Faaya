/*jslint browser: true, regexp: true, devel: true */
(function (window, document, $, undefined) {
    'use strict';
    $(document).on('click', 'div.social-share a.twitter-share-button', function (e) {
        e.preventDefault();
        var type = $(this).data('social-type'),
            long_url = $(this).data('url'),
            action_url = $(this).data('action'),
            text = $(this).data('text'),
            via = $(this).data('via'),
            url = 'http://twitter.com/share?count=none&text=' + encodeURIComponent(text || '') + '&via=' + encodeURIComponent(via || '') + '&url=';
        $.ajax({
            type: 'POST',
            //long_url this is parameter which is transmitted to the action
            url: action_url,
            data: {url: long_url},
            async: false,
            dataType: 'json',
            success: function(response) {
                if (url && response.status_code === 200) {
                    window.open(url + encodeURIComponent(response.data.url), 'sharer', 'toolbar=0,status=0,width=700,height=400');
                }
                if (response && response.status_code !== 200) {
                    new PNotify({
                        text: response.message
                    });
                }
            }
        });
    });

}(window, document, DRjQuery));
