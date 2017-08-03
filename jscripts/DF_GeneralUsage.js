// accepts dataFromForm and url and sends request to the server
// returns the response from the server
function DF_send_data_to_server(dataFromForm, url, return_data){
    // Create our XMLHttpRequest object
    var httpRequest = DF_get_httpRequest(url);
    // Access the onreadystatechange event for the XMLHttpRequest object
    httpRequest.onreadystatechange = function() {
	    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
	    	 return_data.value = httpRequest.responseText;
	    	 //return;
	    }
    }
    // Send the data to PHP now... and wait for response to update the status div
    httpRequest.send(dataFromForm); // Actually execute the request
}

function returnObject()
{
	this.value = "";
}

function DF_get_httpRequest(url)
{
    // Create our XMLHttpRequest object
    var httpRequest = new XMLHttpRequest();
    httpRequest.open("POST", url, true);
    // Set content type header information for sending url encoded variables in the request
    httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    return httpRequest;
}