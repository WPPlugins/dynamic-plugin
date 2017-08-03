 // Applied on loading of document
  
         /*
         loadDefaults - needed to get started values of field default details
         */
         function DF_Entity_loadDefaults() {
             document.forms["defineEntityForm"]["inserttxt"].value=document.forms["defineEntityForm"]["inserttxt_hidden"].value;
             document.forms["defineEntityForm"]["updatetxt"].value=document.forms["defineEntityForm"]["updatetxt_hidden"].value;
             document.forms["defineEntityForm"]["selecttxt"].value=document.forms["defineEntityForm"]["selecttxt_hidden"].value;
             document.forms["defineEntityForm"]["searchtxt"].value=document.forms["defineEntityForm"]["searchtxt_hidden"].value;
             document.forms["defineEntityForm"]["groupnumtxt"].value="5";
             document.forms["defineEntityForm"]["successInserttxt"].value=document.forms["defineEntityForm"]["successInserttxt_hidden"].value;
             document.forms["defineEntityForm"]["failInserttxt"].value=document.forms["defineEntityForm"]["failInserttxt_hidden"].value;
             document.forms["defineEntityForm"]["successUpdatetxt"].value=document.forms["defineEntityForm"]["successUpdatetxt_hidden"].value;
             document.forms["defineEntityForm"]["failUpdatetxt"].value=document.forms["defineEntityForm"]["failUpdatetxt_hidden"].value;
             document.forms["defineEntityForm"]["norowstxt"].value=document.forms["defineEntityForm"]["norowstxt_hidden"].value;
             document.forms["defineEntityForm"]["emailTitle"].value=document.forms["defineEntityForm"]["emailTitle_hidden"].value;
             document.forms["defineEntityForm"]["emailValidationError"].value=document.forms["defineEntityForm"]["emailValidationError_hidden"].value;
             document.forms["defineEntityForm"]["decimalValidationError"].value=document.forms["defineEntityForm"]["decimalValidationError_hidden"].value;
             document.forms["defineEntityForm"]["numberValidationError"].value=document.forms["defineEntityForm"]["numberValidationError_hidden"].value;
             document.forms["defineEntityForm"]["mustFieldValidationError"].value=document.forms["defineEntityForm"]["mustFieldValidationError_hidden"].value;
             
             document.forms["defineEntityForm"]["detailViewShowType"].value=document.forms["defineEntityForm"]["detailViewShowType_hidden"].value;
             document.forms["defineEntityForm"]["sendMailToAdder"].value=document.forms["defineEntityForm"]["sendMailToAdder_hidden"].value;
             document.forms["defineEntityForm"]["validateAction"].value=document.forms["defineEntityForm"]["validateAction_hidden"].value;
             document.forms["defineEntityForm"]["enableCaptcha"].value=document.forms["defineEntityForm"]["enableCaptcha_hidden"].value;
             document.forms["defineEntityForm"]["emailField"].value=document.forms["defineEntityForm"]["emailField_hidden"].value;
             
                               
                                        
         }

         function DF_Entity_CleanField(field)
         {
             field.value = DF_Entity_RemoveImpossible(field.value);
         }

          function DF_Entity_SetSystemName(field)
         {
             if(document.forms["defineEntityForm"]["sysname"].value=="" )
             {
            var newValue = DF_Entity_RemoveImpossible(field.value);
           document.forms["defineEntityForm"]["sysname"].value= newValue;
             }
         }
         
         function DF_Entity_RemoveImpossible(valueT)
         {
             var result = "";

                for(var i = 0; i < valueT.length;i++ )
                {

                    if((valueT.charCodeAt(i)>="a".charCodeAt(0) && valueT.charCodeAt(i)<="z".charCodeAt(0)) ||
                    (valueT.charCodeAt(i)>="A".charCodeAt(0) && valueT.charCodeAt(i)<="Z".charCodeAt(0)))
                    {
                        result += valueT[i];
                    }
                }
                return result;
         }

         function DF_Entity_getPath()
         {
             return document.getElementById("DF_Entity_path").value;
         }

          function DF_Entity_getUrl()
         {
             return "admin-ajax.php";
         }

          function DF_Entity_LoadEntity()
         {
             document.body.style.cursor = 'wait';
              var url = DF_Entity_getUrl();
             var dataFromForm2 = "loadEntityDetails=yes&sysname=" + document.getElementById("entityToLoad").value;
             // Access the onreadystatechange event for the XMLHttpRequest object
             var httpRequest2 = DF_Entity_CreateHttpRequest(url);
             httpRequest2.onreadystatechange = function() {
                 if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
                     document.body.style.cursor = 'default';
                     window.location.href = window.location.href;

                 }
             }
             DF_Entity_SendRequest(httpRequest2,dataFromForm2);
         }

         function DF_Entity_RemoveZero(httpRequest)
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


         function DF_Entity_SendRequest(httpRequest,dataFromForm)
         {
             dataFromForm = "action=defineMyEntities&data=tests&" + dataFromForm;
             httpRequest.send(dataFromForm);
         }

            function DF_Entity_CreateHttpRequest(url)
         {
              var httpRequest = new XMLHttpRequest();
              httpRequest.open("POST", url, true);
              httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
              return httpRequest;
         }

             //Add div elements to the form with help description html
         function DF_Entity_RenewDefineNewEntity(showExtraButtons) {
             var httpRequest = DF_Entity_CreateHttpRequest(DF_Entity_getUrl());
             var DataFromForm = "RenewDefineNewEntity=yes&showExtraButtons="+showExtraButtons;
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     document.getElementById("DefineNewEntity").innerHTML = DF_Entity_RemoveZero(httpRequest.responseText);
                 }
             }
             DF_Entity_SendRequest(httpRequest, DataFromForm);
         }

          /*
            Used to add new Entity.
         */
         function DF_Entity_Define_New_Entity(formType, showExtraButtons) {
             document.body.style.cursor = 'wait';
             // Create our XMLHttpRequest object
             var url = DF_Entity_getUrl();
             var httpRequest = DF_Entity_CreateHttpRequest(url);
             var dataFromForm = "";
             // Create some variables we need to send to our PHP file

             if(formType == 'defineEntity') {
                 dataFromForm = DF_Entity_getDataFromNewEntityForm("AddNew");
             }
              if(formType == 'editEntity') {
                 dataFromForm = DF_Entity_getDataFromEditEntityForm("Edit");
             }
             // Access the onreadystatechange event for the XMLHttpRequest object
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     if(formType == 'defineEntity' || formType == 'editEntity') {
                         document.getElementById("defineEntityResult").innerHTML = DF_Entity_RemoveZero(httpRequest.responseText);

                         DF_Entity_RenewDefineNewEntity(showExtraButtons);
                     }
                     document.body.style.cursor = 'default';
                 }
             }
            
             // Send the data to PHP now... and wait for response to update the status div
             DF_Entity_SendRequest(httpRequest, dataFromForm);  // Actually execute the request
         }


            //delete
           function DF_Entity_Delete_Entity(showExtraButtons) {
               var answer = confirm("Are you sure you want to delete this entity?");
               if(answer) {
                   document.body.style.cursor = 'wait';
                   // Create our XMLHttpRequest object
                   var url = DF_Entity_getUrl();
                   var httpRequest = DF_Entity_CreateHttpRequest(url);
                   var dataFromForm = "";
                   // Create some variables we need to send to our PHP file


                   dataFromForm = DF_Entity_getDataFromDeleteEntityForm();

                   // Access the onreadystatechange event for the XMLHttpRequest object
                   httpRequest.onreadystatechange = function() {
                       if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                           document.getElementById("defineEntityResult").innerHTML = DF_Entity_RemoveZero(httpRequest.responseText);
                           DF_Entity_RenewDefineNewEntity(showExtraButtons);
                           document.body.style.cursor = 'default';
                       }
                   }
                   // Send the data to PHP now... and wait for response to update the status div
                   DF_Entity_SendRequest(httpRequest, dataFromForm);  // Actually execute the request
               }
         }



         /*
         Reads data from entity form to the response parameters
         */
         function DF_Entity_getDataFromNewEntityForm(action) {
             var result = "defineEntity=yes&curraction="+action;
             
             var myFields = new Array("entityname", "sysname", "description","email","inserttxt","searchtxt","updatetxt","selecttxt","groupnumtxt","successInserttxt","failInserttxt","successUpdatetxt","failUpdatetxt","norowstxt"  ,
             "emailTitle","curentStyle", "curentClassName"  ,"sendMailToAdder","emailField" ,"emailText","validateAction" ,"detailViewShowType" ,"emailValidationError",
             "decimalValidationError", "numberValidationError","mustFieldValidationError","enableCaptcha"
             );   
             for(i = 0; i < myFields.length; i++) {
                 result += "&" + myFields[i] + "=" + document.forms["defineEntityForm"][myFields[i]].value;
             }
             return result;
         }

           /*
         Reads data from entity form to the response parameters
         */
         function DF_Entity_getDataFromDeleteEntityForm() {
             var result = "deleteEntity=yes";
             var myFields = new Array( "sysname", "oldentityid");
             for(i = 0; i < myFields.length; i++) {
                 result += "&" + myFields[i] + "=" + document.forms["defineEntityForm"][myFields[i]].value;
             }
             return result;
         }

         /*
         Reads data from entity form to the response parameters
         */
         function DF_Entity_getDataFromEditEntityForm(action) {
             var result = "defineEntity=yes&curraction="+action;
             var myFields = new Array("entityname", "sysname", "description","email","inserttxt","searchtxt","updatetxt","selecttxt","groupnumtxt","successInserttxt","failInserttxt","successUpdatetxt","failUpdatetxt","oldsysname","oldentityid","norowstxt",
             "emailTitle","curentStyle", "curentClassName"  ,"sendMailToAdder","emailField" ,"emailText","validateAction" ,"detailViewShowType" ,"emailValidationError",
             "decimalValidationError", "numberValidationError","mustFieldValidationError","enableCaptcha"
             );
             for(i = 0; i < myFields.length; i++) {
                 result += "&" + myFields[i] + "=" + document.forms["defineEntityForm"][myFields[i]].value;
             }
             return result;
         }

           /*
         Empty Fields of values to start the adding process of the definition all over again
         */
         function DF_Entity_emptyFieldsEntity() {
             var myFields = new Array("entityname", "sysname", "description","email","inserttxt","searchtxt","updatetxt","selecttxt","groupnumtxt","successInserttxt","failInserttxt","successUpdatetxt","failUpdatetxt","norowstxt",
             "emailTitle","curentStyle", "curentClassName"  ,"sendMailToAdder","emailField" ,"emailText","validateAction" ,"detailViewShowType" ,"emailValidationError",
             "decimalValidationError", "numberValidationError","mustFieldValidationError","enableCaptcha"
             );
             for(i = 0; i < myFields.length; i++) {
                 var x = document.forms["defineEntityForm"][myFields[i]].value = "";
                 document.forms["defineEntityForm"][myFields[i]].style.color = "black";
                 document.forms["defineEntityForm"][myFields[i]].style.backgroundColor = "white";
             }
             document.forms["defineEntityForm"]["DefineEntityButton"].disabled = false;
             document.forms["defineEntityForm"]["DefineEntityButton"].style.visibility = "visible";
             document.forms["defineEntityForm"]["DefineEntityButton"].setAttribute("class", "button-primary");
             document.forms["defineEntityForm"]["BackEntityButton"].disabled = false;
             document.forms["defineEntityForm"]["BackEntityButton"].style.visibility = "visible";
             document.forms["defineEntityForm"]["BackEntityButton"].setAttribute("class", "button-primary");
             document.forms["defineEntityForm"]["AddNewButton"].disabled = true;
             document.forms["defineEntityForm"]["AddNewButton"].style.visibility="hidden";
             document.forms["defineEntityForm"]["AddNewButton"].setAttribute("class", "button-primary");
             document.forms["defineEntityForm"]["EditButton"].disabled = true;
             document.forms["defineEntityForm"]["EditButton"].style.visibility="hidden";
             document.forms["defineEntityForm"]["EditButton"].setAttribute("class", "button-primary");
             document.forms["defineEntityForm"]["DeleteButton"].disabled = true;
             document.forms["defineEntityForm"]["DeleteButton"].style.visibility="hidden";
             document.forms["defineEntityForm"]["DeleteButton"].setAttribute("class", "button-primary");
             document.getElementById("descriptionDiv").style.visibility = "visible";
             document.getElementById("descriptionDiv").style.height = " fit-content";
             document.getElementById("descriptionDiv").style.position = "relative";
             document.getElementById("defineEntityResult").innerHTML = "";
             //default values
             //"inserttxt","searchtxt","updatetxt","selecttxt","groupnumtxt","successInserttxt","failInserttxt","successUpdatetxt","failUpdatetxt","norowstxt"
              DF_Entity_loadDefaults();

         }

        



         // get value from radio input radio_name in form form_name
         function DF_Entity_getRadioCheckedValue(form_name, radio_name) {
             var radioArray = document.forms[form_name][radio_name];

             for(var i = 0; i < radioArray.length; i++) {
                 if(radioArray[i].checked) {
                     return radioArray[i].value;
                 }
             }
             return '';
         }


         //check radio value
         function DF_Entity_checkRadioValue(form_name, radio_name, needed_value) {

             var radioArray = document.forms[form_name][radio_name];

             for(var i = 0; i < radioArray.length; i++) {

                 if(radioArray[i].value == needed_value) {
                     radioArray[i].checked = "checked";
                 }
             }
         }
         
         
          function DF_Entity_ShowEntity(divName,IdVal)
         {
             var check=false;
              if(divName=="defineEntityForm")
             {
                  check=true;
                 underDiv="EntityExtraDetails";
                 jpgEl="ShowEntityDetails";
             }
              if(document.getElementById(divName).style.visibility == "hidden") {
                  
                  document.getElementById(divName).style.visibility = "visible"; 
                  document.getElementById(divName).style.height = "inherit";
                  document.getElementById(divName).style.position = "relative";
                  IdVal.style.background = "url('"+DF_Entity_getPath()+"images/ZoomOut.png') no-repeat";
              }
              else
              {
                 document.getElementById(divName).style.visibility = "hidden";
                 document.getElementById(divName).style.height = "0";
                 document.getElementById(divName).style.position = "relative";
                 IdVal.style.background = "url('"+DF_Entity_getPath()+"images/ZoomIn.png') no-repeat";  
                  if(check && document.getElementById(underDiv).style.visibility != "hidden") {
                      DF_Entity_ShowEntity(underDiv,document.getElementById(jpgEl));
                  }   
              }
         }

         
           //Add div elements to the form with help description html
         function DF_Entity_GetHtmlHelps() {
             var httpRequest = DF_Entity_CreateHttpRequest(DF_Entity_getUrl())
             var dataFromForm = "getHelpHtmls=yes";
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     if(DF_Entity_RemoveZero(httpRequest.responseText) != "") {
                         var forNames = DF_Entity_RemoveZero(httpRequest.responseText).split("|");
                         for(var i = 0; i < forNames.length; i++) {
                             var forNameValues = forNames[i].split("=");
                             if(forNameValues.length == 2) {
                                 var forName = forNameValues[0];

                                 var divHelpName = forName + "_help";
                                 var divHelp = document.getElementById(divHelpName);
                                 var labels = document.getElementsByTagName('LABEL');
                                 for(j = 0; j < labels.length; j++) {
                                     if(labels[j].getAttribute('for') == forName) {
                                         labels[j].setAttribute('onmouseover', "DF_Entity_MakeDivVisible(this)");
                                         labels[j].setAttribute('onmouseout', "DF_Entity_MakeDivVisible(this)");
                                         labels[j].style.cursor = "help";
                                         var newdiv = document.createElement("div");
                                         newdiv.setAttribute("id", divHelpName);
                                         newdiv.setAttribute("class", "Df-help-Div");
                                         newdiv.innerHTML = forNameValues[1]
                                         labels[j].appendChild(newdiv);
                                           //labels[j].parentNode .insertBefore(newdiv,labels[j]);
                                         
                                         
                                           
                                           var MyImage=document.getElementById( divHelpName+"img");
                                        if(typeof(MyImage)=="undefined" || MyImage==null)
                                        {
                                         var newImage = document.createElement("img");
                                         newImage.setAttribute("id", divHelpName+"img");
                                           newImage.setAttribute("src",DF_Entity_getPath()+"images/helpEngine.png")  ;
                                           //labels[j].appendChild(newImage);
                                            labels[j].parentNode .insertBefore(newImage,labels[j]);
                                        }
                                     }
                                 }
                             }
                         }
                     }
                 }
             }
             DF_Entity_SendRequest(httpRequest,dataFromForm);
         }
         
         
           function DF_Entity_MakeDivVisible(sender)
         {
             var forName = sender.getAttribute('for');
             var divhelpName = forName+"_help";
             var oldVisValue = document.getElementById(divhelpName).style.visibility;
             var caller_str = String(arguments.callee.caller.toString());
              var visibility_value = "hidden";
               var position_value="relative";
             if(caller_str.indexOf("onmouseover")!=-1)
             {
                 visibility_value = "visible";
                  position_value = "relative";
             }
             if(visibility_value == "visible" && document.getElementById(divhelpName).innerHTML=="") {
                 var httpRequest = DF_Entity_CreateHttpRequest(DF_Entity_getUrl());

                 var dataFromForm = "getHelpHtml=yes&forName=" + forName;
                 httpRequest.onreadystatechange = function() {
                     if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                         if(DFRemoveZero(httpRequest.responseText) != "") {
                            // document.getElementById(divhelpName).innerHTML = DFRemoveZero(httpRequest.responseText);
                             document.getElementById(divhelpName).style.visibility = visibility_value;
                               document.getElementById(divhelpName).style.position = position_value;
                         }
                     }
                 }
                DF_Entity_SendRequest(httpRequest,dataFromForm);
             }
             else
                 {
                     document.getElementById(divhelpName).style.visibility = visibility_value;
                 }
         }