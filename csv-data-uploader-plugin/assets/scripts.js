jQuery(document).ready(function() {
jQuery("#csv-uploader-form").on("submit",function(event) 
{
event.preventDefault();
var formData=new FormData();
jQuery.ajax(
  {
   url:csv_data_uploader_ajax_object.ajax_url,
    data:formData, 
   dataType: 'json',
   method: 'POST',
   proccessData: false,
   contentType:false,
   success: function(response) {
    if(response.success){
     alert(response.message);
    }else {
     alert(response.message);
    }
   }
  });
 });
});