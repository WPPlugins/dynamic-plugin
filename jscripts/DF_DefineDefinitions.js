 // Applied on loading of document
 $(document).ready(DF_loadDefaults);

         function DF_getPath()
         {
             return document.getElementById("DF_path").value;
         }

         function DF_getUrl()
         {
             return "admin-ajax.php";
         }

        function DF_CleanField(field)
         {
             field.value = DF_RemoveImpossible(field.value);
         }
         
         function DF_SetSystemName(field)
         {
             if(document.forms["defineDefinitionForm"]["system_name"].value=="")
             {
            var newValue = DF_RemoveImpossible(field.value);
              if(newValue=="" && field.value!="")
              {
                  var randomnumber=Math.floor(Math.random()*100000);
                  newValue="field_"+randomnumber;
              }
           document.forms["defineDefinitionForm"]["system_name"].value= newValue;
             }
         }

         function DF_RemoveImpossible(valueT)
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

         /*
         loadDefaults - needed to get started values of field default details
         */
         function DF_loadDefaults() {
             DF_TinyInit("TinyTxtArea");
             DF_GetHtmlHelps();
             DF_LoadDefinitionNextOrder();
             DF_Show_Form();
             //DF_GetFormDesign();
         }


        
         function DF_ChangeInputTypeByType()
         {
             
             var selectedType = document.forms["defineDefinitionForm"]["type"].value ;
             var selectedinput = document.forms["defineDefinitionForm"]["input_type"].value ;
             if(selectedType == "double" || selectedType == "integer" || selectedType == "date")
             {
                   document.forms["defineDefinitionForm"]["input_type"].value  = "text";
             }
               if(selectedType == "boolean")
             {
                     document.forms["defineDefinitionForm"]["input_type"].value ="checkbox" ;
             }    
             if(selectedinput=="boolean" && selectedType!="checkbox")
             {
                      document.forms["defineDefinitionForm"]["input_type"].value="text";
             }
             return false;
         }
         
         
         function DF_ChangeTypeByInputType()
         {
             
             var selectedInputType = document.forms["defineDefinitionForm"]["input_type"].value ;
                 var selectedType = document.forms["defineDefinitionForm"]["type"].value ;
               if(selectedInputType == "checkbox")
             {
                     document.forms["defineDefinitionForm"]["type"].value ="boolean" ;
             }  
             else
             {
                       if(selectedType=="boolean")
                       {
                             document.forms["defineDefinitionForm"]["type"].value ="string";
                       }
                       if(selectedInputType=="file")
                       {
                             document.forms["defineDefinitionForm"]["type"].value ="string" ;
                       }
             } 
             return false;
         }
         
         function DF_LoadEntity()
         {
              var url = DF_getUrl();
             var dataFromForm2 = "loadEntityDetails=yes&sysname=" + document.getElementById("entityToLoad").value;
             // Access the onreadystatechange event for the XMLHttpRequest object
             var httpRequest2 = DF_CreateHttpRequest(url);
             httpRequest2.onreadystatechange = function() {
                 if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
                     window.location.href = window.location.href;
                 }
             }
             DF_SendRequest(httpRequest2,dataFromForm2);
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
             dataFromForm = "action=defineDefinitions&data=tests&" + dataFromForm;
             httpRequest.send(dataFromForm);
         }


          function DF_CreateHttpRequest(url)
         {
              var httpRequest = new XMLHttpRequest();
              httpRequest.open("POST", url, true);
              httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
              return httpRequest;
         }






         function DF_LoadDefinitionNextOrder()
         {
             var url = DF_getUrl();
             var dataFromForm2 = "getNextOrder=yes&entity_id=" + document.forms["defineDefinitionForm"]["entity_id"].value;
             // Access the onreadystatechange event for the XMLHttpRequest object
             var httpRequest2 = DF_CreateHttpRequest(url);
             httpRequest2.onreadystatechange = function() {
                 if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
                     document.forms["defineDefinitionForm"]["field_order"].value = DFRemoveZero(httpRequest2.responseText);
                 }
             }

             DF_SendRequest(httpRequest2,dataFromForm2);
         }

         function DF_AddDoTinyMCE(sysname)
         {
             var ed = tinyMCE.get('TinyTxtArea');
             input = "[@" + sysname + "@]";
             ed.execCommand('mceInsertContent', false, input);
             document.getElementById('entityToLoad').focus=true;
         }
         
           function DF_AddDoTinyMCEWithLabel(sysname, labelName)
         {
             var ed = tinyMCE.get('TinyTxtArea');
             input = "<b>"+labelName+":</b>"+"[@" + sysname + "@]";
             ed.execCommand('mceInsertContent', false, input);
             document.getElementById('entityToLoad').focus=true;
         }
         
         
         function df_GetDesignedHtml(sysname, labelName,required)
         {
            
             var requiredHtml=required;
              if(required=="1")
              {
                  requiredHtml="<font color='red'>*</font>";
              }
             return "<p><label for='[@id|"+sysname+"@]'><b>"+labelName+":</b></label>"+requiredHtml+"<br /> [@" + sysname + "@]</p>";
         }
         
           function DF_AddDoTinyMCEWithLabelDesigned(sysname, labelName,required)
         {
             var ed = tinyMCE.get('TinyTxtArea');
             input = df_GetDesignedHtml(sysname, labelName,required);
             ed.execCommand('mceInsertContent', false, input);
             document.getElementById('entityToLoad').focus=true;
         }
         
         //init tiny text editor
         function DF_TinyInit(textAreaId)
         {
             tinyMCE.init({
		// General options
		mode : "exact",
		elements : textAreaId,
		theme : "advanced",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups",
        oninit : DF_GetFormDesign,
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,


		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
         }


         function clearHtml(text)
         {
             return text.replace(/&nbsp;/g, "<br />");
             return text.replace(/&/g, "");
         }

         function DF_SaveFormDesign()
         {
              var answer = confirm("Are you sure you want to save new "+document.getElementById("formType").value +" view design?");
              if(answer) {
                  var httpRequest = DF_CreateHttpRequest(DF_getUrl());
                  var clearedhtml = clearHtml(tinyMCE.get('TinyTxtArea').getContent());
                  var dataFromForm = "SaveFormDesign=yes&entity_id=" + document.forms["defineDefinitionForm"]["entity_id"].value +
              "&formType=" + document.getElementById("formType").value + "&formHtml=" + escape(clearedhtml) + "";

                  httpRequest.onreadystatechange = function() {
                      if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                          document.getElementById("saveFormActionResult").innerHTML = DFRemoveZero(httpRequest.responseText);
                          DF_Show_Form();
                      }
                  }
                  DF_SendRequest(httpRequest, dataFromForm);
              }
         }


          function DF_GetFormDesign()
         {
              var httpRequest = DF_CreateHttpRequest(DF_getUrl());
              var dataFromForm = "getFormDesign=yes&entity_id=" +document.forms["defineDefinitionForm"]["entity_id"].value+
              "&formType="+document.getElementById("formType").value+"";
              httpRequest.onreadystatechange = function() {
                  if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                      var ed = tinyMCE.get('TinyTxtArea');
                      if(typeof(ed)!='undefined')
                      {
                        ed.setContent('');
                        ed.execCommand('mceInsertContent', false, DFRemoveZero(unescape(httpRequest.responseText)))
                        
                         
                      }
                      else
                      {
                          var ed = tinyMCE.get('TinyTxtArea');
                          if(typeof(ed)!='undefined')
                          {
                            ed.setContent('');
                            ed.execCommand('mceInsertContent', false, DFRemoveZero(unescape(httpRequest.responseText)))
                            
                            
                          }
                          
                           
                      }
                       
                  }
              }
             DF_SendRequest(httpRequest,dataFromForm);
         }

         //used for labels with html help strings
         function DF_MakeDivVisible(sender)
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
                 var httpRequest = DF_CreateHttpRequest(DF_getUrl());

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
                DF_SendRequest(httpRequest,dataFromForm);
             }
             else
                 {
                     document.getElementById(divhelpName).style.visibility = visibility_value;
                 }
         }




         //Add div elements to the form with help description html
         function DF_GetHtmlHelps() {
             var httpRequest = DF_CreateHttpRequest(DF_getUrl())
             var dataFromForm = "getHelpHtmls=yes";
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     if(DFRemoveZero(httpRequest.responseText) != "") {
                         var forNames = DFRemoveZero(httpRequest.responseText).split("|");
                         for(var i = 0; i < forNames.length; i++) {
                             var forNameValues = forNames[i].split("=");
                             if(forNameValues.length == 2) {
                                 var forName = forNameValues[0];

                                 var divHelpName = forName + "_help";
                                 var divHelp = document.getElementById(divHelpName);
                                 var labels = document.getElementsByTagName('LABEL');
                                 for(j = 0; j < labels.length; j++) {
                                     if(labels[j].getAttribute('for') == forName) {
                                         labels[j].setAttribute('onmouseover', "DF_MakeDivVisible(this)");
                                         labels[j].setAttribute('onmouseout', "DF_MakeDivVisible(this)");
                                         labels[j].style.cursor = "help";
                                         
                                         
                                         var newdiv = document.createElement("div");
                                         newdiv.setAttribute("id", divHelpName);
                                         newdiv.setAttribute("class", "Df-help-Div");
                                         newdiv.innerHTML = forNameValues[1]
                                         labels[j].appendChild(newdiv);

                                     
                                        // labels[j].parentNode .insertBefore(newdiv,labels[j]);
                                        var MyImage=document.getElementById( divHelpName+"img");
                                        if(typeof(MyImage)=="undefined"  || MyImage==null)
                                        {
                                         var newImage = document.createElement("img");
                                         newImage.setAttribute("id", divHelpName+"img");
                                           newImage.setAttribute("src",DF_getPath()+"images/helpEngine.png")  ;
                                         //  labels[j].appendChild(newImage);
                                           
                                               labels[j].parentNode .insertBefore(newImage,labels[j]);
                                        }
                                     }
                                 }
                             }
                         }
                     }
                 }
             }
             DF_SendRequest(httpRequest,dataFromForm);
         }




         function DF_GetLabelFor(forName) {
             var labels = document.getElementsByTagName('LABEL');
             for(j = 0; j < labels.length; j++) {
                 if(labels[j].getAttribute('for') == forName) {
                     return labels[j];
                 }
             }
         }



         /*
            Used to Add or edit new Definition.
            Used to create javascript files based on definition settings
         */
         function DF_Define_New_Entity_Definition(formType) {
             document.body.style.cursor = 'wait';
             // Create our XMLHttpRequest object
             var url = DF_getUrl();
             var httpRequest = DF_CreateHttpRequest(url);
             var dataFromForm = "";
             // Create some variables we need to send to our PHP file
             if(formType == 'defineDefinition') {
                 return DF_getDefinitionValidation();
             }
             if(formType == 'finish_defining_definitions') {

                 dataFromForm = "finishDefiningDefinitions=yes&sysname=" + document.forms["defineDefinitionForm"]["sysTableName"].value;
             }

             // Access the onreadystatechange event for the XMLHttpRequest object
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     if(formType == 'finish_defining_definitions') {
                         DF_emptyFields();
                         document.getElementById("defineDefinitionResult").innerHTML = DFRemoveZero(httpRequest.responseText);
                         DF_getDefinitionsOfCurrentEntity();
                     }
                     document.body.style.cursor = 'default';
                 }
             }

             // Send the data to PHP now... and wait for response to update the status div
             DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request

         }

         //Asumes that all needed checkes were done
         function DefineNewDefinition()
         {
               document.body.style.cursor = 'wait';
               var url =DF_getUrl();
               var httpRequest = DF_CreateHttpRequest(url);
               var  dataFromForm = DF_getDataFromNewDefinitionForm();
                httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     //"definitionname", "system_name"
                     var labelname= document.forms["defineDefinitionForm"]["definitionname"].value;
                     var sysname= document.forms["defineDefinitionForm"]["system_name"].value;
                     var isrequired = DF_getRadioCheckedValue("defineDefinitionForm", "is_must");
                     var required="";
                     if(isrequired=="1")
                     {
                        required="<font color='red'>*</font>";
                     }
                     if(  labelname &&  sysname && isrequired)
                     {
                        DF_AddDoTinyMCEWithLabelDesigned(sysname,labelname,required); 
                        DF_SaveFormDesign();
                     }
                    DF_emptyFields();
                    document.getElementById("defineDefinitionResult").innerHTML = DFRemoveZero(httpRequest.responseText);
                    DF_getDefinitionsOfCurrentEntity();
                    document.body.style.cursor = 'default';
                 }
             }
             // Send the data to PHP now... and wait for response to update the status div
             DF_SendRequest(httpRequest,dataFromForm); // Actually execute the request
         }


         function DF_getNumValidation(res)
         {
              var url = DF_getUrl();
                  var httpRequest2 =  DF_CreateHttpRequest(url);
             var dataFromForm2 = "getNumberDefinitionFields=yes";
             httpRequest2.onreadystatechange = function() {
                 if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
                     var myFields = DFRemoveZero(httpRequest2.responseText).split("|");
                     for(i = 0; i < myFields.length; i++) {
                         var x = document.forms["defineDefinitionForm"][myFields[i]].value;
                         if(isNaN(x)) {
                             res += " <br /> <font color='red'>" + myFields[i] + " suppose to be number</font> ";
                             document.forms["defineDefinitionForm"][myFields[i]].value = "suppose to be number";
                             document.forms["defineDefinitionForm"][myFields[i]].style.color = "red";
                             document.forms["defineDefinitionForm"][myFields[i]].style.backgroundColor = "orange";
                         }
                     }
                     if(res == "") {
                         DefineNewDefinition();
                     }
                     else {
                         document.getElementById("defineDefinitionResult").innerHTML = res;
                         document.body.style.cursor = "default";
                     }
                 }
             }

              DF_SendRequest(httpRequest2,dataFromForm2);
             return;
         }

          /*
         Validates all neede controles filled with values. Used before sending request
         to add new definition
         */
         function DF_getDefinitionValidation() {
             res = "";
              var url =DF_getUrl();
             var httpRequest =  DF_CreateHttpRequest(url);
             var dataFromForm = "getMustDefinitionFields=yes";
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     var myFields = DFRemoveZero(httpRequest.responseText).split("|");
                     if(myFields != undefined) {
                         for(i = 0; i < myFields.length; i++) {
                             var x = document.forms["defineDefinitionForm"][myFields[i]].value;
                             document.forms["defineDefinitionForm"][myFields[i]].style.color = "black";
                             document.forms["defineDefinitionForm"][myFields[i]].style.backgroundColor = "white";
                             if(x == null || x == "") {
                                 res += " <br /> <font color='red'>" + myFields[i] + " suppose to be filled</font> ";
                                 document.forms["defineDefinitionForm"][myFields[i]].value = "suppose to be filled";
                                 document.forms["defineDefinitionForm"][myFields[i]].style.color = "red";
                                 document.forms["defineDefinitionForm"][myFields[i]].style.backgroundColor = "yellow";
                             }
                         }
                         DF_getNumValidation(res);
                     }
                     else {
                         alert("no response");
                     }
                 }
             }
             DF_SendRequest(httpRequest,dataFromForm);
             return;
         }

         /*
         get html contains all definitions existing for current entity
         */
         function DF_getDefinitionsOfCurrentEntity() {
             var url = DF_getUrl();
             var dataFromForm = "getDefinitionsOfCurrentEntity=yes&entity_id=" + document.forms["defineDefinitionForm"]["entity_id"].value + "&sysTableName=" + document.forms["defineDefinitionForm"]["sysTableName"].value;
             var httpRequest =DF_CreateHttpRequest(url);
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     document.getElementById("definitionsDetails").innerHTML = DFRemoveZero(httpRequest.responseText);
                 }
             }
             DF_SendRequest(httpRequest,dataFromForm);
         }

         /*
            Drop Definition by definition id
         */
         function DF_dropDefinition(varsysName, vardefinition_id, varentity_id, varsysTableName) {
             var answer = confirm("Are you sure you want to delete " + varsysName + " from " + varsysTableName + "?");
             if(answer) {
                 var url =DF_getUrl();
                 var dataFromForm = "dropDefinition=yes&sysName=" + varsysName + "&definition_id=" + vardefinition_id + "&entity_id=" + varentity_id + "&sysTableName=" + varsysTableName;
                 var httpRequest = DF_CreateHttpRequest(url);
                 httpRequest.onreadystatechange = function() {
                     if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                         try {
                             document.getElementById("definitionsActionResult").innerHTML = DFRemoveZero(httpRequest.responseText);
                         }
                         catch(err1)
                                                                                                                                                       { }
                         DF_getDefinitionsOfCurrentEntity();
                         DF_emptyFields();
                     }
                 }
                 DF_SendRequest(httpRequest,dataFromForm);
             }
         }

         /*loads definition details to the controls of define definition form
         */
         function DF_loadDefinitionDetails(definition_id) {
            document.body.style.cursor = 'wait';
             var url = DF_getUrl();
             var dataFromForm = "getDefinitionDetails=yes&definition_id=" + definition_id;
             var httpRequest =   DF_CreateHttpRequest(url);
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {

                     var controlNames = DFRemoveZero(httpRequest.responseText).split("&");
                     for(i = 0; i < controlNames.length; i++) {
                         controlDetails = controlNames[i].split("=");
                         if(controlDetails.length == 2) {
                             try {
                                 if(document.forms["defineDefinitionForm"][controlDetails[0]].type == undefined) {
                                     DF_checkRadioValue("defineDefinitionForm", controlDetails[0], controlDetails[1]);
                                 }
                                 else {
                                     document.forms["defineDefinitionForm"][controlDetails[0]].value = controlDetails[1];
                                 }

                             }
                             catch(err)
                             { }
                         }
                     }

                     document.forms["defineDefinitionForm"]["system_name"].disabled = true;
                     //show definition div
                     if(document.getElementById("DefinitionsDetails").style.visibility == "hidden") {
                         var buttonCaller = document.getElementById("ShowDefinitionDetails");
                         DF_ShowEntity("DefinitionsDetails",document.getElementById("ShowDefinitionDetails"));
                     }
                      document.body.style.cursor = 'default';
                 }
             }
             DF_SendRequest(httpRequest,dataFromForm);

         }

         /*
         Reads data from Definition form to the response parameters
         */
         function DF_getDataFromNewDefinitionForm() {
             var result = "defineDefinition=yes";
             if(document.forms["defineDefinitionForm"]["definition_id"].value && !isNaN(document.forms["defineDefinitionForm"]["definition_id"].value)) {

                 result += "&statusAction=edit&definition_id=" + document.forms["defineDefinitionForm"]["definition_id"].value;
             }
             else {
                 result += "&statusAction=new";
             }
             var myFields = new Array("definitionname", "system_name", "type", "input_type", "values", "field_order", "search_type","helpTxt","classTxt","styleTxt","defaultValue","filterType","orderType");
             for(i = 0; i < myFields.length; i++) {
                 result += "&" + myFields[i] + "=" +  escape( document.forms["defineDefinitionForm"][myFields[i]].value);
             }
             myFields = new Array( "appear_on_group", "is_id", "is_must", "isFilterable");
             for(i = 0; i < myFields.length; i++) {
                 result += "&" + myFields[i] + "=" + DF_getRadioCheckedValue("defineDefinitionForm", myFields[i]);
             }
             return result;
         }

         /*
         Empty Fields of values to start the adding process of the definition all over again
         */
         function DF_emptyFields() {
             var myFields = new Array("definitionname", "system_name", "values", "field_order", "definition_id", "helpTxt","classTxt","styleTxt","defaultValue");
             for(i = 0; i < myFields.length; i++) {
                 var x = document.forms["defineDefinitionForm"][myFields[i]].value = "";
                 document.forms["defineDefinitionForm"][myFields[i]].style.color = "black";
                 document.forms["defineDefinitionForm"][myFields[i]].style.backgroundColor = "white";
             }
             document.forms["defineDefinitionForm"]["system_name"].disabled = false;
             document.getElementById("definitionsActionResult").innerHTML = "";
             document.getElementById("defineDefinitionResult").innerHTML = "";

             DF_LoadDefinitionNextOrder();
         }



         // get value from radio input radio_name in form form_name
         function DF_getRadioCheckedValue(form_name, radio_name) {
             var radioArray = document.forms[form_name][radio_name];

             for(var i = 0; i < radioArray.length; i++) {
                 if(radioArray[i].checked) {
                     return radioArray[i].value;
                 }
             }
             return '';
         }


         //check radio value
         function DF_checkRadioValue(form_name, radio_name, needed_value) {

             var radioArray = document.forms[form_name][radio_name];

             for(var i = 0; i < radioArray.length; i++) {

                 if(radioArray[i].value == needed_value) {
                     radioArray[i].checked = "checked";
                 }
             }
         }

         function DF_Switch(div1name,div2name,IdVal,Id2, textPlace1,textPlace2)
         {
             IdVal2 = document.getElementById(Id2);

              if(document.getElementById(div1name).style.visibility != "hidden") {

                document.getElementById(div1name).style.visibility = "hidden";
                document.getElementById(div1name).style.height = "0";
                document.getElementById(div1name).style.position = "relative";

                 document.getElementById(div2name).style.visibility = "visible";
                 document.getElementById(div2name).style.height = "inherit";
                document.getElementById(div2name).style.position = "relative";
                IdVal.style.background = "url('"+DF_getPath()+"images/ZoomIn.png') no-repeat";
                IdVal2.style.background = "url('"+DF_getPath()+"images/ZoomOut.png') no-repeat";
                document.getElementById(textPlace2).setAttribute("class" , "DF_ChosenLabel");
                document.getElementById(textPlace1).setAttribute("class" , "DF_NotChosenLabel");
            }
            else
            {
                 document.getElementById(div1name).style.visibility = "visible";
                 document.getElementById(div1name).style.height = "inherit";
                 document.getElementById(div1name).style.position = "relative";

                 document.getElementById(div2name).style.visibility = "hidden";
                document.getElementById(div2name).style.height = "0";
                document.getElementById(div2name).style.position = "relative";

                  IdVal.style.background = "url('"+DF_getPath()+"images/ZoomOut.png') no-repeat";
                  IdVal2.style.background = "url('"+DF_getPath()+"images/ZoomIn.png') no-repeat";
                  document.getElementById(textPlace1).setAttribute("class" , "DF_ChosenLabel");
                 document.getElementById(textPlace2).setAttribute("class" , "DF_NotChosenLabel");
            }


         }

        function DF_ShowHide(divname,IdVal)
        {

            if(document.getElementById(divname).style.visibility != "hidden") {
                document.getElementById(divname).style.visibility = "hidden";
                document.getElementById(divname).style.height = "0";
                document.getElementById(divname).style.position = "relative";
                IdVal.style.background = "url('"+DF_getPath()+"images/ZoomIn.png') no-repeat";
            }
            else
            {
                 document.getElementById(divname).style.visibility = "visible";
                 document.getElementById(divname).style.height = "inherit";
                  document.getElementById(divname).style.position = "relative";
                  IdVal.style.background = "url('"+DF_getPath()+"images/ZoomOut.png') no-repeat";
            }
        }

        
         function DF_ShowEntity(divName,IdVal)
         {
              var check=false;
             if(divName=="DefinitionsDetails")
             {
                  check=true;
                 underDiv="TechnicalDetails";
                 jpgEl="definitionTechDetails";
             }
               if(divName=="DefineNewEntityAll")
             {
                  check=true;
                 underDiv="EntityExtraDetails";
                 jpgEl="ShowEntityDetails";
             }
              if(document.getElementById(divName).style.visibility == "hidden") {
                  
                  document.getElementById(divName).style.visibility = "visible"; 
                  document.getElementById(divName).style.height = "inherit";
                  document.getElementById(divName).style.position = "relative";
                  IdVal.style.background = "url('"+DF_getPath()+"images/ZoomOut.png') no-repeat";
              }
              else
              {
                 document.getElementById(divName).style.visibility = "hidden";
                 document.getElementById(divName).style.height = "0";
                 document.getElementById(divName).style.position = "relative";
                 IdVal.style.background = "url('"+DF_getPath()+"images/ZoomIn.png') no-repeat"; 
                  if(check && document.getElementById(underDiv).style.visibility != "hidden") {
                      DF_ShowEntity(underDiv,document.getElementById(jpgEl));
                  }
                     
              }
         }
         
        

        
        //Show updated form:
        function DF_Show_Form() {
        document.body.style.cursor = 'wait';
        // Create our XMLHttpRequest object
        var httpRequest = new XMLHttpRequest();
        // Create some variables we need to send to our PHP file
        var url = DF_getUrl();
        var formType=document.getElementById("formType").value;
        if(formType=="Default") {formType="Insert";}
        var dataFromForm =
                    "buildForm=yes" +
                    "&system_table_name=" +document.forms["defineDefinitionForm"]["sysTableName"].value +
                    "&form_type=" + formType + "&firstTime=yes";
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

    
         