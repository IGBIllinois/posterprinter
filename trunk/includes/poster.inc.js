function validateStep1() {
	var frm = document.forms["posterInfo"];
	var width = frm.posterWidth.value;
	var length = frm.posterLength.value;
	var maxPrinterWidth = frm.maxPrinterWidth.value;
	return validateDimensions(width,length,maxPrinterWidth);

}

function validateStep2() {
	var frm = document.forms["posterInfo"];
	var cfop1 = frm.cfop1.value;
	var cfop2 = frm.cfop2.value;
	var cfop3 = frm.cfop3.value;
	var cfop4 = frm.cfop4.value;
	var cfop = cfop1 + "-" + cfop2 + "-" + cfop3 + "-" + cfop4;
	var activityCode = frm.activityCode.value;
	var posterFile = frm.posterFile.value;
	var email = frm.email.value;
	var name = frm.name.value;
	var valid = true;
	
	if (validateCFOP(cfop) == false) 
		valid = false;
	if (validateActivityCode(activityCode) == false)
		valid = false;
	if (validatePaperTypes() == false)
		valid = false;
	if (validateFinishOptions() == false)
		valid = false;
	if (validatePosterFile(posterFile) == false)
		valid = false;
	if (validateEmail(email) == false)
		valid = false;
	if (validateName(name) == false)
		valid = false;
	return valid;
}

function validateDimensions(width,length,maxPrinterWidth) {
	var widthValid;
	var lengthValid;
	
	if ((width == "") || (width % 1 != 0) || (width <= 0)){
		document.getElementById('widthWarning').innerHTML = "Please enter a valid poster width.";
		widthValid = false;
	}
	else if ((width > maxPrinterWidth) && (length > maxPrinterWidth)) {
		document.getElementById('widthWarning').innerHTML = "Width can't be greater than " + maxPrinterWidth + " inches";
		widthValid = false;
	}
	else {
		document.getElementById('widthWarning').innerHTML = "&nbsp";
		widthValid = true;
	}
	
	if ((length == "") || (length % 1 != 0) || (length <=0)){
		document.getElementById('lengthWarning').innerHTML = "Please enter a valid poster length.";
		lengthValid = false;
	}
	else if (length > 200) {
		document.getElementById('lengthWarning').innerHTML = "Length can't be greater than 200 inches.";
		lengthValid = false;
	}
	else {
		document.getElementById('lengthWarning').innerHTML = "&nbsp";
		lengthValid = true;
	}
	
	if ((widthValid == true) && (lengthValid == true)) {
		return true;
	}
	else {
		return false;
	}
}

function validateCFOP(cfop) {
	var cfopRegex = /^1-\d{6}-\d{6}-\d{6}$/;
	if (cfop == "") {
		document.getElementById('cfopWarning').innerHTML = "Please enter a valid CFOP number.";
		return false;
	}
	else if (!cfop.match(cfopRegex)) {
		document.getElementById('cfopWarning').innerHTML = "Please enter a valid CFOP number.";
		return false;
	}
	else {
		document.getElementById('cfopWarning').innerHTML = "&nbsp";
		return true;
	}
}

function validateActivityCode(activityCode) {
	var activityCodeRegex = /^[a-zA-Z0-9]{6}/;
	if ((activityCode != "") && (!activityCode.match(activityCodeRegex))) {
		document.getElementById('activityCodeWarning').innerHTML = "Please enter a valid activity code.";
		return false;
	}
	else {
		document.getElementById('activityCodeWarning').innerHTML = "&nbsp";
		return true;
	}
	
	
}
function validatePaperTypes() {
	var frm = document.forms["posterInfo"];
	var length;
	if (IsNumeric(frm.paperTypesId.length)) {
		length = frm.paperTypesId.length
	        for (i=0;i <= length; i++) {
        	        if (frm.paperTypesId[i].checked) {
                	        document.getElementById('paperTypesWarning').innerHTML = "&nbsp";
                        	return true;
	                }
        	}
	}

	else {
		if (frm.paperTypesId.checked) {
			document.getElementById('paperTypesWarning').innerHTML = "&nbsp";
			return true;
		}
	}
	document.getElementById('paperTypesWarning').innerHTML = "Please select a Paper Type.";
	return false;
	
}

function validateFinishOptions() {
	var frm = document.forms["posterInfo"];
	var length;
	if (IsNumeric(frm.finishOptionsId.length)) {
		length = frm.finishOptionsId.length
		for (i=0;i <= length; i++) {
        	        if (frm.finishOptionsId[i].checked) {
                	        document.getElementById('finishOptionsWarning').innerHTML = "&nbsp";
                        	return true;
                	}
        	}
	}
	else {
		if (frm.finishOptionsId.checked) {
			document.getElementById('finishOptionsWarning').innerHTML = "&nbsp";
			return true;
		}
	}
	document.getElementById('finishOptionsWarning').innerHTML = "Please select a Finish Option.";
	return false;
	
}

function validatePosterFile(posterFile) {
	if (posterFile.length == 0){
		document.getElementById('posterFileWarning').innerHTML = "Please select a poster file.";
		return false;
	}
	else {
		document.getElementById('posterFileWarning').innerHTML = "&nbsp";
		return true;
	}
}

function validateEmail(email) {
	var emailRegex = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/;
	if (!email.match(emailRegex)) {
		document.getElementById('emailWarning').innerHTML = "Please enter your email.";
		return false;
	}
	else {
		document.getElementById('emailWarning').innerHTML = "&nbsp";
		return true;
	}
}

function validateName(name) {
	if (name != "" && name != " ") {
		document.getElementById('nameWarning').innerHTML = "&nbsp";
		return true;
	}
	else {
		document.getElementById('nameWarning').innerHTML = "Please enter your full name.";
		return false;
	}

}
function IsNumeric(PossibleNumber) {	
	var PNum = new String(PossibleNumber);
	var regex = /[^0-9]/;
	return !regex.test(PNum);
}

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
