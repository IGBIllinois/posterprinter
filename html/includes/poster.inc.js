function cfopAdvance1() {
	var length = document.forms["posterInfo"].cfop1.value.length;
	if (length == 1) {
		document.forms["posterInfo"].cfop2.focus()
	}	
}

function cfopAdvance2() {
	var length = document.forms["posterInfo"].cfop2.value.length;
	if (length >= 6) {
		document.forms["posterInfo"].cfop3.focus()
	}
	
}
function cfopAdvance3() {
	var length = document.forms["posterInfo"].cfop3.value.length;
	if (length >= 6) {
		document.forms["posterInfo"].cfop4.focus()
	}
	
}
function update_posterfile_name() {
	var fileInput = document.getElementById('posterFile');
	var filename = fileInput.files[0].name;
	$('#posterfile-label').text(filename);
}



function uploadComplete(evt) {
        /* This event is raised when the server send back a response */
        //alert(evt.target.responseText);
      }

function uploadFailed(evt) {
        alert("There was an error attempting to upload the file.");
      }

function uploadCanceled(evt) {
        alert("The upload has been canceled by the user or the browser dropped the connection.");
      }

function disableForm() {
                document.getElementById('poster_field').disabled = true;
}
function enableForm() {
                document.getElementById('poster_field').disabled = false;

}



