;(function ($) {
    'use strict';

    // breakpoints were added based on the skin/frontend/rwd/default/js/app.js
    var bp = {
        xsmall: 479,
        small: 599,
        medium: 770,
        large: 979,
        xlarge: 1199
    };
    
    window.MageWorkshopHelper = function (config) {
        this.config = {
            pnotifyPosition: {
                dir1: "down",
                dir2: "left",
                firstpos1: 36,
                firstpos2: 36
            }

        };
        $.extend(this.config, config);
    };

    /** Get PNotify position base on screen width for responsive design */
    MageWorkshopHelper.prototype.stackPNotify = function () {
        var that = this;
        var pnotify_position = that.config.pnotifyPosition;

        if (screen.width > bp.small && screen.width <= bp.medium) {
            pnotify_position.firstpos2 = 0;
        } else if (screen.width <= bp.small) {
            pnotify_position.firstpos2 = 0;
        }

        that.config.pnotifyPosition = pnotify_position;
        return pnotify_position;
    };

    MageWorkshopHelper.prototype.isIOSTabletMobile = function () {
        return (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream);
    };

    MageWorkshopHelper.prototype.isSafari = function () {
        return navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1;
    };

    $.fn.mageWorkshopHelper = function (options) {
        new MageWorkshopHelper(options);
    };
    
}(DRjQuery));
