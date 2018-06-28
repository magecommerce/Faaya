;(function($) {
    $.fn.reviewShare = function(options) {
        var ReviewShare = function() {
            $.extend(true, this.options, options);
            this.bindEvent();
        };

        ReviewShare.prototype.options = {
            customerName: 'Guest',
            customerEmail: null,
            shareReviewByEmailUrl: null,
            defaultFailMessage: 'Something went wrong.',
            currentRecipientCount: null,
            recipientMaxCount: null,
            maxRecipientMessage: 'Max recipients limit exceeded.',
            _validateInstance: null,
            _shareReviewImage: null,
            _isAjaxRequestRunning: false,
            pnotifyDelay: 3000
        };

        ReviewShare.prototype.bindEvent = function () {
            $(document).on('click', '.drw-share-email', {that: this}, this.showEmailPopup);
            $(document).on('click', '.drw-email-close, .email-popup-background', {that: this}, this.hideEmailPopup);
            $(document).on('click', '.drw-email-submit', {that: this}, this.submitEmailShare);
            $(document).on('keyup', {that: this}, this.closePopupOnKeyup);
            $(document).on('click', '.add-recipient-button', {that: this}, this.addRecipientEmail);
            $(document).on('click', '.recipient-btn-remove', {that: this}, this.removeRecipientEmail);
        };

        ReviewShare.prototype.showEmailPopup = function(event) {
            var $drwEmailPopupBackground = $('.drw-email-popup-wrapper');
            var $drwEmailPopupImage      = $('.drw-email-popup-image');
            var shareReviewImage         = $(this).closest('.drw-box.drw-grid-item').find('.drw-image-review img').attr('src');
            if ($(this).parents('#customer-reviews').length) {
                shareReviewImage = $(this).closest('.review-dd').find('.image-popup').attr('href');
                $('html').addClass('email-share-popup');
            }
            var self = event.data.that;

            $('.drw-email-message-link').attr('href', $(this).data('share-url'));
            $('.drw-email-share-name').val(self.options.customerName);
            $('.drw-email-share-email').val(self.options.customerEmail);
            $('.drw-email-share-subject').val($(this).data('email-subject'));
            $('.drw-email-share-mail-body').text();
            $('.drw-email-share-link').val($(this).data('share-url'));
            $drwEmailPopupBackground.removeClass('email-share-hide').addClass('email-share-show');

            if($(this).closest('.drw-box').hasClass('image') || $(this).closest('.review-dd').has('.image-review').length) {
                $('.drw-email-share-image').val(shareReviewImage);
                $drwEmailPopupImage.html('<img src="' + shareReviewImage + '" alt="">');
            } else {
                $drwEmailPopupBackground.addClass('drw-without-image');
            }
        };

        ReviewShare.prototype.closePopupOnKeyup = function (event) {
            if (event.keyCode == 27) {
                event.data.that.hideEmailPopup(event);
            }
        };

        ReviewShare.prototype.hideEmailPopup = function(event) {

            if ((typeof event.options != 'undefined' && !event.options._isAjaxRequestRunning) || (typeof event.data != 'undefined' && !event.data.that.options._isAjaxRequestRunning)) {
                var $drwEmailPopupBackground = $('.drw-email-popup-wrapper');

                if (typeof event.data != 'undefined' && event.data.that.options._validateInstance !== null) {
                    event.data.that.options._validateInstance.validator.reset();
                }

                var $drwShareEmailMessage = $('.drw-share-email-messages');

                if ($drwShareEmailMessage.html().length > 0) {
                    $drwShareEmailMessage.html('');
                }
                $('.drw-email-popup-image').html('');
                $drwEmailPopupBackground.removeClass('email-share-show').removeClass('drw-without-image').addClass('email-share-hide');
            }
        };

        ReviewShare.prototype.addRecipientEmail = function(event) {
            var that = event.data.that;
            if (that.options.currentRecipientCount < that.options.recipientMaxCount) {
                var html = $('<div/>').html('&lt;div class="additional">&lt;a href="#" class="recipient-btn-remove">x&lt;/a>&lt;input type="email" name="drw-email-share-mail-to[]" class="drw-email-share-mail-to input-text required-entry validate-email"/>&lt;/div>').text();
                $(this).before(html);
                that.options.currentRecipientCount++;
            } else {
                $(this).css('display', 'none');
                var notice = new PNotify({
                    text: that.options.maxRecipientMessage,
                    type: 'info',
                    icon: false,
                    buttons: {
                        closer: false,
                        sticker: false
                    }
                });

                notice.get().click(function() {
                    notice.remove();
                });
            }
            return false;
        };

        ReviewShare.prototype.removeRecipientEmail = function(event) {
            var that = event.data.that;
            if (that.options.currentRecipientCount > 0) {
                $(this).closest('.additional').remove();
                $('.add-recipient-button').css('display', 'block');
                if (that.options.currentRecipientCount == 1) {
                    $('.add-recipient-button').trigger("click");
                }
                that.options.currentRecipientCount--;
            }
            return false;
        };

        ReviewShare.prototype.submitEmailShare = function(event) {
            var emailShareFormValidator = new VarienForm('drw-email-share-form');
            var that = event.data.that;

            if (emailShareFormValidator.validator.validate()) {
                event.preventDefault();
                that.emailShareAjax();
            } else {
                that.options._validateInstance = emailShareFormValidator;
            }
        };

        ReviewShare.prototype.emailShareAjax = function(event) {
            var that = this;

            $('.drw-email-loader').removeClass('email-share-hide').addClass('email-share-show');
            $.ajax({
                url: that.options.shareReviewByEmailUrl,
                data: $('#drw-email-share-form').serialize(),
                method: "POST",
                beforeSend: function() {
                    that.options._isAjaxRequestRunning = true;
                }
            }).done(function (response) {
                $('.drw-email-loader').removeClass('email-share-show').addClass('email-share-hide');
            }).fail(function () {
                that.buildMessageList('error', that.options.defaultFailMessage);
            }).complete(function (response) {
                that.options._isAjaxRequestRunning = false;
                response = $.parseJSON(response.responseText);

                that.buildMessageList(response.status, response.message);
                if (response.status == 'success') {
                    new PNotify({
                        text: response.message,
                        type: response.status,
                        icon: false
                    });
                    that.hideEmailPopup(that);
                }
            });
        };

        ReviewShare.prototype.buildMessageList = function(type, message) {
            var drwMessageBlock = $('.drw-share-email-messages');
            drwMessageBlock.html(
                '<li class="' + type + '-msg"><ul><li>' + message + '</li></ul></li>'
            );

            drwMessageBlock.removeClass('email-share-hide').addClass('email-share-show');
        };


        return new ReviewShare(options);
    };

}(DRjQuery));
