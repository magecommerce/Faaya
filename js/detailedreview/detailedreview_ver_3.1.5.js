/*jslint browser: true, regexp: true, devel: true */
(function ($) {
    'use strict';

    var ACTIONS = {
        'OPEN': 'open',
        'CLOSE': 'close',
        'HIDE': 'hide',
        'SHOW': 'show',
        'DESTROY': 'destroy',
        'REMOVE': 'remove'
    };

    var DetailedReview = function (config) {
        this.config = {};
        $.extend(this.config, config);
        this.init();
    };

    DetailedReview.prototype.init = function () {
        this.mageWorkshopHelper = new MageWorkshopHelper();
        this.initfancyBox();
        this.showAjaxLoader();
        this.hideAjaxLoader();
        this.closeForm();
        this.addReviewByPlaceholder();
        this.addReviewByPlaceholderDR();
        this.reviewDateFilters();
        this.showImageName();
        this.checkHash();
        this.showVersionDR();
        this.shortText();
        $(document).on("change", this.config.chooseImageClass, {config: this}, this.handleRemoveImageLink);
        $(document).on("click", this.config.moreImagesLink, {config: this}, this.addImage);
        $(document).on("click", this.config.removeImageLink, {config: this}, this.removeImage);
        $(document).on("click", this.config.reviewDialog, {config: this}, this.showReviewForm);
        $(document).on("submit", this.config.reviewForm, {config: this}, this.submitForm);
        $(document).on("click", this.config.reviewVoteRating, {config: this}, this.voting);
        $(document).on("click", this.config.backButton, {config: this}, this.showReviewList);
        $(document).on("click", this.config.dateFilterLink, {config: this}, this.showFilterList);
        $(document).on("click", this.config.sortsLink, {config: this}, this.showSortsList);
        $(document).on("click", this.config.openedList, {config: this}, this.hideFilterList);
        $(document).on("click", this.config.overallRatingItem, {config: this.config}, this.checkRatingStars);
        $(document).on("click", ('.' + this.config.moreLink), {config: this.config}, this.showShortText);
        $(this.config.prosCheckboxes).change({inverseType: 'cons'}, this.validateProsConsCheckboxes);
        $(this.config.consCheckboxes).change({inverseType: 'pros'}, this.validateProsConsCheckboxes);
        $(document).on("submit", this.config.loginForm, {config: this}, this.submitLoginForm);
        $(document).on("click", this.config.backLink, {config: this}, this.goBackFromSeparatePage);
        $(document).ready(this.showMessage);
    };

    DetailedReview.prototype.voting = function (event) {
        var detailedReviewObject = event.data.config,
            that = this;
        detailedReviewObject.config.voteValue = '';
        if (detailedReviewObject.config.isCustomerLoggedIn || detailedReviewObject.config.isGuestAllowToVote) {
            var $voteType = $(this).hasClass('helpful-btn')? 1 : 0;
            var $messageType = '';
            $.ajax({
                url: $('.helpful-form').attr('action'),
                data: {
                    is_helpful: $voteType,
                    review_id: $(this).closest('.js-helpful-voting').children('input[name=review_id]').attr('value')
                },
                dataType: 'json',
                success: function (data) {
                    if (data['msg']['type'] == 'success') {
                        if (parseInt($voteType)) {
                            $(that).closest('.rating-wrapper').find('.helpful-qty').html(data['helpful']);
                        } else {
                            $(that).closest('.rating-wrapper').find('.unhelpful-qty').html(data['unhelpful']);
                        }
                        $messageType = 'success';
                    } else {
                        $messageType = 'error';
                    }
                    PNotify.removeAll();
                    var pnotify_position = detailedReviewObject.mageWorkshopHelper.stackPNotify();
                    new PNotify({
                        text: data['msg']['text'],
                        type: $messageType,
                        icon: false,
                        stack: pnotify_position
                    });
                }
                //error: function (data) {
                //}
            });
        } else {
            detailedReviewObject.config.voteValue = this;
            detailedReviewObject.initLoginDialog();
        }
    };

    DetailedReview.prototype.formDisplaying = function (obj, action, options) {
        var reviewFormOptions = {
            zIndex: 500
        };
        if (typeof options !== "undefined") {
            $(reviewFormOptions).extend(options);
        }
        var actionMapping = {
            popup: {
                open: ACTIONS.OPEN,
                close: ACTIONS.CLOSE,
                destroy: ACTIONS.DESTROY
            },
            non_popup: {
                open: ACTIONS.SHOW,
                close: ACTIONS.HIDE,
                destroy: ACTIONS.REMOVE
            }
        };
        if (this.config.isShowPopup) {
            this.initReviewDialog();
        } else {
            obj[actionMapping['non_popup'][action]]('fade');
        }
        if (typeof options !== 'undefined') {
            options.each(function (element) {
                obj(element);
            });
        }
        if (!this.config.isShowPopup) {
            this.formSwitcher(action);
        }

    };

    DetailedReview.prototype.formSwitcher = function (action) {
        if (action == ACTIONS.OPEN) {
            $(this.config.reviewTop).hide();
            $(this.config.customerReviews).hide();
        }
        if (action == ACTIONS.CLOSE ) {
            $(this.config.reviewTop).show();
            $(this.config.customerReviews).show();
            $(window).scrollTop($(this.config.reviewsBlock).offset().top - 120);
        }
    };

    DetailedReview.prototype.submitForm = function (event) {
        var that = event.data.config;
            if (that.config.isCaptchaEnabled) {
                $.ajax({
                    url: that.config.checkCaptchaUrl,
                    data: this.serialize(),
                    async: false,
                    success: function (data) {
                        if (data == 'invalid') {
                            $(that.config.captchaError).html(that.config.messages.captchaError);
                            grecaptcha.reset();
                            that.hideReviewButtons();
                        } else {
                            $(that.config.captchaError).html('');
                        }
                    },
                    error: function () {
                        $(that.config.captchaError).html(that.config.messages.someError);
                        that.hideReviewButtons();
                        event.preventDefault();
                        return false;
                    }
                });
                if ($(that.config.captchaError).html() !== '') {
                    return false;
                }
            }
            if (dataForm.validator.validate() == true) {
                $(that.config.reviewSubmitButton).attr('disabled', 'disabled');
                if (that.config.isAjaxSubmit && (!that.getAndroidVersion() || parseFloat(that.getAndroidVersion()) > 4.1)) {
                    that.submitFormAjax();
                } else {
                    if (!that.config.isSeparatePage) {
                        that.formDisplaying(that.initReviewForm(), ACTIONS.CLOSE);
                    }
                    $(that.config.reviewSubmitButton).removeAttr('disabled');
                    document.location.replace(that.config.productPage);
                    return true;
                }
            } else {
                that.hideReviewButtons();
                if (typeof(grecaptcha) !== 'undefined') {
                    grecaptcha.reset();
                    return false;
                }
                event.preventDefault();
                return false;
            }
        event.preventDefault();
        return false;
    };

    DetailedReview.prototype.showMessage = function () {
        if(localStorage.getItem('responseMessage')) {
            var responseMessage = JSON.parse(localStorage.getItem('responseMessage'));
            new PNotify({
                text: responseMessage.text,
                type: (responseMessage.type) ? responseMessage.type : 'success'
            });
            localStorage.setItem('responseMessage', '');
        }
    };

    DetailedReview.prototype.submitFormAjax = function (e) {
        var that = this;
        var $submitForm = $(that.config.reviewForm);

        // var preparedSubmitFormData = $submitForm.serialize();
        var preparedSubmitFormData = new FormData($submitForm[0]);

        if (that.config.allowSizing) {
            var data = that.preparingSizingData($submitForm);
            if (typeof data.sizing !== 'undefined') {
                preparedSubmitFormData.append('sizing', data.sizing);
            }
        }

        $.ajax({
            type: 'POST',
            data: preparedSubmitFormData,
            url: $submitForm.attr('action'),
            contentType: false,
            processData: false,
            success: function (response) {
                response = $.parseJSON(response);

                var responseMessage = {
                    'text': response.messages.replace(/\[\[/g, '<'),
                    'type': response.type
                };

                if (response.success) {
                    that.clearForm($submitForm);


                    if (that.config.isSeparatePage) {
                        var responseText = '<a id="separate-go-back" href="">' + that.config.messages.goBackMessage + '</a>';
                        $($('.reviews-container .product-review')).append(responseText);
                    }

                    that.formDisplaying(that.initReviewForm(), ACTIONS.CLOSE);

                    new PNotify({
                        text: responseMessage.text,
                        type: 'success',
                        stack: that.mageWorkshopHelper.stackPNotify()
                    });

                    if (response.html && !that.config.isSeparatePage) {
                        var html = response.html.replace(/\[\[/g, '<');
                        $('.reviews-container').html(html);
                        that.shortText();
                        that.initfancyBox();
                        if (that.config.isCaptchaEnabled) {
                            if (typeof grecaptcha != 'undefined') {
                                grecaptcha.reset();
                            }
                        }
                    }
                    if (drReviewLoader) {
                        drReviewLoader.removeHashUrl();
                        if (that.config.autoApproveFlag) {
                            drReviewLoader.bindEvent();
                            var tab = $('.review-sorts ul li').get(1);
                            $(tab).trigger('click');
                        }
                    }

                    $.fancybox.close();
                } else {
                    if (typeof(grecaptcha) !== 'undefined') {
                        grecaptcha.reset();
                    }
                    new PNotify({
                        text: responseMessage.text,
                        type: responseMessage.type,
                        width: "400px",
                        stack: that.mageWorkshopHelper.stackPNotify()
                    });
                }
                if (that.config.currentImageCount < 1) {
                    that.config.currentImageCount = 1;
                }
            },
            error: function (data) {
            },
            complete: function() {
                $(that.config.reviewSubmitButton).removeAttr('disabled');
            }
        });
    };

    DetailedReview.prototype.preparingSizingData = function (submitForm) {
        var formFields = submitForm.serializeArray();
        var slider     = document.getElementById('slider');

        // Get sizing data or set default data
        var currentSizingValue = slider.noUiSlider.get()
            ? (Math.round(parseFloat(slider.noUiSlider.get())) + 1)
            : 4;

        var data = {};

        for (var index in formFields) {
            if (formFields.hasOwnProperty(index)) {
                var field = formFields[index];

                if (field.name == 'sizing') {
                    field.value = currentSizingValue ? currentSizingValue : 4;
                }
            }
        }

        if (typeof data['sizing'] == 'undefined') {
            data['sizing'] = currentSizingValue;
        }

        return data;
    };


    DetailedReview.prototype.clearForm = function($form) {
        $form[0].reset();
        this.clearRatings();
        /* remove extra image uploader into Review Form */
        $('.more-images').not(':first').remove();
        /* reset config for image uploaders  count */
        this.config.currentImageCount = 1;
        /* remove selected images after Form submit */
        $('.choosed-image-name').remove();
        $(this.config.chooseImageClass).removeClass('showed');
        $(this.config.chooseImageClass).parents('.more-images').find('.remove-img').css('display','none');
        $("#add-more-images").css('display', 'none');
        if (this.config.allowSizing) {
            // 3 is middle size (default value on init)
            var slider = document.getElementById('slider');
            slider.noUiSlider.set(3);
        }
        $form.find(':checkbox').removeAttr('disabled');
        $('.dropcontainer > ul li').first().click();
        $('.dropcontainer > ul').removeClass('dropdownvisible').addClass('dropdownhidden');
    };

    DetailedReview.prototype.addReviewByPlaceholder = function () {
        if ($(this.config.reviewPlaceholder).length) { // addByPlaceholder
            if ($('#product_tabs_review_tabbed_contents #review-form').length != 0) {
                new PNotify({
                    text: this.config.messages.easyTabAlert,
                    type: 'error',
                    icon: false,
                    stack: this.mageWorkshopHelper.stackPNotify()
                });
            }
            $(this.config.reviewPlaceholder).html($$(this.config.reviewsBlock).clone(true));
        } else if ($(this.config.reviewEasyTab).length) {
            $(this.config.reviewEasyTab).html($$(this.config.reviewsBlock).clone(true));
            dataForm = new VarienForm(this.config.reviewForm.substring(1));
            var validator = new Validation(this.config.reviewForm.substring(1), {immediate : true});
            validator.validate();
        }
    };

    DetailedReview.prototype.addReviewByPlaceholderDR = function() {
        if ($(this.config.reviewPlaceholder).length < 1 && !$(this.config.reviewEasyTab).length && $(this.config.reviewPlaceholderDR).length) { // addByPlaceholderDR
            $(this.config.reviewPlaceholderDR).html($$(this.config.reviewsBlock).clone(true));
        } else if ($(this.config.reviewEasyTab).length) {
            $(this.config.reviewPlaceholderDR).parent().remove();
        }
    };

    DetailedReview.prototype.openReviewEasyTabs = function() {
        if (!$(this.config.reviewPlaceholder).length && $(this.config.reviewEasyTab).length) {
            $('.product-view .product-collateral ul.tabs li').each(function (index, el) {
                var $contents = $('#' + el.id+'_contents');
                if (this.id == 'product_tabs_review_tabbed') {
                    $(this).addClass('active');
                    $contents.show();
                } else {
                    $(this).removeClass('active');
                    $contents.hide();
                }
            });
            if ($('ul.tabs li#product_tabs_review_tabbed a').length) {
                Varien.Tabs.prototype.initTab($('ul.tabs li#product_tabs_review_tabbed a').get(0));
            }
            return true;
        }
        return false;
    };

    DetailedReview.prototype.addImage = function (event) {
        var that = event.data.config;
        if (that.config.currentImageCount < that.config.imageMaxCount) {
            var html = $('<div/>').html('&lt;div class="more-images">&lt;div class="choose-image">&lt;span>' + that.config.messages.chooseFile + '&lt;/span>&lt;input type="file" name="image[]" class="addedInput image_field validate-filesize" value="" />&lt;/div>&lt;a href="#" class="remove-img" style="display: block;">&lt;/a>&lt;div class="clearboth">&lt;/div>').text();
            $('#add-file-input-box').append(html);
            that.showImageName();
            that.config.currentImageCount++;
                $("#add-more-images").css('display', 'none');
        } else {
            $("#add-more-images").css('display', 'none');
            var notice = new PNotify({
                text: that.config.messages.maxUploadNotify,
                type: 'info',
                icon: false,
                buttons: {
                    closer: false,
                    sticker: false
                },
                stack: that.mageWorkshopHelper.stackPNotify()
            });

            notice.get().click(function() {
                notice.remove();
            });
        }
        return false;
    };

    DetailedReview.prototype.removeImage = function (event) {
        var that = event.data.config;
        if (that.config.currentImageCount > 0) {
            var actualImageCount = $('#add-file-input-box').children('.more-images').size();
            $(this).parents('.more-images').remove();
            $("#add-more-images").css('display', 'block');
            if (that.config.currentImageCount == 1 && actualImageCount == 1) {
                $(that.config.moreImagesLink).trigger("click");
            }
            that.config.currentImageCount--;
        }
        if (that.config.imageMaxCount == 1) {
            var html = $('<div/>').html('&lt;div class="more-images">&lt;div class="choose-image">&lt;span>' + that.config.messages.chooseFile + '&lt;/span>&lt;input type="file" name="image[]" class="addedInput image_field" value="" />&lt;/div>&lt;a href="#" class="remove-img" style="display: block;">&lt;/a>&lt;div class="clearboth">&lt;/div>').text();
            $('#add-file-input-box').append(html);
        }
        if (that.config.currentImageCount == 1 && !$('.choosed-image-name').length) {
            $(".review-dialog-block .upload-image .remove-img").css('display', 'none');
        }
        if (that.config.currentImageCount == 0) {
            $(this).parents('.more-images').find('.choosed-image-name').remove();
        }
        return false;
    };

    DetailedReview.prototype.handleRemoveImageLink = function (event) {
        if(event.handleObj.selector == "#add-more-images" ) {
            return false;
        }
        var that = event.data.config;
        if ($(event.handleObj.selector).parents('.more-images').find('.choosed-image-name').length) {
            $(event.handleObj.selector).parents('.more-images').find('.remove-img').css('display','block');
            if (that.config.currentImageCount < that.config.imageMaxCount) {
                $(that.config.moreImagesLink).css('display', 'block');
            }
        }
    };

    DetailedReview.prototype.initReviewForm = function () {
        var $form = $('#review-dialog-block');
        if (this.config.isShowPopup) {
            this.initReviewDialog();
        } else {
            var that = this;
            $form.addClass('non-popup');
            $('.minimize').bind('click', function () {
                that.formDisplaying($form, ACTIONS.CLOSE);
            });
        }
        return $form;
    };

    DetailedReview.prototype.initReviewDialog = function () {
        var reviewDialog = $('#review-dialog-block');

        if (reviewDialog) {
            $.fancybox.open([
                reviewDialog
            ], {
                fitToView     : false,
                closeClick    : false,
                padding       : 15,
                openEffect    : 'fade',
                closeEffect   : 'fade',
                scrollOutside : false,
                autoSize      : true,
                wrapCSS       : 'review-dialog-modal',
                maxWidth      : '100%',
                scrolling     : 'no',
                helpers : {
                    overlay : {
                        locked : false
                    }
                },
                afterClose: function() {
                    if (drReviewLoader) {
                        drReviewLoader.removeHashUrl();
                    }
                }
            });

            return reviewDialog;
        }

        return false;
    };

    DetailedReview.prototype.checkHash = function() {
        if(window.location.hash) {
            var hash = window.location.hash.substring(1);
            if (hash == 'review-form' || hash == 'review-dialog-block') {
                var event = {data:
                {config: this}
                };
                var isReviewEasyTabs = this.openReviewEasyTabs();
                if (isReviewEasyTabs) {
                    this.showReviewForm(event);
                    $('html, body').animate({
                        scrollTop: $('#feedback').offset().top
                    }, 1000);
                }
            }
        }
        return false;
    };

    DetailedReview.prototype.showReviewForm = function (event) {
        event.preventDefault();
        var that = event.data.config;
        if (that.config.onlyVerifiedBuyer) {
            that.showOnlyVerifiedBuyer();
        } else {
            if(that.config.writeReviewOnce) {
                that.allowWriteReviewOnce();
            } else {
                $('form [name=referer]').val($(this).prev().val());
                $('form [name=success_url]').val($(this).prev().prev().val());
                that.checkUserSettings();
            }
        }
    };
    DetailedReview.prototype.checkUserSettings = function () {
        var that = this;
        if (that.config.isCustomerLoggedIn || that.config.isGuestAllowToWrite) {
            if (!that.config.isSeparatePage) {
                that.getReviewForm();
            } else {
                var url = that.config.separatePage;

                if (that.mageWorkshopHelper.isSafari()) {
                    window.location.href = url;
                } else {
                    window.open(url, '_blank');
                }
            }
        } else {
            that.initLoginDialog();
        }
    };

    DetailedReview.prototype.showOnlyVerifiedBuyer = function() {
        var that = this;
        var productId = that.config.productId;
        $.ajax({
            dataType: 'json',
            url: that.config.productIdsAllowReviewUrl,
            data: {product_id: productId},
            success: function (data) {
                if (data.code == 200) {
                    if (data.hasOwnProperty('isVerified') && data.isVerified) {
                        if(that.config.writeReviewOnce) {
                            that.allowWriteReviewOnce();
                        } else {
                            that.checkUserSettings();
                        }
                    } else {
                        new PNotify({
                            text: that.config.messages.onlyVerifiedBuyer,
                            stack: that.mageWorkshopHelper.stackPNotify()
                        });
                    }
                } else {
                    new PNotify({
                        text: data.message,
                        type: 'error',
                        stack: that.mageWorkshopHelper.stackPNotify()
                    });
                }
            },
            error: function () {
            }
        });
    };

    DetailedReview.prototype.allowWriteReviewOnce = function() {
        var that =  this;
        var values = {};
        values['product_id'] = that.config.productId;
        $.ajax({
            url: that.config.checkWriteReviewOnce,
            data: values,
            success: function (data) {
                var jsonObj = JSON.parse(data);
                if (jsonObj.length) {
                    new PNotify({
                        text: that.config.messages.alreadyReviewed,
                        icon: false,
                        stack: that.mageWorkshopHelper.stackPNotify()
                    });
                } else {
                    that.checkUserSettings();
                }
            },
            error: function () {
            }
        });
    };

    DetailedReview.prototype.getReviewForm = function() {
        $('form [name=referer]').val($(this).prev().val());
        $('form [name=success_url]').val($(this).prev().prev().val());
        this.formDisplaying(this.initReviewForm(), ACTIONS.OPEN);
    };

    DetailedReview.prototype.closeForm = function (e) {
        $(document).keyup(function (e) {
            if (e.which == 27) {
                $("#jquery-lightbox").fadeOut("slow");
                $("#jquery-overlay").fadeOut("slow");
            }
        });
    };

    DetailedReview.prototype.initfancyBox = function () {
        $('.image-popup').fancybox(this.config.fancyBoxConfig);
    };

    DetailedReview.prototype.initLoginDialog = function () {

        var loginDialog =  $("#login-dialog-block");

        if (loginDialog) {
            $.fancybox.open([
                loginDialog
            ], {
                fitToView     : false,
                closeClick    : false,
                padding       : 15,
                openEffect    : 'fade',
                closeEffect   : 'fade',
                scrollOutside : false,
                autoSize      : true,
                wrapCSS       : 'login-dialog-modal',
                maxWidth      : '100%',
                scrolling     : 'no',
                helpers : {
                    overlay : {
                        locked : false
                    }
                }
            });
        }
    };

    DetailedReview.prototype.submitLoginForm = function (event) {
        var that = event.data.config;
        var dataLoginForm = new VarienForm('login-form', true);
            if (dataLoginForm.validator && dataLoginForm.validator.validate()) {
                $.ajax({
                    url: that.config.checkLoginUrl,
                    data: $(dataLoginForm.form).serialize(),
                    success: function (data) {
                        var jsonObj = JSON.parse(data);
                        if (jsonObj.data === '1') {
                            $('#login-form').find("input[name='form_key']").attr('value', jsonObj.form_key);
                            dataLoginForm.form.submit();
                            if(that.config.voteValue) {
                                that.config.isCustomerLoggedIn = true;
                                $(that.config.voteValue).click();
                            }
                        } else {
                            $('.account-login p.error-message').html(jsonObj.data);
                        }
                    },
                    error: function () {
                    }
                });
            }
            return false;
    };

    DetailedReview.prototype.submitFormValidate = function () {
        var that = this;
        var dataRegForm = new VarienForm('form-validate', true);
        $('#form-validate').submit(function (event) { //submitFormValidate
            if (dataRegForm.validator && dataRegForm.validator.validate()) {
                var $inputs = $('#form-validate :input');
                var values = {};
                $inputs.each(function () {
                    values[this.name] = $(this).val();
                });
                $.ajax({
                    url: that.config.checkRegistrateUrl,
                    data: values,
                    success: function (data) {
                        if (data === '1') {
                            var redirectUrl = $(dataRegForm.form).find('[name="success_url"]').val();
                            if (redirectUrl == window.location.href) {
                                window.location.reload();
                            } else {
                                window.location.href = redirectUrl;
                            }
                        } else {
                            var jsonObj = JSON.parse(data);
                            var $messageType = '';
                            if (typeof(jsonObj.success) !== 'undefined') {
                                $messageType = 'success';
                            } else if (typeof(jsonObj.error) !== 'undefined') {
                                $messageType = 'error';
                            }
                            var message = $('.account-create p.' + $messageType + '-message').html(jsonObj[$messageType]);
                            $('html, body').animate({
                                scrollTop: message.offset().top + 'px'
                            }, 'fast');
                        }
                    }
                });
            }
            event.preventDefault();
            return false;
        });
    };

    DetailedReview.prototype.showAjaxLoader = function () {
        $(document).ajaxStart(function () { //showAjaxLoader
            $("#imageLoading").show();
        });
    };

    DetailedReview.prototype.hideAjaxLoader = function () {
        $(document).ajaxStop(function () {
            $("#imageLoading").hide();
        });
    };

    DetailedReview.prototype.hideReviewButtons = function () {
        $(this.config.reviewFormButton).get(0).style.display = '';
        document.getElementById(this.config.reviewSpinner).style.display = 'none';
    };
    DetailedReview.prototype.reviewDateFilters = function () {
        var selected = $(this.config.dateFilter).find('.selected').text();
        var selectedSorts = $(this.config.reviewSorts).find('.selected a').text();
        if(selected) {
            $(this.config.dateFilterSpan).text(selected);
        }
        if(selectedSorts) {
            $(this.config.sortsSpan).text(selectedSorts);
        }
    };
    DetailedReview.prototype.showReviewList = function (event) {
        var that = event.data.config;
        $(that.config.reviewTop).show();
        $(that.config.customerReviews).show();
        if(that.config.isShowPopup) {
            $.fancybox.close();
        } else if (that.config.isSeparatePage) {
            that.goBackFromSeparatePage(event);
        } else {
            $(".review-dialog-block").hide();
        }
        if (drReviewLoader) {
            drReviewLoader.removeHashUrl();
        }
    };
    DetailedReview.prototype.showFilterList = function (event) {
        var that = event.data.config;
        $(that.config.dateFilterLink).closest('ul').css({"height" : "auto", "z-index" : "1000"});
        $(that.config.dateFilterLink).find('.dateFilter').addClass('openedList');
    };
    DetailedReview.prototype.showSortsList = function (event) {
        var that = event.data.config;
        if ($(that.config.sortsLink).hasClass('openedList')) {
            return;
        }
        $(that.config.sortsLink).closest('ul').css('height','auto');
        $(that.config.sortsLink).closest('ul').children('li').css('width','100%');
        $(that.config.sortsLink).addClass('openedList');
    };
    DetailedReview.prototype.hideFilterList = function (event) {
        var that = event.data.config;
        var $self = $(this);
        event.stopPropagation();
        if ($self.closest('.select-review-sorts').length) {
            $(that.config.sortsLink).closest('ul').css('height', '40px');
            $(that.config.sortsLink).removeClass('openedList');
        } else {
            $(that.config.dateFilterLink).closest('ul').css({"height" : "40px", "z-index" : "0"});
            $(that.config.dateFilterLink).find('.dateFilter').removeClass('openedList');
        }
    };
    DetailedReview.prototype.showImageName = function () {
        $('.upload-image :input').last().change(function () {
            var filename = $(this).val();
            var lastIndex = filename.lastIndexOf("\\");
            if (lastIndex >= 0) {
                filename = filename.substring(lastIndex + 1);
            }
            if (filename.length >= 25) {
                var fileNameLength = filename.length;
                filename = filename.substr(0, 12) + '...' + filename.substr(fileNameLength - 8, fileNameLength)
            }
            if ($(this).hasClass('showed')) {
                $(this).parent().next().remove();
            } else {
                $(this).addClass('showed');
            }
            var html = $('<div/>').html('&lt;div class="choosed-image-name">&lt;span>' + filename + '</span></div>').text();
            $(this).parent().after(html);
        });
    };
    DetailedReview.prototype.validateProsConsCheckboxes = function (event) {
        var inverseType = event.data.inverseType;
        $('.' + inverseType).find('input[data-property=' + $(this).data('property') + ']').prop('disabled', this.checked);
    };

    DetailedReview.prototype.showVersionDR = function () {
      if (this.getQueryVariable('versionDR')) {
          console.log('version DR ' + this.config.versionDR);
      }
    };

    DetailedReview.prototype.getQueryVariable = function (variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return null;
    };

    DetailedReview.prototype.checkRatingStars = function (event) {
        var tthis = this;
        var $li = $(tthis).parent().children('li');
        $li.removeClass('active');
        $li.find('input.radio').attr("checked", false);
        $li.find(event.data.config.separateRatingStar).css("background", 'url(' + event.data.config.unActiveImageAverage + ') no-repeat');
        $li.each(function(){
            $(this).addClass('active');
            $(this).find(event.data.config.separateRatingStar).css("background", 'url(' + event.data.config.activeImageAverage + ') no-repeat');
            if ( tthis == this ) return false;
        });
        $(this).find('input.radio').attr('checked', true);
        $(this).find('input.radio').prop('checked', true);
    };

    DetailedReview.prototype.clearRatings = function() {
        var that = this;
        var $li = $('.overall-rating-inline li');
        $li.removeClass('active');
        $li.find(that.config.separateRatingStar).css("background", 'url(' + that.config.unActiveImageAverage + ') no-repeat');
        $li.find('input.radio').attr("checked", false);
    };

    DetailedReview.prototype.shortText = function() {
        var that = this;
        $(that.config.shortTextClass).each(function() {
            var content = $(this).html();
            var limit = that.config.shortTextSize;
            if(content.length > limit) {
                var visible = content.substr(0, limit);
                var hidden = content.substr(limit-1, content.length - limit);
                var html = visible + '<span class="more">...&nbsp;</span><span class="' + that.config.moreTextClass + '"><span>' + hidden + '</span>&nbsp;&nbsp;<a href="" class="'+ that.config.moreLink +'">' + that.config.moreText + '</a></span>';
                $(this).html(html);
            }
        });
    };

    DetailedReview.prototype.showShortText = function(event) {
        event.preventDefault();
        var that = this;
        var moreText = $(that).parent('span');
        var moreTextClass = event.data.config.moreTextClass;
        moreText.toggleClass(moreTextClass);
        if (moreText.hasClass(moreTextClass)) {
            $(that).text(event.data.config.moreText);
            moreText.prev('span').show();
        } else {
            $(that).text(event.data.config.lessText);
            moreText.prev('span').hide();
        }

    };

    DetailedReview.prototype.goBackFromSeparatePage = function(event) {
        if (opener) {
            opener.location.hash = '';
            opener.location.reload();
        } else {
            var link = event.data.config.config.productPage;
            if (link.indexOf('#') != (-1)) {
                link.substring(0, link.lastIndexOf("#"))
            }
            window.open(link, '_blank');
        }
        window.close();
    };

    $.fn.detailedReview = function (options) {
        new DetailedReview(options)
    };

    $.fn.CommentComplaint = function(options) {
        var CommentComplaint = function() {
            $.extend(true, this.options, options);
            this.bindEvent();
        };

        CommentComplaint.prototype.options = {};

        CommentComplaint.prototype.bindEvent = function () {
            $(document).on('click', this.options.complaintIcon, {that: this}, this.showAskComplaint);
            $(document).on('click', this.options.complaintCancelButton, {that: this}, this.hideComplaintBlock);
            $(document).on('click', this.options.complaintSubmitButton, {that: this}, this.complaintAjax);
            $(document).on('click', this.options.reportAbuse, {that: this}, this.showComplaintBlock);
        };

        CommentComplaint.prototype.showAskComplaint = function (event) {
            var that = event.data.that;
            var $reportButton = $('.' + this.id);
            if (!$reportButton.hasClass(that.options.showAsk)) {
                $(that.options.reportBtnWrapper).removeClass(that.options.showAsk);
                $reportButton.removeClass(that.options.showAsk);
            }
            $reportButton.toggleClass(that.options.showAsk);
        };

        CommentComplaint.prototype.showComplaintBlock = function (event) {
            var that = event.data.that;
            var $complaintWrapper = $('.' + that.options.complaintWrapper);
            $complaintWrapper.find(that.options.complaintValueChecked).prop('checked', false);
            $(that.options.reportBtnWrapper).removeClass(that.options.showAsk);
            $complaintWrapper.find(that.options.errorClass).remove();
            var replyWrap = $(this).closest(that.options.replyWrap);
            if (replyWrap.length) {
                replyWrap.after($complaintWrapper);
            } else {
                $(this).closest(that.options.topReview).after($complaintWrapper);
            }
            $complaintWrapper.removeClass(that.options.complaintHide).addClass(that.options.complaintShow);
            $('html, body').animate({
                scrollTop: $complaintWrapper.offset().top - that.options.offsetForBetterView
            }, 1000);
            $(document).on('mouseup', {that: that}, that.listenMouseOver);
        };
        CommentComplaint.prototype.listenMouseOver = function (event) {
                var that = event.data.that;
                var $complaintWrapper = $('.' + that.options.complaintWrapper);
                if(event.target.className != $complaintWrapper.attr('class') && !$complaintWrapper.has(event.target).length) {
                    that.hideComplaintBlock(event);
                }
        };
        CommentComplaint.prototype.hideComplaintBlock = function (event) {
            event.preventDefault();
            var that = event.data.that;
            $('.' + that.options.complaintWrapper).removeClass(that.options.complaintShow).addClass(that.options.complaintHide);
            $(that.options.reportBtnWrapper).removeClass(that.options.showAsk);
            $(document).unbind('mouseup');
        };

        CommentComplaint.prototype.complaintAjax = function (event) {
            event.preventDefault();

            var that = event.data.that;
            var $complaintButton = $(this);
            var $complaintWrapper = $('.' + that.options.complaintWrapper);
            that.options.queryParams.review_id      = $complaintButton.closest(that.options.reviewContainer).find(that.options.reviewId).val();
            that.options.queryParams.complaint_id   = $complaintButton.closest($complaintWrapper).find(that.options.complaintValueChecked).val();

            if (typeof that.options.queryParams.complaint_id !== 'undefined') {
                $complaintWrapper.removeClass(that.options.complaintShow).addClass(that.options.complaintHide);
                var pnotify_position = drConfig.pnotifyPosition;
                $.ajax({
                    url: that.options.complaintUrl,
                    data: that.options.queryParams
                }).fail(function () {
                    new PNotify({
                        text: that.options.defaultFailMessage,
                        type: 'error',
                        delay: that.options.delayOfMessageDisplaying,
                        stack: pnotify_position
                    });
                }).complete(function (response) {
                    response = $.parseJSON(response.responseText);
                    new PNotify({
                        text:  response.messages,
                        type: response.type,
                        delay: that.options.delayOfMessageDisplaying,
                        stack: pnotify_position
                    });
                });
                $(document).unbind("mouseup");
            } else {
                if(!$complaintWrapper.find('li.error').length) {
                    $complaintWrapper.append('<li class="error">' + that.options.emptyComplaintId + '</li>');
                }

            }
        };

        return new CommentComplaint(options);
    };

    DetailedReview.prototype.getAndroidVersion = function() {
        var nua = navigator.userAgent.toLowerCase();
        var match = nua.match(/android\s([0-9\.]*)/);
        return match ? match[1] : false;
    }

}(DRjQuery));
