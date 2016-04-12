/** Lets store all social counts in a global so we never load them twice via request */
window.plsocialcounts = {}

!function ($) {

  $( window ).on('pl_page_ready', function(){

    $.plSocial.init() 

  })

  $.plSocial = {
    
    init: function(){
      
      var that = this
      
      /** Get content for shares which is loaded in the <head> */
      that.shareTitle     = pl_shares.title
      that.shareDesc      = pl_shares.desc
      that.shareImg       = pl_shares.image
      that.shareLocation  = pl_shares.url
      

      that.startUp()


      
    
    },

    startUp: function( ){

      var that = this

      $( '.pl-social-counter' ).not('.loaded').each( function(){

        var element   = $(this), 
            social    = element.data('social')

        /** Fetch count on appearance (prevents lag and blocking requests) */
        element.appear( function() {

          that.fetchCount( social, element );
            
        })

        /** Load click events */
        element.not('.event-loaded').on( 'click', function(){

          that.openWindow( social )
        
          return false;
          
        }).addClass('event-loaded')

      })

    }, 



    openWindow: function( social ){

      var that = this, 
        url, name

      /** Sharing Urls */
      
      if( social == 'facebook' ){
        url = '//www.facebook.com/sharer/sharer.php?u='+that.shareLocation
        name = 'FacebookShare'
      }

      else if (social == 'linkedin' ){
        url = '//www.linkedin.com/shareArticle?url='+that.shareLocation+'&title='+that.shareTitle+'&summary='+that.shareDesc
        name = 'LinkedInShare'
      }

      else if (social == 'pinterest' ){
        url = '//pinterest.com/pin/create/button/?url='+that.shareLocation+'&media='+that.shareImg+'&description='+that.shareTitle
        name = 'PinterestShare'
      }
      

      else if (social == 'twitter' ){
        url = '//twitter.com/intent/tweet?text='+ that.shareTitle +' '+that.shareLocation
        name = 'TwitterShare'
      }
      
      if( social == 'google-plus' ){
        url = '//plus.google.com/share?url='+that.shareLocation
        name = 'GoogleShare'
        
      }

      var setup = "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0, left: 50px, top: 50px"
      
      window.open( url, name, setup ) 
      
    }, 



    fetchCount: function( social, btn ){
      
      var that  = this, 
        url   = ''
    
    
      /** Do we have the count in a global? */  
      if( typeof window.plsocialcounts[ social ] != 'undefined' ){

        btn
          .find('.pl-social-count')
          .html( window.plsocialcounts[ social ] )

      } 

      /** Have to make a request */
      else {

        /** Count Request URLs */
        if( social == 'facebook' )
          url = "//graph.facebook.com/?id="+ that.shareLocation +'&callback=?'

        else if (social == 'linkedin' )
          url = '//www.linkedin.com/countserv/count/share?url='+that.shareLocation+'&callback=?'

        else if (social == 'pinterest' )
          url = '//api.pinterest.com/v1/urls/count.json?url='+that.shareLocation+'&callback=?'



        /** JSON Request */
        $.getJSON( url, function( data ) {
          
          /** Get count or shares, preferring shares (?) */
          var theCount = ( (data.count !== 0) && (data.count !== undefined) && (data.count !== null) ) ? data.count : ''
          
          theCount = ( (data.shares !== 0) && (data.shares !== undefined) && (data.shares !== null) ) ? data.shares : theCount
          
          /** Set in global */
          window.plsocialcounts[ social ] = theCount

          /** Set button count */
          btn
            .find('.pl-social-count')
            .html( theCount )
          
        })

      }
      
      
    }
    
      
    
  }

}(window.jQuery);
