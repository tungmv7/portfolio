window.plQuoteRotation = {}

!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-fly-quotes' ).on('template_ready', function(){
    
    $.plFlyQuotes.init( $(this) )

  })

  $.plFlyQuotes = {

    init: function( section ){

      var that        = this, 
          item        = section.find('.pl-fly-quotes-container'), 
          elID        = section.data('clone'),
          theNavItems = '',
          slideNum    = item.find('.the-fly-quote').length, 
          autoSpeed   = item.attr('data-speed')       || 6000,
          navMode     = item.attr('data-mode')                    || 'default'

      /** Do only on load */
      if( ! item.hasClass('loaded') ){

        that.bindUIActions()

        item.addClass('loaded')

      }

      item.find('.the-fly-quote').each( function(i){
    
        if( navMode == 'image' ){
          theNavItems += sprintf('<li class="nav-switch"><span style="background-image: url(%s);"></span></li>', pl_do_shortcode( $(this).attr('data-image') ) )
        } 
        else {
          theNavItems += '<li class="nav-switch"><span class="pl-contrast heavy"></span></li>'
        }
      })

      item
        .find('.the-nav')
        .attr( 'data-theme', navMode )
        .html( theNavItems )
        .find('li')
        .first()
        .trigger('nav-item-click')

      /** Do auto rotation stuff */
      if( autoSpeed != 0 ) {
        
        clearInterval( window.plQuoteRotation[ elID ] )

        window.plQuoteRotation[ elID ] = setInterval( function(){ that.itemRotate( item ) }, autoSpeed )        
      
      } 

      else if( window.plQuoteRotation[ elID ] != null ) {
        clearInterval( window.plQuoteRotation[ elID ] )
        window.plQuoteRotation[ elID ] = null;
      }

    },

    bindUIActions: function(){

      $('body').on('click nav-item-click', '.pl-fly-quotes-container .controls li', function( e ){

        e.stopPropagation()

        var wrapper         = $(this).closest('.pl-fly-quotes-container'),
            $index          = $(this).index(), 
            currentHeight   = wrapper.find('.the-fly-quote').eq($index).height()
        
        if( $(this).hasClass('active') ) 
          return false

        wrapper
          .find('.nav-switch')
          .removeClass('active')
          
        $(this)
          .addClass('active')

        wrapper
          .find('.current-quote')
          .stop()
          .animate({ 
              'opacity':    '0',
              'left':       '25px', 
              'z-index':    '-1' 
            }, 
            400, 
            'easeOutCubic', 
            function(){
              $(this).css( {'left':'-25px'} )
            }
          )

        wrapper
          .find('.the-fly-quote')
          .eq($index)
          .stop(true,true)
          .addClass('current-quote')
          .animate({'opacity':'1','left':'0'},600,'easeOutCubic')
          .css('z-index','20')
          
        wrapper
          .find('.pl-fly-quotes')
          .stop(true,true)
          .animate( {'min-height' : currentHeight + 20 + 'px' }, 450, 'easeOutCubic' )

      })
    }, 

    itemRotate: function( item ){

      var numItems            = item.find('li').length,
          currentItem         = item.find('.nav-switch.active').index()
      
      if( currentItem + 1 == numItems ) {
        item.find('ul li:first-child').trigger('nav-item-click')
      } 

      else {
        item.find('.nav-switch.active').next('li').trigger('nav-item-click')
      }
    }
  }
}(window.jQuery);
