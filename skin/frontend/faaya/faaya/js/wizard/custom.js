
function VideoControls(){
         jQuery(function(){
            jQuery( ".btnPlay" ).each(function(index) {
                var video = jQuery(this).parents('.video-box').children('video');
                jQuery(this).on("click", function(){
                   if(video[0].paused) {
                          console.log('if paused');
                          video[0].play();
                          jQuery(this).addClass('pause');
                      }
                      else {
                          console.log('ealse play');
                          video[0].pause();
                          jQuery(this).removeClass('pause');
                      }
                       return false;
                });
            });
             jQuery('.muted').each(function(index) {
                  var video = jQuery(this).parents('.video-box').children('video');
                 jQuery(this).on("click", function(){
                   video[0].muted = !video[0].muted;
                   jQuery(this).toggleClass('mute');
                   console.log(video[0].muted);
                 });
            });

        });

}


jQuery(document).ready(function () {

    var tableData = '';
    if(jQuery('body').find('.list-table').length > 0){
        var offsetTable = jQuery('.list-table').offset().top
    }
    var TableheaderHeight = jQuery('.page-header').outerHeight();

    jQuery("#tablesortby").change(function () {
       jQuery(".diamond-list-table").find("th."+jQuery(this).val()).trigger("click");
    });

    //var tabLen = jQuery('.tabs li').length;
    if(jQuery('.tabs li').length > 3){
      jQuery('.tabs').addClass('moretabs');
    }

    jQuery.fancyConfirm = function (opts) {
        opts = jQuery.extend(true, {
          title: 'Are you sure?',
          message: '',
          okButton: 'OK',
          noButton: 'Cancel',
          animationDuration : 350,
          animationEffect   : 'material',
          modal : true,
          callback: jQuery.noop
        }, opts || {});

        jQuery.fancybox.open({
          type: 'html',
          src: '<div class="fc-content fc-message-popup p-5 rounded">' +
            '<h2 class="mb-3">' + opts.title + '</h2>' +
            '<p>' + opts.message + '</p>' +
            '<p class="text-right actions">' +
            '<a data-value="0" data-fancybox-close href="javascript:;" class="mr-2">' + opts.noButton + '</a>' +
            '<button data-value="1" data-fancybox-close class="btn btn-primary">' + opts.okButton + '</button>' +
            '</p>' +
            '</div>',
          opts: {
            animationDuration: 350,
            animationEffect: 'material',
            modal: true,
            baseTpl: '<div class="fancybox-container fc-container" role="dialog" tabindex="-1">' +
              '<div class="fancybox-bg"></div>' +
              '<div class="fancybox-inner">' +
              '<div class="fancybox-stage"></div>' +
              '</div>' +
              '</div>',
            afterClose: function (instance, current, e) {
              var button = e ? e.target || e.currentTarget : null;
              var value = button ? jQuery(button).data('value') : 0;

              opts.callback(value);
            }
          }
        });
      }


    jQuery('.tooltip-image').on('click', function (event) {
        jQuery(this).next().find('li').eq(0).find('input').trigger('click');
        //jQuery(this).next().find('.slider-image').find('img').eq(0).css("display", "inline-block");
        event.stopPropagation();
        jQuery('.tooltip-content').removeClass('open');
        jQuery(this).next().addClass('open');

    });
    jQuery('.tooltip-content').on('click', function (event) {
        event.stopPropagation();
    });
    jQuery('.close-btn').click(function () {
        jQuery(this).parent().removeClass('open');
    });

    jQuery(document).click(function (event) {
        if (!jQuery(event.target).hasClass('tooltip-content')) {
            jQuery('.tooltip-content').removeClass('open');
        }
    });


    jQuery(document).on('click',':not(".tooltip-image")', function () {
        jQuery('.tooltip-content').siblings().addClass('open');
        jQuery('.tooltip-content').siblings().removeClass('open');
    });

    jQuery(document).on('click','.compare-remove',function () {
        removeCompare();
    });

    jQuery(document).on('click','.wizard-index-index .diamond-list-table tbody tr td.heading',function () {
        var productId = jQuery(this).parents('tr').find('td').eq(0).find('.productid').val();
        loadAjaxList(productId);
        console.log(productId);
    });
    jQuery(document).on('click','.wizard-sidestone-index .diamond-list-table tbody tr td.heading',function () {
        var productId1 = jQuery(this).parents('tr').find('td').eq(0).find('.productid1').val();
        var productId2 = jQuery(this).parents('tr').find('td').eq(0).find('.productid2').val();
        redirectPage(productId1,productId2);
    });


    jQuery(document).on('click', '.diamond-list-table tbody tr.selected td:last-child',function () {
        updateRows();
        setTimeout(function(){
            jQuery('.diamond-list-table tbody tr').removeClass('odd').removeClass('even');
            jQuery(".diamond-list-table tbody tr:nth-child(odd)").addClass("odd");
            jQuery(".diamond-list-table tbody tr:nth-child(even)").addClass("even");
        },200);

        var offsetTable = jQuery('.list-table').offset().top
        var TableheaderHeight = jQuery('.page-header').outerHeight();
        jQuery('html, body').animate({
            //scrollTop: jQuery('.diamond-list-table tbody tr').eq(0).offset().top
            scrollTop: offsetTable - TableheaderHeight
        }, 1000);
    });


    jQuery('.change-image').change(function() {
        jQuery('.tooltip-multiimage img').hide();
        jQuery('.tooltip-multitext p').hide();
        var target_id = jQuery(this).attr('data-item-id');
        //var target_text_id = jQuery(this).attr('data-text-id');
        jQuery('.tooltip-multiimage img[data-target-id=' + target_id + ']').css('display','inline-block');
        jQuery('.tooltip-multitext p[data-text-id=' + target_id + ']').css('display','inline-block');
        return false;
    });



    viewSlider();

    jQuery(function () {
        jQuery('[data-toggle="tooltip"]').tooltip()
    })

    var selected = [];
    var compareCount = [];

    jQuery(document).on('click', '.compare-icon', function () {

        if(jQuery(this).parents('tr').hasClass('existrow')){
            return false;
        }
        var id = this.id;
        var index = jQuery.inArray(id, selected);

        if (index === -1) {
            selected.push(id);
        } else {
            selected.splice(index, 1);
        }
        if(compareCount.length == 0){
            for (var i = 0; i < jQuery('.diamond-list-table tbody tr').length; i++) {
                compareCount[i] = false;
            }
         }

        jQuery(this).parents('tr').toggleClass('selected');
        if(jQuery.inArray( "selected", jQuery(this).attr('class').split(" ")) >= 0){
            compareCount[jQuery(this).index()] = true;
        }else{
            compareCount[jQuery(this).index()] = false;
        }
        var selectedLength = jQuery('.diamond-list-table tbody tr.selected').length;
        //jQuery( ".comparedata" ).remove();
        jQuery('.diamond-list-table tbody tr').removeClass('uparraow');
        if(selectedLength > 1){
            jQuery('.diamond-list-table tbody tr.selected').eq(selectedLength-1).addClass('uparraow');
        }
        console.log(compareCount);
    });

    jQuery('#button').click(function () {
        table.row('.selected').remove().draw(false);
    });
    jQuery(window).on("load", function () {
        jQuery(".dataTables_scrollBody").mCustomScrollbar();
    });

    //jQuery('.main-content .col-right-side').css('height', jQuery(window).height());

    jQuery('#reset-btn').click(function () {
        jQuery(".custom-options ul li input:checkbox").removeAttr("checked");
        jQuery('.price-range').slider("values", 0, 0.2);
        jQuery('.price-range').slider("values", 1, 6);
        jQuery('.range-slider').siblings('.carat-content').find('input[id="max-carat"]').val(6);
        jQuery('.range-slider').siblings('.carat-content').find('input[id="min-carat"]').val(0.2);
        jQuery('#max-carat').val(6);
        jQuery('#min-carat').val(0.2);

        jQuery( "#depth-mm-slider" ).slider("values", 0, jQuery( "#depth-mm-slider" ).slider("option", "min"));
        jQuery( "#min-depth_mm").val(jQuery( "#depth-mm-slider" ).slider("option", "min")+'%');

        jQuery( "#depth-mm-slider" ).slider("values", 1, jQuery( "#depth-mm-slider" ).slider("option", "max"));
        jQuery( "#max-depth_mm").val(jQuery( "#depth-mm-slider" ).slider("option", "max")+'%');

        jQuery( "#table-per-slider" ).slider("values", 0, jQuery( "#table-per-slider" ).slider("option", "min"));
        jQuery( "#min-table_per").val(jQuery( "#table-per-slider" ).slider("option", "min")+'%');

        jQuery( "#table-per-slider" ).slider("values", 1, jQuery( "#table-per-slider" ).slider("option", "max"));
        jQuery( "#max-table_per").val(jQuery( "#table-per-slider" ).slider("option", "max")+'%');


        loadAjaxList(0,1);
        //jQuery(".attribute ul li input").eq(0).click();
    })
    jQuery('#ring-reset').click(function () {
        jQuery(".custom-options ul li input:checkbox").removeAttr("checked");
        jQuery('.size-slider').slider("values", 0, 4);
        jQuery('.size-slider').slider("values", 1, 9);
        jQuery('#max-ring-size').val(9);
        jQuery('#min-ring-size').val(4);
        loadAjaxList(0,1);
        //jQuery(".attribute ul li input").eq(0).click();
    })

    jQuery('.caratsection .carat-content input').change(function(e) {
        var setIndex = (this.id == "max-carat") ? 1 : 0;
        var id = jQuery(this).attr('id');
        jQuery('#'+id).val(jQuery(this).val());
        jQuery('.price-range').slider("values", setIndex, jQuery(this).val());
        loadAjaxList(0,1);
    })
    jQuery(".price-range").slider({
            min: 0,
            max: 6,
            range: true,
            values: [0.2, 6],
            step:0.1,
            orientation: "vertical",
/*            create: attachSlider,*/
/*            slide: attachSlider,*/
            stop: attachSlider
        }).slider("pips", {
            rest: "label",
            step:10,
        }).slider("float");

        jQuery(".size-slider").slider({
            min: 4,
            max: 9,
            step:0.5,
            range: true,
            values: [4.0, 9.0],
/*            slide: ringSlider,*/
            stop: ringSlider
        }).slider("pips", {
            rest: "label",
            step: 2,
            step:0.5,
        }).slider("float");



        var mindepth = jQuery( "#min-depth_mm" ).val();
        var maxdepth = jQuery( "#max-depth_mm" ).val();
        jQuery( "#depth-mm-slider" ).slider({
            range: true,
            min: parseInt(mindepth),
            max: parseInt(maxdepth),
            step:1,
            values: [ parseInt(mindepth), parseInt(maxdepth) ],
            stop: function( event, ui ) {
                jQuery( "#min-depth_mm" ).val(ui.values[0]+'%');
                jQuery( "#max-depth_mm" ).val(ui.values[1]+'%');
                loadAjaxList(0,1);
            }
        });
        jQuery( "#min-depth_mm" ).val(jQuery("#depth-mm-slider").slider("values",0)+'%');
        jQuery( "#max-depth_mm" ).val(jQuery("#depth-mm-slider").slider("values",1)+'%');



        var mintable = jQuery( "#min-table_per" ).val();
        var maxtable = jQuery( "#max-table_per" ).val();
        jQuery( "#table-per-slider" ).slider({
            range: true,
            min: parseInt(mintable),
            max: parseInt(maxtable),
            step:1,
            values: [ parseInt(mintable), parseInt(maxtable) ],
            stop: function( event, ui ) {
                jQuery( "#min-table_per" ).val(ui.values[0]+'%');
                jQuery( "#max-table_per" ).val(ui.values[1]+'%');
                loadAjaxList(0,1);
            }
        });
        jQuery( "#min-table_per" ).val(jQuery("#table-per-slider").slider("values",0)+'%');
        jQuery( "#max-table_per" ).val(jQuery("#table-per-slider").slider("values",1)+'%');






        jQuery('.more-filters .filter-btn').click(function (event) {
            event.stopPropagation();
            jQuery('.more-filters .more-content').slideToggle();
            setTimeout(function (){
                equalheight('.more-filters .more-content .bandwith li');
                var boxHeight = jQuery('.more-content-wrapper').outerHeight();
                jQuery(".more-filters .more-content").css('min-height', boxHeight);
                changeSlide();
            },200)

        });
        jQuery('.more-filters .more-content > .close-btn').click(function () {
            jQuery('.more-filters .more-content').hide();
        })
        jQuery('.more-filters').on('click', function (event) {
            event.stopPropagation();
        });
        jQuery(document).click(function (event) {
            if (!jQuery(event.target).hasClass('more-filters')) {
                jQuery('.more-filters .more-content').hide()
            }
        });

        jQuery(window).scroll(function() {
            if(jQuery('body').hasClass('wizard-ring-index') == true || jQuery('body').hasClass('wizard-index-index') == true){
                changeSlide();
            }
        });

        function changeSlide() {
            var midOffset = jQuery(window).scrollTop();
            var boxHeight = jQuery('.more-options').offset().top;
            if( (boxHeight-midOffset) >= (boxHeight + 100))
            {
               jQuery('.more-content').removeClass("top");
            } else {
               jQuery('.more-content').addClass("top");
            }
        }


    //attribute mobile dropdonw

    jQuery('.listBtn .toggle').click('touchstart',function (){
        jQuery(this).next().addClass('active');
        jQuery('body').addClass('dropdonwOpen');
        jQuery(this).parents('.attr').addClass('open');
    })
    jQuery('.closeDropdown').click(function (){
        jQuery(this).parent().removeClass('active');
        jQuery('body').removeClass('dropdonwOpen');
        jQuery(this).parents('.attr').removeClass('open');
    })

    itemEquealHeight();

    jQuery('.tooltip-content label').click(function (event) {
        jQuery(this).parent('li').find('input').trigger('click');
        event.preventDefault();
    })


    jQuery('.ring-size-block-select').on('click',function(e){
        e.stopPropagation();
        if(!jQuery(this).hasClass('open')){
            jQuery(this).addClass('open');
        }else{
            jQuery(this).removeClass('open');
        }
    });
    jQuery(document).click(function(){
        jQuery('.ring-size-block-select').removeClass('open');
    });

    jQuery('[data-fancybox="wizard-slider"]').fancybox({
        // Options will go here
         baseClass: "zoom-fancybox",
    });

});


function tableHeaderSticky() {
    //jQuery('.header-table').remove();
    var tblHeading = jQuery('.list-table .diamond-list-table thead');
    if(jQuery('.list-table').find('.header-table').length == 0){
      jQuery('.list-table').prepend('<table class="header-table"></table>');
    }
    setTimeout (function (){
        jQuery('.list-table > .header-table').prepend(tblHeading);
    },500)
    //jQuery('.list-table').prepend('<table class="header-table"></table>');

}

jQuery(window).load(function () {
    itemEquealHeight();
    tableHeaderSticky();

    setTimeout(function (){
        itemEquealHeight();
    },200)

    var liselect = jQuery(".size-dropdown > li.selected").text();
    jQuery(".selected-value > span").text(liselect);
    jQuery(".size-dropdown > li").click(function() {
       jQuery(".selected-value > span").text(jQuery(this).text());
    });

});

jQuery(window).resize(function () {

    if(jQuery('body').find('.list-table').length > 0){
        var offsetTable = jQuery('.list-table').offset().top
        console.log('resize');
    }
    var TableheaderHeight = jQuery('.page-header').outerHeight();

    itemEquealHeight();
    tableHeaderSticky();
    if (jQuery(window).width() < 767) {
        /*jQuery('.carat-for-mobile .listBtn').click(function (){

        })*/
        setTimeout(function(){
            jQuery(".range-slider").detach().appendTo('.dropdown-attribute .range-section');
        },100);
    }
    else {
        setTimeout(function(){
            jQuery(".range-slider").detach().prependTo(".custom-options .right > .range-section");
        },100);

    }
    itemEquealHeight();
});

/***
# Equlae height Function
***/

equalheightnoRow = function (container) {
    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = new Array(),
        jQueryel
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

equalheight = function (container) {
    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = new Array(),
        $el, topPosition = 0;
    jQuery(container).each(function () {
        $el = jQuery(this);
        jQuery($el).height('auto')
        topPostion = $el.position().top;
        if (currentRowStart != topPostion) {
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].height(currentTallest);
            }
            rowDivs.length = 0; // empty the array
            currentRowStart = topPostion;
            currentTallest = $el.height();
            rowDivs.push($el);
        } else {
            rowDivs.push($el);
            currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
        }
        for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
            rowDivs[currentDiv].height(currentTallest);
        }
    });
};

function popupSlider() {
    jQuery(".popup-main-image").removeClass("slick-initialized slick-slider");
    jQuery(".popup-thumb-image").removeClass("slick-initialized slick-slider");

    jQuery('.popup-main-image').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        asNavFor: '.popup-thumb-image',
        fade: true,
        draggable: false
    });
    jQuery('.popup-thumb-image').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        asNavFor: '.popup-main-image',
        dots: false,
        focusOnSelect: true,
        draggable: false
    });
}

function viewSlider() {

    jQuery('.viewcomplete-image').each(function(key, item) {

      var sliderIdName = 'viewcomplete-image' + key;
      var sliderNavIdName = 'viewcomplete-thumb' + key;

      this.id = sliderIdName;
      jQuery('.viewcomplete-thumb')[key].id = sliderNavIdName;

      var sliderId = '#' + sliderIdName;
      var sliderNavId = '#' + sliderNavIdName;

      jQuery(sliderId).slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        draggable: false,
        adaptiveHeight: false,
        asNavFor: sliderNavId,
        //loop: false,
        responsive: [
            {
                breakpoint: 0,
                settings: {
                    arrows: false
                }
            },
            {
                breakpoint: 768,
                settings: {
                    arrows: true
                }
            }

        ]
      });

      jQuery(sliderNavId).slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        dots: false,
        focusOnSelect: true,
        draggable: false,
        asNavFor: sliderId,
        adaptiveHeight: false,
        //loop: false,
        centerMode: false,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
            }
        ]
      });

    });

    jQuery('.viewcomplete-thumb .slick-slide').on('afterChange', function (event, slick, currentSlide) {
        equalheightnoRow(".view-completed-slider .viewcomplete-thumb .slick-slide");
    });

}


function onReady(adataSet)
{
    var columnOrder = [
            { "sTitle": "ACTUAL PHOTO" },
            { "sTitle": "SHAPE","sClass": "heading shapesort" },
            { "sTitle": "CARAT","sClass": "heading caratsort" },
            { "sTitle": "color","sClass": "heading colorsort"},
            { "sTitle": "CLARITY","sClass": "heading claritysort"},
            { "sTitle": "cut","sClass": "heading cutsort"},
            { "sTitle": "price","sClass": "heading pricesort"},
            { "sTitle": "comparison"}
        ];
    var selected = [];
   var config = {
        "aaData": adataSet,
        "aoColumns": columnOrder,
    //"scrollY": 845,
    "scrollX": false,
    paging: false,
    searching: false,
    responsive:true,
    info: false,
    fixedHeader:true,
    select: true,
    "language": {
        "emptyTable":"No Available diamonds"
    },
    initComplete: function () {
            this.api().columns('.heading').every(function (index) {
                var column = this;

            });
        },
    "rowCallback": function (row, data) {
        if (jQuery.inArray(data.DT_RowId, selected) !== -1) {
            jQuery(row).addClass('selected');
        }
    }
  };

    if ( jQuery.fn.DataTable.isDataTable('.diamond-list-table') ) {
        jQuery('.diamond-list-table').DataTable().destroy();
    }
    tableData = jQuery('.diamond-list-table').DataTable(config);
}


function attachSlider() {
    jQuery('#min-carat').val(jQuery('.price-range').slider("values", 0));
    jQuery('#max-carat').val(jQuery('.price-range').slider("values", 1));
   jQuery('.range-slider').siblings('.carat-content').find('input[id="min-carat"]').val(jQuery('.price-range').slider("values", 0));
   jQuery('.range-slider').siblings('.carat-content').find('input[id="max-carat"]').val(jQuery('.price-range').slider("values", 1));
    loadAjaxList(0,1);
}
function ringSlider() {
 jQuery('input[id="min-ring-size"]').val(jQuery('.size-slider').slider("values", 0));
 jQuery('input[id="max-ring-size"]').val(jQuery('.size-slider').slider("values", 1));

    loadAjaxList(0,1);
}
function compare(e){

    alert('compare');
    return false;
}


function itemEquealHeight() {

    equalheightnoRow(".setting-grid .grid > .item");
    //equalheightnoRow(".view-completed-slider .viewcomplete-thumb .slick-slide");
    equalheightnoRow(".setting-grid .grid .item .title");
    equalheightnoRow(".custom-options .shapes .attribute li");
    equalheight(".carat-details .products-list .item");
    equalheightnoRow(".custom-options .metal .attribute li .name");
    equalheightnoRow(".details-content-section .metal-option li");
    equalheight('.more-filters .more-content .bandwith li');
    equalheightnoRow('.setting-grid .grid .item');
    equalheightnoRow('.details-diamond-type.diamond-type .item.diamond-item.diamond-color-item .item-option > ul > li');
    equalheightnoRow('.details-diamond-type.diamond-type .item.diamond-item.clarity-item .item-option > ul > li');
    equalheightnoRow('.custom-options .shapes-2 .attribute li label span.name');
    equalheightnoRow('.full-diamond > div');
    equalheightnoRow('.wizard-ring-index .custom-options .clarity .attribute li label span.name');
    equalheightnoRow('.wizard .details-diamond-type.diamond-type .item.caratr-item li');

    setTimeout(function (){
        equalheight('.wizard .details-diamond-type.diamond-type .item');
    },500)

}

function updateRows(){
    var cnt = 0;
        var selLength = jQuery(".diamond-list-table tbody tr.selected").length;
        jQuery('.diamond-list-table tbody tr').removeClass('comparelast');
        //jQuery('.diamond-list-table tbody tr').removeClass('selected');
        jQuery('.diamond-list-table tbody tr .compare-remove').remove();

        jQuery(".diamond-list-table tbody tr").each(function( index ) {
            var className =  jQuery(this).attr('class');
            if(jQuery.inArray( "selected", className.split(" ")) >= 0){
                jQuery(this).addClass('existrow');
                jQuery('.diamond-list-table tbody tr').eq(index).insertBefore(jQuery('.diamond-list-table tbody tr').eq(cnt));

                if(selLength == cnt+1){
                    jQuery('.diamond-list-table tbody tr').eq(cnt).addClass('comparelast');
                }
                if(cnt == 0){
                    jQuery('.diamond-list-table tbody tr').eq(cnt).find('td:last').append('<a href="javascript:void(0);" class="compare-remove">close</a>');
                }
                cnt++;
            }

        })
}

function removeCompare(){
    jQuery('.diamond-list-table tbody tr .compare-remove').remove();
    jQuery('.diamond-list-table tbody tr').removeClass('selected');
    jQuery('.diamond-list-table tbody tr').removeClass('existrow');
    jQuery( ".comparedata" ).remove();
    return false;
}