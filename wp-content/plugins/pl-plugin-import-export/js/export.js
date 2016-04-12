!function ($) {
  // --> Initialize
$(document).ready(function() {
  
  $.Exporter.init()

})
  
  $.Exporter = {

    init: function(){
    
    $('#pl_import').change(function(){
      $('.pl_import_feedback').hide()
    })
    
    $('.pl_import_submit').click(function(e){
      e.preventDefault();
      var feedback = $('.pl_import_feedback')
      var file = $('#pl_import');
      if( '' == file.val() ) {
        $(feedback).html('<i class="fa fa-exclamation-triangle"></i> No File Selected!').show().fadeOut(1500)
        return false
      }
      if (confirm('Are you sure you want to import?')) {
        $(feedback).html('<i class="fa fa-circle-o-notch fa-spin"></i>').show()
        var fd = new FormData();
        var individual_file = file[0].files[0];
        fd.append("file", individual_file);
        fd.append('action', 'pl_import');  
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: fd,
          contentType: false,
          processData: false,
          success: function(response){
            if( response.success === true ) {
              $(feedback).html('<i class="fa fa-check"></i> Import Complete!').show()
            }
          }
      })
      }
    })
    }
  } // export end

}(window.jQuery);
