var headHeight = jQuery(".page-header").outerHeight();
var footerHeight = jQuery(".footer-container").outerHeight();
var winHeight = jQuery(window).outerHeight();
var mainCheight = winHeight - (headHeight + footerHeight);

jQuery(document).ready(function () {

       if(navigator.platform.toUpperCase().indexOf('MAC')>=0) {
         jQuery('body').addClass('mac-os');
       }
       var ua = navigator.userAgent.toLowerCase();
          if (ua.indexOf('safari') != -1) {
            if (ua.indexOf('chrome') > -1) {
              jQuery('body').addClass('chrome');
            } else {
              jQuery('body').addClass('safari');
            }
          }

          jQuery('html,body').bind('copy paste',function(e) {
                e.preventDefault(); return false; 
            });

     //Disable full page
  /*  jQuery("body").on("contextmenu",function(e){
        return false;
    });
    jQuery("img").on("contextmenu",function(){
       return false;
    });
    jQuery('body').on('copy',function(e) {
        e.preventDefault();
        return false;
    });

    jQuery(document).attr('unselectable','on')
        .css({'-moz-user-select':'-moz-none',
        '-moz-user-select':'none',
        '-o-user-select':'none',
        '-khtml-user-select':'none',
        '-webkit-user-select':'none',
        '-ms-user-select':'none',
        'user-select':'none'
        }).on('selectstart', function(){return false; });
  */


    jQuery(".top-container").css('padding-top', headHeight);
    jQuery(".cms-partner-page .page-header").addClass("sticky-header");
    jQuery(".transparent-header .page-header").addClass("sticky-header");
    jQuery(".backgroundDiv").each(function (e, t) {
        var o = jQuery(this).find(".imageDiv img").attr("src");
        jQuery(this).css("background-image", "url(" + o + ")"), jQuery(this).find(".imageDiv").remove()
    });
    navHeight();
    jQuery('.apply-coupon-btn').click(function () {
        jQuery('.cart-forms #discount-coupon-form .discount').slideToggle();
        jQuery('.cart-forms #amgiftcard-form .discount').hide();
    })

    jQuery('.gift-card-btn').click(function () {
        jQuery('.cart-forms #amgiftcard-form .discount').slideToggle();
        jQuery('.cart-forms #discount-coupon-form .discount').hide();
    })



    jQuery('.takearest-section .faq-tooltip-options > .faq-tooltip-image').click(function () {

            jQuery(this).next().toggleClass('active')
        })
        /*
            jQuery(".amshopby-container-top .amshopby-item-top").each(function( index ) {
                var tr = jQuery(this).find('dt').html();
                tr = tr.toLowerCase().replace(/ /g,"-");
                jQuery(this).addClass(tr);

            })
        */
    var $clipper = jQuery('.shipping-offer');
    jQuery(".cms-partner .top-container, .cms-self .top-container,.cms-proposal-ring .top-container").each(function () {
        $clipper.clone().appendTo(jQuery(this));
    });
    stickymenu();
    equelItems();
    //equalheightnoRow('.diamond-type .item');

    jQuery('.nav-primary li.parent').prepend('<span class="submenu"></span>');
    jQuery('.nav-primary li.parent .submenu').click(function (){
        jQuery(this).toggleClass('active-subnav');
        jQuery('.nav-primary li.parent > ul').slideToggle();
    });

    jQuery(document).on('click','.carat-details .products-list .item .availability',function() {
        jQuery('html,body').animate({
                scrollTop: jQuery(".wizard-toolbar").offset().top - 100},
            'slow');
    });
    jQuery(document).on('click touchstart',function() {
        jQuery('.skip-content').removeClass('skip-active');
    });
	
	jQuery('.skip-content,.skip-link.skip-nav,.skip-link.skip-search').on('click touchstart',function(e){
		e.stopPropagation();
	});
	

    categoryViewBannerImage();
    containerHeight()

    /*Transparent Header*/
      if(jQuery('body').find('.inner-top-banner').length > 0){
        jQuery('body').addClass('transparent-header');
      }
	  
	  jQuery('.sizeSelectpicker').selectpicker();


		  var pgurl = window.location.href;  
		jQuery(".footer-menu ul li a").each(function() {
			 if (jQuery(this).attr("href") == pgurl || jQuery(this).attr("href") == '')
				jQuery(this).addClass("active");
		  });
		  jQuery(".nav-primary li a").each(function() {
			 if (jQuery(this).attr("href") == pgurl || jQuery(this).attr("href") == '')
				jQuery(this).addClass("active");
		  });

});

/*-----------------------------*
* Load Function
*-------------------------------*/
jQuery(window).load(function () {
    navHeight();
    equelItems();
    jQuery(".amshopby-container-top .amshopby-item-top").each(function (index) {
        var tr = jQuery(this).find('dt').html();
        tr = tr.toLowerCase().replace(/ /g, "-");
        jQuery(this).addClass(tr);
    })
    stickymenu();
    //fullHeight();
    setTimeout(function (){

        equalheightnoRow('.diamond-type .item');

    },200);

  categoryViewBannerImage();
  containerHeight()

  /* jQuery('.product-shop').mCustomScrollbar({
     mouseWheelPixels: 10,
     scrollbarPosition: "inside"
  }); */


   /*Transparent Header*/
      if(jQuery('body').find('.inner-top-banner').length > 0){
        jQuery('body').addClass('transparent-header');
      }

});

/*-----------------------------*
* Resize Function
*-------------------------------*/
jQuery(window).resize(function () {
    navHeight();
    equelItems();
    setTimeout(function () {
        //equalheight('.diamond-type .item');
    }, 400);
    stickymenu();
    //fullHeight();
    categoryViewBannerImage();
    containerHeight()
});

/*-----------------------------*
* Scroll Function
*-------------------------------*/
jQuery(window).on('scroll',function () {
    /*Home header*/
    var scroll = jQuery(window).scrollTop();
    var cateDesc = jQuery('.category-description').outerHeight();
    if (scroll >= 100) {
        jQuery(".cms-self .page-header, .cms-partner .page-header").addClass("sticky-header");
        jQuery(".transparent-header .page-header").addClass("sticky-header");
    }
    else {
        jQuery(".cms-self .page-header, .cms-partner .page-header").removeClass("sticky-header");
        jQuery(".transparent-header .page-header").removeClass("sticky-header");
    }
    /* jQuery('.block-layered-nav').removeClass('sticky')
    if(scrollY > cateDesc ) {
        jQuery('.block-layered-nav').addClass('sticky')
    }else {
        jQuery('.block-layered-nav').removeClass('sticky')
    }   */

    /*banner*/
    var scroll = jQuery(window).scrollTop();
    if (scroll <= winHeight) {
        jQuery(".home-banner").animate();
    }


})


/*-----------------------------*
* UDF Function
*-------------------------------*/
function containerHeight(){
    var headHeight = jQuery(".page-header").outerHeight();
    var footerHeight = jQuery(".footer-container").outerHeight();
    var winHeight = jQuery(window).outerHeight();
    var mainCheight = winHeight - (headHeight + footerHeight);
    jQuery('.cms-home .main-container').css({'height':mainCheight});
}
function navHeight() {
    var headHeight = jQuery(".page-header").outerHeight();
    var winHeight = jQuery(window).outerHeight();
    var img = jQuery('.trending-carousel .owl-item .image > img').outerHeight();
    jQuery('.trending-carousel .owl-nav > div').height(img);
    //jQuery('.home-banner').css("height", winHeight - headHeight);
    jQuery('.home-banner').css({"height": winHeight});
}

// Cancel
jQuery(function( $ ) {
   $(".cancel").click(function(){
	   $(this).hide();
	   $(".stikers").hide();
   });
});
					
jQuery(function( $ ) {
    var scrolling = "Scrolling",
        stopped = "Stopped";

        $( window ).scroll(function() {
            //$output.html( scrolling );
            //console.log( scrolling );
            jQuery('.page-header').removeClass('mobile-sticky-header');
            clearTimeout( $.data( this, "scrollCheck" ) );
            $.data( this, "scrollCheck", setTimeout(function() {
                //$output.html( stopped );
                jQuery('.page-header').addClass('mobile-sticky-header');
                // console.log( stopped );
            }, 350) );

            /*Header*/          
            var sticky = jQuery('.page-header'),
            scroll = jQuery(window).scrollTop(),
            headHeight = jQuery(".page-header").outerHeight();

            if (scroll >= headHeight + 200){
                sticky.addClass('hide-h');
            }else{
                sticky.removeClass('hide-h'); 
                sticky.removeClass('mobile-sticky-header');
            }

        });

});

function stickymenu() {

     var headerHeight = jQuery('#header').outerHeight();
     jQuery('body').css('padding-top', headerHeight);

    // if (jQuery(window).width() > 771) {
    //     var headerHeight = jQuery('#header').outerHeight();
    //     jQuery('body').css('padding-top', headerHeight);
    // }
    // else {
    //     var headerHeight = jQuery('#header').outerHeight();
    //     jQuery('body').css('padding-top', 0);
    // }
}

/*function fullHeight() {
    jQuery(window).scroll(function () {
        var headerHeight = jQuery('.top-container').outerHeight();

        if (scroll <= outerHeight) {
            jQuery('.shipping-offer').removeClass("active");
        }
        else {
            jQuery('.shipping-offer').addClass("active");
        }
        jQuery('.shipping-offer').addClass("active");
    });
}*/

equalheight = function (container) {
    if (jQuery(window).width() > 767) {
        var currentTallest = 0
            , currentRowStart = 0
            , rowDivs = new Array()
            , jQueryel, topPosition = 0;
        jQuery(container).each(function () {
            jQueryel = jQuery(this);
            jQuery(jQueryel).innerHeight('auto')
            topPostion = jQueryel.position().top;
            if (currentRowStart != topPostion) {
                for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                    rowDivs[currentDiv].innerHeight(currentTallest);
                }
                rowDivs.length = 0; // empty the array
                currentRowStart = topPostion;
                currentTallest = jQueryel.innerHeight();
                rowDivs.push(jQueryel);
            }
            else {
                rowDivs.push(jQueryel);
                currentTallest = (currentTallest < jQueryel.innerHeight()) ? (jQueryel.innerHeight()) : (currentTallest);
            }
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].innerHeight(currentTallest);
            }
        });
    }
    else {
        jQuery(container).innerHeight('auto')
    }
}
equalheightnoRow = function (container) {
    var currentTallest = 0
        , currentRowStart = 0
        , rowDivs = new Array()
        , jQueryel
    jQuery(container).each(function () {
        jQueryel = jQuery(this);
        jQuery(jQueryel).innerHeight('auto')
        rowDivs.push(jQueryel);
        currentTallest = (currentTallest < jQueryel.innerHeight()) ? (jQueryel.innerHeight()) : (currentTallest);
        for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
            rowDivs[currentDiv].innerHeight(currentTallest);
        }
    });
}





function equelItems() {

    equalheightnoRow('.diamond-type .item');
    equalheightnoRow('.diamond-cuts-section .item .image');
    equalheightnoRow('.diamond-cuts-section .item');
    equalheightnoRow('.propose-category-section .content .grid .item');
    equalheightnoRow('.diamond-grown-catagory .category-grid .item');
    equalheightnoRow('.my-wishboard .wishboard-grid .item');
    equalheightnoRow('.my-wishboard .wishboard-grid .item .multi-images');
    equalheightnoRow('.product-view .details-diamond-type.diamond-type .item.diamond-item .item-option li');
    equalheightnoRow('#wishlist-table.clean-table .wishlist-grid .item');
    equalheightnoRow('.multiwishlist_popup > div');
    equalheightnoRow('.block-layered-nav .filter-options.metal_color .list ol > li');
    equalheightnoRow('.block-layered-nav .filter-options.karat .list ol > li');
    equalheightnoRow('.post-list.latestpost .item, .post-list.alltimefavourite .item');
    equalheightnoRow('.post-list.latestpost .item h2, .post-list.alltimefavourite .item h2');
    equalheightnoRow('.block-layered-nav .block-content .filter-options.metal > .list li');

    equalheightnoRow('.personalised-by-you .blocks .item .image');
    equalheightnoRow('.personalised-by-you .blocks .item');

}



function categoryViewBannerImage() {
    var categoryViewImage =  jQuery('.categoryView-image img').outerHeight();
    //jQuery('.category-description').css('min-height', categoryViewImage);
}

jQuery(function( $ ) {
jQuery(".tandc-list .faq-a").hide(); 	jQuery(".tandc-list .faq-list:first .faq-a").show(); 	jQuery(".tandc-list .faq-q:first").addClass("active"); 	jQuery("#all").click(); jQuery(".tandc-list faq-list:first .faq-q").addClass("active"); 	jQuery(".tandc-list .faq-q").click(function() { 	if(!jQuery(this).hasClass("active")) 	{ 		jQuery(".tandc-list .faq-a").stop(true).slideUp(200); 		jQuery(".tandc-list .faq-q").removeClass("active"); 		jQuery(this).addClass("active"); 		jQuery(this).next(".faq-a").stop(true).slideDown(200); 	} 	else{ 		jQuery(".tandc-list .faq-a").stop(true).slideUp(200); 		jQuery(".tandc-list .faq-q").removeClass("active"); 	} 	});
});
