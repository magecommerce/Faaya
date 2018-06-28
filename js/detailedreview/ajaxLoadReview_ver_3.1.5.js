/*jslint browser: true, regexp: true, devel: true */
(function ($) {
    'use strict';
    $.fn.ajaxLoad = function (options) {

        var Loader = function (options) {
            $.extend(true, this.options, options);
            this.hasIdReview();
            this.bindEvent();
            this.getRatingStyles(drConfig);
        };

        Loader.prototype.options = {
            ajaxErrorMessage: 'Error, contact customer support.',
            ajaxErrorTemplate: '<ul class="messages"><li class="notice-msg"><ul><li><span>{{ajaxErrorMessage}}</span></li></ul></li></ul>',
            clearFiltersMessage: 'Clear all filters',
            clearFiltersTemplate: '<span><a class="clear-filters-link" href="javascript:void(0)" >{{clearFiltersMessage}}</a></span>',
            clearFiltersCssClass: '.clear-filters-link',
            clearFiltersArray: ['keywords', 'images', 'video', 'highest_contributors', 'verified_buyers', 'admin_response'],
            container: '#customer-reviews',
            filters: {
                checkboxes: 'input[data-type="review-filter"]',
                limit: '.limiter select[data-type="limiter"]',
                page: '.pages a[data-type="r-page"]',
                ranges: 'a[data-type="range-filter"]',
                searchForm: '#review_search_mini_form',
                selectSort: '.review-sorts select[data-type="sort"]',
                tabsSort: 'li[data-type="sort"]',
                sortsSpan: '.top-dropdown-sorts a',
                dateFilterSpan: '.review-date-filters .top-title'
            },
            queryParams: {
                sort: 'date_desc',
                range: null,
                keywords: null,
                images: null,
                video: null,
                highest_contributors: null,
                verified_buyers: null,
                admin_response: null,
                r_p: null,
                r_limit: null,
                r_id: null,
                product_id: null
            },
            url: null
        };

        Loader.prototype.clearFilters = function (event) {
            var that = event.data.that;
            $.each(that.options.clearFiltersArray, function (key, value) {
                that.options.queryParams[value] = null;
            });
            that.ajaxLoad();
            $(that.options.filters.checkboxes).removeAttr('checked');
            $(that.options.filters.searchForm).find('#review-search').val('');
        };

        Loader.prototype.hasIdReview = function () {
            var that = this;
            if (window.location.hash) {
                var hash = window.location.hash;
                if ('#rw_' === hash.replace(/[0-9]/g, '')) {
                    this.options.queryParams.r_id = hash.match(/\d+/)[0];
                    var ajaxResp = this.ajaxLoad();

                    ajaxResp.success(function () {
                        that.scrollToEl(hash);
                    });
                }
            }
        };
        Loader.prototype.scrollToEl = function(el) {
            var container = $(el).parent(),
                rowPos = $(el).offset(),
                height = $(window).height();

            if (container) {
                var containerBackgroundColor = container.css('background-color');
                container.css('background-color', '#ffffcc');
            }

            var $page = $("html, body");

            $(window).bind("scroll mousedown DOMMouseScroll mousewheel keyup", function(e){
                if ( e.which > 0 || e.type === "mousedown" || e.type === "mousewheel"){
                    $page.stop().unbind('scroll mousedown DOMMouseScroll mousewheel keyup'); // This identifies the scroll as a user action, stops the animation, then unbinds the event straight after (optional)
                }
            });

            if (rowPos) {
                setTimeout(function() {
                    $page.animate({scrollTop: rowPos.top - height/2}, 'slow');
                }, 1000);
            }

            if (containerBackgroundColor) {
                container.animate({backgroundColor: containerBackgroundColor}, 8000 );
            }
        };

        Loader.prototype.removeHashUrl = function () {
            if ($.browser.msie && $.browser.version <= 9) {
                window.location = document.URL.replace(/#.*$/, "");
            } else {
                history.replaceState({}, document.title, document.URL.replace(/#.*$/, ""));
            }
        };
        Loader.prototype.removeClearFiltersLink = function () {
            var that = this;
            var shouldRemoveLink = true;
            $.each(this.options.clearFiltersArray, function (key, value) {
                if (that.options.queryParams[value]) {
                    shouldRemoveLink = false;
                    return false;
                }
            });

            if (shouldRemoveLink) {
                $(this.options.clearFiltersCssClass).parents('span').remove();
            }
        };

        Loader.prototype.addClearFilters = function () {
            if (!$(this.options.clearFiltersCssClass).length) {
                $(this.options.filters.searchForm).after(this.options.clearFiltersTemplate.replace('{{clearFiltersMessage}}', this.options.clearFiltersMessage));
            }
        };

        Loader.prototype.bindEvent = function () {
            $(this.options.filters.tabsSort).on('click', {that: this}, this.prepareAjaxLoad);
            $(this.options.filters.selectSort).change({that: this}, this.prepareAjaxLoad);
            $(this.options.filters.ranges).on('click', {that: this}, this.prepareAjaxLoad);
            $(this.options.filters.checkboxes).change({that: this}, this.prepareAjaxLoad);
            $(this.options.filters.searchForm).submit({that: this}, this.prepareAjaxLoad);
            $(this.options.filters.limit).change({that: this}, this.prepareAjaxLoad);
            $(this.options.filters.page).on('click', {that: this}, this.prepareAjaxLoad);
            $(this.options.clearFiltersCssClass).on('click', {that: this}, this.clearFilters);
        };

        Loader.prototype.unbindEvent = function () {
            $.each(this.options.filters, function (key, value) {
                $(value).unbind();
            });
        };

        Loader.prototype.switchTab = function (tab) {
            $(this.options.filters.tabsSort).removeClass('selected');
            $(this.options.filters.tabsSort).closest('ul').css('height', '40px');
            $(tab).addClass('selected');
            $(this.options.filters.sortsSpan).text($(tab).text());
        };
        Loader.prototype.switchRange = function (tab) {
            $(tab).closest('ul').css('height', '40px');
            $(this.options.filters.dateFilterSpan).text($(tab).text());
        };

        Loader.prototype.updateParams = function (element) {
            var filterType = $(element).data('type');
            switch (filterType) {
                case 'sort':
                    this.options.queryParams.sort = $(element).is("select") ? $(element).val() : $(element).data('sort');
                    break;
                case 'range-filter':
                    this.options.queryParams.range = $(element).data('value');
                    break;
                case 'review-filter':
                    this.options.queryParams[$(element).data('filter')] = $(element).prop('checked') ? 1 : 0;
                    break;
                case 'search':
                    this.options.queryParams.keywords = $(element).find('.input-text').val().trim();
                    break;
                case 'limiter':
                    this.options.queryParams.r_limit = $(element).val();
                    break;
                case 'r-page':
                    this.options.queryParams.r_p = $(element).data('number');
                    break;
                default:
                    break;
            }

            //add link "Clear filters"(checkboxes and search form)
            if ($.inArray(filterType, ['review-filter', 'search']) >= 0) {
                this.addClearFilters();
            }
            // reset pagination if new filter was applied
            if ($.inArray(filterType, ['sort', 'limiter', 'r-page']) === -1) {
                this.options.queryParams.r_p = null;
            }
        };

        Loader.prototype.prepareAjaxLoad = function (event) {
            var that = event.data.that || null;
            if (event.type === 'submit' && $(this).find('#review-search').val().length <= 0) {
                new PNotify({
                    text: 'Search form is empty.',
                    icon: false,
                    delay: 5000
                });
                return false;
            }
            if ($(this).data('type') === 'sort' && !$(this).is("select")) {
                that.switchTab(this);
            }
            if ($(this).data('type') === 'range-filter') {
                that.switchRange(this);
            }
            that.updateParams(this);

            var $replyWrap = $('.reply-wrapper');
            if($replyWrap.closest('.reply-list-wrap').length) {
                $('#customer-reviews').after($replyWrap.removeClass('reply-show').addClass('reply-hide'));
            }

            that.ajaxLoad();
            return false;
        };

        Loader.prototype.ajaxLoad = function () {
            var that = this;
            this.unbindEvent();
            var ajax;
            ajax = $.ajax({
                url: this.options.url,
                dataType: 'json',
                data: this.options.queryParams
            }).done(function (response) {
                var $container = $(document).find(that.options.container).length > 0 ? $(document).find(that.options.container) : $('.reviews-container ul.messages');
                $container.siblings('.complaint-list-wrapper').remove();
                $container.replaceWith(response.html);
                that.shortText(drConfig);
                that.getRatingStyles(drConfig);
                if (response.hasOwnProperty('reviewsCount')) {
                    $.each(response.reviewsCount, function (key, value) {
                        $('a[data-type="range-filter"][data-value=' + key + ']').find('span > span').html(' (' + value + ')');
                    });
                    var easyTabsLink = $('a[href="#product_tabs_review_tabbed"]');
                    if (easyTabsLink.length) {
                        var newCount = easyTabsLink.text().split('(')[0] + '(' + response.reviewsCount[999] + ')';
                        easyTabsLink.text(newCount);
                    }
                }
                if (response.hasOwnProperty('countReviewsWithRating') && response.hasOwnProperty('avgRating')) {
                    var average = '';
                    if (response.countReviewsWithRating == 1) {
                        average = response.countReviewsWithRating + ' review';
                    } else if (response.countReviewsWithRating == 0) {
                        average = response.countReviewsWithRating + ' reviews';
                        $('.rating-stars-views').find('.rating').css('width', '0%');
                    } else {
                        average = response.countReviewsWithRating + ' reviews';
                    }
                    $('.average-based-on').html(average);
                    $('.rating-stars-views').find('.rating').css('width', Math.round(response.avgRating / response.countReviewsWithRating * 20) + '%');
                    if (response.hasOwnProperty('qtyMarks') && $(document).find('.separate-rating').length > 0) {
                        var i = 5;
                        $('.separate-rating').children('.mark-rating').each(function () {
                            if (response.avgRating > 0) {
                                $(this).find('.scroll-rating').css('width', Math.round(response.qtyMarks[i] / response.countReviewsWithRating * 100) + '%');
                                $(this).find('.rating-percent span').html(response.qtyMarks[i]);
                            } else {
                                $(this).find('.scroll-rating').css('width', '0');
                                $(this).find('.rating-percent span').html('0');
                            }
                            i--;
                        });
                    }
                }
                if (response.hasOwnProperty('averageSizing')) {
                    var $averageSizingBar = $('.sizing-bar.average');
                    if (response.averageSizing) {
                        $averageSizingBar.find('div').css('width', response.averageSizing['optionWidth'] + '%');
                        $averageSizingBar.find('.sizing-pointer').css('margin-left', response.averageSizing['optionWidth'] + '%');
                        $averageSizingBar.find('.sizing-label').css('margin-left', response.averageSizing['optionWidth'] + '%');
                        $averageSizingBar.find('.sizing-label').css('right', response.averageSizing['indent']);
                        $averageSizingBar.find('.sizing-label').html(response.averageSizing['optionValue']);
                    }
                }
                that.hasIdReview();
                if (that.options.queryParams.r_id) {
                    that.options.queryParams.r_id = null;
                    // Set timeout to wait till reviews render
                    setTimeout( function () {
                        that.removeHashUrl();
                    }, 1000);

                }
                var event;
                if(document.createEvent) {
                    event = document.createEvent("CustomEvent");
                    event.initEvent("updateDateDR", false, true);
                    window.dispatchEvent(event);
                }
            }).fail(function () {
                $(that.options.container).html(that.options.ajaxErrorTemplate.replace('{{ajaxErrorMessage}}', that.options.ajaxErrorMessage));
            }).complete(function () {
                that.removeClearFiltersLink();
                that.bindEvent();
            });
            return ajax;
        };

        Loader.prototype.getRatingStyles = function (config) {
            $('.reviews-container .ratings-table .rating-box').css("background", 'url(' + config.unActiveImageSeparate + ') repeat-x');
            $('.reviews-container .ratings-table .rating-box .rating').css("background", 'url(' + config.activeImageSeparate + ') repeat-x');
            $('.review-top .average-rating .rating-box').css("background-image", 'url(' + config.unActiveImageAverage + ')');
            $('.review-top .average-rating .rating-box .rating').css("background-image", 'url(' + config.activeImageAverage + ')');
            $('.overall-raiting .overall-raiting-value li .separate-rating-star').css("background", 'url(' + config.unActiveImageAverage + ') no-repeat');
            $('.category-products .ratings .amount').css("float", "left");
        };
        Loader.prototype.shortText = function (config) {
            $(config.shortTextClass).each(function() {
                var content = $(this).html();
                var limit = config.shortTextSize;
                if(content.length > limit) {
                    var visible = content.substr(0, limit);
                    var hidden = content.substr(limit-1, content.length - limit);
                    var html = visible + '<span class="more">...&nbsp;</span><span class="' + config.moreTextClass + '"><span>' + hidden + '</span>&nbsp;&nbsp;<a href="" class="'+ config.moreLink +'">' + config.moreText + '</a></span>';
                    $(this).html(html);
                }
            });
        };

        return new Loader(options);
    };
}(DRjQuery));
