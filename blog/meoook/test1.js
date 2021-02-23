function testPass() {
	var params = {
	  "uname" : document.getElementById("uname").value,
	  "passwd" : document.getElementById("passwd").value
	};
	AjaxPost("dodo1.php", params);
}

function createAjaxRequestObject() {
	var xmlhttp;

	if(window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else { // code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	// Create the object
	return xmlhttp;
}

function AjaxPost(ajaxURL, parameters) {
	var http3 = createAjaxRequestObject();

	http3.onreadystatechange = function() {
		if (http3.readyState == 4 && http3.status == 200) {
			if (http3.responseText == 'ok') {
				document.getElementById("message").setAttribute("class", "red");
			}
			else {
				document.getElementById("message").innerHTML = http3.responseText;
			}
	//		document.getElementsByTagName("INPUT")[0].setAttribute("class", "red"); 
	//		document.getElementsByTagName("INPUT")[1].setAttribute("class", "red"); 
		}
	};
	// Create parameter string
	var parameterString = "";
	var isFirst = true;
	for(var index in parameters) {
		if(!isFirst) {
			parameterString += "&";
		}
		parameterString += encodeURIComponent(index) + "=" + encodeURIComponent(parameters[index]);
		isFirst = false;
	}
	// Make request
	http3.open("POST", ajaxURL, true);
	http3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http3.send(parameterString);
}