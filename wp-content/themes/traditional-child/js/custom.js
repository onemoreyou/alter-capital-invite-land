"use strict";

(function( $ ) {

$(document).ready(function(e) {
	
	
	

if($('.mucha_cpt_carousel').length){

$('.mucha_cpt_carousel').each(function(index, slider){

	$(slider).slick({

		autoplay:($(this).attr('slider-autoplay')=="true") ? true : false,

		arrows:($(this).attr('slider-arrows')=="true") ? true : false,

		slidesToShow: Number($(this).attr('slider-lgcount')),

		slidesToScroll:Number($(this).attr('slider-lgslide')),		

		dots:($(this).attr('slider-dots')=="true") ? true : false,

		swipeToSlide:false,

		autoplaySpeed: Number($(this).attr('slider-autoplay-speed')),

		responsive: [

		{

		  breakpoint: 1025,

		  settings: {

			slidesToShow: Number($(this).attr('slider-tblcount')),

			slidesToScroll: Number($(this).attr('slider-tblslide')),  

		  }

		},

		{

		  breakpoint: 767,

		  settings: {

			slidesToShow: Number($(this).attr('slider-moblcount')),

			slidesToScroll: Number($(this).attr('slider-moblslide')),  

		  }

		},

		{

		  breakpoint: 420,

		  settings: {

			slidesToShow: Number($(this).attr('slider-mobpcount')),

			slidesToScroll: Number($(this).attr('slider-mobpslide')), 

			  dots:false,

			  arrows:true

		  }

		}

	  ]

	});

});

}



});
})( jQuery );