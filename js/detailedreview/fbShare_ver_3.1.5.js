/*jslint browser: true */
var fbShare = function(id) {
    var data = (window['fbShareComment' + id]) ? window['fbShareComment' + id] : fbShareComment;
    var notifyDelay = 3000;
    FB.ui(
        {
            method: 'share_open_graph',
            action_type: 'og.shares',
            display: 'popup',
            action_properties: JSON.stringify({
                object: {
                    'og:url': decodeURIComponent(data[id].url),
                    'og:title': data[id].name,
                    'og:description': data[id].detail,
                    'og:image': data[id].image
                }
            })
        },
        function(response) {
            if (typeof response != 'undefined') {
                new PNotify({
                    text: data.success,
                    type: 'success',
                    delay: notifyDelay
                });
            } else {
                new PNotify({
                    text: data.error,
                    type: 'error',
                    delay: notifyDelay
                });
            }
        }
    );
};
