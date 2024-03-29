"use strict";

(function (document, undefined) {
  
    jQuery(document).ready(function(){
        jQuery('.rps-carousel').slick({
            slidesToShow: 4,
            slidesToScroll: 3,
            dots:false,
            centerMode: false,
            infinite: false,
            responsive: [
              {
                breakpoint: 1024,
                settings: {
                  slidesToShow: 3,
                  slidesToScroll: 2,
                }
              },
              {
                breakpoint: 700,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 1
                }
              },
              {
                breakpoint: 480,
                settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1
                }
              }
            ]
        });
      });
     
    
})(document);