// PageLines Tools Initializer

!function ($) {
   



  $.plCode = {

    /**
     * Gets the list of sections available of a certain type.
     * @param  {string} type    type of area, full width area or content
     * @param  {object} section the section where the 'add' as clicked
     * @return {string} html output for placement in panel
     */
    init: function( btn ){

      var that  = this,
        config = {
          name:     'Custom Styling', 
          panels:   that.thePanels(), 
          key:      'templates',
          call: function(){
            that.bindListActions()
          }
        }


      $.plEditing.sidebarEngine( config )

      

    }, 

    bindListActions: function( btn ){

      var that    = this, 
        codemirrors = {}


      $('.pl-code-editor').each( function(i){
        
        var container     = $(this).parent(),
          lessEl      = $i('#pl-custom-less'),
          cm_mode     = $(this).data('mode'),
          cm_config     = $.extend( {}, cm_base_config, { mode : cm_mode } )
          
        
        codemirrors[ 'item' + i ] = CodeMirror.fromTextArea($(this).get(0), cm_config)
      
        $(this).parent().addClass('is-ready')



        codemirrors[ 'item' + i ].on("change", function(cm, change) { 
          
          
          $.plEditing.setNeedsSave()

          lessEl.text(cm.doc.getValue())

          that.parseInput( container, lessEl.text() )

          $pl().extraData.styles = { less: $i('#pl-custom-less').text(), css: $i('#pl-custom-css').text() }

          
        })

        that.parseInput( container, lessEl.text() )
        
      })

    }, 

    parseInput: function( container, text ){

      less.render( text, function (e, output) {

        if( e ){
          
          container.find('.less-errors').html('<strong>LESS Error:</strong><br/>' + e.message).addClass('has-error')
        } 
        else {
          container.find('.less-errors').html('<strong>No LESS Errors</strong>').removeClass('has-error')

          $i('#pl-custom-css').text( output.css )

          container.find('.custom-css-render').val( output.css )
        }
      
      }); 

    }, 
  
    thePanels: function(){

      var that  = this,
        panels = {
          builder:  {
            title:    'Custom Styles', 
          //  format:   'full', 
            opts:   [
              {
                type:     'custom_code',
                callback:   that
              }
            ]
          },

        }

      return panels

    },

    opt_type_custom_code: function(){

      var that  = this, 
        value   = $i('#pl-custom-less').text(), 
        inputs  = ''


      inputs += '<label>Enter Custom LESS/CSS Here</label>'
      
      inputs += sprintf( '<textarea class="pl-form-control custom-less pl-code-editor" data-mode="less" placeholder="#site{ // Rules Here }">%s</textarea>', value )
      inputs += '<div class="less-errors">No Errors</div>'
    
      return sprintf('<div class="form-group">%s</div>', inputs)
      
    }, 

  }


}(window.jQuery);