;(function ($) {
    'use strict';

    $.fn.commentOnReview = function(options) {
        var CommentOnReview = function() {
            $.extend(true, this.options, options);
            this.bindEvent();
            this.wrapNicknameToSpan();
        };

        CommentOnReview.prototype.options = {
            saveReplyUrl: null,
            delayOfMessageDisplaying: 8000,
            emptyDetailMessage: 'Please enter text of the reply',
            isMainReview: false,
            showElements: 2,
            isCaptchaEnabled: false,
            checkCaptchaUrl: '',
            queryParams: {
                entity_pk_value: null,
                nickname: null,
                title: null
            },
            minDetailLength: 1,
            maxDetailLength: 2000,
            detailErrorMessages: {
                emptyDetail: 'Please enter text of the reply',
                lengthDetail: 'Reply must be min 1 and max 2000 characters'
            },
            nicknameSuffix: '@',
            defaultFailMessage: 'Something went wrong.'
        };

        CommentOnReview.prototype.bindEvent = function () {
            $(document).on('click', '.reply-button', {that: this}, this.showReplyBlock);
            $(document).on('click', '.reply-cancel-button', {that: this}, this.hideReplyBlock);
            $(document).on('click', '.reply-submit-button', {that: this}, this.saveReplyAjax);
        };

        CommentOnReview.prototype.showReplyBlock = function (event) {
            var commentOnReview = event.data.that;
            var $replyWrapper = $('.reply-wrapper');
            var $self = $(this);

            commentOnReview.options.isMainReview = $self.hasClass('reply-review');
            var author = commentOnReview.getAuthorName($self);
            commentOnReview.options.queryParams.authorName = author;

            $('.reply-message').html('');
            $('.reply-captcha-wrapper .captcha-error').html('');
            var replyComment = $('.reply-comment');

            replyComment
                .removeClass('error-detail')
                .html('<span id="reply-comment-nickname" class="reply-comment-nickname" contenteditable="false">' + author + '</span>');

            if (commentOnReview.options.isMainReview) {
                $self.closest('.reply-action').after($replyWrapper);
            } else {
                $self.closest('.reply-action-wrapper').after($replyWrapper);
            }

            $replyWrapper.removeClass('reply-hide').addClass('reply-show');

            //Set focus in comment editable div after span with nickname
            replyComment.focus();
            if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                replyComment[0].scrollIntoView(true);
            }
            commentOnReview.setFocusAfterNickname();
        };

        CommentOnReview.prototype.setFocusAfterNickname = function () {
            setTimeout(function() {
                var elem = document.getElementById('reply-comment-nickname');
                var sel, range;

                if (window.getSelection) {
                    sel = window.getSelection();
                } else {
                    sel = document.selection.createRange().text;
                }

                if (sel.getRangeAt && sel.rangeCount) {
                    range = sel.getRangeAt(0);
                    range.deleteContents();

                    var textNode = document.createTextNode('\u00A0');
                    range.setStartAfter(elem);
                    range.insertNode(textNode);
                    range.setStartAfter(textNode);
                    range.collapse(true);
                    sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }, 10);
        };

        CommentOnReview.prototype.hideReplyBlock = function () {
            $('.reply-wrapper, .reply-message-wrapper').removeClass('reply-show').addClass('reply-hide');
            $('.reply-message').html('');
        };

        CommentOnReview.prototype.saveReplyAjax = function (event) {
            var that = event.data.that;
            var $replyButton = $(this);
            that.options.queryParams.entity_pk_value = $replyButton.closest('.review-dd').find('.review-id').val();
            that.options.queryParams.title           = $replyButton.closest('.review-dd').find('.title:first').text();
            that.options.queryParams.detail          = $replyButton.closest('.reply-wrapper').find('.reply-comment').text();

            if (that.options.queryParams.isMainReview) {
                that.options.queryParams.nickname = that.getAuthorName(event);
            }

            var validate = that.validate(that.options.queryParams.detail, $replyButton);

            if (validate['status'] === true) {
                $('.reply-message-wrapper').removeClass('reply-show').addClass('reply-hide');
                $('.reply-wrapper .reply-comment-wrapper .reply-comment').removeClass('error-detail');

                if (that.options.isCaptchaEnabled) {
                    var recaptcharesponse = that.checkRecaptcha();

                    if (recaptcharesponse) {
                        that.replyAjax($replyButton);
                    }
                } else {
                    that.replyAjax($replyButton);
                }
            } else {
                that.generateErrorMessage(validate['message']);
            }
        };

        CommentOnReview.prototype.validate = function(fullDetailContent, event) {
            var that         = this;
            var result       = [];
            result['status'] = false;

            var detailContent = that.deleteAuthorName(fullDetailContent, event);

            if (!Boolean(detailContent)) {
                result['message'] = that.options.detailErrorMessages.emptyDetail;
                return result;
            }

            if (!that.validateDetailLength(detailContent)) {
                result['message'] = that.options.detailErrorMessages.lengthDetail;
                return result;
            }

            result['status'] = true;
            return result;
        };

        CommentOnReview.prototype.getAuthorName = function(element) {
            var author = this.options.nicknameSuffix;

            if (this.options.isMainReview) {
                author += $(element.closest('.item-review-wrapper')).find('.review-dt .nickname').text().trim();

                if (/\s/g.test(author)) {
                    var authorPrefix = author.substr(author.indexOf(' ') + 1);

                    author = author.substr(0, author.indexOf(' '));

                    authorPrefix = authorPrefix.match(/\b(\w)/g);
                    authorPrefix = authorPrefix.join('');

                    author += authorPrefix;
                }
            } else {
                author += $(element.closest('.reply-wrap')).find('.reply-author').text();
            }

            return author;
        };

        CommentOnReview.prototype.deleteAuthorName = function(fullDetailContent, event) {
            return fullDetailContent.trim().replace(this.getAuthorName(event), '').trim();
        };

        CommentOnReview.prototype.validateDetailLength = function(detailContent) {
            var that = this;

            return detailContent.length >= that.options.minDetailLength && detailContent.length <= that.options.maxDetailLength;
        };

        CommentOnReview.prototype.replyAjax = function ($replyButton) {
            var that = this;
            var queryParams = that.options.queryParams;
            if (!(queryParams.detail).includes(queryParams.authorName)) {
                queryParams.detail = queryParams.authorName + ' ' + queryParams.detail;
            }
            $.ajax({
                url: that.options.saveReplyUrl,
                data: queryParams
            }).done(function (response) {

                var $replyWrap = $($replyButton.closest('.reply-list-wrap')).find('.reply-list');

                if (response.html) {
                    $replyWrap.append(response.html);
                }

                that.manageReplyDisplay($replyWrap);

                that.hideReplyBlock();

                if (response.html) {
                    var countOfReplies = $($replyButton.closest('.reply-list-wrap')).find('.reply-count');

                    countOfReplies.text(parseInt(countOfReplies.text()) + 1);
                }

                var event = document.createEvent("CustomEvent");
                event.initEvent("updateDateDR", false, true);
                window.dispatchEvent(event);
            }).fail(function () {
                new PNotify({
                    text: that.options.defaultFailMessage,
                    type: 'error',
                    delay: that.options.delayOfMessageDisplaying
                });
            }).complete(function (response) {
                response = $.parseJSON(response.responseText);

                new PNotify({
                    text: that.wrapMessages(response.messages),
                    type: response.type,
                    delay: that.options.delayOfMessageDisplaying
                });
            });
        };

        CommentOnReview.prototype.manageReplyDisplay = function($replyWrap) {
            var $replyLast = $replyWrap.find('.reply').last();
            if($replyWrap.find('.reply').length > this.options.showElements) {
                var $replyExpander = $replyWrap.closest('.reply-list-wrap').find('.reply-expander');
                $replyExpander.addClass('show');
                if($replyExpander.hasClass('collapse')) {
                    $replyLast.addClass('expanded');
                }
            } else {
                $replyLast.addClass('expanded');
            }
        };

        CommentOnReview.prototype.generateErrorMessage = function (errorMessage) {
            var replyMessageWrapper = $('.reply-message-wrapper');
            $('.reply-wrapper .reply-comment-wrapper .reply-comment').addClass('error-detail');

            var messageContent = replyMessageWrapper.find('.reply-message');

            messageContent.html('<li>' + errorMessage + '</li>').fadeIn(200);

            replyMessageWrapper.removeClass('reply-hide').addClass('reply-show');
        };

        CommentOnReview.prototype.checkRecaptcha = function () {
            var that = this;
            var replyForm = $('#reply-form');
            var checkCaptchaStatus = false;

            $.ajax({
                url: that.options.checkCaptchaUrl,
                data: replyForm.serialize(),
                async: false,
                success: function (data) {
                    if (data == 'invalid') {
                        $('.captcha-error').html('You have entered wrong captcha');
                        grecaptcha.reset();
                    } else {
                        $('.captcha-error').html('');
                        checkCaptchaStatus = true;
                    }
                }
            });

            return checkCaptchaStatus;
        };

        CommentOnReview.prototype.wrapNicknameToSpan = function () {
            /* TODO Refactoring */
            var that = this;

            $('.reply-list-wrap .reply-wrap .reply-info .reply-detail').each(function () {
                var str = $(this).text().trim();
                var wrappedDetail = '<span class="reply-comment-nickname" contenteditable="false">' + str.replace(/\u00A0/g, " ").replace(' ','</span> ');

                $(this).html(wrappedDetail);
            });
        };

        CommentOnReview.prototype.wrapMessages = function(messages) {
            var result = '';

            for (var index in messages) {
                if (messages.hasOwnProperty(index)) {
                    result += '<p>' + messages[index] + '</p>';
                }
            }

            return result;
        };

        return new CommentOnReview(options);
    };

    $.fn.CommentExpander = function(options) {
        var CommentExpander = function() {
            $.extend(true, this.options, options);
            this.bindEvent();
        };

        CommentExpander.prototype.options = {
            showElements: 2,
            classz: {
                replyWrapper: '.reply-list-wrap',
                replyList: '.reply-list'
            }
        };

        CommentExpander.prototype.bindEvent = function () {
            $(document).on('click', '.reply-show-hide', {that: this}, this.expandComment);
        };

        CommentExpander.prototype.expandComment = function (event) {
            var that = event.data.that;
            var $self = $(this);
            var $replyList = $self.closest(that.options.classz.replyWrapper).find(that.options.classz.replyList);
            var $li = $replyList.find('.reply');
            var showElements = that.options.showElements;
            var $replyExpander = $self.closest('.reply-expander');

            if ($replyList.hasClass('open')) {
                $replyList.removeClass('open');
                $li.slice(showElements, $li.length).removeClass('expanded');
                $replyExpander.removeClass('collapse').addClass('expand');
            } else {
                $replyList.addClass('open');
                $li.slice(showElements, $li.length).addClass('expanded');
                $replyExpander.removeClass('expand').addClass('collapse');
            }
        };

        return new CommentExpander(options);
    };
}(DRjQuery));
