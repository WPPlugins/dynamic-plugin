<?php
include_once 'DF_CMSFunctions.php';
include_once 'DF_Crypt.php';

class DF_JavascriptDefine
{
    //goes over definition list and creates the javascript function of
    // reading from page elements and making a response
    //function always rewrites existing functions and returns created filename
    //remember to load group again after editing details of one
    public function create_JavaScript($sysTableName, $formType, $Definitions)
    {
    }
    
    public function create_jsValidate($SysTableName, $FormType, $Definitions)
    {
        //go over definitions and creates all validation functions.
        //updates to each Definition it's own function of validation
        //this function is called with the control id while building form
    }
    
    
    public function createValidation($definition)
        {
            //creates validation java function connected to the control
        }
        
    public function create_jsInsert($sysTableName, $definitions)
    {
        //function javascrit gets sysname, form type, random num
        //call function jsValidate with the random num
        //foreach definition in definitions
            //search for all control in form with random num
            //by type of control read its value and concatenate to the url line
            //send request to the php with corrent parameters
            //if response is ok, write response message to the alert message with color green in the form and
            //clear all the controls
            //else write response to the alert message with color red
         
          $form_type="Insert";   
              $javascript = 
    	"function {$sysTableName}_Insert(random_number, form_name)
		{
		    var httpRequest = new XMLHttpRequest();
			var url = \"DF_DynamicRequest.php\";
		   	var dataFromForm =\"processForm=yes";
           $count=0;
           
         
    	foreach ($definitions as $definition)
    	{
            $definition->formType="Insert";
            $definition->sysTableName=$sysTableName;
            $controlIdPrefix="\"".$definition->getIdNoRandom(true)."\"";
            //$id = "{$this->system_name}_{$this->input_type}_{$this->sysTableName}_{$this->formType}_{$this->randomNum}";
            if($count>0) $javascript .= "\"";
    		$javascript .="&$definition->system_name=\" + document.forms[form_name][".$controlIdPrefix."+\"_\"+ random_number].value +";
          
                $count++;
               
   		} 
   		$javascript .="\"&random_number=\" + random_number +
   		\"&form_type=\" + \"".$form_type."\" +
   		\"&system_table_name=\" + \"".$sysTableName."\" +
   		\"&page_num=\" + 0 +
   		\"&index_num=\" + 0;
	    httpRequest.open(\"POST\", url, true);
	    httpRequest.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
	    httpRequest.onreadystatechange = function() {
		    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		    	if (httpRequest.responseText.search(\"success\") != -1)
		        {
                    document.getElementById(\"newFormResult\").innerHTML = httpRequest.responseText;
		    		// send request for single form
		    		//Make new request with sys name, identifying number and action update single
		    		var httpRequest2 = new XMLHttpRequest();
		    		var url = \"DF_DynamicRequest.php\";
		    	   	var dataFromForm2 = 
		    	   		\"buildForm=yes\" +
		    	   		\"&random_number=\" + random_number +
		    	   		\"&form_type=Insert\" +
		    	   		\"&system_table_name=\"+ \"".$sysTableName."\" ;
		    	   	httpRequest2.open(\"POST\", url, true);
		    	   	httpRequest2.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
		    	   	httpRequest2.onreadystatechange = function() {
		    		    if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
		    		    	document.getElementById(\"newForm\").innerHTML = httpRequest2.responseText;
		    		    }
		    	    }
		    	    // Send the data to PHP now... and wait for response to update the status div
		    	   	httpRequest2.send(dataFromForm2); // Actually execute the request
		        }else{
		        	document.getElementById(\"newFormResult\").innerHTML = httpRequest.responseText;
		        }
		    }
	    }
	    // Send the data to PHP now... and wait for response to update the status div
	    httpRequest.send(dataFromForm); // Actually execute the request
		}";
   		
   		if (file_put_contents("javascript\\{$sysTableName}_Insert.js", $javascript) == false)
    	//if (fwrite($handle, $javascript) == false)
    	{
    		return DF_DBFuncs::translate("Error - didn/'t succeed to write to javascript file");
    	}
    	return "created javascript files";
            
    }
    
     public function create_jsUpdateOne($sysTableName, $definitions)
    {
    
      $form_type="UpdateDetails"; 
       $javascript = 
    	"function {$sysTableName}_UpdateDetails(random_number, form_name)
		{
		    var httpRequest = new XMLHttpRequest();
			var url = \"DF_DynamicRequest.php\";
		   	var dataFromForm =\"processForm=yes";
           $count=0;
           
         
    	foreach ($definitions as $definition)
    	{
            $definition->formType="UpdateDetails";
            $definition->sysTableName=$sysTableName;
            $controlIdPrefix="\"".$definition->getIdNoRandom(true)."\"";
            
            if($count>0) $javascript .= "\"";
    		$javascript .="&$definition->system_name=\" + document.forms[form_name][".$controlIdPrefix."+\"_\"+ random_number].value +";
          
            $javascript .="\""."&hidden_$definition->system_name=\" + document.forms[form_name][".$controlIdPrefix."+\"_\"+ random_number+\"_hidden\"].value +";
            $count++;
          
   		} 
   		$javascript .="\"&random_number=\" + random_number +
   		\"&form_type=\" + \"".$form_type."\" +
   		\"&system_table_name=\" + \"".$sysTableName."\" +
   		\"&page_num=\" + 0 +
   		\"&index_num=\" + 0;
	    httpRequest.open(\"POST\", url, true);
	    httpRequest.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
	    httpRequest.onreadystatechange = function() {
		    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		    	if (httpRequest.responseText.search(\"success\") != -1)
		        {
                    document.getElementById(\"newFormResult\").innerHTML = httpRequest.responseText;
		    		// send request for single form
		    		//Make new request with sys name, identifying number and action update single
		    		var httpRequest2 = new XMLHttpRequest();
		    		var url = \"DF_DynamicRequest.php\";
		    	   	var dataFromForm2 = 
		    	   		\"buildForm=yes\" +
		    	   		\"&random_number=\" + random_number +
		    	   		\"&form_type=UpdateDetails\" +
		    	   		\"&system_table_name=\" + \"".$sysTableName."\";
		    	   	httpRequest2.open(\"POST\", url, true);
		    	   	httpRequest2.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
		    	   	httpRequest2.onreadystatechange = function() {
		    		    if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
		    		    	document.getElementById(\"singleForm\").innerHTML = httpRequest2.responseText;
		    		    	// send request for group form
		    	    		//Make new request with sys name, identifying number and action update group
		    	    		var httpRequest3 = new XMLHttpRequest();
		    	    		var url = \"DF_DynamicRequest.php\";
		    	    	   	var dataFromForm3 = 
		    	    	   		\"buildForm=yes\" +
		    	    	   		\"&random_number=\" + random_number +
		    	    	   		\"&form_type=UpdateGroup\" +
		    	    	   		\"&system_table_name=\" + \"".$sysTableName."\";
		    	    	   	httpRequest3.open(\"POST\", url, true);
		    	    	   	httpRequest3.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
		    	    	   	httpRequest3.onreadystatechange = function() {
		    	    		    if(httpRequest3.readyState == 4 && httpRequest3.status == 200) {
		    	    		    	document.getElementById(\"groupForm\").innerHTML = httpRequest3.responseText;
		    	    		    }
		    	    	    }
		    	    	    // Send the data to PHP now... and wait for response to update the status div
		    	    	   	httpRequest3.send(dataFromForm3); // Actually execute the request
		    		    }
		    	    }
		    	    // Send the data to PHP now... and wait for response to update the status div
		    	   	httpRequest2.send(dataFromForm2); // Actually execute the request
		        }else{
		        	document.getElementById(\"newFormResult\").innerHTML = httpRequest.responseText;
		        }
		    }
	    }
	    // Send the data to PHP now... and wait for response to update the status div
	    httpRequest.send(dataFromForm); // Actually execute the request
		}";
   		
   		if (file_put_contents("javascript\\{$sysTableName}_UpdateDetails.js", $javascript) == false)
    	//if (fwrite($handle, $javascript) == false)
    	{
    		return DF_DBFuncs::translate("Error - didn/'t succeed to write to javascript file");
    	}
    	return "created javascript files";
    }
    
     public function create_jsUpdateGroup($sysTableName, $formType, $Definitions,$keys)
    {
    }
    
     public function create_jsSearch($sysTableName, $definitions)
    {
    	/*
    	$handle = fopen("javascript\\{$sysTableName}_search", "rw");
    	if ($handle == false)
    	{
    		return DF_DBFuncs::translate("Error - didn't succeed to open javascript file");
    	}
    	*/
        $form_type="Search";
        
        $function_prefix="{$sysTableName}";

    	$javascript = 
    	"function {$function_prefix}_".$form_type."(random_number, form_name)
		{
            document.body.style.cursor = 'wait';
		    var httpRequest = new XMLHttpRequest();
			var url = \"DF_DynamicRequest.php\";
		   	var dataFromForm =\"processForm=yes";
           $count=0;
           
          
    	foreach ($definitions as $definition)
    	{
            //$id = "{$this->system_name}_{$this->input_type}_{$this->sysTableName}_{$this->formType}_{$this->randomNum}";
            $controlIdPrefix="\"$definition->system_name\"+\"_\"+
            \"$definition->input_type\" +\"_\"+\"".$sysTableName."\"+\"_\"+ \"".$form_type."\"";
            $definition->formType="Search";
            $definition->sysTableName=$sysTableName;

            $controlIdPrefix="\"".$definition->getIdNoRandom(true)."\"";

            if($count>0) $javascript .= "\"";
    		$javascript .="&$definition->system_name=\" + document.forms[form_name][".$controlIdPrefix."+\"_\"+ random_number].value +";
                $count++;
               
   		} 
   		$javascript .="\"&random_number=\" + random_number +
   		\"&form_type=\" + \"".$form_type."\" +
   		\"&system_table_name=\" + \"".$sysTableName."\" +
   		\"&page_num=\" + 0 +
   		\"&index_num=\" + 0;
	    httpRequest.open(\"POST\", url, true);
	    httpRequest.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
	    httpRequest.onreadystatechange = function() {
		    if(httpRequest.readyState == 4 && httpRequest.status == 200) {
		    	if (httpRequest.responseText.search(\"success\") != -1)
		        {
		    		// send request for single form
		    		//Make new request with sys name, identifying number and action update single
		    		var httpRequest2 = new XMLHttpRequest();
		    		var url = \"DF_DynamicRequest.php\";
		    	   	var dataFromForm2 = 
		    	   		\"buildForm=yes\" +
		    	   		\"&random_number=\" + random_number +
		    	   		\"&form_type=UpdateDetails\" +
		    	   		\"&system_table_name=\" + \"".$sysTableName."\";
		    	   	httpRequest2.open(\"POST\", url, true);
		    	   	httpRequest2.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
		    	   	httpRequest2.onreadystatechange = function() {
		    		    if(httpRequest2.readyState == 4 && httpRequest2.status == 200) {
                            
		    		    	
		    		    	// send request for group form
		    	    		//Make new request with sys name, identifying number and action update group
		    	    		var httpRequest3 = new XMLHttpRequest();
		    	    		var url = \"DF_DynamicRequest.php\";
		    	    	   	var dataFromForm3 = 
		    	    	   		\"buildForm=yes\" +
		    	    	   		\"&random_number=\" + random_number +
		    	    	   		\"&form_type=UpdateGroup\" +
		    	    	   		\"&system_table_name=\" + \"".$sysTableName."\";
		    	    	   	httpRequest3.open(\"POST\", url, true);
		    	    	   	httpRequest3.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
		    	    	   	httpRequest3.onreadystatechange = function() {
		    	    		    if(httpRequest3.readyState == 4 && httpRequest3.status == 200) {

                                    document.getElementById(\"newFormResult\").innerHTML = httpRequest.responseText;
                                    document.getElementById(\"singleForm\").innerHTML = httpRequest2.responseText;
		    	    		    	document.getElementById(\"groupForm\").innerHTML = httpRequest3.responseText;
                                    document.body.style.cursor = 'default';
		    	    		    }
		    	    	    }
		    	    	    // Send the data to PHP now... and wait for response to update the status div
		    	    	   	httpRequest3.send(dataFromForm3); // Actually execute the request
		    		    }
		    	    }
		    	    // Send the data to PHP now... and wait for response to update the status div
		    	   	httpRequest2.send(dataFromForm2); // Actually execute the request
		        }else{
                    document.body.style.cursor = 'default';
		        	document.getElementById(\"newFormResult\").innerHTML = httpRequest.responseText;
		        }
		    }
	    }
	    // Send the data to PHP now... and wait for response to update the status div
	    httpRequest.send(dataFromForm); // Actually execute the request
		}";
   		
   		if (file_put_contents("javascript\\{$sysTableName}_Search.js", $javascript) == false)
    	//if (fwrite($handle, $javascript) == false)
    	{
    		return DF_DBFuncs::translate("Error - didn/'t succeed to write to javascript file");
    	}
    	return "created javascript files";
    }
    
}
?>