!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-boxes' ).on('template_ready', function(){

    $.plBoxes.init( $(this) )

  })

  $.plBoxes = {

    init: function( section ){


      section.find('.pl-counter:not(.counted):visible').each(function(){
              
        var cntr = $(this)

        cntr.appear( function() {

          var the_number = parseInt( cntr.text().replace(/\D/g,'') )
        
          cntr.countTo({
              from: 0
            , to: the_number
            , speed: 2000
            , refreshInterval: 30
            ,   formatter: function( value, options){ // jshint ignore:line
              
              value = Math.round( value )
              var n =  value.toString()
              
              n = n.replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            
              return n
            }
          }).addClass('counted')
        
        })
        
      })

    }

  }



}(window.jQuery);