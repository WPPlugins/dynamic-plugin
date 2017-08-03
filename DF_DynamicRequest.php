<?php
/*
Reads System name from request
Gets the correct definitions
Reads definitions values from request
Uses the correct function
Echo the result
*/
include_once 'DF_DynamicForms.php';
include_once 'DF_GeneralSettings.php';
include_once 'DF_CMSFunctions.php';
include_once 'DF_Session.php';
//include_ince 'DF_Definition.php';
class DF_DynamicRequest
{
     public function Manage($PostVariables)
    {
        
        $mydec=new DF_Crypt(DF_DBFuncs::getDecryptionKey());
        $DynamicForms = new DF_DynamicForms();
           //CheckCaptcha
         if(isset($PostVariables['validateRecaptcha']))
        {
            echo $DynamicForms->CheckCaptcha($_SERVER["REMOTE_ADDR"], $PostVariables['challenge'], $PostVariables['responce']);
        }
        
        if(isset($PostVariables['getDefinitions']))
        {
            echo $DynamicForms->get_AllDefinitionsToForm($PostVariables['system_table_name'], $PostVariables['form_type'], $PostVariables['random_number']);
        }
        
        
         if(isset($PostVariables['validateMail']))
        {
            echo $DynamicForms->validateMail($PostVariables['mailNum']);
        }
        
         if(isset($PostVariables['removeEmail']))
        {
            echo $DynamicForms->RemoveEmail($PostVariables['email']);
        }
        
         if(isset($PostVariables['sendMailToList']))
        {
            echo $DynamicForms->sendMailToList($PostVariables['currId'],$PostVariables['sysid']);
        }

        if(isset($PostVariables['buildForm'])) {

           
             if($DynamicForms->IsAuthorized($PostVariables['system_table_name'],$PostVariables['form_type'],$PostVariables['random_number'],"build"))
            {
              
                    if(isset($PostVariables['page_num']))
                    {
                        DF_Session::StoreSession("".$PostVariables['random_number']."_pageNum",$PostVariables['page_num']);
                    }
                     if(isset($PostVariables['index_num']))
                    {
                        DF_Session::StoreSession("".$PostVariables['random_number']."_indexNum",$PostVariables['index_num']);
                    }

                     if(isset($PostVariables['orderby']))                                                                    
                    {
                        DF_Session::StoreSession("".$PostVariables['random_number']."_orderby",$PostVariables['orderby']);
                    }

                     if(isset($PostVariables['ordertype']))
                    {
                         DF_Session::StoreSession("".$PostVariables['random_number']."_ordertype",$PostVariables['ordertype']);
                    }

                     if(isset($PostVariables['deleteRow']))
                    {
                            //check security
                            $readOnlyS=DF_Session::LoadSession("{$PostVariables['random_number']}_readOnly");
                            if($readOnlyS!=null)
                            {
                                $myDF_Crypt=new DF_Crypt(DF_DBFuncs::getDecryptionKey());
                                $readOnlyToSession=$readOnlyS;
                                $readOnlyToSession=$myDF_Crypt->Decrypt($readOnlyToSession);
                                if($readOnlyToSession)
                                {
                                    return DF_DBFuncs::translate("Has no right to delete </br>");
                                }
                            	else
                            	{
                            		 echo $DynamicForms->DeleteRow($PostVariables['system_table_name'],$PostVariables['deleteId']);
                            	}
                            }

                    }

                    
                    if(isset($PostVariables['firstTime']) ||  $PostVariables['form_type']=="UpdateGroup")
                    {

                           if(isset($PostVariables['Clear']))
                        {

                            $readOnly = ($PostVariables['Clear']=="true")? true:false;
                        }
                        if(isset($PostVariables['noAllForms']))
                        {
                            echo $DynamicForms->CreateFormGeneral($PostVariables['system_table_name'], $PostVariables['form_type'], $PostVariables['random_number'],0,$PostVariables['noAllForms'],$readOnly);
                        }
                        else
                        {
                            echo $DynamicForms->CreateFormGeneral($PostVariables['system_table_name'], $PostVariables['form_type'], $PostVariables['random_number'],0,'no',$readOnly);
                        }
                    }
                    else
                    {
                         if(isset($PostVariables['Clear']))
                        {

                            $readOnly = ($PostVariables['Clear']=="true")? true:false;
                            echo $DynamicForms->createForm($PostVariables['system_table_name'], $PostVariables['form_type'], $PostVariables['random_number'],0,$readOnly);
                        }
                        else
                        {
                            echo $DynamicForms->createForm($PostVariables['system_table_name'], $PostVariables['form_type'], $PostVariables['random_number']);
                        }
                    }
            }
            else
            {
                
               echo DF_DBFuncs::translate("User is not authorized");
            }
        }

        if(isset($PostVariables['processForm'])) {

            if($DynamicForms->IsAuthorized($PostVariables['system_table_name'],$PostVariables['form_type'],$PostVariables['random_number'],"process"))
            {
                    // building definitions array
                    $definitions = array();
                    $currUrl="";
                    foreach ($PostVariables as $key => $value)
                    {
                        
                        // checking if we've done with all the definitions and
                        // started with the other parameters of $_POST
                               
                        if ($key == "processForm")
                        {
                            continue;
                        }
                        
                        if ($key == "currurl")
                        {
                            $currUrl=$value;
                        }
                       
                       if (empty($value) == true && $value!=0)
                        {
                            continue;
                        }
                        if (strpos ($key, "column_")>-1)
                        {   
                                // finding the previous values in hidden fields (in case of UpdateDetails action)
                                if (strpos ($key, "hidden") === 0)
                                {
                                    $sysNameArr=  split("_", $key);
                                    $system_name = $DynamicForms->getSysNameById($sysNameArr[2]);
                                    $definitions[$system_name]->previous_value = html_entity_decode(stripslashes($value));
                                    continue;
                                }
                                $sysNameArr=  split("_", $key);
                                $definition = new DF_Definition();
                                $definition->system_name =$DynamicForms->getSysNameById( $sysNameArr[1]);
                                $definition->value =html_entity_decode(stripslashes($value));
                                $definitions[$definition->system_name] = $definition;
                        }
                        

                    }


                    $noAllForms='no';
                    if(isset($PostVariables['noAllForms']))
                        {
                            $noAllForms=$PostVariables['noAllForms'];
                        }



                    echo $DynamicForms->processForm(
                            $PostVariables['system_table_name'],
                            $PostVariables['form_type'],
                            $PostVariables['random_number'],
                            $PostVariables['page_num'],
                            $PostVariables['index_num'],
                            $PostVariables['system_table_name'],
                            $definitions,$noAllForms,$currUrl);
            }
            else
            {
              
               echo DF_DBFuncs::translate("User is not authorized");
            }

        }
        
    }
}
?>