(function ($) {
    $(document).ready(function() {
        $('.image-popup').fancybox({
            "autoScale": true,
            "autoDimensions": true,
            "helpers": {
                "overlay": {
                    "locked": false
                }
            }
        });
    });
}(DRjQuery));
