            
            
            function DF_getUrl()
         {
             return "admin-ajax.php";
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
             dataFromForm = "action=saveOptions&data=tests&" + dataFromForm;
             httpRequest.send(dataFromForm);
         }

            function DF_CreateHttpRequest(url)
         {
              var httpRequest = new XMLHttpRequest();
              httpRequest.open("POST", url, true);
              httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
              return httpRequest;
         }

          function DF_SaveOptions() {
             var httpRequest = DF_CreateHttpRequest(DF_getUrl());
             var DataFromForm = DF_getDataFromNewEntityForm('save'); 
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     document.getElementById("OptionsDiv").innerHTML = DFRemoveZero(httpRequest.responseText);
                 }
             }
             DF_SendRequest(httpRequest, DataFromForm);
         }
         
          function DF_getDataFromNewEntityForm(action) {
             var result = "SaveOptions=yes&curraction="+action;
             var myFields = new Array("sec_key");
             for(i = 0; i < myFields.length; i++) {
                 result += "&" + myFields[i] + "=" + document.forms["SaveOptions"][myFields[i]].value;
             }
             return result;
         }