<?php

include_once 'DF_CMSFunctions.php';
include_once 'DF_Definition.php';
include_once 'DF_Value.php';
include_once 'DF_GeneralSettings.php';
include_once 'DF_Session.php';


class DF_DataBase
{

    ///Definitions:

	// builds and returns query used to create new definition
	private function build_add_definition_query($Definition,$statusAction="new")
	{
         $sqlStart="INSERT INTO ";
         $sqlEnd="";
        if($statusAction=="edit")
        {

            $sqlStart="Update";
            $sqlEnd=" where definition_id=".$Definition->definition_id;
        }

		$entityId=DF_Session::LoadSession("entity_id");
		$query = " df_sys_definitions SET entity_id= {$entityId},

		system_name='".$Definition->system_name."',
		name='".$Definition->name."',
		type='".$Definition->type."',
		input_type='".$Definition->input_type."',
		field_order=".$Definition->field_order.",

		appear_on_group=".$Definition->appear_on_group.",
		is_id=".$Definition->is_id.",
		is_must=".$Definition->is_must.",
		search_type='".$Definition->search_type."',
        
       
        helpTxt='".$Definition->helpTxt."',
        classTxt='".$Definition->classTxt."',
        styleTxt='".mysql_real_escape_string($Definition->styleTxt)."',
        defaultValue='".$Definition->defaultValue."',
        
        isFilterable=".$Definition->isFilterable.",
        filterType='".$Definition->filterType."',
        orderType='".$Definition->orderType."'
        ";

		//values_data='{$mysqli->real_escape_string($Definition->values)}',

		// the below data is non mandatory fields - checking if they're not empty



      
		return $sqlStart . $query . $sqlEnd;
	}


	// updating df_sys_name_value_pairs table if there're names,values in this definition
	private function insert_names_values_data($definition_id,$values_pairs_array,$statusAction="new")
	{

        if($statusAction=="edit")
        {
            $query=" delete from df_sys_name_value_pairs
			where definition_id={$definition_id}";
            $results = DF_DBFuncs::sqlExecute($query);
			if ( $results["boolean"] != true)
			{
                return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to delete old values")."</font>";
            }
        }
        else
        {
            // retreiving last inserted definition_id (of this definition)
            $query = "SELECT definition_id FROM df_sys_definitions
            ORDER BY definition_id DESC LIMIT 1";
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $row = $results["data"][0];
                $definition_id=$row["definition_id"];
            }else {
                die(DF_DBFuncs::translate("Retreiving last definition_id from database was not successfull"));
            }
	    }
		foreach ($values_pairs_array as $name_value_pair)
		{
			$query = "INSERT INTO df_sys_name_value_pairs
			SET definition_id={$definition_id},
			name='".trim($name_value_pair->name)."',
			value='".trim($name_value_pair->value)."'";

			$results = DF_DBFuncs::sqlExecute($query);
			if ( $results["boolean"] != true)
			{
				return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to insert names-values pairs into df_sys_name_value_pairs table,
				mysql error number is ").$results[error_number]."</font>";
			}
		}
	}

    // adds definition and updates $sysTableName
    // $sysTableName - table that was created before when new entity was created
    public function add_definition($sysTableName,$Definition,$statusAction="new")
    {
        if (($Definition->input_type=="checkbox" && $Definition->type!="boolean"))
            return "<font color='red'>".    DF_DBFuncs::translate("Field of checkbox must be of boolean type ")."</font> ";
        if ( ($Definition->input_type!="checkbox" && $Definition->type=="boolean"))
            return "<font color='red'>".   DF_DBFuncs::translate(" Field of ").DF_DBFuncs::translate($Definition->type). DF_DBFuncs::translate(" type must be of ").DF_DBFuncs::translate("checkbox").DF_DBFuncs::translate(" input type ")."</font> ";
        if ( ( $Definition->type=="double" || $Definition->type=="integer" || $Definition->type=="date") && $Definition->input_type!="text")
            return "<font color='red'>".    DF_DBFuncs::translate(" Field of ").DF_DBFuncs::translate($Definition->type). DF_DBFuncs::translate(" type must be of ").DF_DBFuncs::translate("text").DF_DBFuncs::translate(" input type ")."</font> ";
        
        if($statusAction=="new")
        {
            //check if there is not already such definition in definitions and there is no such column in the table
            $FirstCheck = " show columns from " . $sysTableName . " where Field='".$Definition->system_name . "'";
                $results = DF_DBFuncs::sqlGetResults($FirstCheck);
                if ( $results["boolean"] == true && count($results["data"])>0)
                {
                    return "<font color='red'>".DF_DBFuncs::translate(" Field ").$Definition->system_name.DF_DBFuncs::translate(" already exists in form ") . $sysTableName."</font>";
                }
                $SecondCheck = " select * from df_sys_definitions where system_name='".$Definition->system_name . "' and entity_id= ".DF_Session::LoadSession("entity_id")." ";
                $results = DF_DBFuncs::sqlGetResults($SecondCheck);
                if ( $results["boolean"] == true && count($results["data"])>0)
                {
                    return "<font color='red'>".DF_DBFuncs::translate(" Field ") . $Definition->system_name.DF_DBFuncs::translate(" already exists in fields of ") . $sysTableName."</font>";
                }
        }
    	// updates df_sys_definitions table
    	$query = self::build_add_definition_query($Definition,$statusAction);
    	$results = DF_DBFuncs::sqlExecute($query);
    	if ( $results["boolean"] != true)
    	{


    		return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed save "). $Definition->name . DF_DBFuncs::translate(" into DF_sysDefinitions ,
    			mysql error number is") . $results[error_number]."</font>";
    	}


    	// updating df_sys_name_value_pairs table if there're names,values in this definition
    	if (is_array($Definition->values))
    	{
    		$resultAddValues=self::insert_names_values_data($Definition->definition_id,$Definition->values,$statusAction);
            $pos = strlen( strstr($resultAddValues,"Didn't succeed"));
            if($pos>0)
            {
                return $resultAddValues;
            }
    	}

        if($statusAction=="edit")
        {
            //change the column
            $query = "ALTER TABLE ". $sysTableName ."
   			change ".$Definition->system_name." ".$Definition->system_name." ". self::getDefinitionType($Definition->type)." ";

            $results = DF_DBFuncs::sqlExecute($query);
             if ( $results["boolean"] != true)
            {

                return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to alter ").  $sysTableName. DF_DBFuncs::translate(" form,
                    mysql error number is "). $results[error_number]."</font>";
            }
        }
        else
        {
    	    // adding new definition to the table
    	    $query = "ALTER TABLE ". $sysTableName ."
   			    ADD ".$Definition->system_name." ". self::getDefinitionType($Definition->type)."  ";

            $results = DF_DBFuncs::sqlExecute($query);

            if ( $results["boolean"] != true)
    	    {

    		    return DF_DBFuncs::translate("Didn't succeed to alter "). $sysTableName . DF_DBFuncs::translate(" form,
    			    mysql error number is "). $results[error_number];
    	    }
        }


    	return "<font color='green'> ". DF_DBFuncs::translate("Field")." ". $Definition->system_name . " ". DF_DBFuncs::translate("saved") . " </font>";
    }

    public function dropDefinition($sysName, $definition_id,$entity_id,$sysTableName)
    {

        $query = " delete from df_sys_definitions where definition_id=".$definition_id;
        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {
            return DF_DBFuncs::translate("Didn't succeed to delete "). $sysName . DF_DBFuncs::translate(" from sys_df_definitions,
                mysql error number is "). $results[error_number];
        }
        $query = " Alter table ".$sysTableName." drop ". $sysName."";
        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {
            return DF_DBFuncs::translate("Didn't succeed to drop column "). $sysName . DF_DBFuncs::translate(" from ") . $sysTableName . DF_DBFuncs::translate(",
                mysql error number is "). $results[error_number];
        }
        $query = " delete from df_sys_name_value_pairs where definition_id=".$definition_id;
        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {
            return DF_DBFuncs::translate("Didn't succeed to delete "). $sysName . DF_DBFuncs::translate("from df_sys_name_value_pairs,
                mysql error number is "). $results[error_number];
        }
        return "<font color='green'>".  DF_DBFuncs::translate("Definition")." ". $sysName." ". DF_DBFuncs::translate("removed from system") . "</font>";

    }

      public static function getDefinitionType($MyType)
    {
        $result=" varchar(50) ";
        switch($MyType)
        {
            case "double":
                $result=" float ";
                break;
            case "integer":
                $result=" bigint ";
                break;
            case "string":
                $result=" varchar(255)  COLLATE utf8_unicode_ci ";
                break;
            case "text":
                $result=" Text  COLLATE utf8_unicode_ci ";
                break;
            case "boolean":
                $result=" bit ";
                break;
             case "date":
                $result=" date ";
                break;
             case "boolean":
                $result=" TINYINT(1) ";
                break;
             default:
                $result=" varchar(50)  COLLATE utf8_unicode_ci ";
                break;
        }
        return $result;

    }

     //used in DF_DefineEntitiesRequest.php in order to edit definition details later
    public function getDefinitionsProperties($definition_id)
    {
        $result="";
        $count=0;
         $query = "SELECT * from df_sys_definitions where definition_id=".$definition_id;
            $results = DF_DBFuncs::sqlGetResults($query);

            if ( $results["boolean"] == true)
            {

                $row = $results["data"][0];
                foreach($row as $key=>$value)
                {


                    if($count>0)
                        $result .="&";
                    if($key=="name")
                    {
                       $result.="definitionname=".$value;
                    }
                    else
                    {
                        $result.=$key . "=" .$value;
                    }

                    $count++;

                }
            }

            $query=" Select * from df_sys_name_value_pairs where definition_id=".$definition_id;
            $results = DF_DBFuncs::sqlGetResults($query);

            if ( $results["boolean"] == true)
            {
                $rows=count($results["data"]);
                if($rows>0)
                {
                    if($count>0)
                        $result .="&";
                     $result .= "values=";
                      $count2=0;
                    for($i=0;$i<$rows;$i++)
                    {
                        if($count2>0)
                        $result .= ",";
                        $row = $results["data"][$i];
                        $result .=$row["name"];
                        $count2++;
                    }
                }


            }
           return $result;
    }


    public function getAllDefinitionsForCheck($entity_id)
    {
         $query = "SELECT * from df_sys_definitions where entity_id=".$entity_id."";
            $results = DF_DBFuncs::sqlGetResults($query);
             return $results;
    }


    public function getDefinitions($SystemTableName)
    {
        $query = "select * from df_sys_definitions where entity_id = (select entity_id from df_sys_entities where sysname='".$SystemTableName."')";
         $results = DF_DBFuncs::sqlGetResults($query);
         return $results;
    }

      //gets next order of new definition to be added
    public function getNextOrder($entity_id)
    {
        $result=0;
            $query = "SELECT Max(field_order) as max_order from df_sys_definitions where entity_id=".$entity_id ."";
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $arrLen=count($results["data"]);
                if ( $arrLen>0)
                {
                    $row = $results["data"][0];
                    $result=$row["max_order"]+1;
                }
            }
            return $result;
    }

   /*
    //gets category details from database
    public function getCategoryDetails($category_name,$entity_id)
    {
        $result="0|0";
            $query = "SELECT category_order,category_area from df_sys_definitions where entity_id=".$entity_id ." and category_name='".$category_name."'";
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $arrLen=count($results["data"]);
                if ( $arrLen>0)
                {
                    $row = $results["data"][0];
                    $result=$row["category_order"]."|".$row["category_area"];
                }
            }
            return $result;
    }

   */


    public function GetMustFieldNamesDefinition()
    {
        $result="";
        $myFields = array("definitionname", "system_name", "type", "input_type", "field_order");
        for($i=0;$i<count($myFields);$i++)
        {
            if($i>0) $result.="|";
            $result.=$myFields[$i];
        }
        return $result;
    }

    public function GetNumberFieldNamesDefinition()
    {
        $result="";
        $myFields = array("field_order");
        for($i=0;$i<count($myFields);$i++)
        {
            if($i>0) $result.="|";
            $result.=$myFields[$i];
        }
        return $result;
    }


    public function getSysNameById($id)
    {
        $result="";
        $query = "select * from df_sys_definitions where definition_id=".$id;
        $results = DF_DBFuncs::sqlGetResults($query);
        if ( $results["boolean"] == true)
        {
            $result=$results['data'][0]['system_name'];
        }
        return $result;
    }

    public function get_AllDefinitionsToForm($sysTableName,$formType,$randomNumber,$formName,$appearOnGroup)
    {

           $result="";
           $count=0;
           $definitions = $this->get_AllDefinitions($sysTableName,$formType,$randomNumber,$formName,$appearOnGroup);
           $sysDetails = $this->getSysDetails($sysTableName);
           foreach($definitions as $definition)
           {

               if($count>0) $result.="###";
               //0
               $result.="column_".$definition->definition_id;
               $result.="@@@";
               //1
               $result.=$definition->getIdNoRandom(true);
               $result.="@@@";
               //2
               $result.=($definition->is_must == "1")?"true":"false";
               $result.="@@@";
               //3
               $result.=($definition->type=="integer" ||$definition->type=="double" ) ? "true" : "false";
                $result.="@@@";
                //4
               $result.=($definition->input_type=="radio" ) ? "true" : "false";
                $result.="@@@";
                //5
               $result.=($sysDetails['emailField']==$definition->system_name)?"true":"false";
               $result.="@@@";
               //6
               $result.= $sysDetails['mustFieldValidationError'];
               $result.="@@@";
               //7
               $result.= $sysDetails['numberValidationError'];
               $result.="@@@";
               //8
               $result.= $sysDetails['decimalValidationError'];
               $result.="@@@";
               //9
               $result.= $sysDetails['emailValidationError'];
               //10
                $result.="@@@";
               $result.=$definition->name;
               
               
               

               $count++;
           }
           return $result;

    }
    // get definitions(only names, not values)
    // $appearOnGroup is used to filter definitions for UpdateGroup form
    public function get_AllDefinitions($sysTableName,$formType,$randomNumber,$formName,$appearOnGroup)
    {

        $query = "SELECT df_sys_definitions.*
         FROM df_sys_definitions, df_sys_entities
        WHERE df_sys_definitions.entity_id=df_sys_entities.entity_id
        AND df_sys_entities.sysname = '$sysTableName'
        $appearOnGroup
        ORDER BY field_order";

        
        $results = DF_DBFuncs::sqlGetResults($query);
        if ( $results["boolean"] == true)
        {
           
            $definitions = array();
            foreach ($results["data"] as $result_row)
            {
                $definition = new DF_Definition();
                $definition->sysTableName = $sysTableName;
                $definition->formType = $formType;
                $definition->randomNum = $randomNumber;

                //TODO: Make reflection!!!
                $definition->definition_id = $result_row['definition_id'];
                $definition->name = $result_row['name'];
                $definition->system_name = $result_row['system_name'];
                $definition->type = $result_row['type'];
                $definition->input_type = $result_row['input_type'];
                $definition->field_order = $result_row['field_order'];


                if($result_row['appear_on_group']=="1" || ord($result_row['appear_on_group'])==1)
                {
                    $definition->appear_on_group="1";
                }
               if($result_row['is_id']=="1" || ord($result_row['is_id'])==1)
                {
                    $definition->is_id="1";
                }
                if($result_row['is_must']=="1" || ord($result_row['is_must'])==1)
                {
                    $definition->is_must="1";
                }
               
               if($result_row['isFilterable']=="1" || ord($result_row['isFilterable'])==1)
                {
                    $definition->isFilterable="1";
                }
               
                $definition->search_type = $result_row['search_type'];

                $definition->helpTxt = $result_row['helpTxt'];
                $definition->classTxt = $result_row['classTxt'];
                $definition->styleTxt = html_entity_decode(stripslashes($result_row['styleTxt']));
                $definition->defaultValue = $result_row['defaultValue'];
                $definition->filterType = $result_row['filterType'];
                $definition->orderType = $result_row['orderType'];
                
               


                if (strcmp($definition->input_type, "checkbox") == 0 ||
                        strcmp($definition->input_type, "radio") == 0 ||
                        strcmp($definition->input_type, "dropdownlist") == 0)
                        {
                            // retrieving names values data from database
                            $query = "SELECT name, value FROM df_sys_name_value_pairs
                            WHERE definition_id={$result_row['definition_id']}";
                            $results_values_query = DF_DBFuncs::sqlGetResults($query);
                            if ($results_values_query["boolean"] == true)
                            {
                                // create a copy of the array
                                $definition->values = array();
                                foreach($results_values_query["data"] as $name_value_data)
                                {
                                    $definition->values[] = $name_value_data;
                                }
                            }
                        }
                $definitions[$definition->system_name] = $definition;
            }
        }
        //print_r($definitions);

        return $definitions;
    }


    //get definition in order to buld search form (definitions without values)
     public function get_SearchDetailsDefinitions($sysTableName,$formType,$randomNumber,$formName=0)
    {
        return $this->get_AllDefinitions($sysTableName,$formType,$randomNumber,$formName,"");
    }

    //get definitions(only names, not values) in order to buld Update group part
    public function get_UpdateGroupDefinitions($sysTableName,$formType,$randomNumber,$formName=0)
    {
        return $this->get_AllDefinitions($sysTableName,$formType,$randomNumber,$formName,
                " AND df_sys_definitions.appear_on_group=1 ");
    }

    //get definitions in order to build insert form (definitions without values)
    public function get_InsertDefinitions($sysTableName,$formType,$randomNumber,$formName=0)
    {
        return $this->get_AllDefinitions($sysTableName,$formType,$randomNumber,$formName,"");
    }

     //get definition in order to buld Update form (definitions with values)
    // retrieves query from the session and runs it
    public function get_UpdateDefinitions($sysTableName,$formType,$randomNumber,$formName="")
    {
		$entityDetails=$this->getSysDetails($sysTableName);
		$rownums=$GLOBALS["pageSize"];
		if(is_numeric($entityDetails['groupnumtxt']))
		{
			$rownums=(int)$entityDetails['groupnumtxt'];
		}
        $query = DF_Session::LoadSession("".$randomNumber."_query");
        $dfcrypt = new DF_Crypt(DF_DBFuncs::getDecryptionKey());
        $query=$dfcrypt->Decrypt($query);

        // adding to query limit of page num and index num
        $index = 0;
        $indexOne =  DF_Session::LoadSession("".$randomNumber."_pageNum") * $rownums + DF_Session::LoadSession("".$randomNumber."_indexNum");
        $indexGroup = DF_Session::LoadSession("".$randomNumber."_pageNum") *$rownums;
        // if it is single update we take 1 raw from the database
        // if it is group update we take $GLOBALS["pageSize"] raws
        $raws_number = ($formType=='UpdateDetails') ? '1' : $rownums;

                 $orderBy=DF_Session::LoadSession("".$randomNumber."_orderby");
                 $orderType =DF_Session::LoadSession("".$randomNumber."_ordertype"); 
        if($orderBy!=null && $orderType!=null )
        {
            
            $query.="order by ";
            $countOrderBy = 0;
            $orderBys= split(",",$orderBy);
            $orderTypes= split(",",$orderType);
            foreach($orderBys as $orderByC)
            {
                if($countOrderBy>0)
                {
                              $query .=",";
                }
                $defsysname=self::getdefsysname($orderByC);
                $query .=$defsysname." ".$orderTypes[$countOrderBy];    
                $countOrderBy++;
            }
            
        }

        $limit =  ($formType=='UpdateDetails') ? " LIMIT $indexOne, $raws_number" :  " LIMIT $indexGroup, $raws_number" ;
        if($formType=="Export")
        {
            $limit="";
        }
        $query .= $limit;
        
        $results = DF_DBFuncs::sqlGetResults($query);
        if ( $results["boolean"] == true)
        {
            if ($results["data"] != null)
            {
                $myformType=$formType;
                if($formType=="Export")
                {
                    $myformType="UpdateDetails";
                }
                $definitions = self::get_AllDefinitions($sysTableName,$myformType,$randomNumber,"","");
               
                
                // we take the names of the definitions
                $definitions_system_names = array_keys($results["data"][0]);
                
                foreach ($definitions_system_names as $definition_system_name)
                {
                     
                    // we store all the values of this definition in array
                    $definitions[$definition_system_name]->value = array();
                }
                foreach ($results["data"] as $result_row)
                {
                    foreach ($definitions_system_names as $definition_system_name)
                    {
                        if (strcmp($definition_system_name, "temp_column") != 0)
                        {
                            
                            $definitions[$definition_system_name]->value[] = $result_row[$definition_system_name];
                        }
                    }
                }
            }
            return $definitions;
        }
        return DF_DBFuncs::translate("Problem with database - can/'t retreive definitions values");
    }

    public function getdefsysname($defid)
    {
        $result="id";
        $query="select * from df_sys_definitions where definition_id=".$defid;
         $results = DF_DBFuncs::sqlGetResults($query);
        if ( $results["boolean"] == true)
        {
            if ($results["data"] != null)
            {
                $result=$results["data"][0]["system_name"];
            }
        }
        return $result;
    }


    ///Entities:
    public function GetEntityDetails($entity_id)
    {

            /*
            $query = "SELECT  * FROM df_sys_entities where entity_id=".$entity_id;

            $results = DF_DBFuncs::sqlGetResults($query);

            if ( $results["boolean"] == true)
            {

                $row = $results["data"][0];
                return $row;

            }
            return array();
            */
    }

	public function getSysDetails($sysName)
	{
		$query = "SELECT  * FROM df_sys_entities where sysname='".$sysName."'";

		$results = DF_DBFuncs::sqlGetResults($query);

		if ( $results["boolean"] == true)
		{

			$row = $results["data"][0];
			return $row;

		}
		return array();
	}

   
   
   
   public function GetEntityDefinitionHtml($showExtraButtons=true)
    {
        return  $this->GetNewEntityDefinitionHtml($showExtraButtons);
    }
   
   
   /*
    public function GetEntityDefinitionHtmlOLD($showExtraButtons=true)
    {
        $mydatabase = new DF_DataBase();
         $dir = plugin_dir_url(__FILE__);
        
         
        $dirImages=$dir."/images/";

        $force=true;
           
        $mydatabase->start_session_values();

        $disButtons=$this->GetDisabledButtons($showExtraButtons);
        $enButtons=$this->GetEnabledButtons($showExtraButtons);
        $entity_id = DF_Session::LoadSession('entity_id');
        if(!isset($entity_id))
        {
            DF_Session::StoreSession('entity_id',0);
           
           $htmlResult="<form name='defineEntityForm'>
           
          
          
                <table class='DF_datagrid'>
                <tr>
                <td >
                    <label >".DF_DBFuncs::translate("Form settings") ."</label>
                </td>
                </tr>  
                <tr>    
                <td>
                     <div id='descriptionDiv'>
                     <input type='hidden' name='oldsysname' id='oldsysname' value='' />
                     <input type='hidden' name='oldentityid' id='oldentityid' value='' />
                    <table>
                    <tr><td>
                      <label for='entityname'>". DF_DBFuncs::translate("Name")."</label></td>
                            <td><input id='entityname' name='entityname' type='text' value=''  onchange=\"return DF_Entity_SetSystemName(this)\" /></td>
                         </tr>
                           <tr>
                        <td>
                         <label  for='email'>". DF_DBFuncs::translate("Receive email on insert")."</label></td>

                                    <td ><input id='email' name='email' type='text' value=''  /></td>  </tr>
                         <tr>
                        <td>
                         <label  for='groupnumtxt'>". DF_DBFuncs::translate("Num of rows in group")."</label></td>

                                    <td ><input id='groupnumtxt' name='groupnumtxt' type='text' value='5'  /></td>
                                    </tr> 
                         
                        <tr><td colspan='2'> ";
                           $htmlResult.='  <label class="Df-table-category">'. DF_DBFuncs::translate("Advanced settings").'</label><input type="button" id="ShowEntityDetails" style="background:url(\''. $dirImages.'ZoomIn.png\') no-repeat; width:26px; height:26px; background-color: transparent; border: transparent;  "  onClick="return DF_Entity_ShowEntity(\'EntityExtraDetails\',this)" />';
                         
                      $htmlResult.= "  <div id='EntityExtraDetails'  style='visibility: hidden;position: relative;height:0;'><table>  
                        <tr>
                            <td><label for='sysname'>". DF_DBFuncs::translate("System Name")."</label></td>
                            <td><input id='sysname' name='sysname' type='text' value='' onchange=\"return DF_Entity_CleanField(this)\"  /></td>
                        </tr>
                        <tr>
                        <td>
                          <label  for='description'>". DF_DBFuncs::translate("Description")."</label></td>

                                    <td ><textarea id='description'  name='description' rows='4' cols='30'></textarea></td>
                                    </tr>

                       


						<tr>
						<td>
                         <label  for='inserttxt'>". DF_DBFuncs::translate("Insert button text")."</label></td>

                                    <td ><input id='inserttxt' name='inserttxt' type='text' value='". DF_DBFuncs::translate("Save")."'  /></td>
                                    </tr>

							<tr><td>
                         <label  for='searchtxt'>". DF_DBFuncs::translate("Search button text")."</label></td>

                                    <td ><input id='searchtxt' name='searchtxt' type='text' value='". DF_DBFuncs::translate("Search")."'  /></td>
                                    </tr>
                        <tr>
							<td>
                         <label  for='updatetxt'>". DF_DBFuncs::translate("Update button text")."</label></td>

                                    <td ><input id='updatetxt' name='updatetxt' type='text' value='". DF_DBFuncs::translate("Save changes")."'  /></td>
                                    </tr>
                        <tr>

						<td>
						 <label  for='selecttxt'>". DF_DBFuncs::translate("Select button text")."</label></td>

                                    <td ><input id='selecttxt' name='selecttxt' type='text' value='". DF_DBFuncs::translate("Select")."'  /></td>
                                    </tr>
                        
                        <tr>
						<td>
						 <label  for='successInserttxt'>". DF_DBFuncs::translate("Success Insert Message")."</label></td>

                                    <td ><input id='successInserttxt' name='successInserttxt' type='text' value='". DF_DBFuncs::translate("Saved")."'  /></td>
                                    </tr>
                        <tr>
						<td>
						 <label  for='failInserttxt'>". DF_DBFuncs::translate("Fail Insert Message")."</label></td>

                                    <td ><input id='failInserttxt' name='failInserttxt' type='text' value='". DF_DBFuncs::translate("Failed on save")."'  /></td>
                                    </tr>
                        <tr>
						<td>
						 <label  for='successUpdatext'>". DF_DBFuncs::translate("Success Update Message")."</label></td>

                                    <td ><input id='successUpdatetxt' name='successUpdatetxt' type='text' value='". DF_DBFuncs::translate("Updated")."'  /></td>
                                    </tr>
                        <tr>
                        	<td>
						 <label  for='failUpdatext'>". DF_DBFuncs::translate("Fail Update Message")."</label></td>

                                    <td ><input id='failUpdatetxt' name='failUpdatetxt' type='text' value='". DF_DBFuncs::translate("Failed on Save")."'  /></td>
                                    </tr>
                           <tr>
                            <td>
                         <label  for='norowstxt'>". DF_DBFuncs::translate("No Results Message")."</label></td>

                                    <td ><input id='norowstxt' name='norowstxt' type='text' value='". DF_DBFuncs::translate("No results")."'  /></td>
                                    </tr> 
                             <tr><td>
                                 <input id='inserttxt_hidden' name='inserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Save")."'  />
                                 <input id='searchtxt_hidden' name='searchtxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Search")."'  />
                                 <input id='updatetxt_hidden' name='updatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Save changes")."'  />
                                 <input id='selecttxt_hidden' name='selecttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Select")."'  />
                                 <input id='successInserttxt_hidden' name='successInserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Saved")."'  />
                                 <input id='failInserttxt_hidden' name='failInserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Failed on save")."'  />
                                 <input id='successUpdatetxt_hidden' name='successUpdatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Updated")."'  />
                                 <input id='failUpdatetxt_hidden' name='failUpdatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Failed on Save")."'  />
                                  <input id='norowstxt_hidden' name='norowstxt_hidden' type='hidden' value='". DF_DBFuncs::translate("No results")."'  />
                                 
                             </td></tr>
                             
                             </table></div></td></tr>
                        </table>
                         </div>
                </td>
                </tr>
                <tr>
                <td>
                                <input type='button' id='EditButton'  disabled='disabled' class='button-primary'  onClick='return DF_Entity_Define_New_Entity(\"editEntity\")' value='". DF_DBFuncs::translate("Save")."' />
                                ".$disButtons."
                </td>
                </tr>
                
                </table>
 	        </form>";
            
              $htmlResult.="<img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_Entity_GetHtmlHelps()\" />";
        }
        else
        {
            
            $query = "SELECT  * FROM df_sys_entities where entity_id=".DF_Session::LoadSession('entity_id');


            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $row = $results["data"][0];
            }


            
                if(count($row)>0)
                {
                $htmlResult="<form name='defineEntityForm'>
                <table class='DF_datagrid'>
                <tr>
                <td >
                    <label >".DF_DBFuncs::translate("Form settings") ."</label>
                </td>
                </tr>
                <tr>
                <td>
                     <div id='descriptionDiv'>
                     <input type='hidden' name='oldsysname' id='oldsysname' value='". $row['sysname'] ."' />
                     <input type='hidden' name='oldentityid' id='oldentityid' value='". $row['entity_id'] ."' />
                    <table>
                    <tr><td>
                      <label for='entityname'>". DF_DBFuncs::translate("Name")."</label></td>
                            <td><input id='entityname' name='entityname' type='text' value='".  $row['name'] ."' onchange=\"return DF_Entity_SetSystemName(this)\" /></td>
                         </tr>
                         
                          <tr>
                         <td>
                         <label  for='email'>". DF_DBFuncs::translate("Receive email on insert")."</label></td>

                                    <td ><input id='email' name='email' type='text' value='".$row['email']."'  /></td>
                                    </tr>
                            <tr>
                        <td>
                         <label  for='groupnumtxt'>". DF_DBFuncs::translate("Num of rows in group")."</label></td>

                                    <td ><input id='groupnumtxt' name='groupnumtxt' type='text' value='".$row['groupnumtxt']."'  /></td>
                                    </tr>        
                         <tr><td colspan='2'> ";
                           $htmlResult.='  <label class="Df-table-category" >'. DF_DBFuncs::translate("Advanced settings").'</label><input type="button" id="ShowEntityDetails" style="background:url(\''. $dirImages.'ZoomIn.png\') no-repeat; width:26px; height:26px; background-color: transparent; border: transparent;  "  onClick="return DF_Entity_ShowEntity(\'EntityExtraDetails\',this)" />';
                         
                      $htmlResult.= "  <div id='EntityExtraDetails'  style='visibility: hidden;position: relative;height:0;'><table>
                        <tr>
                            <td><label for='sysname'>". DF_DBFuncs::translate("System Name")."</label></td>
                            <td><input id='sysname' name='sysname' type='text' value='". $row['sysname'] ."'  onchange=\"return DF_Entity_CleanField(this)\" disabled='true'/></td>
                        </tr>
                        <tr>
                        <td>
                          <label  for='description'>". DF_DBFuncs::translate("Description")."</label></td>

                                    <td ><textarea id='description'  name='description' rows='4' cols='30'>".$row['description']."</textarea></td>
                                    </tr>
                       
                        <tr>
                        	<td>
                         <label  for='inserttxt'>". DF_DBFuncs::translate("Insert button text")."</label></td>

                                    <td ><input id='inserttxt' name='inserttxt' type='text' value='".$row['inserttxt']."'  /></td>

                        <tr>
							<td>
                         <label  for='searchtxt'>". DF_DBFuncs::translate("Search button text")."</label></td>

                                    <td ><input id='searchtxt' name='searchtxt' type='text' value='".$row['searchtxt']."'  /></td>
                                    </tr>
                        <tr>
							<td>
                         <label  for='updatetxt'>". DF_DBFuncs::translate("Update button text")."</label></td>

                                    <td ><input id='updatetxt' name='updatetxt' type='text' value='".$row['updatetxt']."'  /></td>
                                    </tr>
                        <tr>
                        <td>
						 <label  for='selecttxt'>". DF_DBFuncs::translate("Select button text")."</label></td>

                                    <td ><input id='selecttxt' name='selecttxt' type='text' value='".$row['selecttxt']."'  /></td>
                                    </tr>
                        
                        <tr>
						<td>
						 <label  for='successInserttxt'>". DF_DBFuncs::translate("Success Insert Message")."</label></td>

                                    <td ><input id='successInserttxt' name='successInserttxt' type='text' value='".$row['successInserttxt']."'  /></td>
                                    </tr>
                        <tr>
						<td>
						 <label  for='failInserttxt'>". DF_DBFuncs::translate("Fail Insert Message")."</label></td>

                                    <td ><input id='failInserttxt' name='failInserttxt' type='text' value='".$row['failInserttxt']."'  /></td>
                                    </tr>
                        <tr>
						<td>
						 <label  for='successUpdatext'>". DF_DBFuncs::translate("Success Update Message")."</label></td>

                                    <td ><input id='successUpdatetxt' name='successUpdatetxt' type='text' value='".$row['successUpdatetxt']."'  /></td>
                                    </tr>
                        <tr>
                        	<td>
						 <label  for='failUpdatext'>". DF_DBFuncs::translate("Fail Update Message")."</label></td>

                                    <td ><input id='failUpdatetxt' name='failUpdatetxt' type='text' value='".$row['failUpdatetxt']."'  /></td>
                                    </tr>
                        <tr>
                        	<td>
						 <label  for='norowstxt'>". DF_DBFuncs::translate("No Results Message")."</label></td>

                                    <td ><input id='norowstxt' name='norowstxt' type='text' value='".$row['norowstxt']."'  /></td>
                                    </tr>
                                      <tr><td>
                                 <input id='inserttxt_hidden' name='inserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Save")."'  />
                                 <input id='searchtxt_hidden' name='searchtxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Search")."'  />
                                 <input id='updatetxt_hidden' name='updatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Save changes")."'  />
                                 <input id='selecttxt_hidden' name='selecttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Select")."'  />
                                 <input id='successInserttxt_hidden' name='successInserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("saved")."'  />
                                 <input id='failInserttxt_hidden' name='failInserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Failed on save")."'  />
                                 <input id='successUpdatetxt_hidden' name='successUpdatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Updated")."'  />
                                 <input id='failUpdatetxt_hidden' name='failUpdatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Failed on save")."'  />
                                  <input id='norowstxt_hidden' name='norowstxt_hidden' type='hidden' value='". DF_DBFuncs::translate("No results")."'  />
                                 
                             </td></tr>
                         </table></div></td></tr>
                        </table>
                         </div>
                </td>
                </tr>
                <tr>
                <td>
                                <input type='button' id='EditButton'  class='button-primary' onClick='return DF_Entity_Define_New_Entity(\"editEntity\")' value='". DF_DBFuncs::translate("Save")."' />
                                ".$enButtons."
                </td>
                </tr>
                </table>


 	        </form>";
             $htmlResult.="<img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_Entity_GetHtmlHelps()\" />";
                }
                else{
                    $htmlResult=DF_DBFuncs::translate(" No Results  ");
                    }
        }
    return $htmlResult;
    }

     */
   
    
    public function CreateEmptyEntityResults()
    {
        $results = array();
          $query="SHOW COLUMNS FROM df_sys_entities";
           $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $rows = $results["data"];
                foreach($rows as $row)
                {
                  $result[$row["Field"]]=""; 
                  
                }
                
            }
            return $results;
    }
    
    
   public function GetTextInputRow($title, $fieldName,$row)
   {
       $res=" <tr>
                            <td>
                         <label  for='".$fieldName."'>". DF_DBFuncs::translate($title)."</label></td>

                                    <td ><input id='".$fieldName."' name='".$fieldName."' type='text' value='".$row[$fieldName]."'  /></td>
                         </tr>";
       return $res;
   }
   
   
   public function GetTextAreaInputRow($title, $fieldName,$row)
   {
       $res=" <tr>
                            <td>
                         <label  for='".$fieldName."'>". DF_DBFuncs::translate($title)."</label></td>

                                    <td ><textarea id='".$fieldName."' name='".$fieldName."' rows='4' cols='30'  >".$row[$fieldName]."</textarea></td>
                         </tr>";
       return $res;
   }
   
     
    public function GetDropDownInputRow($title, $fieldName,$row, $values)
   {
       
       $options = "";
       
       $current=0;
       if(count($values)>0)
       {
           
           foreach($values as $key=>$value)
           {
               $selected="";
               if ($row[$fieldName]==$key || ($row[$fieldName]=="" && $current==0) )
                            {
                                $selected="selected='selected'";
                            }
               $options.=" <option value='".$key."' ".$selected." >".DF_DBFuncs::translate($value)."</option>   ";
               $current++;
           }
       }
       
       else
       {
           $options = "<option value='' ".$selected." >-</option> ";
       }
       $res=" <tr>
                            <td>
                         <label  for='".$fieldName."'>". DF_DBFuncs::translate($title)."</label></td>

                                    <td >
                                     <select id='".$fieldName."' name='".$fieldName."'>
                                     ".$options."
                        </select>
                       
                         </tr>";
       return $res;
   }
   
    public function GetDropDownInputRegular($title, $fieldName,$row, $values)
   {
       
       $options = "";
       if(count($values)>0)
       {
           foreach($values as $key=>$value)
           {
               $selected="";
               if ($row[$fieldName]==$key)
                            {
                                $selected="selected='selected'";
                            }
               $options.=" <option value='".$key."' ".$selected." >".DF_DBFuncs::translate($value)."</option>   ";
           }
       }
       else
       {
           $options = "<option value='' ".$selected." >-</option> ";
       }
       $res=" <br />
                         <label  for='".$fieldName."'>". DF_DBFuncs::translate($title)."</label></td>

                                    <td >
                                     <select id='".$fieldName."' name='".$fieldName."'>
                                     ".$options."
                        </select>
                       
                         ";
       return $res;
   }
   
   
   public function GetCurrentFields($sysName)
   {
       $fieldNames = array();
           $definitions=$this->getDefinitions($sysName) ;
          
           if($definitions["boolean"] == true && count($definitions["data"])>0)
           {
               foreach($definitions["data"] as $definition)
               {
                   
                   $fieldNames[$definition["system_name"]]=$definition["name"];
               }
           }
           return $fieldNames;
   }
   
   
    public function GetEntityMainDefinitionsHtml($row)
    {
        $htmlResult="";
          $dir = plugin_dir_url(__FILE__);
        
        $FieldsOfEntity = array();
        $FieldsOfEntity[""]="-";
        
        $ValidateValues=array();
       $ValidateValues["0"]="False" ;
        $ValidateValues["1"]="True";
        
        
        $ViewStyles=array();
        $ViewStyles["up"] = "Upper";
        $ViewStyles["internal"]="In the row";
        
        $disable=" ";
         if(isset($row['entity_id'] ) && $row['entity_id'] !="")
         {
            
             $disable=" disabled='true' ";
             $FieldsOfEntity=$this->GetCurrentFields($row['sysname']);
             $FieldsOfEntity[""]="-";
         }
        $dirImages=$dir."/images/";
       $htmlResult.="  <div id='descriptionDiv'>
                     <input type='hidden' name='oldsysname' id='oldsysname' value='". $row['sysname'] ."' />
                     <input type='hidden' name='oldentityid' id='oldentityid' value='". $row['entity_id'] ."' />
                    <table class='DF_datagrid'>
                    <tr><td>
                      <label for='entityname'>". DF_DBFuncs::translate("Name")."</label></td>
                            <td><input id='entityname' name='entityname' type='text' value='".  $row['name'] ."' onchange=\"return DF_Entity_SetSystemName(this)\" /></td>
                         </tr>
                         
                          <tr>
                         <td>
                         <label  for='email'>". DF_DBFuncs::translate("Receive email on insert")."</label></td>

                                    <td ><input id='email' name='email' type='text' value='".$row['email']."'  /></td>
                                    </tr>
                            <tr>
                        <td>
                         <label  for='groupnumtxt'>". DF_DBFuncs::translate("Num of rows in group")."</label></td>

                                    <td ><input id='groupnumtxt' name='groupnumtxt' type='text' value='".$row['groupnumtxt']."'  /></td>
                                    </tr>        
                         <tr><td colspan='2'> ";
                           $htmlResult.='  <label class="Df-table-category" >'. DF_DBFuncs::translate("Advanced settings").'</label><input type="button" id="ShowEntityDetails" style="background:url(\''. $dirImages.'ZoomIn.png\') no-repeat; width:26px; height:26px; background-color: transparent; border: transparent;  "  onClick="return DF_Entity_ShowEntity(\'EntityExtraDetails\',this)" />';
                         
                      $htmlResult.= "  <div id='EntityExtraDetails'  style='visibility: hidden;position: relative;height:0;'><table>
                        <tr>
                            <td><label for='sysname'>". DF_DBFuncs::translate("System Name")."</label></td>
                            <td><input id='sysname' name='sysname' type='text' value='". $row['sysname'] ."'  onchange=\"return DF_Entity_CleanField(this)\" ".$disable."/></td>
                        </tr>
                        <tr>
                        <td>
                          <label  for='description'>". DF_DBFuncs::translate("Description")."</label></td>

                                    <td ><textarea id='description'  name='description' rows='4' cols='30'>".$row['description']."</textarea></td>
                                    </tr>
                       
                        <tr>
                     
                    
                       ".$this->GetTextInputRow("Insert button text","inserttxt",$row)."
                        ".$this->GetTextInputRow("Search button text","searchtxt",$row)."
                         ".$this->GetTextInputRow("Update button text","updatetxt",$row)."
                         ".$this->GetTextInputRow("Select button text","selecttxt",$row)."
                         ".$this->GetTextInputRow("Success Insert Message","successInserttxt",$row)."
                         ".$this->GetTextInputRow("Fail Insert Message","failInserttxt",$row)."
                         ".$this->GetTextInputRow("Success Update Message","successUpdatetxt",$row)."
                         ".$this->GetTextInputRow("Fail Update Message","failUpdatetxt",$row)."
                         ".$this->GetTextInputRow("No Results Message","norowstxt",$row)."
                         
                        
                         ".$this->GetDropDownInputRow("Send Mail To Adder","sendMailToAdder",$row,$ValidateValues)."
                         ".$this->GetDropDownInputRow("Email Field","emailField",$row,$FieldsOfEntity)."
                         ".$this->GetTextInputRow("Email Title","emailTitle",$row)."
                         ".$this->GetTextAreaInputRow("Email Text","emailText",$row)."
                         ".$this->GetDropDownInputRow("Validate Action","validateAction",$row,$ValidateValues)."
                          ".$this->GetTextInputRow("Div Style","curentStyle",$row)."
                         ".$this->GetTextInputRow("Div Class Name","curentClassName",$row)."
                         ".$this->GetDropDownInputRow("Details View Show Type","detailViewShowType",$row,$ViewStyles)."
                         ".$this->GetTextInputRow("Email Validation Error","emailValidationError",$row)."
                         ".$this->GetTextInputRow("Decimal Validation Error","decimalValidationError",$row)."
                         ".$this->GetTextInputRow("Number Validation Error","numberValidationError",$row)."
                         ".$this->GetTextInputRow("Must Field Validation Error","mustFieldValidationError",$row)."
                         ".$this->GetDropDownInputRow("Enable Captcha","enableCaptcha",$row,$ValidateValues)."
                         
                         
                                      <tr><td>
                                 <input id='inserttxt_hidden' name='inserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Save")."'  />
                                 <input id='searchtxt_hidden' name='searchtxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Search")."'  />
                                 <input id='updatetxt_hidden' name='updatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Save changes")."'  />
                                 <input id='selecttxt_hidden' name='selecttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Select")."'  />
                                 <input id='successInserttxt_hidden' name='successInserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("saved")."'  />
                                 <input id='failInserttxt_hidden' name='failInserttxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Failed on save")."'  />
                                 <input id='successUpdatetxt_hidden' name='successUpdatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Updated")."'  />
                                 <input id='failUpdatetxt_hidden' name='failUpdatetxt_hidden' type='hidden' value='". DF_DBFuncs::translate("Failed on save")."'  />
                                 <input id='norowstxt_hidden' name='norowstxt_hidden' type='hidden' value='". DF_DBFuncs::translate("No results")."'  />
                                 
                                 <input id='emailTitle_hidden' name='emailTitle_hidden' type='hidden' value='". DF_DBFuncs::translate("New row inserted")."'  />
                                 <input id='emailValidationError_hidden' name='emailValidationError_hidden' type='hidden' value='". DF_DBFuncs::translate("Wrong Email Input")."'  />
                                 <input id='decimalValidationError_hidden' name='decimalValidationError_hidden' type='hidden' value='". DF_DBFuncs::translate("Wrong Decimal Input")."'  />
                                 <input id='numberValidationError_hidden' name='numberValidationError_hidden' type='hidden' value='". DF_DBFuncs::translate("Wrong Number Input")."'  />
                                 <input id='mustFieldValidationError_hidden' name='mustFieldValidationError_hidden' type='hidden' value='". DF_DBFuncs::translate("Field Must Be Set")."'  />
                                 
                                 
                                 <input id='detailViewShowType_hidden' name='detailViewShowType_hidden' type='hidden' value='up'  />
                                 <input id='sendMailToAdder_hidden' name='sendMailToAdder_hidden' type='hidden' value='0'  />
                                 <input id='validateAction_hidden' name='validateAction_hidden' type='hidden' value='0'  />
                                 <input id='enableCaptcha_hidden' name='enableCaptcha_hidden' type='hidden' value='0'  />
                                 <input id='emailField_hidden' name='emailField_hidden' type='hidden' value=''  />
                                 
                             </td></tr>
                         </table></div></td></tr>
                        </table>
                         </div>
                              ";
                         return $htmlResult;
    }
    
    
    
     public function GetNewEntityDefinitionHtml($showExtraButtons=true)
    {
        $mydatabase = new DF_DataBase();
         $dir = plugin_dir_url(__FILE__);
        
         
        $dirImages=$dir."/images/";

        $force=true;
           
        $mydatabase->start_session_values();

        $disButtons=$this->GetDisabledButtons($showExtraButtons);
        $enButtons=$this->GetEnabledButtons($showExtraButtons);
        $entity_id = DF_Session::LoadSession('entity_id');
        $row=array();
        $isTable=false;
       if(isset($entity_id))
       {
        $query = "SELECT  * FROM df_sys_entities where entity_id=".$entity_id;

         
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                    $row = $results["data"];
                if(count($row)>0)
                {
                    
                    $isTable=true;
                      $row = $results["data"][0];
                }
            } 
            
       }
        if(!isset($entity_id) || !$isTable)
        {
            DF_Session::StoreSession('entity_id',0);
           
            $row = $this->CreateEmptyEntityResults();
            $tableDiv=$this->GetEntityMainDefinitionsHtml($row); 
          
            $dir =  plugin_dir_url(__FILE__);
           
            
           $htmlResult="<form name='defineEntityForm'>
           
          
                  <div class='DF_datagrid' >
                <table >
                <thead>
                <tr>
                <th>
                    <label >".DF_DBFuncs::translate("Form settings") ."</label>
                </th>
                </tr> 
                </thead> 
                <tbody>
                <tr>    
                <td>
                       ".$tableDiv."    <img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_Entity_loadDefaults()\" />
                </td>
                </tr>
                <tr>
                <td>
                                <input type='button' id='EditButton'  disabled='disabled' class='button-primary'  onClick='return DF_Entity_Define_New_Entity(\"editEntity\")' value='". DF_DBFuncs::translate("Save")."' />
                                ".$disButtons."
                </td>
                </tr>
                  </tbody>
                </table>
                </div>
             </form>";
            
              $htmlResult.="<img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_Entity_GetHtmlHelps()\" />";
        }
        else
        {
          


            
               
                    $tableDiv=$this->GetEntityMainDefinitionsHtml($row); 
                $htmlResult="<form name='defineEntityForm'>
                 <div class='DF_datagrid' >
                <table>
                <thead>
               
                <tr>
                <th >
                    <label >".DF_DBFuncs::translate("Form settings") ."</label>
                </th>
                </tr>
                 <thead>
                 <tbody>
                <tr>
                <td>
                                   ".$tableDiv."
                </td>
                </tr>
                <tr>
                <td>
                                <input type='button' id='EditButton'  class='button-primary' onClick='return DF_Entity_Define_New_Entity(\"editEntity\")' value='". DF_DBFuncs::translate("Save")."' />
                                ".$enButtons."
                </td>
                </tr>
                </tbody>
                </table>
                 </div>

             </form>";
             $htmlResult.="<img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_Entity_GetHtmlHelps()\" />";

        }
        
    return $htmlResult;
    }
    
    public function getEntitiesList($inputName)
    {
        return $this->getEntitiesListWithId($inputName,$inputName);
    }
    
     public function getEntitiesListWithId($inputName,$inputId)
    {
        $result="<select name='".$inputName."' id='".$inputId."'>";
         $query = "SELECT * from df_sys_entities order by entity_id";
            $results = DF_DBFuncs::sqlGetResults($query);
             if ( $results["boolean"] == true)
            {
                for($i=0;$i<count($results['data']);$i++)
                {
                    $row=$results['data'][$i];
                    $result.=" <option value='".$row['sysname']."'>".$row['name']."</option>";
                }
            }
             $result.=" </select>";
             return $result;
    }
    
     public function getEntitiesListWithIdSelected($inputName,$inputId, $selectedValue,$id)
    {
        $result="<label for='".$id."'>".DF_DBFuncs::translate( 'Form Name' ) ."</label> <select name='".$inputName."' id='".$inputId."'>";
         $query = "SELECT * from df_sys_entities order by entity_id";
            $results = DF_DBFuncs::sqlGetResults($query);
             if ( $results["boolean"] == true)
            {
                for($i=0;$i<count($results['data']);$i++)
                {
                    $row=$results['data'][$i];
                    $extra="";
                    if($row['sysname']==$selectedValue)
                    {
                              $extra = " selected=\"selected\"";
                    }
                    $result.=" <option ".$extra." value='".$row['sysname']."'>".$row['name']."</option>";
                }
            }
             $result.=" </select>";
             return $result;
    }
    
    
    public function getTypesWithNameIdSelected($inputName,$inputId, $selectedValue, $id)
    {
          $result.="<label for='".$id."'>".DF_DBFuncs::translate( 'View Type' )."</label><select name=\"".$inputName."\" id=\"".$inputId."\"> ";
           $options = array("Insert","Search","Search_ReadOnly", "DefaultDisplay");
           foreach($options as $option)
           {
                $extra ="";
                if($option==$selectedValue)
                    {
                              $extra = " selected=\"selected\"";
                    }
                $result.="<option ".$extra." value=\"".$option."\">". DF_DBFuncs::translate($option)."</option>";   
           }
            
            $result.="  </select>  ";
          return $result;
    }

    public function getEntitiesListRes()
    {
         $query = "SELECT * from df_sys_entities order by entity_id";
         $results = DF_DBFuncs::sqlGetResults($query);

         return $results;
    }

     public function getEntityId($entityName)
    {
         $result=0;
         $query = "SELECT * from df_sys_entities where sysname='".$entityName."'";
          $results = DF_DBFuncs::sqlGetResults($query);
             if ( $results["boolean"] == true && count($results['data'])>0)
            {
                    $row=$results['data'][0];
                    $result=$row['entity_id'];

            }
             return $result;

    }

	public function isInDfEntities($systemName)
	{
		$result = false;
		$query = "SELECT * from df_sys_entities where sysname='".$systemName."'";
		$results = DF_DBFuncs::sqlGetResults($query);
		if ( $results["boolean"] == true && count($results['data'])>0)
		{
			$row=$results['data'][0];
			$result= true;
		}
		return $result;

	}
    //used in DF_DefineEntities in order to print the existing definitions.
    //called also as an ajax request after some actions on the page.
    //For example when new definition added
    public function getDefinitionsOfCurrentEntity($entity_id,$sysTableName)
    {
        $dir = plugin_dir_url(__FILE__);
        
        if(!isset($entity_id) || $entity_id==null)
        {
            return DF_DBFuncs::translate('No Fields');
        }
         
        $dirImages=$dir."/images/";
            $result="";
            $query = "SELECT * from df_sys_definitions where entity_id=".$entity_id ." order by field_order";
            $results = DF_DBFuncs::sqlGetResults($query);
            $extra="";
            if ( $results["boolean"] == true)
            {
                $arrLen=count($results["data"]);

                if ( $arrLen>0)
                {

                      $result.= "<table >";
                      $result.= "<tr><td colspan='6' ><b>". DF_DBFuncs::translate('Fields of selected Form:') ." '{$sysTableName}'</b>";
                       
                      $result.= ' <input type="button" id="ShowEntityDetails" style="background:url(\''. $dirImages.'ZoomIn.png\') no-repeat; width:26px; height:26px; background-color: transparent; border: transparent;  "  onClick="return DF_ShowEntity(\'DefineNewEntityAll\',this)" />';
                      $result.= "</td></tr>";
                      $result.='<tr><td colspan="5">   <div id="DefineNewEntityAll" style="visibility: hidden;position: relative;height:0;">
                        <div id="DefineNewEntity">
             <input type="hidden" id="DF_Entity_path" value="'. DF_DBFuncs::getCurrentPluginURL().'"/>
     

             '. $this->GetEntityDefinitionHtml(false).'
         </div>


          <div id="defineEntityResult" ></div> </div></td></tr>  ';
                      $result.= "<thead><tr>
                      <th ></th>
                      <th ><b><label for='field_order_table'>". DF_DBFuncs::translate('Field Order') ."</b></th>
                      <th ><b><label for='definitionname_table'>". DF_DBFuncs::translate('Field Name') ."</label></b></th>
                      <th ><b><label for='input_type_table'>". DF_DBFuncs::translate('Input Type') ."</label></b></th>
                      <th ></th>

                      <th ></th>
                      </tr></thead><tbody>";
                      $extra=" </tbody></table>";
                    for($i=0;$i< $arrLen;$i++)
                    {
                        $class="";
                        if($i%2 == 1 )
                        {
                           $class=" class='alt' ";
                        }

                        $row = $results["data"][$i];

                        $result.= "<tr $class>
                        <td >";
                        if($row["system_name"]!="id")
                        {
                            $result.="<input type='image' title='".DF_DBFuncs::translate('Delete')."' src='".$dir."/images/Delete.png'  onClick='return DF_dropDefinition(\"".$row["system_name"]."\", ".$row["definition_id"].",".$entity_id.",\"".$sysTableName."\")'/>";
                        }
                         $result.="</td>
                        <td $class>". $row["field_order"] ."</td>
                        <td $class>". $row["name"] ."</td>
                        <td $class>". $row["input_type"] ."</td>
                        <td $class>";
                        if($row["system_name"]!="id")
                        {
                            $result.="<input type='image' title='".DF_DBFuncs::translate('Edit')."' src='".$dir."/images/Edit.png' onClick='return DF_loadDefinitionDetails(".$row["definition_id"].")'  />";
                        }
                        $result.="</td>
                         <td >";
                         $required="";
                             if($row["is_must"]=="1")
                             {
                                      $required="1";
                             }
                            $result.="<input type='image' title='".DF_DBFuncs::translate('Add To Form')."' src='".$dir."/images/Add.png' onClick='return DF_AddDoTinyMCEWithLabelDesigned(\"".$row["system_name"]."\",\"".$row["name"]."\", \"".$required."\")'  />";

                         $result.="</td>";
                         
                         $result.="</tr>";
                        //". DF_DBFuncs::translate('Delete')."
                    }
                }
                else
                {
                        $result.= "<table class='DF_datagrid'>";
                      $result.= "<tr>
                      <td ><b>".DF_DBFuncs::translate("No fields created yet for form:'").$sysTableName. "'</b></td>

                      </tr>";
                      $extra=" </table>";
                }
                $result.= $extra;

            }
            return $result;
    }


    public function GetEntitiesChose()
    {
         $result="";

         $result.=$this->getEntitiesList('entityToLoad');


        $result.="<input type=\"button\" class=\"button-primary\" onClick=\"return DF_Entity_LoadEntity()\" value=\"". DF_DBFuncs::translate("Load Form")."\" />";

        return $result;
    }

    
  
    
   
   public function utf8_urldecode($str) {
        $str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
        return html_entity_decode($str,null,'UTF-8');;
    }
   
    public function GetDisabledButtons($showExtraButtons)
    {
        $result="";
        $showExtraButtonsText=($showExtraButtons ? "true":"false" );
        if($showExtraButtons)
        {
        $result.="<input type='button' id='DeleteButton'  disabled='disabled' class='button-primary' style='visibility:hidden'  onClick='return DF_Entity_Delete_Entity(\"".$showExtraButtonsText."\")' value='". DF_DBFuncs::translate("Delete")."' />";
                $result.="<input type='button' id='AddNewButton'  disabled='disabled' class='button-primary' style='visibility:hidden' onClick='return DF_Entity_emptyFieldsEntity()' value='". DF_DBFuncs::translate("New")."' />";
        }
                $result.="<input type='button' id='DefineEntityButton'   class='button-primary' onClick='return DF_Entity_Define_New_Entity(\"defineEntity\",\"".$showExtraButtonsText."\")' value='". DF_DBFuncs::translate("Save")."' />";
         if($showExtraButtons)
         {
                $result.="<input type='button' id='BackEntityButton'  disabled='disabled'  class='button-primary' style='visibility:hidden' onClick='return DF_Entity_RenewDefineNewEntity(\"".$showExtraButtonsText."\")' value='". DF_DBFuncs::translate("Cancel")."' /> ";
         }
return $result;

    }

    
   
    
    public function GetEnabledButtons($showExtraButtons)
    {
        $result="";
        $showExtraButtonsText=($showExtraButtons ? "true":"false" );
        if($showExtraButtons)
         {
        $result.="<input type='button' id='DeleteButton'  class='button-primary' onClick='return DF_Entity_Delete_Entity(\"".$showExtraButtonsText."\")' value='". DF_DBFuncs::translate("Delete")."' />";
                                $result.="<input type='button' id='AddNewButton'  class='button-primary' onClick='return DF_Entity_emptyFieldsEntity()' value='". DF_DBFuncs::translate("Add")."' />";
         }
         
                                $result.="<input type='button' id='DefineEntityButton' disabled='disabled' style='visibility:hidden' class='button-primary' onClick='return DF_Entity_Define_New_Entity(\"defineEntity\",\"".$showExtraButtonsText."\")' value='". DF_DBFuncs::translate("Save")."' />";
         if($showExtraButtons)
         {
                                $result.="<input type='button' id='BackEntityButton' disabled='disabled' style='visibility:hidden' class='button-primary' onClick='return DF_Entity_RenewDefineNewEntity(\"".$showExtraButtonsText."\")' value='". DF_DBFuncs::translate("Cancel")."' />          ";
         }
return $result;

    }



    ///Form Design:
    //save form design
    public function SaveFormDesign($entity_id,$formType, $formHtml)
    {

        $query = " delete from df_sys_forms where entity_id=".$entity_id." and formType='".$formType."'";
        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
    	{
    		return DF_DBFuncs::translate("didn't success to remove old form
    			mysql error number is ") . $results[error_number];
    	}
         $query = " insert into df_sys_forms set entity_id=".$entity_id.",formType='".$formType."',formHtml='".mysql_real_escape_string($formHtml)."' ";
        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
    	{
    		return DF_DBFuncs::translate(",
    			mysql error number is "). $results[error_number];
    	}
        //Check if all necessary definitions are in the form, if not, give user a warning
        $warning = "";
        $results = $this->getAllDefinitionsForCheck($entity_id);

        if( $results["boolean"]==true &&count($results['data'])>0)
        {
            for($i=0;$i<count($results['data']);$i++)
            {
                $row=$results['data'][$i];
                if($row['system_name']!="id" && ($row['is_id'] || $row['is_must']))
                {
                    $pos=strrpos($formHtml,"[@".$row['system_name']."@]");

                    if($pos==false)
                    {
                        $warning.="</br> <strong><font color='red'>{$row['system_name']}</font></strong>". DF_DBFuncs::translate(" is must and is not in the form.");
                    }
                }
            }
            if($warning!="")
            {
                $warning="<br/> <font color='orange'> ". DF_DBFuncs::translate("Warning! Missing fields in the form ").
                DF_DBFuncs::translate(" System will add the column automatically at the end of the form").$warning."</font>";
            }
        }
        return "<font color='green'>".DF_DBFuncs::translate('success').$warning."</font>";
    }


    public function getFormDesign($entity_id,$formType)
    {
         $result="";
         if(!isset($entity_id) || $entity_id==null)
         {
             return                DF_DBFuncs::translate("No View To Show");
         }
         $query = "SELECT * from df_sys_forms where entity_id=".$entity_id." and formType='".$formType."'";
            $results = DF_DBFuncs::sqlGetResults($query);
             if ( $results["boolean"] == true && count($results['data'])>0)
            {
                    $row=$results['data'][0];
                    $result.=html_entity_decode(stripslashes($row['formHtml']));
            }
             return $this->utf8_urldecode($result);
    }


    /*
    //creates html for showing definitions for current entity
    public function getCategoriesOfCurrentEntity($entity_id)
    {
        $result="";
            $query = "SELECT category_name from df_sys_definitions where entity_id=".$entity_id ." group by category_name";
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $arrLen=count($results["data"]);
                if ( $arrLen>0)
                {

                     for($i=0;$i< $arrLen;$i++)
                    {
                        if($i>0) $result.="|";
                         $row = $results["data"][$i];
                         $result.=$row["category_name"];
                    }
                }
            }
            return $result;
    }
    */





    ///Help Html:
    //creates html for showing definitions for current entity
    public function getHelpHtml($forName)
    {
        $result="";

            $query = "SELECT Text from df_sys_helphtml where forName='".$forName ."' ";
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $arrLen=count($results["data"]);
                if ( $arrLen>0)
                {
                    $row = $results["data"][0];
                    $result.=DF_DBFuncs::translate($row["Text"]);
                }
            }
            return $result;
    }

     //creates htmls for showing in definitions form
    public function getHelpHtmls()
    {
        $result="";

            $query = "SELECT forName,Text from df_sys_helphtml  ";
            $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {

                $arrLen=count($results["data"]);
                if ( $arrLen>0)
                {
                    for($i=0;$i<$arrLen;$i++)
                    {
                        if($i>0) $result.="|";
                        $row = $results["data"][$i];
                        $result.=$row["forName"]."=".DF_DBFuncs::translate($row["Text"]);
                    }
                }
            }

            return $result;
    }




    ///Tables:
    //creates new df_sysname_table at database and updates df_sys_entities table
    //returns result message
    public function delete_table( $sysTableName, $entity_id)
    {

        // Add more columns like: id, add date, change date, creating user, changing user, status: created/updated/approved/deleted
    	$query = "drop TABLE ".$sysTableName;

        //TODO: add default definitions to the definitions table with mark not to remove.
    	$results = DF_DBFuncs::sqlExecute($query);
    	if ( $results["boolean"] != true)
    	{
    		// the table already exists
    		if ($results["error_number"] == 1050)
    		{
    			return DF_DBFuncs::translate("Didn't succeed to delete form "). $sysTableName  ;
    		}
    		return DF_DBFuncs::translate("Didn't succeed to delete form ").$sysTableName . DF_DBFuncs::translate(" ,mysql error number is ") . $results[error_number];
    	}
        //inserts new entity row to df_sys_entities
        $query = "delete from df_sys_entities where entity_id=".$entity_id;

        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {

        	return DF_DBFuncs::translate("Didn't succeed to delete form row from df_sys_entities table");
        }

	     $this->start_session_values(true);
        return DF_DBFuncs::translate("Form "). $sysTableName . DF_DBFuncs::translate(" was deleted.");
    }


    //creates new df_sysname_table at database and updates df_sys_entities table
    //returns result message
    public function create_table($Name, $sysTableName, $Description,$email,$inserttxt,$searchtxt,$updatetxt,$selecttxt,$groupnumtxt
    	,$successInserttxt,$failInserttxt,$successUpdatetxt,$failUpdatetxt,$norowstxt,$emailTitle,$curentStyle,$curentClassName  ,
        $sendMailToAdder,$emailField ,$emailText,$validateAction ,$detailViewShowType ,$emailValidationError,$decimalValidationError,
        $numberValidationError,$mustFieldValidationError,$enableCaptcha)
    {

    	if(!is_numeric($groupnumtxt))
    	{
    		$groupnumtxt=$GLOBALS["pageSize"];
    	}
        $FirstCheck = " show tables like '".$sysTableName . "'";
        $results = DF_DBFuncs::sqlGetResults($FirstCheck);
        if ( $results["boolean"] == true && count($results["data"])>0)
        {
            return "<font color='red'>".DF_DBFuncs::translate(" Form ").$sysTableName.DF_DBFuncs::translate(" already exists  ")."</font>";
        }
        $SecondCheck = " select * from df_sys_entities where sysname='".$sysTableName . "' ";
        $results = DF_DBFuncs::sqlGetResults($SecondCheck);
        if ( $results["boolean"] == true && count($results["data"])>0)
        {
            return "<font color='red'>"." Form " . $sysTableName." already exists in forms  "."</font>";
        }
        // Add more columns like: id, add date, change date, creating user, changing user, status: created/updated/approved/deleted
    	$query = "CREATE TABLE ".$sysTableName."
        (
        id bigint NOT NULL AUTO_INCREMENT,
        updateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        createUser varchar(255)  COLLATE utf8_unicode_ci,
        status varchar(255)  COLLATE utf8_unicode_ci,
        PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";


        //TODO: add default definitions to the definitions table with mark not to remove.
    	$results = DF_DBFuncs::sqlExecute($query);
    	if ( $results["boolean"] != true)
    	{
    		// the table already exists
    		if ($results["error_number"] == 1050)
    		{

    			return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to create new "). $sysTableName . DF_DBFuncs::translate(" form,it already exists")."</font>" ;
    		}

    		return DF_DBFuncs::translate("Didn't succeed to create new").  $sysTableName . DF_DBFuncs::translate(",mysql error number is "). $results[error_number];
    	}
        //inserts new entity row to df_sys_entities
        $query = "INSERT INTO df_sys_entities SET entity_id=null,
        name='".$Name."',
        sysname='".$sysTableName."',
		description='".$Description."',
        inserttxt='".$inserttxt."',
        searchtxt='".$searchtxt."',
        updatetxt='".$updatetxt."',
        selecttxt='".$selecttxt."',
        groupnumtxt='".$groupnumtxt."',
        successInserttxt='".$successInserttxt."',
        failInserttxt='".$failInserttxt."',
        successUpdatetxt='".$successUpdatetxt."',
        failUpdatetxt='".$failUpdatetxt."',
        norowstxt='".$norowstxt."',
        emailTitle='".$emailTitle."',
        curentStyle='".$curentStyle."',
        curentClassName  ='".$curentClassName  ."',
        sendMailToAdder=".$sendMailToAdder.",
        emailField ='".$emailField ."',
        emailText='".$emailText."',
        validateAction =".$validateAction .",
        detailViewShowType ='".$detailViewShowType ."',
        emailValidationError='".$emailValidationError."',
        decimalValidationError='".$decimalValidationError."',
        numberValidationError='".$numberValidationError."',
        enableCaptcha=".$enableCaptcha.",
        mustFieldValidationError='".$mustFieldValidationError."',
        
        email='".$email."';";

        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {

        	return DF_DBFuncs::translate("Didn't succeed to add new form row into forms definitions table");
        }
         $this->start_session_values(true);


           //inserts new entity row to df_sys_entities
        $query = "INSERT INTO df_sys_definitions SET entity_id=".DF_Session::LoadSession("entity_id").",
        name='id',
        system_name='id',
        input_type='hidden',
        type='integer',
        search_type='equal',
        is_must=0,
        is_id=1,
        appear_on_group=1,
        field_order=0
        ";

        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {

        	return DF_DBFuncs::translate("Didn't succeed to add new field row into fields definitions table");
        }
         $this->start_session_values(true);



        return "<font color='green'>".DF_DBFuncs::translate("Form "). $sysTableName . DF_DBFuncs::translate(" was created.")."</font>";
    }



    //creates new df_sysname_table at database and updates df_sys_entities table
    //returns result message
	public function edit_table($Name,  $sysTableName, $Description,$email,$inserttxt,$searchtxt,$updatetxt,$selecttxt,$groupnumtxt
		,$successInserttxt,$failInserttxt,$successUpdatetxt,$failUpdatetxt,$norowstxt,
        $emailTitle,$curentStyle,$curentClassName  ,$sendMailToAdder,$emailField ,$emailText,$validateAction ,$detailViewShowType ,
        $emailValidationError,$decimalValidationError,$numberValidationError,$mustFieldValidationError,$enableCaptcha,
         $oldsysTableName,  $oldsysId)
    {

		if(!is_numeric($groupnumtxt))
		{
			$groupnumtxt=$GLOBALS["pageSize"];
		}
        if($sysTableName!=$oldsysTableName)
        {
            $FirstCheck = " show tables like '".$sysTableName . "'";
            $results = DF_DBFuncs::sqlGetResults($FirstCheck);
            if ( $results["boolean"] == true && count($results["data"])>0)
            {
                return "<font color='red'>".DF_DBFuncs::translate(" Form ").$sysTableName.DF_DBFuncs::translate(" already exists  ")."</font>";
            }
            $SecondCheck = " select * from df_sys_entities where sysname='".$sysTableName . "' ";
            $results = DF_DBFuncs::sqlGetResults($SecondCheck);
            if ( $results["boolean"] == true && count($results["data"])>0)
            {
                return "<font color='red'>".DF_DBFuncs::translate(" Form ") . $sysTableName.DF_DBFuncs::translate(" already exists in entites  ")."</font>";
            }


            // Add more columns like: id, add date, change date, creating user, changing user, status: created/updated/approved/deleted
    	    $query = "Rename TABLE ".$oldsysTableName." to ".$sysTableName."";

            //TODO: add default definitions to the definitions table with mark not to remove.
    	    $results = DF_DBFuncs::sqlExecute($query);
    	    if ( $results["boolean"] != true)
    	    {
    		    // the table already exists
    		    if ($results["error_number"] == 1050)
    		    {

    			    return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to edit "). $sysTableName . DF_DBFuncs::translate("form,is already exists")."</font>" ;
    		    }

    		    return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to edit "). $sysTableName . DF_DBFuncs::translate(" ,mysql error number is "). $results[error_number]."</font>";
    	    }
        }

        //inserts new entity row to df_sys_entities
        $query = "update df_sys_entities SET
        name='".$Name."',
        sysname='".$sysTableName."',
        description='".$Description."',
        inserttxt='".$inserttxt."',
        searchtxt='".$searchtxt."',
        updatetxt='".$updatetxt."',
        selecttxt='".$selecttxt."',
        groupnumtxt='".$groupnumtxt."',
        successInserttxt='".$successInserttxt."',
        failInserttxt='".$failInserttxt."',
        successUpdatetxt='".$successUpdatetxt."',
        failUpdatetxt='".$failUpdatetxt."',
        norowstxt='".$norowstxt."',
        emailTitle='".$emailTitle."',
        curentStyle='".$curentStyle."',
        curentClassName  ='".$curentClassName  ."',
        sendMailToAdder=".$sendMailToAdder.",
        emailField ='".$emailField ."',
        emailText='".$emailText."',
        validateAction =".$validateAction .",
        detailViewShowType ='".$detailViewShowType ."',
        emailValidationError='".$emailValidationError."',
        decimalValidationError='".$decimalValidationError."',
        numberValidationError='".$numberValidationError."',
        enableCaptcha=".$enableCaptcha.",
        mustFieldValidationError='".$mustFieldValidationError."',
        email='".$email."'
        where entity_id=".$oldsysId."
        ";

        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {

        	return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to add new entity row into df_sys_entities table")."</font>";
        }
        $this->start_session_values(true);
        return "<font color='green'>".DF_DBFuncs::translate("Form "). $oldsysTableName .DF_DBFuncs::translate(" was changed.")."</font>";
    }





    ///Sessions
    //loads the last entity added to the database
    public function start_session_values($force=false)
    {
        $sysTableName = DF_Session::LoadSession("sysTableName");
        $sysId = DF_Session::LoadSession("entity_id"); 
        if($sysTableName==null || $force==true || $sysId==0 || $sysId==null)
        {
            $query = "SELECT  entity_id,sysname  FROM df_sys_entities ORDER BY entity_id DESC LIMIT 1";
            $results = DF_DBFuncs::sqlGetResults($query);

            if ( $results["boolean"] == true)
            {
               
                $row = $results["data"][0];
                DF_Session::StoreSession("entity_id",$row["entity_id"]);
                DF_Session::StoreSession("sysTableName",$row["sysname"]);
            }else
            {
                $query = "SELECT  * FROM df_sys_entities";


                $results = DF_DBFuncs::sqlGetResults($query);
                if ( $results["boolean"] == true)
                {
                    $row = $results["data"];
                    if(count($row)>0)
                    {
                      
                         DF_Session::StoreSession("entity_id",$results["data"][0]['entity_id']); 
                         DF_Session::StoreSession("sysTableName",$results["data"][0]['sysname']); 
                    }
                    else
                    {
                       
                     DF_Session::StoreSession("entity_id",NULL);
                     DF_Session::StoreSession("sysTableName",NULL);
                    }
                }
                else
                {
                   
                    DF_Session::StoreSession("entity_id",NULL);
                    DF_Session::StoreSession("sysTableName",NULL);
                } 
               
            }
        }

    }


    public function start_session_values_by_id($sysname)
    {

        $query = "SELECT  entity_id,sysname  FROM df_sys_entities where sysname='".$sysname."'";
        $results = DF_DBFuncs::sqlGetResults($query);

        if ( $results["boolean"] == true)
        {

            $row = $results["data"][0];
              DF_Session::StoreSession("entity_id",$row["entity_id"]);
                DF_Session::StoreSession("sysTableName",$row["sysname"]);
         

        }else
        {
            die(DF_DBFuncs::translate("Retreiving last entity_id from database was not successfull"));
        }

    }




    ///Rows:
    public function deleteRow($sysTableName,$id)
    {
        $query = "delete from ".$sysTableName." where id=".$id;
        $results = DF_DBFuncs::sqlExecute($query);
        if ( $results["boolean"] != true)
        {
            return "<font color='red'>".DF_DBFuncs::translate("Didn't succeed to delete from "). $sysTableName . DF_DBFuncs::translate(" ,mysql error number is "). $results[error_number]."</font>";
        }
        return "<font color='green'>".DF_DBFuncs::translate("Deleted successefuly")."</font>";
    }

    public function getNumOfPages($sysTableName,$formType,$randomNumber,$formName="")
    {
        $result=0;

        $entityDetails=$this->getSysDetails($sysTableName);
        $rownums=$GLOBALS["pageSize"];
        
        DF_DBFuncs::WriteError($entityDetails['groupnumtxt'] ." is numeric:" .is_numeric($entityDetails['groupnumtxt']) );
        if(is_numeric($entityDetails['groupnumtxt']))
        {
            $rownums=(int)$entityDetails['groupnumtxt'];
            
        }

        
    	$query = DF_Session::LoadSession("".$randomNumber."_query");
         $dfcrypt = new DF_Crypt(DF_DBFuncs::getDecryptionKey());
        $query=$dfcrypt->Decrypt($query);
    	$queryCheck = "select count(*) as cnt from (".$query.") as A";
        $pageSize = $rownums;
        $results = DF_DBFuncs::sqlGetResults($queryCheck);
    	if ( $results["boolean"] == true)
    	{
    		if ($results["data"] != null)
    		{
                $result= $results['data'][0]['cnt'];
            }
        }
        $extra = 0;
        $extra = ($result % $pageSize == 0) ? 0 : 1;
        $result=intval(($result / $pageSize))+$extra;

        return $result;

    }

    
    private function updateTableWithNewField($fieldName, $fieldType, $tableName, $defaultValue="No Value", $isNumericValue=false)
    {
        $query = " show columns from ".$tableName." where Field='".$fieldName."'";
        $results=DF_DBFuncs::sqlGetResults($query);
        if($results['boolean']==false || count($results['data'])==0)
        {
            $query="ALTER TABLE `".$tableName."` ADD
              `".$fieldName."` ".$fieldType."
              ";

            $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                die(DF_DBFuncs::Translate("Couldn't alter table df_sys_entities"));
            }
        }
        
        if($defaultValue!="No Value")
        {
            $valueIs= $defaultValue;
            if(!$isNumericValue)
               {
                    $valueIs="'".DF_DBFuncs::Translate($defaultValue)."'";
               }
            $query="update `".$tableName."` set
                  `".$fieldName."` =".$valueIs." where `".$fieldName."` is null or `".$fieldName."`=''
                  ";

            $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                die(DF_DBFuncs::Translate("Couldn't alter table df_sys_entities"));
            }
        }
    }

    private function addDefinitionsFields()
    {
         
         $this->updateTableWithNewField("helpTxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_definitions");
          $this->updateTableWithNewField("classTxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_definitions");
          $this->updateTableWithNewField("styleTxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_definitions");
          $this->updateTableWithNewField("defaultValue","varchar(100) COLLATE utf8_unicode_ci","df_sys_definitions");
          $this->updateTableWithNewField("isFilterable","bit(1) NOT NULL","df_sys_definitions","0",true);
          $this->updateTableWithNewField("filterType","varchar(100) COLLATE utf8_unicode_ci","df_sys_definitions");
          $this->updateTableWithNewField("orderType","varchar(100) COLLATE utf8_unicode_ci","df_sys_definitions");
    }

	private function createEntityRows()
	{
        $this->updateTableWithNewField("email","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities")  ;
		$this->updateTableWithNewField("inserttxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Save");
		$this->updateTableWithNewField("searchtxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Search");
        $this->updateTableWithNewField("updatetxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Save changes");
        $this->updateTableWithNewField("selecttxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Show details");
        $this->updateTableWithNewField("groupnumtxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","5");
        $this->updateTableWithNewField("successInserttxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Details saved");
        $this->updateTableWithNewField("failInserttxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Fail to save");
        $this->updateTableWithNewField("successUpdatetxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Changes saved");
        $this->updateTableWithNewField("failUpdatetxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Failed to save");
        $this->updateTableWithNewField("norowstxt","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","No results");
        
        $this->updateTableWithNewField("emailTitle","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","New row inserted");
        $this->updateTableWithNewField("curentStyle","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities");
        $this->updateTableWithNewField("curentClassName","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities");
        $this->updateTableWithNewField("sendMailToAdder","bit(1) NOT NULL","df_sys_entities","0",true);
        $this->updateTableWithNewField("emailField","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities");
        $this->updateTableWithNewField("emailText","text COLLATE utf8_unicode_ci","df_sys_entities");
        $this->updateTableWithNewField("validateAction","bit(1) NOT NULL","df_sys_entities","0",true);
        $this->updateTableWithNewField("detailViewShowType","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Up");
        
        $this->updateTableWithNewField("emailValidationError","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Wrong Email Input");
        //enableCaptcha
        $this->updateTableWithNewField("enableCaptcha","bit(1) NOT NULL","df_sys_entities","0",true);
        $this->updateTableWithNewField("decimalValidationError","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Wrong Decimal Input");
        $this->updateTableWithNewField("numberValidationError","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Wrong Number Input");
        $this->updateTableWithNewField("mustFieldValidationError","varchar(100) COLLATE utf8_unicode_ci","df_sys_entities","Field Must Be Set");
        
	}

    public function GetTabPrefix()
    {
         $tabPrefix="";
         global $wpdb;
         if (function_exists('is_multisite') && is_multisite())
         {
                $tabPrefix="";
                $tabPrefix = $wpdb->prefix ;
         }
         return $tabPrefix;
    }
    ///System:
    public function createDatabaseSystem()
    {
        
       
        $query="CREATE TABLE IF NOT EXISTS `df_sys_translations` (
          `keyVal` varchar(255) DEFAULT NULL,
          `language` varchar(5) DEFAULT NULL,
          `translation` text CHARACTER SET utf8 COLLATE utf8_unicode_ci
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_translations"));
        }

        //  $this->loadTranslations();
          $this->loadTranstlationsFromFile();
        $query="CREATE TABLE IF NOT EXISTS `df_sys_entities` (
          `entity_id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `sysname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `description` text COLLATE utf8_unicode_ci NOT NULL,
          `permission` enum('1','2') COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`entity_id`),
          UNIQUE KEY `sysname` (`sysname`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_entities"));
        }

    	//TODO: Add Alter with email details
    	$this->createEntityRows();


        $query="CREATE TABLE IF NOT EXISTS `df_sys_definitions` (
          `entity_id` int(11) NOT NULL,
          `system_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
          `input_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
          `values` text COLLATE utf8_unicode_ci,
          `field_order` int(11) NOT NULL,
          `appear_on_group` bit(1) NOT NULL,
          `is_id` bit(1) NOT NULL,
          `is_must` bit(1) NOT NULL,
          `search_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
          `definition_id` int(11) NOT NULL AUTO_INCREMENT,
          PRIMARY KEY (`definition_id`),
          UNIQUE KEY `definition_id` (`definition_id`),
          KEY `entity_id` (`entity_id`),
          KEY `definition_id_2` (`definition_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_definitions"));
        }

        $this->addDefinitionsFields();

         $query="CREATE TABLE IF NOT EXISTS `df_sys_forms` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `entity_id` int(11) NOT NULL,
          `formHtml` text NOT NULL,
          `formType` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_forms"));
        }

         $query="CREATE TABLE IF NOT EXISTS `df_sys_helphtml` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `forName` varchar(255) NOT NULL,
          `Text` text,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_helphtml"));
        }

        $query = " delete from df_sys_helphtml";
         $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("couldn't delete from df_sys_helphtml"));
        }

        $query="INSERT INTO `df_sys_helphtml` ( `forName`, `Text`) VALUES
        ( 'sysname', 'This is the form’s table system name – name of table which will store all data collected by this form. Additionally, you can use it in your code, if you wish to query form’s collected data'),
        ( 'system_name', 'This is the name of the field as it will be stored on form (this is system name of table field as it will be stored in database. You can use it in your code.)'),
        ( 'definitionname', 'This tag showcases the field name as it will be displayed in forms'),
        ( 'definitionname_table', 'This tag showcases the field name as it will be displayed in forms'),
        ( 'type', 'This is the field type as it will be stored in the database.'),
        ( 'input_type', 'From here you can set the control type that will be created in the form. This control type is used for the manipulation of field data.'),
        ( 'input_type_table', 'From here you can set the control type that will be created in the form. This control type is used for the manipulation of field data.'),
        ( 'search_type', 'This search type will be applied on the fields of the search form or default view, in case you fill in the default search value'),
        ( 'is_id', 'By choosing this option, you will prevent users from inserting two rows with the same value in this field. Its useful for storing unique ids of different kinds, like passport id, unique file name, etc'),
        ( 'is_must', 'Use this if you want to ensure that the field will be given a certain value. The field will be validated with JavaScript function, on an add/edit action basis.'),
        ('entityname','This option represents the representation name of the form. All form’s views refer to one table that is stored in the database. This table name is – system name.'),
        ('email','This feature enables you to receive a notification email whenever a new data is inserted via form’s views.'),
        ('norowstxt','This text will be display if there were no results in search'),
        ('description','This tag represents your own information field. From here you can store important system information about current form.'),
        ('inserttxt','In here you can configure your own text-based commands to be shown on the form’s views. The text that you set will be shown on the specific action buttons'),
        ('searchtxt','In here you can configure your own text-based commands to be shown on the form’s views. The text that you set will be shown on the specific action buttons'),
        ('updatetxt','In here you can configure your own text-based commands to be shown on the form’s views. The text that you set will be shown on the specific action buttons'),
        ('selecttxt','In here you can configure your own text-based commands to be shown on the form’s views. The text that you set will be shown on the specific action buttons'),
        ('groupnumtxt','In the default view or search results of this form, users will be able to see the displayed X rows of the form collected data within each page of results'),
        ('successInserttxt','These texts will be shown whenever a specific action is successful or not.'),
        ('failInserttxt','These texts will be shown whenever a specific action is successful or not.'),
        ('successUpdatetxt','These texts will be shown whenever a specific action is successful or not.'),
        ('failUpdatetxt','These texts will be shown whenever a specific action is successful or not.'),
        ('norowstxt','These texts will be shown whenever a specific action is successful or not.'),
        ('appear_on_group','Choose this option if you would like current field be displayed in group results while taking search action'),
        ('field_order','This option defines the columns number of this field, as it will be displayed in group view result. '),
        ('field_order_table','This field is responsible for field order in group view (bottom part) in search results'),
        ('helpTxt','Use this text area to define special help text, that will be visible to the user who is applying for your form. This help area will look like your help area near each setting in the form.'),
        ('classTxt','Use this text area when you want to apply a special class style to your theme from the field control. This class name will be inserted between the class tags of the control.'),
        ('styleTxt','Use this text area to define the style which will be used between the style tags while input control is created.'),
        ('defaultValue','This value will be used in default query as a data source for default group view '),
        ('isFilterable','This option will enable a special search above the group view display. This search form will be used for filtering search results. Use Filter Type setting to define the way this field will be displayed in the limited search form: text area or multiple choice'),
        ('filterType','Diplay type of filter of current field. Relevant only if you enabled filter on this field'),
        ('orderType','Through this setting you can define the order of fields which are displayed in default view. Rows will be ordered by all fields , which were selected by their Field Order.'),
        ('emailTitle','This option enables you to define the emails title, which will be sent to the form administrator thats listed in the mailbox below. The same title will also be sent to the specific action maker, if “send mail to adder” configured as “true”'),
        ('curentStyle','This area can be used to input your own div style. The text that will be inserted during the divs creation inside style tags.'),
        ('curentClassName','If you know which the special class in your theme is, than you can insert it here for the divs creation.'),
        ('sendMailToAdder','If you want the adder to receive a notification to his email, and you have created a special field to store his email, then you have to define this variable as true. This option is very useful when it comes to mailing lists, inventory items, contact forms and more. You will also need to choose your default Email field. In case you didnt define fields yet, come back later or simply access this field later from fields and forms Table -> edit form.'),
        ('emailField','Field of current entity which will contain email of adder'),
        ('emailText','This is the text that will be added at the beginning of the email, which will be sent to the adder plus any other recipient listed below.'),
        ('validateAction','This is an important field, in case your aim is to use this plugin as a mailing list platform. In this case, you might want the adder to validate his email, after adding it to the list. If you set the variable to true, then the adder will receive a special link on his email as soon as he registers. In addition, this variable can be used to check and send emails only to those who previously validated their email addresses.'),
        ('enableCaptcha','Will enable you to add human check whenever you insert new data to the table.'),
        ('detailViewShowType','This variable enables you to choose the place of the row where details will get displayed according to the group results. These results are displayed in relation with the specific search or default view, and they can be place either above the group results or inside the row.'),
        ('emailValidationError','These text areas are used to define the information that will be displayed on users end, in case javascript will fail on validating the input fields on form’s views.'),
        ('decimalValidationError','These text areas are used to define the information that will be displayed on users end, in case javascript will fail on validating the input fields on form’s views.'),
        ('numberValidationError','These text areas are used to define the information that will be displayed on users end, in case javascript will fail on validating the input fields on form’s views.'),
        ('formTypeLabel','Default - This view type is the one to be used as default, if no other one was defined. Insert - Will be used as an insert view of current form. Search - Will be used as a search view, help users finding specific data collected by form. Edit - will be used as a single details view, for the purpose of editing and viewing data collected by form'),
        ('mustFieldValidationError','These text areas are used to define the information that will be displayed on users end, in case javascript will fail on validating the input fields on form’s views.'),
        ( 'values', 'If you would like to enable multiple choices on the users end, from within the defined variables, then please fill this text area with possible values, delimiting them with a comma. Example: value 1, value 2, value 3');";
         $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't insert into df_sys_helphtml"));
        }
       

         $query="CREATE TABLE IF NOT EXISTS `df_sys_name_value_pairs` (
        `definition_id` bigint(20) NOT NULL,
        `name` varchar(255) DEFAULT NULL,
        `value` varchar(255) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_name_value_pairs"));
        }

        
          
        $query="CREATE TABLE IF NOT EXISTS `df_sys_queries` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `entity_id` int(11) NOT NULL,
          `name` varchar(255) NOT NULL,
          `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `user` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";

         $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_queries"));
        }

        $query="CREATE TABLE IF NOT EXISTS `df_sys_queries_values` (
          `query_id` int(11) NOT NULL,
          `system_name` varchar(255) NOT NULL,
          `value` varchar(255) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

         $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_queries_values"));
        }

        $query="CREATE TABLE IF NOT EXISTS `df_sys_general` (
          `sec_key` varchar(255) NOT NULL,
          `rowsNum` int(11) NOT NULL,
          `search_text` varchar(255) NOT NULL,
          `insert_text` varchar(255) NOT NULL,
          `search_ro_text` varchar(255) NOT NULL,
          `select_text` varchar(255) NOT NULL,
          `update_text` varchar(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_general"));
        }

        $query = " select count(*) as cnt from df_sys_general";
         $results=DF_DBFuncs::sqlGetResults($query);
        if($results['boolean']==true)
        {
            if(count($results['data'])>0)
            {
                $seckey="SecKey".rand(1000000,10000000000);
                if($results['data'][0]['cnt']<1)
                {

                       $query="INSERT INTO `df_sys_general` (`sec_key`, `rowsNum`, `search_text`, `insert_text`, `search_ro_text`, `select_text`, `update_text`) VALUES
                        ('{$seckey}', 5, 'Search', 'Save', 'Search', 'Select', 'Update');";
                }
                else
                {
                     $query="update `df_sys_general` set `sec_key`='{$seckey}';";
                }
                $results=DF_DBFuncs::sqlExecute($query);
                if($results['boolean']!=true)
                {
                    die(DF_DBFuncs::Translate("Couldn't create table df_sys_general"));
                }
            }
        }
        
      

      
      $query="CREATE TABLE IF NOT EXISTS `df_sys_validations` (
          `sysname` varchar(255) DEFAULT NULL,
          `sys_id` int(11) NOT NULL,
          `validationNum`  varchar(255) DEFAULT NULL,
          `created` DATETIME,
           `is_validated` bit(1) NOT NULL,
          `validated` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_validations"));
        }
        
         $query="CREATE TABLE IF NOT EXISTS `df_sys_removes` (
          `email` varchar(255) DEFAULT NULL,
          `created` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_validations"));
        }
        
        
        $query="CREATE TABLE IF NOT EXISTS `df_sys_sent_mails` (
          `post_id` int(11) NOT NULL,
          `sysname`  varchar(255) DEFAULT NULL,
          `created` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't create table df_sys_sent_mails"));
        }
    }

    public function SetSentMail($sysname,$postId)
    {
          $query = " insert into df_sys_sent_mails (post_id,sysname,created) values (".$postId.",'".$sysname."',NOW())";
             
               $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                die(DF_DBFuncs::Translate("Could not remove email"));
            }
    }
    
    public function IsMailSent($sysname,$postId)
    {
    
           $query = " select count(*) as cnt, created  from df_sys_sent_mails where post_id=".$postId." and sysname = '".$sysname."'";
          
         $results=DF_DBFuncs::sqlGetResults($query);
        if($results['boolean']==true)
        {
            if(count($results['data'])>0 && $results['data'][0]['cnt']>0)
            {
                return  $results['data'][0]['created'];
            }
        }
        return null;
    }
    
    public function RemoveEmail($email)
    {
                $query = " insert into df_sys_removes (email,created) values ('".$email."',NOW())";
             
               $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                die(DF_DBFuncs::Translate("Could not remove email"));
            }
            
            return DF_DBFuncs::Translate("Email removed from database");
    }
    
    
    public function InsertNewValidation($sysname, $id, $validationNum)
    {
               $query = " insert into df_sys_validations (sysname,sys_id,validationNum,created) values ('".$sysname."',".$id.",'".$validationNum."',NOW())";
             
               $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                die(DF_DBFuncs::Translate("Couldn't not add to validation table"));
            }
    }
    
     public function Validate($validationNum)
    {
        
         $query = " select count(*) as cnt, is_validated from df_sys_validations where validationNum = '".$validationNum."'";
         $results=DF_DBFuncs::sqlGetResults($query);
        if($results['boolean']==true)
        {
            if(count($results['data'])>0 && $results['data'][0]['cnt']>0)
            {
                if($results['data'][0]['is_validated']==1)
                {
                     return  DF_DBFuncs::Translate("Mail already validated");
                }
                 $query =" update df_sys_validations set is_validated = 1 , validated=NOW() where validationNum = '".$validationNum."'"; 
                    $results=DF_DBFuncs::sqlExecute($query);
                    if($results['boolean']!=true)
                    {
                        die(DF_DBFuncs::Translate("Couldn't validate"));
                    }
            }
              return  DF_DBFuncs::Translate("Mail successefuly validated");
        }
        else
        {
             return  DF_DBFuncs::Translate("There is no mail with this validation number");
        }
       
      
    }
    
    public function IsValidated($sysname, $id)
    {
    
           $query = " select count(*) as cnt, validated  from df_sys_validations where is_validated=1 and sysname = '".$sysname."' and sys_id=".$id;
          
         $results=DF_DBFuncs::sqlGetResults($query);
        if($results['boolean']==true)
        {
            if(count($results['data'])>0 && $results['data'][0]['cnt']>0)
            {
                return  $results['data'][0]['validated'];
                }
        }
        return null;
    }
    
    
    public function GetMailsList($sysName)
    {
        $entityName = $sysName;
        $entityDetails = $this->getSysDetails($sysName);
          $values=array();
          $extra = "";
        if(!empty($entityDetails['validateAction']) && $entityDetails['validateAction']=="1")
        {
            $extra = " and id in ( select sys_id from df_sys_validations where sysname='".$entityName."' and is_validated=1  )";
        }
        $query=" select * from ".$entityName  ." where (not email in (select email from df_sys_removes)) ";
        $query.=$extra;
        
         $results=DF_DBFuncs::sqlGetResults($query);
            if($results['boolean']==true && count($results['data'])>0)
            {
                
                foreach($results['data'] as $row)
                {
                      if(!in_array($row[$entityDetails['emailField']],$values))
                      {
                        array_push($values,$row[$entityDetails['emailField']]);
                      }
                     
                }
                 array_unique($values);
            }
        
        return $values;
        
    }
    
    public function getMailingLists()
    {
        $values = array();
       $query = "select * from df_sys_entities where not emailField='-'"; 
        $results=DF_DBFuncs::sqlGetResults($query);
      
            if($results['boolean']==true && count($results['data'])>0)
            {
                 
                foreach($results['data'] as $row)
                {
                     $values[$row['sysname']]=$row['name'];
                }
            }
        
        return $values;
    }
    
    public function getPostForMysql($string)
    {
        return mysql_real_escape_string(String::getPost($key));
    }

    public function loadDecryptionKey()
    {
        if($GLOBALS["EncriptionKeyLoaded"]=="false")
        {
            $query = " select sec_key as cnt from df_sys_general";
            $results=DF_DBFuncs::sqlGetResults($query);
            if($results['boolean']==true && $results['data'].length>0)
            {
                $GLOBALS["EncriptionKey"]=  $results['data']['0']['sec_key'];
                $GLOBALS["EncriptionKeyLoaded"]="true";
            }
        }
        return $GLOBALS["EncriptionKey"];
    }


  
   
    
    public function loadTranstlationsFromFile()
    {
        
         $query="delete from df_sys_translations";

        $results=DF_DBFuncs::sqlExecute($query);
        if($results['boolean']!=true)
        {
            die(DF_DBFuncs::Translate("Couldn't delete from form df_sys_translations"));
        }
        
        $file = @fopen(dirname(__FILE__).'/translations.txt', "r") or die(file_exists(dirname(__FILE__).'/translations.txt'));;  
        
         
          $count=0;
        while (!feof($file))
        {
            $query="INSERT INTO `df_sys_translations` (`keyVal`, `language`, `translation`) VALUES ";
            
            // Get the current line that the file is reading
            $currentLine = fgets($file) ;
            
            $valuesToInsert = explode("|", $currentLine);
            
             $query.="('".$valuesToInsert[0]."', '".trim($valuesToInsert[1])."', '".$valuesToInsert[2]."')";

               $count++;
               
               $query.=";";
               $results=DF_DBFuncs::sqlExecute($query);
                
        }   

       
        fclose($file) ;
    }
    
    
    
    
}
?>