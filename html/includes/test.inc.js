//Callback handler for form submit event
$("#posterInfo").submit(function(e)
{
 
    var formObj = $(this);
    var formURL = formObj.attr("action");
    var formData = new FormData(this);
    $.ajax({
        url: formURL,
    type: 'POST',
        data:  formData,
    mimeType:"multipart/form-data",
    contentType: false,
        cache: false,
        processData:false,
    success: function(data, textStatus, jqXHR)
    {
	alert("success"); 
    },
     error: function(jqXHR, textStatus, errorThrown) 
     {
	alert("error");
     }          
    });
    e.preventDefault(); //Prevent Default action. 
    e.unbind();
}); 
$("#posterInfo").submit(); //Submit the form
