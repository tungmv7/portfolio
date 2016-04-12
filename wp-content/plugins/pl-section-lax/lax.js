!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-lax' ).on('template_ready', function(){

    $.plLaxSection.init( $(this) )

  })


  /** A JS object to encapsulate functions related to the section */
  $.plLaxSection = {

    init: function( section ){

      var that  = this, 
          clone = section.data('clone')
          el    = section.find('.pl-lax-window'), 
          img   = el.attr('data-image')

      el
        .not('.loaded')
        .parallax({imageSrc: img})
        .addClass('loaded')       

      $( sprintf('[data-for="%s"]', clone )).find('.parallax-slider').attr('src', img) 

      /** Adding section doesn't work correctly without this.. i suspect it is a image loading issue. */
      /** Try imagesLoaded or something to test that.. but workin for now. */
      setTimeout(function(){
        $(window).trigger('resize').trigger('scroll');
      }, 100)
      
      
    }
  }
  
/** end of jQuery wrapper */
}(window.jQuery);