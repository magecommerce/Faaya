/*!
 * Based on Waterwheel Carousel Version 2.3.0
 * http://www.bkosborne.com
 *
 * Dual licensed under GPLv3 or MIT
*/
;(function ($) {
  'use strict';
/* Added for responsiveness start - pinwheel */
	var $event = jQuery.event,
	$special,
	currEvent,
	resizeTimeout;

	$special = $event.special.debouncedresize = {
		setup: function() {
			jQuery( this ).on( "resize", $special.handler );
		},
		teardown: function() {
			jQuery( this ).off( "resize", $special.handler );
		},
		handler: function( event, execAsap ) {
			//Added to get event type in reload function to fix rezie issue incase of ios
			currEvent = event.type;
			// Save the context
			var context = this,
				args = arguments,
				dispatch = function() {
					// set correct event type
					event.type = "debouncedresize";
					$event.dispatch.apply( context, args );
				};

			if ( resizeTimeout ) {
				clearTimeout( resizeTimeout );
			}

			execAsap ?
				dispatch() :
				resizeTimeout = setTimeout( dispatch, $special.threshold );
		},
		threshold: 200
	};
/* Added for responsiveness end - pinwheel */
	$.fn.pinwheelAlt = function (startingOptions) {

		// Adds support for intializing multiple carousels from the same selector group
		if (this.length > 1) {
			this.each(function() {
				$(this).pinwheelAlt(startingOptions);
			});
			return this; // allow chaining
		}
	
		var carousel = this;
		var options = {};
		var data = {};
		var pluginData = {
			currentCenterNum:     options.startingFeature,
			containerWidth:       0,
			containerHeight:      0,
			totalFeatureCount:    $(this).children("div.pinwheel_slideri").length,
			currentlyMoving:      false,
			featuresContainer:    $(this),
			featuresArray:        [],
			containerIDTag:       "#"+$(this).attr("id"),
			timeoutVar:           null,
			rotationsRemaining:   0,
			itemsToAnimate:       0,
			borderWidth:	    0
		};
		function initializeCarouselData() {
			data = {
				itemsContainer:         $(carousel),
				totalItems:             $(carousel).find("div.pinwheel_slideri").length,
				containerWidth:         $(carousel).width(),
				containerHeight:        $(carousel).height(),
				currentCenterItem:      null,
				previousCenterItem:     null,
				items:                  [],
				calculations:           [],
				carouselRotationsLeft:  0,
				currentlyMoving:        false,
				itemsAnimating:         0,
				currentSpeed:           options.speed,
				intervalTimer:          null,
				currentDirection:       'forward',
				leftItemsCount:         0,
				rightItemsCount:        0,
				performingSetup:        true
			};
			/* Added for responsiveness start - pinwheel */			
			if(options.trackerIndividual==true || options.trackerSummation==true)
				data.containerHeight=options.sliderMaxHeight*(data.containerWidth/options.sliderMaxWidth)+20;
			else
				data.containerHeight=options.sliderMaxHeight*(data.containerWidth/options.sliderMaxWidth);
			data.itemsContainer.height(data.containerHeight);
			/* Added for responsiveness end - pinwheel */
			data.itemsContainer.find('.pinwheel_slideri').removeClass(options.activeClassName);
			
		}
		// get previous feature number
		var getPreviousNum = function(num) {
			if ((num - 1) == 0) {
				return pluginData.totalFeatureCount;
			}
			else {
				return num - 1;
			}
		}

		// get next feature number
		var getNextNum = function(num) {
			if ((num + 1) > pluginData.totalFeatureCount) {
				return 1;
			}
			else {
				return num + 1;
			}
		}

		var getNextNumberCustom = function(num) {
			return num + 1;
      
		}

	/**
	* This function will set the autoplay for the carousel to
	* automatically rotate it given the time in the options
	* Can clear the autoplay by passing in true
	*/
	function autoPlay(stop) {
		// clear timer
		clearTimeout(data.autoPlayTimer);
		// as long as no stop command, and autoplay isn't zeroed...
		if (!stop && options.autoPlay !== 0) {
		// set timer...
			data.autoPlayTimer = setTimeout(function () {
		  		// to move the carousl in either direction...
		 		 if (options.autoPlay > 0) {
		    			moveOnce('forward');
		  		 } 
				 else {
		    			moveOnce('backward');
		  		 }
			}, Math.abs(options.autoPlay));
		}
	}

	/**
	* This function will preload all the images in the carousel before
	* calling the passed in callback function. This is only used so we can
	* properly determine the width and height of the items. This is not needed
	* if a user instead manually specifies that information.
	*/
	function preload(callback) {
		if (options.preloadImages === false) {
			callback();
			return;
		}
		var $imageElements = data.itemsContainer.find('img'), loadedImages = 0, totalImages = $imageElements.length;

		$imageElements.each(function () {
			$(this).bind('load', function () {
			  	  // Add to number of images loaded and see if they are all done yet
				  loadedImages += 1;
				  if (loadedImages === totalImages) {
					  // All done, perform callback
					  callback();
					  return;
				  }
			});
			// May need to manually reset the src to get the load event to fire
			// http://stackoverflow.com/questions/7137737/ie9-problems-with-jquery-load-event-not-firing
			$(this).attr('src', $(this).attr('src'));
			// If browser has cached the images, it may not call trigger a load. Detect this and do it ourselves
			if (this.complete) {
				$(this).trigger('load');
			}
		});
	}

	/**
	* Makes a record of the original width and height of all the items in the carousel.
	* If we re-intialize the carousel, these values can be used to re-establish their
	* original dimensions.
	*/
	function setOriginalItemDimensions() {
		data.itemsContainer.find('.pinwheel_slideri').each(function () {
			if ($(this).data('original_width') == undefined || options.forcedImageWidth > 0) {
				$(this).data('original_width', $(this).width());
			}
			if ($(this).data('original_height') == undefined || options.forcedImageHeight > 0) {
				$(this).data('original_height', $(this).height());
			}
		});
	}

	/**
	* Users can pass in a specific width and height that should be applied to every image.
	* While this option can be used in conjunction with the image preloader, the intended
	* use case is for when the preloader is turned off and the images don't have defined
	* dimensions in CSS. The carousel needs dimensions one way or another to work properly.
	*/
	function forceImageDimensionsIfEnabled() {
		if (options.forcedImageWidth && options.forcedImageHeight) {
			data.itemsContainer.find('.pinwheel_slideri').each(function () {
				$(this).width(options.forcedImageWidth);
				$(this).height(options.forcedImageHeight);
			});
		}
	}

	/**
	* For each "visible" item slot (# of flanking items plus the middle),
	* we pre-calculate all of the properties that the item should possess while
	* occupying that slot. This saves us some time during the actual animation.
	*/
	function preCalculatePositionProperties() {
		// The 0 index is the center item in the carousel
		var $firstItem = data.itemsContainer.find('.pinwheel_slideri:first');
		data.calculations[0] = {
			distance: 0,
			offset:   0,
			opacity:  1
		}
		// Then, for each number of flanking items (plus one more, see below), we
		// perform the calcations based on our user options
		var horizonOffset = options.horizonOffset;
		var separation=((data.containerWidth/options.sliderMaxWidth)*options.separation);
		for (var i = 1; i <= options.flankingItems + 2; i++) {
			if (i > 1) {
				horizonOffset *= options.horizonOffsetMultiplier;
				separation *= options.separationMultiplier;
			}
			data.calculations[i] = {
				distance: data.calculations[i-1].distance + separation,
				offset:   data.calculations[i-1].offset + horizonOffset,
				opacity:  data.calculations[i-1].opacity * options.opacityMultiplier
			}
		}
		// We performed 1 extra set of calculations above so that the items that
		// are moving out of sight (based on # of flanking items) gracefully animate there
		// However, we need them to animate to hidden, so we set the opacity to 0 for
		// that last item
		if (options.edgeFadeEnabled) {
			data.calculations[options.flankingItems+1].opacity = 0;
		} 
		else {
			data.calculations[options.flankingItems+1] = {
				distance: 0,
				offset: 0,
				opacity: 0
			}
		}

	}

	/**
	* Here we prep the carousel and its items, like setting default CSS
	* attributes. All items start in the middle position by default
	* and will "fan out" from there during the first animation
	*/
	function setupCarousel() { 
		// Fill in a data array with jQuery objects of all the images
		data.items = data.itemsContainer.find('.pinwheel_slideri');
		for (var i = 0; i < data.totalItems; i++) {
			var $item=data.items[i] = $(data.items[i]);
			$item.data('idx',i+1);
		}

		// May need to set the horizon if it was set to auto
		if (options.horizon === 0) {
			if (options.orientation === 'horizontal') {
				options.horizon = data.containerHeight / 2;
			} 
			else {
		 		options.horizon = data.containerWidth / 2;
			}
		}
		/* Added for image size calculation - start */ 
		/*data.itemsContainer
		.css('position','relative')
		.find('img')
		  .each(function () {
			    // Figure out where the top and left positions for center should be
			    var centerPosLeft, centerPosTop;
			    if (options.orientation === 'horizontal') {
				    centerPosLeft = (data.containerWidth / 2) - ($(this).data('original_width') / 2);
				    centerPosTop = options.horizon - ($(this).data('original_height') / 2);
			    } 
			    else {
				    centerPosLeft = options.horizon - ($(this).data('original_width') / 2);
				    centerPosTop = (data.containerHeight / 2) - ($(this).data('original_height') / 2);
			    }
			    $(this)
			      // Apply positioning and layering to the images
			      .css({
				'left': centerPosLeft,
				'top': centerPosTop,
				'visibility': 'visible',
				'position': 'absolute'
		
			      })
			      // Give each image a data object so it remembers specific data about
			      // it's original form
			      .data({
				top:             centerPosTop,
				left:            centerPosLeft,
				oldPosition:     0,
				currentPosition: 0,
				depth:           0,
				opacity:         0
			      })
			      // The image has been setup... Now we can show it
			      .show();
		  });*/

		/* Added for image size calculation - end */

		// Default all the items to the center position
		data.itemsContainer
		.css('position','relative')
		.find('.pinwheel_slideri')
		  .each(function () {
			    // Figure out where the top and left positions for center should be
			    var centerPosLeft, centerPosTop;
			    if (options.orientation === 'horizontal') {
				    centerPosLeft = (data.containerWidth / 2) - ($(this).data('original_width') / 2);
				    centerPosTop = options.horizon - ($(this).data('original_height') / 2);
			    } else {
				    centerPosLeft = options.horizon - ($(this).data('original_width') / 2);
				    centerPosTop = (data.containerHeight / 2) - ($(this).data('original_height') / 2);
			    }
			    $(this)
			      // Apply positioning and layering to the images
			      .css({
				'left': centerPosLeft,
				'top': centerPosTop,
				'visibility': 'visible',
				'position': 'absolute',
				'z-index': 0,
				'opacity': 0
			      })
			      // Give each image a data object so it remembers specific data about
			      // it's original form
			      .data({
				top:             centerPosTop,
				left:            centerPosLeft,
				oldPosition:     0,
				currentPosition: 0,
				depth:           0,
				opacity:         0
			      })
			      // The image has been setup... Now we can show it
			      .show();
			 
		  });
		

	}
	/* This function will set up the two different types of trackers used */
	var setupTrackers = function()
	{ 		
		/*if(jQuery(".tracker-individual-container").length==0){ //Added for window resize*/
			if (options.trackerIndividual) {
				// construct the tracker list
				var $list = $("<ul></ul>");
				$list.addClass("tracker-individual-container");
				for (var i = 0; i < pluginData.totalFeatureCount; i++) {
					// item position one plus the index
					var counter = i+1;
					// Build the DOM for the tracker list
					var $trackerBlip = $("<div>"+counter+"</div>");
					$trackerBlip.addClass("tracker-individual-blip");
					//Added 1 line for Pinwheel
					$trackerBlip.addClass("tracker-"+(i+1));
					$trackerBlip.css("cursor","pointer");
					 
					//$trackerBlip.attr("id","tracker-"+(i+1));
					if(i==0){$trackerBlip.addClass("tracker-individual-blip-selected");}
					var $listEntry = $("<li></li>");
					$listEntry.append($trackerBlip);
					$listEntry.css("float","left");
					$listEntry.css("list-style-type","none");
					$list.append($listEntry);
				}
				// add the blip list and then make sure it's visible
				$(pluginData.containerIDTag).append($list);
				$list.hide().show();
			}

			if (options.trackerSummation) {
				// Build the tracker div that will hold the tracking data
				var $tracker = $('<div></div>');
				$tracker.addClass('tracker-summation-container');
				// Collect info in spans
				var $current = $('<span></span>').addClass('tracker-summation-current').text(options.startingFeature);
				var $total = $('<span></span>').addClass('tracker-summation-total').text(pluginData.totalFeatureCount);
				var $middle = $('<span></span>').addClass('tracker-summation-middle').text(' of ');
				// Add it all together
				$tracker.append($current).append($middle).append($total);
				// Insert into DOM
				$(pluginData.containerIDTag).append($tracker);
			}
		//}//end if of whethere class is present or not
	}

	var updateTracker = function(oldCenter, newCenter) { 

		if (options.trackerIndividual) {
			// get selectors for the two trackers
			var $trackerContainer = pluginData.featuresContainer.find(".tracker-individual-container");
			var $oldCenter = $trackerContainer.find(".tracker-"+oldCenter);
			var $newCenter = $trackerContainer.find(".tracker-"+newCenter);
			// change classes
			$oldCenter.removeClass("tracker-individual-blip-selected");
			$newCenter.addClass("tracker-individual-blip-selected");
		}
		if (options.trackerSummation) {
			var $trackerContainer = pluginData.featuresContainer.find('.tracker-summation-container');
			$trackerContainer.find('.tracker-summation-current').text(newCenter);
		}
	}


	/**
	* This will find the shortest distance to travel the carousel from
	* one position to another position. It will return the shortest distance
	* in number form, and will be positive to go to the right and negative for left
	*/
	var findShortestDistance = function(from, to) {
		var goingToLeft = 0, goingToRight = 0, tracker;
		tracker = from;
		// see how long it takes to go to the left
		while (tracker != to) {        	
			goingToLeft++;
			tracker = getPreviousNum(parseInt(tracker));
		}

		tracker = from;
		// see how long it takes to to to the right
		while (tracker != to) {
			goingToRight++;
			tracker = getNextNum(parseInt(tracker));
		}

		// whichever is shorter
		return (goingToLeft < goingToRight) ? goingToLeft*-1 : goingToRight;
	}
	/**/
	
	// Did someone click one of the individual trackers?

	$(document).on("click", pluginData.containerIDTag+" .tracker-individual-blip" , function () {
		var goTo = $(this).text();
		var currentlyAt=$(pluginData.containerIDTag+" .tracker-individual-container .tracker-individual-blip-selected").text();
		if (goTo != currentlyAt) {
			// find the shortest distance to move the carousel
			var shortest = findShortestDistance(currentlyAt,goTo);
			updateTracker(parseInt(currentlyAt),parseInt(goTo));
			var itemPosition = shortest;
			if (options.imageNav == false) {
				return;
			}
			// Do nothing if the carousel is already moving
			if (data.currentlyMoving) {
				return;
			}
			data.previousCenterItem = data.currentCenterItem;
			// Remove autoplay
			autoPlay(true);
			options.autoPlay = 0;
			var rotations = Math.abs(itemPosition);
			if (itemPosition == 0) {
				options.clickedCenter($(this));
			} 
			else {
			// Fire the 'moving' callbacks
				options.movingFromCenter(data.currentCenterItem);
				options.movingToCenter($(this));
				if (itemPosition < 0 ) {
				 	data.currentDirection = 'backward';
					rotateCarousel(rotations);
				}
				else if (itemPosition > 0) {
					data.currentDirection = 'forward';
					rotateCarousel(rotations);
				}
			}

		}

	});

	/**
	* All the items to the left and right of the center item need to be
	* animated to their starting positions. This function will
	* figure out what items go where and will animate them there
	*/
	function setupStarterRotation() {
		options.startingItem = (options.startingItem === 0) ? Math.round(data.totalItems / 2) : options.startingItem;
		data.rightItemsCount = Math.ceil((data.totalItems-1) / 2);
		data.leftItemsCount = Math.floor((data.totalItems-1) / 2);
		// We are in effect rotating the carousel, so we need to set that
		data.carouselRotationsLeft = 1;
		// Center item
		moveItem(data.items[options.startingItem-1], 0);
		data.items[options.startingItem-1].css('opacity', 1);
		// All the items to the right of center
		var itemIndex = options.startingItem - 1;
		for (var pos = 1; pos <= data.rightItemsCount; pos++) {
			(itemIndex < data.totalItems - 1) ? itemIndex += 1 : itemIndex = 0;
			data.items[itemIndex].css('opacity', 1);
			moveItem(data.items[itemIndex], pos);
		}
		// All items to left of center
		var itemIndex = options.startingItem - 1;
		for (var pos = -1; pos >= data.leftItemsCount*-1; pos--) {
			(itemIndex > 0) ? itemIndex -= 1 : itemIndex = data.totalItems - 1;
			data.items[itemIndex].css('opacity', 1);
			moveItem(data.items[itemIndex], pos);
		}
	}

	/**
	* Given the item and position, this function will calculate the new data
	* for the item. One the calculations are done, it will store that data in
	* the items data object
	*/
	function performCalculations($item, newPosition) {
		var pFactor=data.containerWidth/options.sliderMaxWidth;      
		var newDistanceFromCenter = Math.abs(newPosition);
		// Distance to the center
		if (newDistanceFromCenter < options.flankingItems + 1) {
			var calculations = data.calculations[newDistanceFromCenter];
		} 
		else {
			var calculations = data.calculations[options.flankingItems + 1];
		}
		var distanceFactor = Math.pow(options.sizeMultiplier, newDistanceFromCenter);
		var newWidth = distanceFactor * ($item.data('original_width')* pFactor);
		var newHeight = distanceFactor * ($item.data('original_height')* pFactor);
		var newOffset = calculations.offset
		var newDistance = calculations.distance; //privious distance + seperation
		if (newPosition < 0) {
			newDistance *= -1;   //if left slide then new distance will be negative
		}

		if (options.orientation == 'horizontal') {
			var center = data.containerWidth / 2;
			var newLeft = center + newDistance - (newWidth / 2);
			var newTop = (options.horizon*pFactor) - newOffset - (newHeight / 2);
		} 
		else {
			var center = data.containerHeight / 2;
			var newLeft = options.horizon - newOffset - (newWidth / 2);
			var newTop = center + newDistance - (newHeight / 2);
		}
		var newOpacity;
		if (newPosition === 0) {
			newOpacity = 1;
		} else {
			newOpacity = calculations.opacity;
		}
		// Depth will be reverse distance from center
		var newDepth = options.flankingItems + 2 - newDistanceFromCenter;
		$item.data('width',newWidth);
		$item.data('height',newHeight);
		$item.data('top',newTop);
		$item.data('left',newLeft);
		$item.data('oldPosition',$item.data('currentPosition'));
		$item.data('depth',newDepth);
		$item.data('opacity',newOpacity);
	}

	function moveItem($item, newPosition) { 
		// Only want to physically move the item if it is within the boundaries
		// or in the first position just outside either boundary
		if (Math.abs(newPosition) <= options.flankingItems + 1) {
			performCalculations($item, newPosition);
			data.itemsAnimating++;
			$item
			.css('z-index',$item.data().depth)
			// Animate the items to their new position values
			.animate({
				    left:    $item.data().left,
				    width:   $item.data().width,
				    height:  $item.data().height,
				    top:     $item.data().top,
				    opacity: $item.data().opacity
				  }, data.currentSpeed, options.animationEasing, function () {
				    // Animation for the item has completed, call method
				    itemAnimationComplete($item, newPosition);
			});
		} 
		else {
			$item.data('currentPosition', newPosition)
			// Move the item to the 'hidden' position if hasn't been moved yet
			// This is for the intitial setup
			if ($item.data('oldPosition') === 0) {
				$item.css({
				    'left':    $item.data().left,
				    'width':   $item.data().width,
				    'height':  $item.data().height,
				    'top':     $item.data().top,
				    'opacity': $item.data().opacity,
				    'z-index': $item.data().depth
				  });
			}
		}

	}

	/**
	* This function is called once an item has finished animating to its
	* given position. Several different statements are executed here, such as
	* dealing with the animation queue
	*/
	function itemAnimationComplete($item, newPosition) { 
		data.itemsAnimating--;
		$item.data('currentPosition', newPosition);

		// Keep track of what items came and left the center position,
		// so we can fire callbacks when all the rotations are completed
		if (newPosition === 0) {
			data.currentCenterItem = $item;
		}
		// all items have finished their rotation, lets clean up
		if (data.itemsAnimating === 0) {
			data.carouselRotationsLeft -= 1;
			data.currentlyMoving = false;
			// If there are still rotations left in the queue, rotate the carousel again
			// we pass in zero because we don't want to add any additional rotations
			if (data.carouselRotationsLeft > 0) {
				rotateCarousel(0);
				// Otherwise there are no more rotations and...
			}
			else {
			
			
			  //added by prashant
			  //Changes for animation
			  var anim=data.currentCenterItem.find(".pinwheel_slider_thumbnail").data("anim");
			  var contentanim=data.currentCenterItem.find(".pinwheel_text span").data("anim");
			  var titleanim=data.currentCenterItem.find(".slider_htitle").data("anim");
			  data.currentCenterItem.find(".pinwheel_slider_thumbnail").removeClass("pinwheel-animated");
			  data.currentCenterItem.find(".pinwheel_slider_thumbnail").removeClass("pinwheel-"+anim);
			  
			  data.currentCenterItem.find(".pinwheel_text span").removeClass("pinwheel-animated");
			  data.currentCenterItem.find(".pinwheel_text span").removeClass("pinwheel-"+contentanim);
			  
			  data.currentCenterItem.find(".slider_htitle").removeClass("pinwheel-animated");
			  data.currentCenterItem.find(".slider_htitle").removeClass("pinwheel-"+titleanim);
	 
			//Added For Video
			/* ----------------------------------------------------
			Code for Iframe Auto Pause when moves to next slide
			-------------------------------------------------------*/
			var allIframes = jQuery(pluginData.containerIDTag).find(".pinwheel_eshortcode iframe");
			var allIfrmDivs = jQuery(pluginData.containerIDTag).find(".pinwheel_eshortcode .pinwheel-iframe");
			if(allIframes.length > 0 || allIfrmDivs.length > 0 ) {
				jQuery(allIframes).each(function(index, elm) {
					var iframeHtml, prnt;
					iframeHtml = jQuery(this)[0].outerHTML.replace(/<iframe/,"<div class=\'pinwheel-iframe\'").replace("</iframe>","</div>");
					prnt = jQuery(elm).parent();
					jQuery(elm).remove();
					prnt.html(iframeHtml);
				});
		
				var currIframe = data.currentCenterItem.find(".pinwheel_eshortcode .pinwheel-iframe");

				if(currIframe.length > 0) {
					var ifrmdivHtml = currIframe[0].outerHTML.replace(/<div class="pinwheel-iframe"/,"<iframe").replace("</div>","</iframe>");
					var prnt = currIframe.parent();
					currIframe.remove();
					prnt.html(ifrmdivHtml);
				}
			}
			//For positioning using Focus Point JS
			if(options.timthumb=='2') {	
				 data.currentCenterItem.find(".focuscrop").focusPoint('adjustFocus');
			}
			/* ----------------------------------------------------
			END - Code for Iframe fix
				---------------------------------------------------- */

				// Reset the speed of the carousel to original
				data.currentSpeed = options.speed;
				data.currentCenterItem.removeClass(options.leftClassName);
				data.currentCenterItem.removeClass(options.rightClassName);
				data.currentCenterItem.removeClass(options.hiddenClassName);
				data.currentCenterItem.addClass(options.activeClassName);
				
				if (data.performingSetup === false) {
					options.movedToCenter(data.currentCenterItem);
					options.movedFromCenter(data.previousCenterItem);
		  		}
				data.performingSetup = false;
				
				//Add classes to left slides
				for(var t = 0;t < data.leftItemsCount; t++){
					var lft=getPreviousNum(data.currentCenterItem.data('idx')-t);
					if(lft < 0){
						 lft=data.totalItems+lft;		
					}
					if(data.items[lft-1].hasClass(options.leftClassName))
						data.items[lft-1].removeClass(options.leftClassName);
					if(data.items[lft-1].hasClass(options.rightClassName))
						data.items[lft-1].removeClass(options.rightClassName);
					if(data.items[lft-1].hasClass(options.hiddenClassName))
						data.items[lft-1].removeClass(options.hiddenClassName);
					if(data.items[lft-1].data('opacity')== 0){
						if(data.items[lft-1].hasClass(options.leftClassName))
							data.items[lft-1].removeClass(options.leftClassName);
						if(data.items[lft-1].hasClass(options.rightClassName))
							data.items[lft-1].removeClass(options.rightClassName);
						data.items[lft-1].addClass(options.hiddenClassName);		
					}
					else{
						data.items[lft-1].addClass(options.leftClassName);
					}
					
				}	
				//Add classes to right Slides
				var rghtcnt=Math.ceil((data.totalItems-1) / 2);
				for(var t = 0;t < rghtcnt; t++){
					var right=getNextNumberCustom(data.currentCenterItem.data('idx')+t);
					if(right > data.totalItems){
						 right=right-data.totalItems;		
					}
	
					if(data.items[right-1].hasClass(options.leftClassName))
						data.items[right-1].removeClass(options.leftClassName);
					if(data.items[right-1].hasClass(options.rightClassName))
						data.items[right-1].removeClass(options.rightClassName);
					if(data.items[right-1].hasClass(options.hiddenClassName))
						data.items[right-1].removeClass(options.hiddenClassName);
					if(data.items[right-1].data('opacity')== 0){
						if(data.items[right-1].hasClass(options.leftClassName))
							data.items[right-1].removeClass(options.leftClassName);
						if(data.items[right-1].hasClass(options.rightClassName))
							data.items[right-1].removeClass(options.rightClassName);
						data.items[right-1].addClass(options.hiddenClassName);		
					}
					else{
						data.items[right-1].addClass(options.rightClassName);
					}
					
				}
				//Update tracker
				var oldTrackerPosition=parseInt(jQuery(pluginData.containerIDTag+" .tracker-individual-container .tracker-individual-blip-selected").text());
				updateTracker(parseInt(oldTrackerPosition),parseInt(data.currentCenterItem.data('idx')));
				//Slide up caption if captionBelow is true
				if(options.captionBelow==true){ 
				jQuery(pluginData.containerIDTag).find(".pinwheel_left_slide .carousel-caption").css({"display":"none"});
				jQuery(pluginData.containerIDTag).find(".pinwheel_right_slide .carousel-caption").css({"display":"none"});
				/* Added for minimizing text on load - start */				
				if(options.minimize==false){
				jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").stop(true,true).slideUp(0);
				jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").slideDown(400);
				jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption .texthide").css("display","block"); 
				}
				else{
				jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").css("display","none");
				jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .textshow").css("display","block");
				}
				/* Added for minimizing text on load - end */	
	/* Added for show and hide of caption - start */	
	jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption .texthide").bind('click', function(){
		jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").slideUp("slow");  
		jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .textshow").css("display","block");
	});
	jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .textshow").bind('click', function(){
		jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").stop(true,true).slideUp(0);
		jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").slideDown(400);
		jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption .texthide").css("display","block"); 
		$(this).hide();
	});
	jQuery(pluginData.containerIDTag).find(".pinwheel_right_slide .textshow").css({"display":"none"});
	jQuery(pluginData.containerIDTag).find(".pinwheel_left_slide .textshow").css({"display":"none"});
	/* Added for show and hide of caption - end */
			}
				//Reset & initate the autoPlay
				
			  //added by prashant
			  //Changes for animation
			  data.currentCenterItem.find(".pinwheel_slider_thumbnail").addClass("pinwheel-animated");
			  data.currentCenterItem.find(".pinwheel_slider_thumbnail").addClass("pinwheel-"+anim);
			  
			  data.currentCenterItem.find(".pinwheel_text span").addClass("pinwheel-animated");
			  data.currentCenterItem.find(".pinwheel_text span").addClass("pinwheel-"+contentanim);
			  
			  data.currentCenterItem.find(".slider_htitle").addClass("pinwheel-animated");
			  data.currentCenterItem.find(".slider_htitle").addClass("pinwheel-"+titleanim);
				
				autoPlay();
			}//else
		}//if
	}

	/**
	* Function called to rotate the carousel the given number of rotations
	* in the given direciton. Will check to make sure the carousel should
	* be able to move, and then adjust speed and move items
	*/
	function rotateCarousel(rotations) { 
		//Hide carousel-caption while rotating	
		jQuery(pluginData.containerIDTag).find(".pinwheel_center_slide .carousel-caption").css({"display":"none"});
		
		// Check to see that a rotation is allowed
		if (data.currentlyMoving === false) {
			// Remove active class from the center item while we rotate
			data.currentCenterItem.removeClass(options.activeClassName);
			data.currentlyMoving = true;
			data.itemsAnimating = 0;
			data.carouselRotationsLeft += rotations;
			if (options.quickerForFurther === true) {
				// Figure out how fast the carousel should rotate
				if (rotations > 1) {

				}
				// Assure the speed is above the minimum to avoid weird results
				data.currentSpeed = (data.currentSpeed < 100) ? 100 : data.currentSpeed;
			}
			// Iterate thru each item and move it
			for (var i = 0; i < data.totalItems; i++) {
				var $item = $(data.items[i]);
				var currentPosition = $item.data('currentPosition');
				var newPosition;
				if (data.currentDirection == 'forward') {
					newPosition = currentPosition - 1;
				} 
				else {
					newPosition = currentPosition + 1;
				}
				// We keep both sides as even as possible to allow circular rotation to work.
				// We will "wrap" the item arround to the other side by negating its current position
				var flankingAllowance = (newPosition > 0) ? data.rightItemsCount : data.leftItemsCount;
				if (Math.abs(newPosition) > flankingAllowance) {
					newPosition = currentPosition * -1;
					// If there's an uneven number of "flanking" items, we need to compenstate for that
					// when we have an item switch sides. The right side will always have 1 more in that case
					if (data.totalItems % 2 == 0) {
						newPosition += 1;
					} 
				}
				moveItem($item, newPosition);
			}
		} 
	}

	/**
	* The event handler when an image within the carousel is clicked
	* This function will rotate the carousel the correct number of rotations
	* to get the clicked item to the center, or will fire the custom event
	* the user passed in if the center item is clicked
	*/
	$(this).find('.pinwheel_slideri').bind("click", function () {
		var itemPosition = $(this).data().currentPosition;
		if (options.imageNav == false) {
			return;
		}
		// Don't allow hidden items to be clicked
		if (Math.abs(itemPosition) >= options.flankingItems + 1) {
			return;
		}
		// Do nothing if the carousel is already moving
		if (data.currentlyMoving) {
			return;
		}
		data.previousCenterItem = data.currentCenterItem;
		// Remove autoplay
		autoPlay(true);
		options.autoPlay = 0;
		var rotations = Math.abs(itemPosition);
		if (itemPosition == 0) {
			options.clickedCenter($(this));
		}
		else{
			// Fire the 'moving' callbacks
			options.movingFromCenter(data.currentCenterItem);
			options.movingToCenter($(this));
			if (itemPosition < 0) {
				data.currentDirection = 'backward';
				rotateCarousel(rotations);
			}
			else if (itemPosition > 0) {
				data.currentDirection = 'forward';
				rotateCarousel(rotations);
			}
		}
	});


	/**
	* The user may choose to wrap the images is link tags. If they do this, we need to
	* make sure that they aren't active for certain situations
	*/
	$(this).find('img.pinwheel_slider_thumbnail').parent('a').bind("click", function (event) { 	
		var isCenter = $(this).parent('.pinwheel_slideri').data().currentPosition == 0;
		// should we disable the links?
		if (options.linkHandling === 1 || // turn off all links
		(options.linkHandling === 2 && !isCenter)) // turn off all links except center
		{ 	
			if (event.preventDefault) { event.preventDefault(); } else { event.returnValue = false; }
		}
	});

	function nextItemFromCenter() {
		var $next = data.currentCenterItem.next();
		if ($next.length <= 0) {
			$next = data.currentCenterItem.parent().children().first();
		}
		return $next;
	}

	function prevItemFromCenter() {
		var $prev = data.currentCenterItem.prev();
		if ($prev.length <= 0) {
			$prev = data.currentCenterItem.parent().children().last();
		}
		return $prev;
	}

	/**
	* Intiate a move of the carousel in either direction. Takes care of firing
	* the 'moving' callbacks
	*/
	function moveOnce(direction) {
		if (data.currentlyMoving === false) {
			data.previousCenterItem = data.currentCenterItem;
			options.movingFromCenter(data.currentCenterItem);
			if (direction == 'backward') {
				options.movingToCenter(prevItemFromCenter());
				data.currentDirection = 'backward';
			} 
			else if (direction == 'forward') {
				options.movingToCenter(nextItemFromCenter());
				data.currentDirection = 'forward';
			}
		}
		rotateCarousel(1);
	}
    
	/**
	* Navigation with arrow keys
	*/
	$(document).keydown(function(e) {
		if (options.keyboardNav) {
		// arrow left or up
			if ((e.which === 37 && options.orientation == 'horizontal') || (e.which === 38 && options.orientation == 'vertical')) {
				autoPlay(true);
				options.autoPlay = 0;
				moveOnce('backward');
				// arrow right or down
			} 
			else if ((e.which === 39 && options.orientation == 'horizontal') || (e.which === 40 && options.orientation == 'vertical')) {
				autoPlay(true);
				options.autoPlay = 0;
				moveOnce('forward');
			}
			// should we override the normal functionality for the arrow keys?
			if (options.keyboardNavOverride && (
			(options.orientation == 'horizontal' && (e.which === 37 || e.which === 39)) ||
			(options.orientation == 'vertical' && (e.which === 38 || e.which === 40))
			)) {
				e.preventDefault();
				return false;
			}
		}
	});
	/**
	* Public API methods
	*/
	var pinwheelWindowWidth = jQuery(window).width();
	this.reload = function (newOptions) { 
		if( (currEvent == 'resize' && (jQuery(window).width() != pinwheelWindowWidth) ) || currEvent == undefined ) {
			pinwheelWindowWidth = jQuery(window).width();
			/*added to hide carousel-caption while resize*/
			jQuery(pluginData.containerIDTag).find(".carousel-feature .carousel-caption").css({"display":"none"});
			if (typeof newOptions === "object") {
				var combineDefaultWith = newOptions;
			} 
			else {
				var combineDefaultWith = {};
			}
			options = $.extend({}, $.fn.pinwheelAlt.defaults, newOptions);
			if((options.sliderMaxWidth/2) > jQuery(pluginData.containerIDTag).width()) {
				options.flankingItems=1;
				options.separation=300;
			}
			initializeCarouselData();
			data.itemsContainer.find('.pinwheel_slideri').hide();
			forceImageDimensionsIfEnabled();
			preload(function () {
				setOriginalItemDimensions();
				preCalculatePositionProperties();
				setupCarousel();
				setupTrackers();        
				setupStarterRotation();
				if(options.timthumb=='2') {	
					jQuery("#"+options.slider_handle).find(".focuscrop").focusPoint();
				}
			});
		} 
	}
    
	this.next = function() {
		autoPlay(true);
		options.autoPlay = 0;
		moveOnce('forward');
	}
	this.prev = function () {
		autoPlay(true);
		options.autoPlay = 0;
		moveOnce('backward');
	}
	this.reload(startingOptions);
	return this;
};

$.fn.pinwheelAlt.defaults = {
	// number tweeks to change apperance
	startingItem:               1,		// item to place in the center of the carousel. Set to 0 for auto
	separation:                 150, 	// distance between items in carousel
	separationMultiplier:       0.75, 	// multipled by separation distance to increase/decrease distance for each additional item
	horizonOffset:              0,   	// offset each item from the "horizon" by this amount (causes arching)
	horizonOffsetMultiplier:    0,   	// multipled by horizon offset to increase/decrease offset for each additional item
	sizeMultiplier:             0.75, 	// determines how drastically the size of each item changes
	opacityMultiplier:          1, 		// determines how drastically the opacity of each item changes
	horizon:                    180,   	// how "far in" the horizontal/vertical horizon should be set from the container wall. 0 for auto
	flankingItems:              2,   	// the number of items visible on either side of the center                  
	startingFeature:	    1,
	// animation
	speed:                      600,      	// speed in milliseconds it will take to rotate from one to the next
	animationEasing:            'swing', 	// the easing effect to use when animating
	quickerForFurther:          true,     	// set to true to make animations faster when clicking an item that is far away from the center
	edgeFadeEnabled:            false,    	// when true, items fade off into nothingness when reaching the edge. false to have them move behind the center image
	trackerIndividual:    	    true,
	trackerSummation:     	    true,
	// misc
	linkHandling:               2,          // 1 to disable all (used for facebox), 2 to disable all but center (to link images out)
	autoPlay:                   0,       // indicate the speed in milliseconds to wait before autorotating. 0 to turn off. Can be negative
	orientation:               'horizontal',// indicate if the carousel should be 'horizontal' or 'vertical'
	activeClassName:           'pinwheel_center_slide', // the name of the class given to the current item in the center
	leftClassName:		   'pinwheel_left_slide pinwheel_feature_slide',
	rightClassName:		   'pinwheel_right_slide pinwheel_feature_slide',
	hiddenClassName:	   'pinwheel_hidden_slide',
	keyboardNav:                false,     // set to true to move the carousel with the arrow keys
	keyboardNavOverride:        true,      // set to true to override the normal functionality of the arrow keys (prevents scrolling)
	imageNav:                   true,      // clicking a non-center image will rotate that image to the center
	// preloader
	preloadImages:              true,  	// disable/enable the image preloader. 
	forcedImageWidth:           450,     	// specify width of all images; otherwise the carousel tries to calculate it
	forcedImageHeight:          300,     	// specify height of all images; otherwise the carousel tries to calculate it
	// callback functions
	leavingCenter:              $.noop,
	movingToCenter:             $.noop, 	// fired when an item is about to move to the center position
	movedToCenter:              $.noop, 	// fired when an item has finished moving to the center
	clickedCenter:              $.noop, 	// fired when the center item has been clicked
	movingFromCenter:           $.noop, 	// fired when an item is about to leave the center position
	movedFromCenter:            $.noop, 	// fired when an item has finished moving from the center
	captionBelow:               true,
	sliderMaxWidth: 	    900,
	sliderMaxHeight:	    350,
	minimize:		    0	
};
})(jQuery);
