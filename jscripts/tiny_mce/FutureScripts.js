/*
         //Get category details according to the inserted value of category name
         function DF_CategoryDetails()
         {
             var httpRequest=DF_CreateHttpRequest(DF_getUrl())
             var dataFromForm = "CategoryDetails=yes&category_name="+document.forms["defineDefinitionForm"]["category_name"].value+"&entity_id="+document.forms["defineDefinitionForm"]["entity_id"].value;
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     var controlNames = DFRemoveZero(httpRequest.responseText).split("|");
                     if(controlNames.length == 2) {
                         document.forms["defineDefinitionForm"]["category_order"].value = controlNames[0];
                         document.forms["defineDefinitionForm"]["category_area"].value = controlNames[1];
                     }
                 }
             }
                     DF_SendRequest(httpRequest,dataFromForm);
         }

         */

         /*
         function DF_LoadCategorisAutoCompleteList()
         {
             var url = DF_getUrl();
             var httpRequest =  DF_CreateHttpRequest(url);
             var dataFromForm = "getCategories=yes&entity_id=" + document.forms["defineDefinitionForm"]["entity_id"].value;
             httpRequest.onreadystatechange = function() {
                 if(httpRequest.readyState == 4 && httpRequest.status == 200) {
                     var controlNames = DFRemoveZero(httpRequest.responseText).split("|");
                     $("input#category_name").autocomplete({
                         source: controlNames
                     });
                     if(controlNames.length > 0) {
                         document.forms["defineDefinitionForm"]["category_name"].value = controlNames[0];
                         DF_CategoryDetails();
                     }
                     else {
                         document.forms["defineDefinitionForm"]["category_order"].value = 0;
                     }
                 }
             }
             DF_SendRequest(httpRequest,dataFromForm);
         }

         */