!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-starter' ).on('template_ready', function(){

    $.plStarterSection.init( $(this) )

  })

  /** A JS object to encapsulate functions related to the section */
  $.plStarterSection = {

    init: function( section ){

      var that       = this

      /**
       * plRenderItem()
       * Use a wrapper to look for and clone elements with the .pl-render-item class
       * This prevents the binding code from getting confused by the slider code
       *
       * (If it finds one already there, remove it and create a new one)
       */
      var rendered   = plRenderItem( section )

      /** Apply the JS to the  */
      rendered
        .not('.loaded')
        .addClass('do-something-here')  // Here is where you'd apply any scripts
        .addClass('loaded')             // Use a loaded class to prevent things triggering multiple times ()
      
    }
  }
  
/** end of jQuery wrapper */
}(window.jQuery);