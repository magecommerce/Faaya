;(function($) {
    $.widget(
       'mageworkshop.reviewWall',
       {
           options: {
               template: null,
               baseUrl: null,
               baseUrlMedia: null,
               filter: null,
               endReviewMessage: 'All reviews were loaded',
               unknownSearchCriteriaMessage: 'Sorry, no reviews matched your criteria.',
               pnotifyDelay: 3000
           },

           _create: function () {
               $(window).on("scroll", $.proxy(this._onScrollEvent, this));

               var self = this;

               this.checkScrollExist = function() {
                   if (document.documentElement.scrollHeight == document.documentElement.clientHeight && $('#drw-end-reviews').length == 0) {
                       self._ajaxRequest();
                   }
               };

               self.interval = setInterval(this.checkScrollExist, 500);
           },

           _setOption: function (key, value) {
               switch( key ) {
                   case "baseUrl":
                       break;
               }

               $.Widget.prototype._setOption.apply( this, arguments );
               this._super( "_setOption", key, value );
           },

           destroy: function () {
               $.widget.prototype.destroy.call(this);
           },

           _onScrollEvent: function(event) {
               var pageNavElement = document.getElementById("drw-ajax-load-grid");

               if (pageNavElement) {
                   var pageNavElementTop = pageNavElement.getBoundingClientRect().top;
                   var pageNavElementBottom = pageNavElement.getBoundingClientRect().bottom;

                   if ((pageNavElementTop >= 0) && (pageNavElementBottom <= window.innerHeight) && $('#drw-end-reviews').length == 0) {
                       this._ajaxRequest();
                   }
               }
           },

           searchEvent: function() {
               this._ajaxRequest(true);
           },

           voteEvent: function(event) {
               var self = this;
               var that = event;
               var $voteType = $(that).hasClass('helpful-btn') ? 1 : 0;
               $.ajax({
                   url: $('.drw-helpful-form').attr('action'),
                   data: {
                       is_helpful: $voteType,
                       review_id: $(that).closest('.drw-js-helpful-voting').children('input[name=review_id]').attr('value')
                   },
                   dataType: 'json',
                   success: function (data) {
                       if (data['msg']['type'] == 'success') {
                           if (parseInt($voteType)) {
                               $(that).closest('.drw-rating-wrapper').find('.drw-helpful-qty').html(data['helpful']);

                           } else {
                               $(that).closest('.drw-rating-wrapper').find('.drw-unhelpful-qty').html(data['unhelpful']);
                           }
                           new PNotify({
                               text: data['msg']['text'],
                               type: 'success',
                               delay: self.options.pnotifyDelay
                           });
                       } else {
                           new PNotify({
                               text: data['msg']['text'],
                               type: 'error',
                               delay: self.options.pnotifyDelay
                           });
                       }
                   }
               });
           },

           _removeReviews: function(container) {
               $('.drw-box.drw-grid-item').remove();

               container.masonry('reloadItems').masonry('layout');
           },

           _ajaxRequest: function(isSearch) {
               var container       = $("#drw-container");
               var nextPageElement = $('.drw-next-page');
               var nextPage        = parseInt(nextPageElement.attr('data-next-page'));
               var searchElement   = $('#review-search');
               var searchValue     = searchElement.val().trim();
               var ajaxLoadGrid    = $('#drw-ajax-load-grid');
               var ajaxLoader      = $('.drw-loader');
               var self            = this;
               var emptyResponseMessage = self.options.endReviewMessage;

               if (typeof isSearch !== 'undefined') {
                   nextPage = 1;

                   var hiddenSearch = $('.drw-hidden-search');
                   hiddenSearch.attr('value', searchValue);

                   emptyResponseMessage = self.options.unknownSearchCriteriaMessage;

                   $('#drw-end-reviews').remove();

                   this._removeReviews(container);
               }

               ajaxLoader.addClass('show');
               ajaxLoadGrid.remove();

               $.ajax({
                   method: "POST",
                   url: this.options.baseUrl + 'mageworkshop_reviewwall/ajax/reviews/',
                   async: true,
                   data: {
                       p: nextPage,
                       keywords: searchValue,
                       filter: this.options.filter
                   },
                   dataType: "json"
               }).done(function (response) {
                   var $newGridItems = [];

                   if (Object.keys(response).length) {
                       var lastReviewID = response[Object.keys(response)[Object.keys(response).length-1]].review_id;

                       for (var index in response) {
                           if(response.hasOwnProperty(index)) {
                               var newGridItem = self._getItemElement(response[index]);
                               $newGridItems.push(newGridItem);

                               if (index == lastReviewID) {
                                   var ajaxLoadGrid = document.createElement('div');
                                   ajaxLoadGrid.className = 'drw-box drw-grid-item';
                                   ajaxLoadGrid.id        = 'drw-ajax-load-grid';
                                   $newGridItems.push(ajaxLoadGrid);
                               }
                           }
                       }

                       nextPageElement.attr('data-next-page', nextPage + 1);

                       var jQueryGridItems = $($newGridItems);

                       jQueryGridItems.hide();

                       container.append(jQueryGridItems).imagesLoaded(function () {
                           jQueryGridItems.show();
                           container.masonry('appended', jQueryGridItems);
                           container.masonry('on', 'layoutComplete', function() {
                               ajaxLoader.removeClass('show');
                           });
                       }).masonry();
                   } else {
                       if(!$('#drw-end-reviews').length) {
                           var emptyResponseBlock = $(
                               '<div id="drw-end-reviews" class="drw-box drw-grid-item">' +
                               '<span>' + emptyResponseMessage + '</span>' +
                               '</div>'
                           );
                       }
                       ajaxLoader.removeClass('show');
                       container.append(emptyResponseBlock.show());
                   }

               }).fail(function () {

               }).complete(function () {
                   // I catch an issue on low connection it doesn't show, to prevent such behaviour
                   $('.drw-grid-item').show();
               });
           },

           _getItemElement: function(review) {
               var published = this._getTimeElapsedString(review.created_at);
               var data = {
                   baseUrl: this.options.baseUrl,
                   baseMediaUrl: this.options.baseUrlMedia,
                   review: review,
                   published: published
               };
               var template = this.options.template;
               var templateFunction = doT.template( template );
               var newGridItemContent = templateFunction( data );
               var div = document.createElement('div');
               div.className = 'drw-box drw-grid-item ' + this.options.cssClassName + ' drw-show';
               div.innerHTML = newGridItemContent;
               return div;
           },

           _getTimeElapsedString: function(date) {
               var seconds = Math.floor((new Date() - new Date(date)) / 1000);

               var interval = Math.floor(seconds / 31536000);

               if (interval > 1) {
                   return interval + ((interval != 1 ) ? " years ago by " : " year ago by ") ;
               }
               interval = Math.floor(seconds / 2592000);
               if (interval > 1) {
                   return interval + + ((interval != 1 ) ? " months ago by " : " month ago by ");
               }
               interval = Math.floor(seconds / 86400);
               if (interval > 1) {
                   return interval + ((interval != 1 ) ? " days ago by " :  " day ago by ");
               }
               interval = Math.floor(seconds / 3600);
               if (interval > 1) {
                   return interval + ((interval != 1 ) ? " hours ago by " : " hours ago by ");
               }
               interval = Math.floor(seconds / 60);
               if (interval > 1) {
                   return interval + ((interval != 1 ) ? " minutes ago by " : " minute ago by ");
               }
               return Math.floor(seconds) + " seconds ago by ";
           }
       }
    );
}(DRjQuery));

