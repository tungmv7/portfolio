!function ($) {

  /** Set up initial load and load on option updates (.pl-trigger will fire this) */
  $( '.pl-sn-pins' ).on('template_ready', function(){

    $.plPins.init( $(this) )

  })

  $.plPins = {

    init: function( section ){


      var that    = this, 
          cloneID = section.data('clone')


      $(window).on('resize', function(){
        that.setMasonry( section )
      })

      that.setMasonry( section )

      section.find('.postpin-list').each( function(){

        var theList   = $(this),
            theListID = theList.parent().data('id'),
            colWidth  = theList.data('pin-width'),
            gtrWidth  = theList.data('gutter-width'),
            loadStyle = theList.data('loading'),
            pinsUrl   = theList.data('url')

        if( loadStyle == 'infinite' ){

          

          theList.infinitescroll({
            navSelector :   '.iscroll',
            nextSelector :  '.iscroll a',
            itemSelector :  '.postpin-list .isotope-item',
            loadingText :   '<i class="pl-icon pl-icon-refresh pl-icon-spin"></i> Loading',
            loadingImg :    pinsUrl + '/load.gif',
            donetext :      'No more pages to load.',
            debug :         false,
            loading: {
              finishedMsg: 'No more pages to load.'
            }

          }, function( arrayOfNewElems ) {

            theList.imagesLoaded(function(){


              theList
                .isotope('appended', $( arrayOfNewElems ) )


              $(window).trigger('resize')
            })

          })

        } 

        else {

          var theLoadLink = theList.parent().find('.fetchpins a')

          theLoadLink.on('click', function(e) {

            e.preventDefault();

            theLoadLink
              .addClass('loading')
              .html('<i class="pl-icon pl-icon-refresh pl-icon-spin spin-fast"></i> &nbsp;  Loading...');

            $.ajax({
              type:     "GET",
              url:      theLoadLink.attr('href') + '#pinboard',
              dataType: "html",
              success: function(out) {

                var newContainer = $( out ).find( sprintf('[data-clone="%s"]', cloneID ) ),
                    result = newContainer.find( '.isotope-item' ),
                    nextlink = newContainer.find( '.fetchpins a' ).attr('href')



                theList.append( result )

                theList.imagesLoaded(function(){

                  theList
                    .isotope('appended', result)

                });

                theLoadLink
                  .removeClass('loading')
                  .text('Load More Posts');



                if (nextlink != undefined)
                  theLoadLink.attr('href', nextlink);
                else
                  theLoadLink.parent().remove();

              }
            });
          });
        }

      })


      
    }, 

    setMasonry: function( section ){

        var that = this,
            theList = section.find('.postpin-list')



        

      //var galWidth      = theList.width()

      theList.find('.isotope-item').addClass('pl-col-sm-4 pl-col-xs-12')



      //if ( galWidth < 1200 ){
      //
      //  theList.find('.isotope-item').removeClass('pl-col-lg-3')
      //}
      //
      //if ( galWidth < 768 ){
      //
      //  theList.find('.isotope-item').removeClass('pl-col-sm-4')
      //}
      //
      //if ( galWidth < 480 ){
      //  theList.find('.isotope-item').removeClass('pl-col-ss-6')
      //}

      theList.imagesLoaded(function(){


          theList.isotope({
            resizable:      false,
            itemSelector :  '.isotope-item',
            filter:         '*',
            layoutMode:     'masonry'
          })
            .isotope( )
            .addClass('done-loading')

       


        })

    }

  }



}(window.jQuery);