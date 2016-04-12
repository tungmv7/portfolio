!function ($) {


  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-meganav' ).on('template_ready', function(){

    $.plMegaNav.init( $(this) )

    $.plMegaMobile.init( $(this) )

  })

  $.plMegaNav = {

    init: function( section ){
      
      var that  = this

      var megas = section.find('.meganav-nav-wrap').attr('data-megas').split(',')

      var droplefts = section.find('.meganav-nav-wrap').attr('data-dropleft').split(',')

      var menu  = section.find('.sf-menu')
      
      /*
       * If there is a container in the header, make sure it has higher z-index
       */
      var nav = $('header .pl-sn-container').css('z-index', 4)

      /** Sticky Menu option */
      if( $('.meganav-wrap').hasClass('do-sticky')){

        section.not('.sticky-loaded').stick_in_parent({parent: 'body'}).addClass('sticky-loaded')

      }
      else {
        section.trigger("sticky_kit:detach").removeClass('sticky-loaded')
      }

      /** Drop downs */
      menu.not('.loaded').superfish({
         delay: 800,
         speed: 'fast',
         speedOut: 'fast',             
         animation:   {opacity:'show'}
      }).addClass('loaded')


      menu.children('li').each(function( index ){

        var number = index + 1

        /** MegaMenus */
        if( $.inArray( number.toString(), megas ) >= 0 ){

          var cols = $(this).find('> .sub-menu > li').length

          $(this).addClass( 'megamenu mega-span-' + cols )

        }
        else{
          $(this).removeClass('megamenu')
        }

        /** Drop Downs to the left */
        if( $.inArray( number.toString(), droplefts ) >= 0 ){
          $(this).addClass( 'dropleft' )
        }
        else{
          $(this).removeClass('dropleft')
        }

      })

    
    }
  }



  $.plMegaMobile = {
    
    init: function( section ){
      var that = this
      
      that.setup( section  )

    },

    setup: function( section ){

      var that        = this,
        menuToggle    = section.find('.mm-toggle'),
        siteWrap      = $('body'),
        mobileMenu    = $('.pl-meganav-mobile')
        

      
      section.delegate('.mm-toggle', 'click', function(e){

        /** Because a body click will remove, without this it opens AND closes */
        e.stopPropagation()

        console.log(e)

        that.loadMobileMenu( $(this) )

        
      })

    },  

    loadMobileMenu: function( btn ){

      var that      = this,
          siteWrap    = $('body'),
          mobileMenu  = $('.pl-meganav-mobile'), 
          btn         = btn || false 


      if( btn ){

        var selector    = btn.data( 'selector' ), 
          section     = btn.closest('.pl-sn')

        var selector = selector || ''

        if( selector != '' ){

          var el    = section.find( selector ).first().clone()

          el
            .attr('class', 'mobile-menu')
            .attr('data-bind', '')
            .data('bind', '')
            .find('*[style]').attr('style', '')
            .end()
            .find('.sub-indicator').remove()

        //  var wrapper = sprintf('<div class="pl-meganav-mobile mm-hidden"><div class="mm-holder"><div class="mm-menus">%s</div></div></div>', el)

          $('.mm-menus').html(el)

        }   
        
      }

        

      $('.mm-menus').find('.sub-menu').each( function(){

        var subContainer  = $(this).parent(), 
          theLink     = subContainer.children('a')

          theLink.append('<span class="sub-toggle"><i class="pl-icon pl-icon-angle-right show-closed"></i><i class="pl-icon pl-icon-angle-down show-open"></i></span>')

      })


      mobileMenu.removeClass('mm-hidden')

      if( ! siteWrap.hasClass('show-mobile-menu') ){
        
        siteWrap.addClass('show-mobile-menu')
        mobileMenu.addClass('show-menu')
        
        
        $('.pl-meganav-mobile').on('click', function(e){ e.stopPropagation() })

        $('body, .mm-close').on('click', function(e){

          if( $(e.target).closest('.mm-toggle').length == 0 ){

            siteWrap.removeClass('show-mobile-menu')
            mobileMenu
              .removeClass('show-menu')
              .addClass('mm-hidden')

            $( this ).off( e );

          }
          
        })


      } 

      else {
        siteWrap.removeClass('show-mobile-menu')
        mobileMenu.removeClass('show-menu')         
      }   

      plAddNewLinks()



      $('.sub-toggle').on('click', function(e){

        e.stopPropagation()

        var container = $(this).parent().parent()

        if( ! container.hasClass('menu-show') )
          container.addClass('menu-show')
        else
          container.removeClass('menu-show')

        return false;
      })

    }
  }
  



}(window.jQuery);
