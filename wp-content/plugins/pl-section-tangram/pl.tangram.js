!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */

  $( '.pl-sn-tangram' ).on('template_ready', function(){

    $.plTangram.init( $(this) )

  })

  $.plTangram = {

    init: function( section ){

      var that      = this

      /**
       * Use a wrapper to look for and clone elements with the .pl-render-item class
       * This prevents the binding code from getting confused by the slider code
       */
      var rendered    = plRenderItem( section ),
          items       = ( parseInt(rendered.attr('data-max')) )    ? parseInt(rendered.attr('data-max')) :  2,
          speed       = ( parseInt(rendered.attr('data-speed')) )  ? parseInt(rendered.attr('data-speed')) :  2000, 
          autoplay    = ( 1 !== rendered.attr('data-max') )         ? true : false
  


        /** Wait for all images to load */
      rendered.not('.loaded').slick({
        infinite:         true,
        slidesToShow:     items,
        slidesToScroll:   1,
        autoplay:         autoplay,
        autoplaySpeed:    speed,
        nextArrow:        '<span class="clicknav next-arrow"><i class="icon icon-angle-right"></i></span>', 
        prevArrow:        '<span class="clicknav prev-arrow"><i class="icon icon-angle-left"></i></span>', 
        responsive: [
          {
            breakpoint: 650,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2
            }
          },
          {
            breakpoint: 500,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          },
        ]
      
      }).addClass('loaded')
      
    }
  }
  
}(window.jQuery);