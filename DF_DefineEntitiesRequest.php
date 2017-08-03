<?php
/*
Read the action type from request
Reads all needed parameters
Uses needed functions
Returns needed request
*/
include_once 'DF_GeneralSettings.php';
include_once 'DF_Definition.php';
include_once 'DF_Value.php';
include_once 'DF_DataBase.php';
include_once 'DF_JavascriptDefine.php';
include_once 'DF_Session.php';
include_once 'DF_DynamicRequest.php';



class DefineEntitiesRequest
{
    public function Manage($PostVariables)
    {
        if(is_admin())
        {
        $database = new DF_DataBase();
         $DynamicForms = new DF_DynamicForms();
        //get form design
        if(isset($PostVariables['loadEntityDetails']))
        {
            echo $database->start_session_values_by_id($PostVariables['sysname']);
        }

         if(isset($PostVariables['buildForm'])) {
             $readOnly=false;
             echo $DynamicForms->CreateFormGeneral($PostVariables['system_table_name'], $PostVariables['form_type'], 0,0,'no',$readOnly);
         }


        //get form design
        if(isset($PostVariables['getNumberDefinitionFields']))
        {
            echo $database->GetNumberFieldNamesDefinition();
        }

        //get form design
        if(isset($PostVariables['getMustDefinitionFields']))
        {
            echo $database->GetMustFieldNamesDefinition();
        }

        //get form design
        if(isset($PostVariables['getFormDesign']))
        {
            echo $database->getFormDesign($PostVariables['entity_id'],$PostVariables['formType']);
        }

        //save form design
        if(isset($PostVariables['SaveFormDesign']))
        {

            echo $database->SaveFormDesign($PostVariables['entity_id'],$PostVariables['formType'],html_entity_decode(stripslashes($PostVariables['formHtml'])));

        }

        //renew DefineNewEntity div
        if(isset($PostVariables['RenewDefineNewEntity']))
        {
            $showExtraButtons=true;
            if(isset($PostVariables['showExtraButtons']) && $PostVariables['showExtraButtons']=="false")
            {
                  $showExtraButtons=false;
            }    
            echo $database->GetEntityDefinitionHtml($showExtraButtons);
        }

        //Not In USE get help html
        if(isset($PostVariables['getHelpHtml']))
        {
            echo $database->getHelpHtml($PostVariables['forName']);
        }

        //get next order
        if(isset($PostVariables['getHelpHtmls']))
        {
            echo $database->getHelpHtmls();
        }

        /*
        //get next order
        if(isset($_POST['CategoryDetails']))
        {
            echo $database->getCategoryDetails($_POST['category_name'],$_POST['entity_id']);
        }
        */
        //get next order
        if(isset($PostVariables['getNextOrder']))
        {
            echo $database->getNextOrder($PostVariables['entity_id']);
        }

        /*
        //get categories existing in definitions
        if(isset($_POST['getCategories']))
        {
            echo $database->getCategoriesOfCurrentEntity($_POST['entity_id']);
        }
        */

        //delete definition
        if(isset($PostVariables['dropDefinition']))
        {
            echo $database->dropDefinition($PostVariables['sysName'], $PostVariables['definition_id'],$PostVariables['entity_id'],$PostVariables['sysTableName']);
         }

        //get definition of current html
        if(isset($PostVariables['getDefinitionsOfCurrentEntity']))
        {
            echo $database->getDefinitionsOfCurrentEntity($PostVariables['entity_id'],$PostVariables['sysTableName']);
        }

        //get definition details
        if(isset($PostVariables['getDefinitionDetails']))
        {
            echo  $database->getDefinitionsProperties($PostVariables['definition_id']);
        }

        // creating new entity
        if(isset($PostVariables['defineEntity']))
        {
            if($PostVariables['curraction']=="AddNew")
            {
                echo  $database->create_table($PostVariables['entityname'], $PostVariables['sysname'], $PostVariables['description'], $PostVariables['email']
                	, $PostVariables['inserttxt'], $PostVariables['searchtxt'], $PostVariables['updatetxt'], $PostVariables['selecttxt'], $PostVariables['groupnumtxt'], $PostVariables['successInserttxt']
                	, $PostVariables['failInserttxt'], $PostVariables['successUpdatetxt'], $PostVariables['failUpdatetxt'], $PostVariables['norowstxt'] ,
                    $PostVariables["emailTitle"],$PostVariables["curentStyle"], $PostVariables["curentClassName"]  ,$PostVariables["sendMailToAdder"],$PostVariables["emailField"] ,
                    $PostVariables["emailText"],$PostVariables["validateAction"] ,$PostVariables["detailViewShowType"] ,$PostVariables["emailValidationError"],
             $PostVariables["decimalValidationError"], $PostVariables["numberValidationError"],$PostVariables["mustFieldValidationError"]  ,$PostVariables['enableCaptcha']
                    );
            }

            if($PostVariables['curraction']=="Edit")
            {
            	echo  $database->edit_table($PostVariables['entityname'], $PostVariables['sysname'], $PostVariables['description'],
            		 $PostVariables['email'], $PostVariables['inserttxt'], $PostVariables['searchtxt'], $PostVariables['updatetxt'],
            		 $PostVariables['selecttxt'], $PostVariables['groupnumtxt'], $PostVariables['successInserttxt']
            		, $PostVariables['failInserttxt'], $PostVariables['successUpdatetxt'], $PostVariables['failUpdatetxt']
            		, $PostVariables['norowstxt'],  $PostVariables["emailTitle"],$PostVariables["curentStyle"], $PostVariables["curentClassName"]  ,$PostVariables["sendMailToAdder"],$PostVariables["emailField"] ,
                    $PostVariables["emailText"],$PostVariables["validateAction"] ,$PostVariables["detailViewShowType"] ,$PostVariables["emailValidationError"],
             $PostVariables["decimalValidationError"], $PostVariables["numberValidationError"],$PostVariables["mustFieldValidationError"],  $PostVariables['enableCaptcha'],
            		 $PostVariables['oldsysname'], $PostVariables['oldentityid']);
            }
        }

        // creating new entity
        if(isset($PostVariables['deleteEntity']))
        {

            echo  $database->delete_table( $PostVariables['sysname'], $PostVariables['oldentityid']);

        }

        // creating new definition
        if(isset($PostVariables['defineDefinition']))
        {
            
            foreach($PostVariables as $key=>$value)
            {
                $PostVariables[$key] = html_entity_decode(stripslashes($PostVariables[$key]));
            }
            $definition = new DF_Definition();

            $statusAction= $PostVariables['statusAction'];
            if($statusAction=="edit")
            {
               echo $definition->definition_id;
                $definition->definition_id=$PostVariables['definition_id'];
            }

            $definition->name = $PostVariables['definitionname'];
            $definition->system_name = $PostVariables['system_name'];
            $definition->type = $PostVariables['type'];
            $definition->input_type = $PostVariables['input_type'];
            $definition->field_order = $PostVariables['field_order'];


            $definition->appear_on_group = $PostVariables['appear_on_group'];
            $definition->is_id = $PostVariables['is_id'];
            $definition->is_must = $PostVariables['is_must'];
            $definition->search_type = $PostVariables['search_type'];

            $definition->helpTxt = $PostVariables['helpTxt'];
            $definition->classTxt = $PostVariables['classTxt'];
            $definition->styleTxt = $PostVariables['styleTxt'];
            $definition->defaultValue = $PostVariables['defaultValue'];
            $definition->isFilterable = $PostVariables['isFilterable'];
            $definition->filterType = $PostVariables['filterType'];
            $definition->orderType = $PostVariables['orderType'];

            // building $definition->values array if there're pair of names and values in this definition
            // I suppose that this string is combined like this :
            // name1,value1 name2,value2 name3,value3 ...
            if (isset($PostVariables['values']))
            {
                $definition->values = array();
                $names_values = explode(",", $PostVariables['values']);
                foreach ($names_values as $name_value_pair)
                {
                    $name_value_array = explode("-", $name_value_pair);
                    if($name_value_array.length==2)
                    {
                        $definition->values[] = new DF_Value($name_value_array[0], $name_value_array[1]);
                    }
                    else
                    {
                        $definition->values[] = new DF_Value($name_value_array[0], $name_value_array[0]);
                    }
                }
            }

            //();
            echo $database->start_session_values();
            $sysTableName = DF_Session::LoadSession("sysTableName");
            if($sysTableName!=null)
            {
                echo $database->add_definition($sysTableName, $definition, $statusAction);
            }else{
                echo "</br>Cannot add new definition - no Form Name was stored in the session before</br>";
            }
        }

        // creating javascript files
        if(isset($PostVariables['finishDefiningDefinitions']))
        {
            if(isset($PostVariables['sysname']))
            {
            $definitions = $database->get_AllDefinitions($PostVariables['sysname'], "", "", "", "");
            echo "<br /> Js Search :";
            echo DF_JavascriptDefine::create_jsSearch($PostVariables['sysname'], $definitions);
            echo "<br /> Js Update Details :";
            echo DF_JavascriptDefine::create_jsUpdateOne($PostVariables['sysname'], $definitions);
            echo "<br /> Js Insert Details :";
            echo DF_JavascriptDefine::create_jsInsert($PostVariables['sysname'], $definitions);
            }
            else
            {
                echo "some sysname problem";
            }
        }
        }
        else
        {
            echo 'No Permissions';
        }
    }
}

?>
