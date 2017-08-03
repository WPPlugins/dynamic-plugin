
window.onload = function()
{
     var specialAction = df_GetUrlParameter("DF_ValidateMail");
     if(typeof(specialAction)!="undefined" && specialAction == "true")
     {
           var mailSpecialNum =    df_GetUrlParameter("DF_MailNum");
           if(typeof(mailSpecialNum)!="undefined" && mailSpecialNum != "")
             {
                   DF_ValidateMail  (mailSpecialNum);
             }
     }
     
     var specialAction = df_GetUrlParameter("DF_RemoveEmail");
     if(typeof(specialAction)!="undefined" && specialAction == "true")
     {
           var removeEmail =    df_GetUrlParameter("email");
           if(typeof(removeEmail)!="undefined" && removeEmail != "")
             {
                   DF_RemoveEmail(removeEmail);
             }
     }
     
}

 function DF_AddUpClick(MyButtonId, ResultTextId,startText,nooutputField)
 {
     var uploader = document.getElementById(MyButtonId);
     if(typeof(uploader)!="undefined")
     {
         upclick(
         {
          element: uploader,
          action: "?action=DFloadFile&fieldId="+escape(ResultTextId),
          onstart:
            function(filename)
            {
              alert(startText+filename);
            },
          oncomplete:
            function(response_data) 
            {
                 var ResultText= document.getElementById(ResultTextId);
                 if(typeof(ResultText)!="undefined")
                 {
                    
                    if(response_data.search("error") != -1)
                    {
                         alert(response_data);
                    }
                    else
                    {
                       
                        ResultText.value= response_data;
                        
                        ResultText.style.visibility = "hidden";
                        uploader.style.visibility = "hidden";
                        var fileParams = response_data.split("|");
                         var newdiv = document.createElement("div");
                                        
                         newdiv.innerHTML ="<a href='"+ fileParams[0]+"' target='_blank'>view file</a>"
                         ResultText.parentNode .insertBefore(newdiv,ResultText);
                    }
                 }
                 else
                 {
                     alert(nooutputField)
                 }
             
            }
         });
        
     }
 }
 
 
 function DF_AddImageClick(MyButtonId, ResultTextId,startText,nooutputField)
 {
     var uploader = document.getElementById(MyButtonId);
     if(typeof(uploader)!="undefined")
     {
         upclick(
         {
          element: uploader,
          accept:'image/*',
          action: "?action=DFloadFile&fieldId="+escape(ResultTextId),
          onstart:
            function(filename)
            {
              alert(startText+filename);
            },
          oncomplete:
            function(response_data) 
            {
                 var ResultText= document.getElementById(ResultTextId);
                 if(typeof(ResultText)!="undefined")
                 {
                    
                    if(response_data.search("error") != -1)
                    {
                         alert(response_data);
                    }
                    else
                    {
                       
                        ResultText.value= response_data;
                        
                        ResultText.style.visibility = "hidden";
                        uploader.style.visibility = "hidden";
                        
                        
                         
                        var fileParams = response_data.split("|");
                         var newdiv = document.createElement("div");
                        
                        var img = new Image();
                        img.src = fileParams[0];
                        img.onload = function() {
                            
                            this.width= this.width*150/  this.height;
                            this.height=150;
                            
                        }
                        newdiv.appendChild(img);            
                         
                         ResultText.parentNode.insertBefore(newdiv,ResultText);
                    }
                 }
                 else
                 {
                     alert(nooutputField)
                 }
             
            }
         });
        
     }
 }



function DF_AddRecaptcha(elementId, publicKey, theme)
{
        var element = document.getElementById(elementId);
        if(typeof(element)!="undefined" && typeof(Recaptcha)!="undefined" && typeof(Recaptcha.create)!="undefined")
        {
            Recaptcha.create(publicKey, element, {
                 theme: theme,
                 callback: Recaptcha.focus_response_field});
        }
}

function DF_ValidateRecaptcha(callbackFunction)
{
     document.body.style.cursor = 'wait';
        // Create our XMLHttpRequest object
        var httpRequest = new XMLHttpRequest();
        // Create some variables we need to send to our PHP file
        var url = DF_getUrl();
        var dataFromForm =
                    "validateRecaptcha=yes" +"&"+"challenge="+Recaptcha.get_challenge()+"&responce="+Recaptcha.get_response();
                   
        httpRequest.open("POST", url, true);
        // Set content type header information for sending url encoded variables in the request
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        // Access the onreadystatechange event for the XMLHttpRequest object
        httpRequest.onreadystatechange = function() {
            if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                var return_data = DFRemoveZero(httpRequest.responseText);
                if(return_data=="true")
                {
                  Recaptcha.destroy();
                  callbackFunction.MyFunc(callbackFunction.systemName, callbackFunction.random_number,callbackFunction.form_name,callbackFunction.DataFromAction);
                }
                else
                {
                    alert (return_data);
                  Recaptcha.reload();  
                }
            }
        }
        // Send the data to PHP now... and wait for response to update the status div
        DF_SendRequest(httpRequest,dataFromForm);  
}


function DF_SendMailToMailingList(ValidatonMessage,Alertstr)
{
    var answer = confirm(ValidatonMessage);
         if(answer) {
             
            var currID= df_GetUrlParameter("post");
            var mysys= document.getElementById('df_sys_id');
            
            if(typeof(currID)!="undefined" && typeof(mysys)!="undefined" )
            {
                              DF_SendMailToList(currID,mysys.value);
            }
            else
            {
                alert(Alertstr);
            }
         }
        
}

function DF_SendMailToList(currId,sysid) {
        document.body.style.cursor = 'wait';
        // Create our XMLHttpRequest object
        var httpRequest = new XMLHttpRequest();
        // Create some variables we need to send to our PHP file
        var url = DF_getUrl();
        var dataFromForm =
                    "sendMailToList=yes" +"&"+"currId="+currId+"&sysid="+sysid;
                   
        httpRequest.open("POST", url, true);
        // Set content type header information for sending url encoded variables in the request
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        // Access the onreadystatechange event for the XMLHttpRequest object
        httpRequest.onreadystatechange = function() {
            if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                var return_data = DFRemoveZero(httpRequest.responseText);
                alert(return_data);
                  document.body.style.cursor = 'default';
            }
        }
        // Send the data to PHP now... and wait for response to update the status div
        DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
    }
    

function DF_ValidateMail(mailNum) {
        document.body.style.cursor = 'wait';
        // Create our XMLHttpRequest object
        var httpRequest = new XMLHttpRequest();
        // Create some variables we need to send to our PHP file
        var url = DF_getUrl();
        var dataFromForm =
                    "validateMail=yes" +"&"+"mailNum="+mailNum;
                   
        httpRequest.open("POST", url, true);
        // Set content type header information for sending url encoded variables in the request
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        // Access the onreadystatechange event for the XMLHttpRequest object
        httpRequest.onreadystatechange = function() {
            if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                var return_data = DFRemoveZero(httpRequest.responseText);
                alert(return_data);
                  document.body.style.cursor = 'default';
            }
        }
        // Send the data to PHP now... and wait for response to update the status div
        DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
    }
    
    
    function DF_RemoveEmail(email) {
        document.body.style.cursor = 'wait';
        // Create our XMLHttpRequest object
        var httpRequest = new XMLHttpRequest();
        // Create some variables we need to send to our PHP file
        var url = DF_getUrl();
        var dataFromForm =
                    "removeEmail=yes" +"&"+"email="+email;
                   
        httpRequest.open("POST", url, true);
        // Set content type header information for sending url encoded variables in the request
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        // Access the onreadystatechange event for the XMLHttpRequest object
        httpRequest.onreadystatechange = function() {
            if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                var return_data = DFRemoveZero(httpRequest.responseText);
                alert(return_data);
                  document.body.style.cursor = 'default';
            }
        }
        // Send the data to PHP now... and wait for response to update the status div
        DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
    }
    

function df_AddFormType()
       {
           var mysys= document.getElementById('df_system_table_name');
           var myform= document.getElementById('df_form_type');
           if(mysys!=undefined && myform!=undefined)
           {
               var input = "[@"+mysys.value+"-"+myform.value+"@]";
                /*
                var ed = tinyMCE.get('content');
                if(ed != undefined) {
                    ed.execCommand('mceInsertContent', false, input)
                }
                */
                
                 df_appendText(input);
                
           }


       }

        function df_GetUrlParameter(sParam)
       {
              var sPageURL = window.location.search.substring(1);
            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++)
           {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == sParam)
                {
                           return sParameterName[1];
                }
           }
       }
   
       
       function df_appendText(text) {
        //Insert content
            if(typeof(window.parent) != "undefined" && typeof(window.parent.send_to_editor) != "undefined")
            {
               window.parent.send_to_editor(text);
            }
            else
            {
                  
               var codeToInsert= document.getElementById('df_code_label');
                 codeToInsert.innerHTML=" <b>Insert this code:</b> <br /> "+text;  
                 
            }
        }

       /**
        *
        * Add Date Picker
        **/
       function DF_DatePicker(textId)
       {
       		new datepickr(textId, { dateFormat: 'Y-m-d' });
        	//	$( "#"+textId ).datepicker();

       }

        /**
        *
        * Add Text Editor
        **/
         function DF_TinyInit(textAreaId)
         {

         	var options = {
				layout: "<div class='zariaToolbar'>[bold][italic][underline][justify-left][justify-full][justify-center][justify-right][unordered-list][ordered-list][font][size][link][unlink][html]</div>[edit-area]",
				buttons: [
						{name:'bold', label:'Bold', cmd:'bold', className:'bold'},
						{name:'italic', label:'Italic', cmd:'italic', className:'italic'},
						{name:'underline', label:'Underline', cmd:'underline', className:'underline'},
						{name:'justify-left', label:'Align Left', cmd:'justifyleft', className:'justifyleft'},
						{name:'justify-full', label:'Justify', cmd:'justifyfull', className:'justifyfull'},
						{name:'justify-center', label:'Align Center', cmd:'justifycenter', className:'justifycenter'},
						{name:'justify-right', label:'Align Right', cmd:'justifyright', className:'justifyright'},
						{name:'unordered-list', label:'Unordered List', cmd:'insertunorderedlist', className:'insertunorderedlist'},
						{name:'ordered-list', label:'Ordered List', cmd:'insertorderedlist', className:'insertorderedlist'},
						{name:'font', label:'Font', cmd:'fontname', menu:[{label:'Arial', value:'Arial'},{label:'Courier', value:'Courier'},{label:'Times New Roman', value:'Times New Roman'}]},
						{name:'size', label:'Size', cmd:'fontsize', menu:[{label:'12pt', value:'3'},{label:'24pt', value:'6'},{label:'36pt', value:'7'}]},
						{name:'link', label:'Link', cmd:'createlink', className:'link', prompt:"Enter your URL: "},
						{name:'unlink', label:'Unlink', cmd:'unlink', className:'unlink'},
						{name:'html', label:'html toggle', toggleMode: true, className:'html'}
				]
			};

         	var defaultArea = new Zaria(textAreaId, options);
			document.getElementById(textAreaId).onchange=function(){
				defaultArea.syncContent();
			}

			//function getDefault() {
			//	defaultArea.syncContent();
				//alert(document.getElementById(textAreaId).value);
		//	}


       /*
             tinyMCE.init({
		// General options
		mode : "textareas",
		elements : textAreaId,

	});

	      tinyMCE.execCommand("mceAddControl", false, textAreaId);
	     */

         }





        function DF_getPath()
         {
             return document.getElementById("DF_path").value;
         }

           function DF_getUrl()
         {
             if(document.URL.indexOf("wp-admin") != -1) {
                 return "admin-ajax.php";
             }
             else
             {
                 return "/wp-admin/admin-ajax.php"
             }
         }
         
            function DF_getUrlPost()
         {
             if(document.URL.indexOf("wp-admin") != -1) {
                 return "admin.php";
             }
             else
             {
                 return "/wp-admin/admin.php"
             }
         }

          function DFRemoveZero(httpRequest)
        {
            var result = "";
            for(var i = 0; i < httpRequest.length-1;i++ )
            {
                if(httpRequest.charCodeAt(i)>30)
                {
                    result += httpRequest[i];
                }
            }
                return result;
        }
         function DF_SendRequest(httpRequest,dataFromForm)
         {
             dataFromForm = "action=DFDynamicRequest&data=tests&" + dataFromForm;
              httpRequest.send(dataFromForm);
         }


         function DF_getPathTests()
         {
             return document.getElementById("DF_pathTests").value;
         }
  
  function DF_ajax_post() {
        document.body.style.cursor = 'wait';
        // Create our XMLHttpRequest object
        var httpRequest = new XMLHttpRequest();
        // Create some variables we need to send to our PHP file
        var url = DF_getUrl();
        var dataFromForm =
                    "buildForm=yes" +
                    "&system_table_name=" + document.forms["test_form"]["system_table_name"].value +
                	"&form_type=" + document.forms["test_form"]["form_type"].value + "&firstTime=yes";
        httpRequest.open("POST", url, true);
        // Set content type header information for sending url encoded variables in the request
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        // Access the onreadystatechange event for the XMLHttpRequest object
        httpRequest.onreadystatechange = function() {
            if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                var return_data = DFRemoveZero(httpRequest.responseText);
                document.getElementById("testField").innerHTML = return_data;
                  document.body.style.cursor = 'default';
            }
        }
        // Send the data to PHP now... and wait for response to update the status div
        DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
    }


         function DF_CreateHttpRequest(url)
         {
              var httpRequest = new XMLHttpRequest();
              httpRequest.open("POST", url, true);
              httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
              return httpRequest;
         }


          function DF_goOverDefinitions(systemName,formType,form_name,random_number) {
             res = "";
              var url = DF_getUrl();
             var httpRequest =  DF_CreateHttpRequest(url);
             var dataFromForm = "getDefinitions=yes&system_table_name="+systemName+"&form_type="+formType+"&random_number="+random_number;
             if(formType == "Search_ReadOnly") {
                 dataFromForm = "getDefinitions=yes&system_table_name="+systemName+"&form_type=Search&random_number="+random_number;
             }

             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     var DataFromAction = "processForm=yes";
                     var res = "";

                     var myFields = DFRemoveZero(httpRequest.responseText).split("###");
                     var DataFromActionHidden = "";
                     for(var i = 0; i < myFields.length; i++) {
                         var FieldDetails = myFields[i].split("@@@");

                         if(FieldDetails.length > 5) {
                             var currValue = "";
                             //is radio button
                             var currId = FieldDetails[1] + "_" + random_number;
                             if(FieldDetails[4] == "true") {
                                 //radio button
                                 currId = FieldDetails[1] + "_" + random_number + "_Array";
                             }
                             
                             if(document.getElementById(currId) == undefined &&( formType == "Search" || formType == "Search_ReadOnly")) {
                                 continue;
                             }
                             if(FieldDetails[4] == "true") {
                                 currValue = DF_MAgetRadioCheckedValue(form_name, currId);
                             }
                             else {
                             	 if(document.getElementById(currId).type == "textarea" && document.getElementById(currId).onchange != undefined && typeof(document.getElementById(currId).onchange ) == "function")
                             	 {
                             	 	document.getElementById(currId).onchange();
                             	 	currValue=escape(document.getElementById(currId).value);
                             	 }
								 else
								 {
                                     if(document.getElementById(currId).type == "checkbox")
                                     {
                                         if (document.getElementById(currId).checked)
                                         {
                                            currValue = 1;
                                         }
                                         else
                                         {
                                            currValue = 0;
                                         }
                                     }
                                     else
                                     {
                                 	    currValue = document.getElementById(currId).value;
                                     }
                                 }
                             }

                             DataFromAction += "&" + FieldDetails[0] + "=" + currValue;
                             if(FieldDetails[4] != "true") {
                                 document.getElementById(currId).style.color = "default";
                                 document.getElementById(currId).style.backgroundColor = "default";
                             }
                             if(formType != "Search" && formType != "Search_ReadOnly") {
                                 if(FieldDetails[2] == "true") {
                                     if(!DF_CheckIsFilled(currValue)) {
                                         res += "<br />"+FieldDetails[10]+": " + FieldDetails[7] + "";
                                         if(FieldDetails[4] != "true") {
                                             document.getElementById(currId).style.color = "red";
                                             document.getElementById(currId).style.backgroundColor = "yellow";
                                         }
                                     }
                                 }
                                 if(FieldDetails[3] == "true") {
                                     if(!DF_CheckIsNumber(currValue)) {
                                         res += "<br /> "+FieldDetails[10]+": " + FieldDetails[8] ;
                                         if(FieldDetails[4] != "true") {
                                             document.getElementById(currId).style.color = "red";
                                             document.getElementById(currId).style.backgroundColor = "orange";
                                         }
                                     }
                                 }
                                  if(FieldDetails[5] == "true") {
                                     if(!DF_CheckIsMail(currValue)) {
                                         res += "<br /> "+FieldDetails[10]+": " + FieldDetails[9] ;
                                         if(FieldDetails[4] != "true") {
                                             document.getElementById(currId).style.color = "red";
                                             document.getElementById(currId).style.backgroundColor = "orange";
                                         }
                                     }
                                 }
                                 if(formType == "UpdateDetails") {
                                     DataFromActionHidden += "&hidden_" + FieldDetails[0] + "=" + document.getElementById(currId+ "_hidden").value;
                                 }
                             }
                         }
                     }
                     if(res == "") {
                         //process logic
                         DataFromAction += "&form_type=" + formType +
   		                            "&system_table_name=" + systemName + "&random_number=" + random_number;
                         if(formType == "Insert") {
                             
                           //(systemName, random_number, form_name, DataFromAction)
                           var captchaid=random_number + "_recaptcha";
                           var MyDFrecaptcha=document.getElementById(captchaid);
                           if(typeof(MyDFrecaptcha)!="undefined" && MyDFrecaptcha!=null)
                           {
                               
                                   var callback = function(){};
                                   callback.MyFunc= DFMake_InsertAction;
                                   callback.systemName=systemName;
                                   callback.random_number=random_number;
                                   callback.form_name=form_name;
                                   callback.DataFromAction=DataFromAction;
                                    DF_ValidateRecaptcha(callback);
                           }
                           else
                           {
                                DFMake_InsertAction(systemName, random_number, form_name, DataFromAction);
                           }
                         }
                         if(formType == "UpdateDetails") {
                             DFMake_UpdateDetailsAction(random_number, form_name, systemName, DataFromAction + DataFromActionHidden);
                         }
                         if(formType == "Search" || formType == "Search_ReadOnly") {
                             DataFromAction += "&page_num=" + 0 + "&index_num=" + 0;

                             DFMake_SearchAction(random_number, form_name, systemName, DataFromAction);
                         }
                     }
                     else {
                         document.getElementById("newFormResult" + random_number).innerHTML ="<font color='red'>"+ res+"</font>";
                         document.body.style.cursor = 'default';
                     }
                 }
             }
             DF_SendRequest(httpRequest,dataFromForm);
             return;
         }



         function DFMake_InsertAction(systemName,random_number, form_name, dataFromForm)
		{
             document.body.style.cursor = 'wait';
		    document.getElementById("newFormResult" + random_number).innerHTML = "";
		    var url = DF_getUrl();
            var currUrl=window.location;
            dataFromForm =dataFromForm+ "&currurl="+currUrl;
            var httpRequest = DF_CreateHttpRequest(url);
	    httpRequest.onreadystatechange = function() {
		    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		    var result=DFRemoveZero(httpRequest.responseText);
		    		        var resultparams=result.split("@@");

		    	if (resultparams[0].search("success") != -1)
		        {
		    		// send request for single form
		    		//Make new request with sys name, identifying number and action update single
		    		var httpRequest2 = DF_CreateHttpRequest(url);
		    	   	var dataFromForm2 =
		    	   		"buildForm=yes" +
		    	   		"&random_number=" + random_number +
		    	   		"&form_type=Insert" +
		    	   		"&system_table_name="+ systemName ;
		    	   	httpRequest2.onreadystatechange = function() {
		    		    if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {

                            document.getElementById("newFormResult"+random_number).innerHTML = resultparams[1];
		    		    	document.getElementById("singleForm"+random_number).innerHTML = DFRemoveZero(httpRequest2.responseText);
                            document.body.style.cursor = 'default';
		    		    }
		    	    }
		    	    // Send the data to PHP now... and wait for response to update the status div
		    	   	DF_SendRequest(httpRequest2,dataFromForm2); // Actually execute the request
		        }else{
		        	document.getElementById("newFormResult"+random_number).innerHTML = result;
                    document.body.style.cursor = 'default';
		        }
		    }
	    }  
	    // Send the data to PHP now... and wait for response to update the status div
	    DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
		}
        
        function DF_Reset(random_number, form_name,systemName)
        {
             var url = DF_getUrl();
              var httpRequest2 = DF_CreateHttpRequest(url);
                       var dataFromForm2 =
                           "buildForm=yes" +
                           "&random_number=" + random_number +
                           "&form_type=Insert" +
                           "&system_table_name="+ systemName ;
                       httpRequest2.onreadystatechange = function() {
                        if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {

                            document.getElementById("newFormResult"+random_number).innerHTML = "";
                            document.getElementById("singleForm"+random_number).innerHTML = DFRemoveZero(httpRequest2.responseText);
                            document.body.style.cursor = 'default';
                        }
                    }
                    // Send the data to PHP now... and wait for response to update the status div
                       DF_SendRequest(httpRequest2,dataFromForm2); // Actually execute the request
        }



        function DFMake_UpdateDetailsAction(random_number, form_name,systemName,dataFromForm)
		{
            document.body.style.cursor = 'wait';
		    document.getElementById("newFormResult" + random_number).innerHTML = "";
		    var url = DF_getUrl();
            var httpRequest =DF_CreateHttpRequest(url);
	        httpRequest.onreadystatechange = function() {
		    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		    var result=DFRemoveZero(httpRequest.responseText);
		    		        var resultparams=result.split("@@");
		    	if (resultparams[0].search("success") != -1)
		        {

		    		// send request for single form
		    		//Make new request with sys name, identifying number and action update single
		    		var httpRequest2 = DF_CreateHttpRequest(url);
		    	   	var dataFromForm2 =
		    	   		"buildForm=yes" +
		    	   		"&random_number=" + random_number +
		    	   		"&form_type=UpdateGroup" +
		    	   		"&system_table_name=" + systemName+
                        "&noAllForms=yes";
		    	   	httpRequest2.onreadystatechange = function() {
		    		    if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
                              document.getElementById("allforms"+random_number).innerHTML = DFRemoveZero(httpRequest2.responseText);
                             document.getElementById("newFormResult"+random_number).innerHTML = resultparams[1];
							 document.body.style.cursor = 'default';
		    		    //GetGroupResults(url,random_number,systemName)
		    		    }
		    	    }
		    	    // Send the data to PHP now... and wait for response to update the status div
		    	   	DF_SendRequest(httpRequest2,dataFromForm2); // Actually execute the request
		        }else{
                    document.body.style.cursor = 'default';
		        	document.getElementById("newFormResult"+random_number).innerHTML = result;
		        }
		    }
	    }
	    // Send the data to PHP now... and wait for response to update the status div
	    DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
		}


       function DFMake_SearchAction(random_number, form_name,systemName,dataFromForm)
		{

            document.body.style.cursor = 'wait';
            document.getElementById("newFormResult" + random_number).innerHTML = "";
		    var url = DF_getUrl();
            var httpRequest = DF_CreateHttpRequest(url);
            dataFromForm += "&noAllForms=yes";
	    	httpRequest.onreadystatechange = function() {
		    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		    	if (DFRemoveZero(httpRequest.responseText).search("success") != -1)
		        {

                    document.getElementById("allforms"+random_number).innerHTML = DFRemoveZero(httpRequest.responseText) + " ";
                    document.body.style.cursor = 'default';

		        }else{
                    document.body.style.cursor = 'default';
		        	document.getElementById("newFormResult"+random_number).innerHTML = DFRemoveZero(httpRequest.responseText);
		        }
		    }
	    }
	    // Send the data to PHP now... and wait for response to update the status div
	    DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
		}

       function DF_CheckIsMail(str) {
    var lastAtPos = str.lastIndexOf('@');
    var lastDotPos = str.lastIndexOf('.');
    return (lastAtPos < lastDotPos && lastAtPos > 0 && str.indexOf('@@') == -1 && lastDotPos > 2 && (str.length - lastDotPos) > 2);
        }
        
        function DF_CheckIsNumber(x)
        {
            var result = true;
            if (isNaN(x)) result=false;
            return result;
        }

         function DF_CheckIsFilled(x)
        {
            var result = true;
            if (x == null || x == "") result=false;
            return result;
        }

        function DFMake_Reorder(random_number,systemName,orderby,ordertype,readOnly)
		{
            document.body.style.cursor = 'wait';
		    document.getElementById("newFormResult" + random_number).innerHTML = "";
		    var url = DF_getUrl();
            var httpRequest = DF_CreateHttpRequest(url);

		   	var dataFromForm ="buildForm=yes&random_number=" + random_number +
   		    "&form_type=" + "UpdateGroup" +
   		    "&system_table_name=" + systemName +
   		    "&orderby=" + orderby+
            "&ordertype=" + ordertype +
                            "&noAllForms=yes&Clear="+readOnly;
	        httpRequest.onreadystatechange = function() {
		        if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                    	     document.getElementById("allforms"+random_number).innerHTML = DFRemoveZero(httpRequest.responseText);
                            document.body.style.cursor = 'default';
		        }
	        }
	        // Send the data to PHP now... and wait for response to update the status div
	        DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
		}

         function DFMake_DeleteRow(random_number,systemName,id,readOnly)
        {
            var answer = confirm("Are you sure you want to delete this row?");
               if(answer) {
                    document.body.style.cursor = 'wait';
                    document.getElementById("newFormResult" + random_number).innerHTML = "";
                    var url = DF_getUrl();
                    var httpRequest = DF_CreateHttpRequest(url);

                       var dataFromForm ="buildForm=yes&random_number=" + random_number +
                       "&form_type=" + "UpdateGroup" +
                       "&system_table_name=" + systemName +
                    "&deleteRow=yes&deleteId="+id+
                    "&index_num=" + 0+
                                    "&Clear="+readOnly;
                    httpRequest.onreadystatechange = function() {
                        if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                                     document.getElementById("allforms"+random_number).innerHTML = DFRemoveZero(httpRequest.responseText);
                                    document.body.style.cursor = 'default';
                        }
                    }
                    // Send the data to PHP now... and wait for response to update the status div
                    DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
               }
        }


        function DF_ExportHTMLTableToExcel(random_number,systemName,readOnly)
        {
            document.body.style.cursor = 'wait';
            document.getElementById("newFormResult" + random_number).innerHTML = "";
            var url = DF_getUrl();
            var httpRequest = DF_CreateHttpRequest(url);

               var dataFromForm ="buildForm=yes&random_number=" + random_number +
               "&form_type=" + "Export" +
               "&system_table_name=" + systemName +"&Clear="+readOnly;;
            httpRequest.onreadystatechange = function() {
                if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                       var thisTable = httpRequest.responseText;
                       window.open('data:application/vnd.ms-excel,'+DFRemoveZero(httpRequest.responseText));
                       document.body.style.cursor = 'default';
                }
            }
            DF_SendRequest(httpRequest,dataFromForm);
        }

        function DFMake_ChangePage(random_number,systemName,newPage,readOnly)
		{
            document.body.style.cursor = 'wait';
		    document.getElementById("newFormResult" + random_number).innerHTML = "";
		    var url = DF_getUrl();
            var httpRequest = DF_CreateHttpRequest(url);

		   	var dataFromForm ="buildForm=yes&random_number=" + random_number +
   		    "&form_type=" + "UpdateGroup" +
   		    "&system_table_name=" + systemName +
   		    "&page_num=" + newPage+
            "&index_num=" + 0+
                            "&noAllForms=yes&Clear="+readOnly;
	        httpRequest.onreadystatechange = function() {
		        if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                    	     document.getElementById("allforms"+random_number).innerHTML = DFRemoveZero(httpRequest.responseText);
                            document.body.style.cursor = 'default';
		    	    //GetGroupResults(url,random_number,systemName)
		        }
	        }
	        // Send the data to PHP now... and wait for response to update the status div
	        DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
		}


         function DFMake_ChangeIndex(random_number, systemName,newIndex,readOnly)
		{
            document.body.style.cursor = 'wait';
		    document.getElementById("newFormResult" + random_number).innerHTML = "";
		   var url = DF_getUrl();
            var httpRequest = DF_CreateHttpRequest(url);
		   	var dataFromForm ="buildForm=yes&random_number=" + random_number +
   		"&form_type=" + "UpdateGroup" +
   		"&system_table_name=" + systemName +
   		"&index_num=" + newIndex+
                        "&noAllForms=yes&Clear="+readOnly;
		   	httpRequest.onreadystatechange = function() {
		   	    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		   	        document.getElementById("allforms" + random_number).innerHTML = DFRemoveZero(httpRequest.responseText);
		   	        document.body.style.cursor = 'default';
		   	      //  GetGroupResults(url, random_number, systemName)
		   	    }
		   	}
	    DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
		}

        function GetGroupResults(url,random_number,systemName)
        {
            var httpRequest3 = DF_CreateHttpRequest(url);
		    	var dataFromForm3 =
		    	    "buildForm=yes" +
		    	    "&random_number=" + random_number +
		    	    "&form_type=UpdateGroup" +
		    	    "&system_table_name=" + systemName;
		    	httpRequest3.onreadystatechange = function() {
		    	    if(httpRequest3.readyState == 4 && httpRequest3.status == 200) {
		    	    	document.getElementById("groupForm"+random_number).innerHTML = DFRemoveZero(httpRequest3.responseText);
		    	    }
		    	}
                DF_SendRequest(httpRequest3,dataFromForm3)
        }

		function DFMake_Insert(random_number, form_name, systemName) {
            document.body.style.cursor = 'wait';
		    DF_goOverDefinitions(systemName, "Insert", form_name, random_number);
		}





        function DFMake_Search(random_number, form_name,systemName)
		{
            document.body.style.cursor = 'wait';
            DF_goOverDefinitions(systemName, "Search", form_name, random_number);
		}

         function DFMake_Search_ReadOnly(random_number, form_name,systemName)
		{
            document.body.style.cursor = 'wait';
            DF_goOverDefinitions(systemName, "Search_ReadOnly", form_name, random_number);
		}


        function DFMake_UpdateDetails(random_number, form_name,systemName)
		{
            document.body.style.cursor = 'wait';
              DF_goOverDefinitions(systemName, "UpdateDetails", form_name, random_number);

		}

         // get value from radio input radio_name in form form_name
         function DF_MAgetRadioCheckedValue(form_name, radio_name) {
             var radioArray = document.forms[form_name][radio_name];

             for(var i = 0; i < radioArray.length; i++) {
                 if(radioArray[i].checked) {
                     return radioArray[i].value;
                 }
             }
             return '';
         }
         
         
         function df_ShowHide(divName)
         {
             if(document.getElementById(divName).style.visibility == "hidden") {
                  
                  document.getElementById(divName).style.visibility = "visible"; 
                  document.getElementById(divName).style.height = "inherit";
                  document.getElementById(divName).style.position = "absolute";
                  
              }
              else
              {
                 document.getElementById(divName).style.visibility = "hidden";
                 document.getElementById(divName).style.height = "0";
                 document.getElementById(divName).style.position = "absolute";
              }
         }