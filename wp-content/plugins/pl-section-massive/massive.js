!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-massive' ).on('template_ready', function(){

    $.plMassiveSection.init( $(this) )

  })


  /** A JS object to encapsulate functions related to the section */
  $.plMassiveSection = {

    init: function( section ){

      var that  = this

      that.doMassive( section )

      section.find('.massive-text').on('edited', function(){

        that.doMassive( section )
        
      })

      $(window).on('redraw', function(){
        that.doMassive( section )
      })

      $(window).on('resize', function(){
        that.doMassive( section )
      })

      
    }, 

    doMassive: function( section ){

      section
        .find('.massive-text')
        .slabText()

    }
  }
  
/** end of jQuery wrapper */
}(window.jQuery);