<?php
include_once 'DF_Value.php';
include_once 'DF_DataBase.php';
include_once 'DF_Crypt.php';
include_once 'DF_CMSFunctions.php';
include_once 'DF_Session.php';
include_once 'DF_Recaptcha.php';


// all forms are created in special area. This area id is transfere to functions
class DF_DynamicForms
{
	private $database;

	function __construct()
	{
		$this->database = new DF_DataBase();

	}

    
    public function SetAuthorization($systemName, $formType,$randomNum,$action)
    {
        
         $mydf=new DF_Crypt( DF_DBFuncs::getDecryptionKey());

         $_SESSION[$mydf->Encrypt('REMOTE_ADDR')] = $mydf->Encrypt($_SERVER['REMOTE_ADDR']);  
         $_SESSION[$mydf->Encrypt('SystemName'."_".$randomNum)] = $mydf->Encrypt($systemName."_".$randomNum);
         $_SESSION[$mydf->Encrypt('Form'.$formType."_".$action."_".$randomNum)] = $mydf->Encrypt($formType."_".$action."_".$randomNum);
       
    }

    public function IsAuthorized($systemName, $formType,$randomNum,$action)
    {
         
        $db = new DF_DataBase();
        $result=true;
         $mydf=new DF_Crypt( DF_DBFuncs::getDecryptionKey());
         if(!isset($_SESSION[$mydf->Encrypt('REMOTE_ADDR')]) || $mydf->Decrypt($_SESSION[$mydf->Encrypt('REMOTE_ADDR')])!=$_SERVER['REMOTE_ADDR'])
              { 
            $result=false;}
         if(!isset($_SESSION[$mydf->Encrypt('SystemName'."_".$randomNum)]) || $mydf->Decrypt($_SESSION[$mydf->Encrypt('SystemName'."_".$randomNum)]) != $systemName."_".$randomNum)
            {
                
            $result=false;}
            $value = $mydf->Decrypt($_SESSION[$mydf->Encrypt('Form'.$formType."_".$action."_".$randomNum)]);
            $value2  =  trim(  $formType."_".$action."_".$randomNum);
            $value2= preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($value2));
            
         if(!isset($_SESSION[$mydf->Encrypt('Form'.$formType."_".$action."_".$randomNum)]) || strcmp($value , $value2) !=0)
           { 
               
               
            $result=false;}

            if (is_super_admin() )
            {
              
                $result= true;
            }
        //no mater what admin cannot actualy make changes on other tables that are not defined by the plugin
        if(!$db->isInDfEntities($systemName))
            {  $result=false;}
       
         return $result;
    }


    public function GetAddPanelBoxToPost()
    {

           $result=	"<label for=\"system_table_name\">". DF_DBFuncs::translate("Form Name")."</label>";

        $mydatabase = new DF_DataBase();
        $result.= $mydatabase->getEntitiesList('df_system_table_name');

      $result.="
  </p>
  <p>
    <label for=\"df_form_type\">". DF_DBFuncs::translate("View Type")."</label>
     <select name=\"df_form_type\" id=\"df_form_type\">
            <option value=\"Insert\" selected=\"selected\">". DF_DBFuncs::translate("Insert")."</option>
            <option value=\"Search\">". DF_DBFuncs::translate("Search")."</option>
            <option value=\"Search_ReadOnly\">". DF_DBFuncs::translate("Search - Read Only")."</option>
            <option value=\"DefaultDisplay\">". DF_DBFuncs::translate("Default Display")."</option>
          </select>
          
           
  </p>
   <p>
   <input type=\"button\" class=\"button-primary\"  onclick=\"return df_AddFormType()\" name=\"addToPost\" id=\"addToPost\" value=\"". DF_DBFuncs::translate("Add To Post")."\" />
           <br/>
           <div id='df_code_label'></div>
           
           
           
    ";
    $extra="";
   
    $values = $this->database->getMailingLists();
    
    if(count($values)>0)
    {
        $extra = "<p>";
        $currValue = array();
        $currValue[$values[0]]  = $values[0];
       $extra.= $this->database->GetDropDownInputRegular(DF_DBFuncs::translate("Mailing lists"),"df_sys_id",$currValue,$values) ;
       $extra.="</p><p> <input type=\"button\" class=\"button-primary\"  onclick=\"return DF_SendMailToMailingList('". DF_DBFuncs::translate("Are you sure you would like to send this email to mails list?")."','". trim(preg_replace('/\s\s+/', ' ',DF_DBFuncs::translate("There are no post parameter on the line or no mailing list selected")))."')\" name=\"sendMails\" id=\"sendMails\" value=\"". DF_DBFuncs::translate("Send current post to mailing list")."\" /></p>" ;
    }
    
        return $result.$extra;

    }
    
    public function GetWidgetBox()
    {

           $result=    "<p><label for=\"system_table_name\">". DF_DBFuncs::translate("Form Name")."</label>";

        $mydatabase = new DF_DataBase();
        $result.= $mydatabase->getEntitiesList('df_system_table_name');

      $result.="
  </p>
  <p>
    <label for=\"df_form_type\">". DF_DBFuncs::translate("View Type")."</label>
     <select name=\"df_form_type\" id=\"df_form_type\">
            <option value=\"Insert\" selected=\"selected\">". DF_DBFuncs::translate("Insert")."</option>
            <option value=\"Search\">". DF_DBFuncs::translate("Search")."</option>
            <option value=\"Search_ReadOnly\">". DF_DBFuncs::translate("Search - Read Only")."</option>
            <option value=\"DefaultDisplay\">". DF_DBFuncs::translate("Default Display")."</option>
          </select>
          
           
  </p>
   
    ";
        return $result;

    }

    public function get_AllDefinitionsToForm($sysTableName,$formType,$randomNumber)
    {
        return $this->database->get_AllDefinitionsToForm($sysTableName,$formType,$randomNumber,"","");
    }

    
    
    public function getSysNameById($id)
    {
        return $this->database->getSysNameById($id);
    }
	// returns html code for table update data
	private function get_UpdateGroupHtml($SystemTableName, $formType, $randomNumber, $FormNum,$readOnly,$singleDiv)
	{
		$entityDetails=$this->database->getSysDetails($SystemTableName);
        $dir = plugin_dir_url(__FILE__);
        $dirImages=$dir."/images/";
        $mydec=new DF_Crypt(DF_DBFuncs::getDecryptionKey());
        $readOnlytxt = ($readOnly)? "true":"false";

		$html = "<table style='text-align:center;padding:5px;' border=\"0\">";
		$definitions = $this->database->get_UpdateDefinitions(
				$SystemTableName,'UpdateGroup',$randomNumber);
        $index=0;
		if (count($definitions) != 0)
		{

            $html .= "<tr>";
			// printing table head
			//$results = $this->database->getDefinitions($SystemTableName);

            //for delete
            $btnExcel="";
            if(!$readOnly)
            {
            $btnExcel="<input type=\"button\" style=\"background:url('".$dirImages."Save.png') no-repeat; width:20px; background-color: transparent; border: transparent;   \" onclick=\"return DF_ExportHTMLTableToExcel({$randomNumber}, '{$SystemTableName}','{$readOnlytxt}')\"
                name=\"saveExcel\" id=\"saveExcel\" />";
            }
            $html .="<td>".$btnExcel."</td>";

                $countTd=0;
            
            //Titles
           foreach($definitions as $definition)
           {
               //TODO: Add two buttons ups and down
               if($definition->appear_on_group=="1")
               {
                   $countTd++;
                    $systemNameDec = $definition->definition_id;
                    $ordertypeDecASC = "ASC";
                    $ordertypeDecDESC = "DESC";
                    $html .= "<td style='padding:15px;margin:15px;'>
                    <input type=\"button\" style=\"background:url('".$dirImages."Up.png') no-repeat; width:20px; background-color: transparent; border: transparent;
                    \"  onClick=\"return DFMake_Reorder(".$randomNumber.", '".$SystemTableName."','".$systemNameDec."','".$ordertypeDecASC."','{$readOnlytxt}')\" />
                    <strong>".$definition->name."</strong>
                     <input type=\"button\" style=\"background:url('".$dirImages."Down.png') no-repeat; width:20px; background-color: transparent; border: transparent;
                    \"  onClick=\"return DFMake_Reorder(".$randomNumber.", '".$SystemTableName."','".$systemNameDec."','".$ordertypeDecDESC."','{$readOnlytxt}')\" />
                    </td>";
               }
           }
             //for select
            $html .= "<td></td>";
			$html .= "</tr>";
               $currId=0;
			// printing table data rows
			$keys = array_keys($definitions);
			$definitions_values_size = count($definitions[$keys[0]]->value);
			for ($i = 0; $i < $definitions_values_size; $i++)
			{
			    $html .= "<tr>";
                if(!$readOnly)
                {
                   
                    foreach ($definitions as $definition)
                    {
                          if($definition->name=="id")
                            {
                                $currId =   $definition->value[$i];
                            }
                    }
                    if($currId!=0)
                    {
                        $html .= "<td>   <input type=\"button\" style=\"background:url('".$dirImages."Delete.png') no-repeat; width:20px; background-color: transparent; border: transparent;
                            \"  onClick=\"return DFMake_DeleteRow(".$randomNumber.", '".$SystemTableName."',".$currId.",'{$readOnlytxt}')\" />
                            </td>";
                    }
                    else
                    {
                        $html .= "<td> </td>";
                    }
                }
                 else
                    {
                        $html .= "<td> </td>";
                    }
			    foreach ($definitions as $definition)
			    {
                     if($definition->name=="id")
                            {
                                $currId =   $definition->value[$i];
                            }
                    if($definition->appear_on_group=="1")
                       {
                    		$value=html_entity_decode(stripslashes($definition->value[$i]));
                            $value = $this->ReloadValueIfFile($value,$definition);
                           
                            if(DF_Session::LoadSession("".$randomNumber."_indexNum")==$index)
                            {
                                $html .= "<td  style='padding:15px;margin:15px;'><strong>{$value}</strong></td>";
                            }
                            else
                            {
			                    $html .= "<td  style='padding:15px;margin:15px;'>{$value}</td>";
                            }
                       }
                    
			    }

                $tdValidate="";
                
                  if(!empty($entityDetails['validateAction']) && $entityDetails['validateAction']=="1" )
                {
                    $validationNum=rand(1,10000000000);
                    $validated = $this->database->IsValidated($SystemTableName,$currId);
                    
                    $isValidated =($validated==null)?" Not Validated ": DF_DBFuncs::translate("Validated on")." ".$validated;  
                    $tdValidate="<td>".$isValidated."</td>" ;
                    $countTd++;
                        
                }
                if(DF_Session::LoadSession("".$randomNumber."_indexNum")==$index)
                    {

                        $html.="<td  style='padding:15px;margin:15px;'> <input type=\"button\" disabled=\"disabled\" readonly=\"readonly\" onClick=\"return DFMake_ChangeIndex({$randomNumber}, '".$SystemTableName."',{$index},'{$readOnlytxt}')\" value=\"". DF_DBFuncs::translate($entityDetails['selecttxt'])."\" /></td>";
                        $html .= $tdValidate."</tr>";
                      
                      
                          if($entityDetails['detailViewShowType']=="internal")
                            {
                                $html.="<tr><td colspan='".($countTd+3)."'>".$singleDiv."</td></tr>" ;
                            }
                    }
                    else
                    {
                        $html.="<td  style='padding:15px;margin:15px;'> <input type=\"button\"    onClick=\"return DFMake_ChangeIndex({$randomNumber}, '".$SystemTableName."',{$index},'{$readOnlytxt}')\" value=\"". DF_DBFuncs::translate($entityDetails['selecttxt'])."\" /></td>";
                        
                        
                        $html .= $tdValidate."</tr>";
                    }
			    
               
			     $index++;
            }


		}
       
		$html .= "</table>";
        //TODO:
        //get page number
        //();
        $pages = $this->database->getNumOfPages($SystemTableName,$formType,$randomNumber,"");
        for($i=0;$i<$pages;$i++)
        {

            if(DF_Session::LoadSession("".$randomNumber."_pageNum") !=$i)
            {
                $html.="<input type=\"button\" onClick=\"return DFMake_ChangePage({$randomNumber}, '".$SystemTableName."',{$i},'{$readOnlytxt}')\" value=\"". ($i+1)."\" />";
            }
            else
            {
                $html.="<input type=\"button\" readonly=\"readonly\"  disabled=\"disabled\" onClick=\"return DFMake_ChangePage({$randomNumber}, '".$SystemTableName."',{$i},'{$readOnlytxt}')\" value=\"". ($i+1)."\" />";
            }
        }

         if($entityDetails['detailViewShowType']!="internal")
        {
            $html=$singleDiv.$html;
        }
		return $html;
	}

    private function ReloadValueIfFile($value,$definition)
    {
        if ($definition->input_type=="file"&&!empty($value))
        {
              $myvalues=explode("|",$value);
              
              $isError= strrpos($myvalues[0],"error");
              if(!$isError && count($myvalues)>0 && strlen($myvalues[0])>0 )
              {
                  $dir =  plugin_dir_url(__FILE__);
                 $value="<a href='".$myvalues[0]."' target='_blank'><img  src='".$dir."images\Save.png'></a>"; 
              }
        }
        
         if ($definition->input_type=="image"&&!empty($value))
        {
              $myvalues=explode("|",$value);
              
              $isError= strrpos($myvalues[0],"error");
              if(!$isError && count($myvalues)>0 && strlen($myvalues[0])>0 )
              {
                  $dir =  plugin_dir_url(__FILE__);
                  list($width, $height) = getimagesize($myvalues[0]);
                  $size=getimagesize($myvalues[0]);
                
                  if($size[0]>0 && $size[1]>0)
                  {
                       
                           $width=$size[0]*150/ $size[1];
                           $height=150;
                  
                            $value="<img src='".$myvalues[0]."' width='".$width."px' height='".$height."px'/>"; 
                           
                  }
              }
        }
        
        //list($width, $height) = getimagesize('path_to_image');
                            return $value;
    }
    
    public function createExport($SystemTableName, $formType, $randomNumber, $FormNum,$readOnly)
    {
        $dir = plugin_dir_url(__FILE__);
        $dirImages=$dir."/images/";
        $mydec=new DF_Crypt(DF_DBFuncs::getDecryptionKey());
        $readOnlytxt = ($readOnly)? $mydec->Encrypt("true"):$mydec->Encrypt("false");

        $html = "";
        $definitions = $this->database->get_UpdateDefinitions(
                $SystemTableName,$formType,$randomNumber);
        $index=0;
        if (count($definitions) != 0)
        {

                  $html .= "<table>";
                  $html .= "<tr>";
           foreach($definitions as $definition)
           {

               //TODO: Add two buttons ups and down
               if($definition->appear_on_group=="1")
               {
                    $systemNameDec = $definition->definition_id;
                    $html .= " <td>
                    ".$definition->name." </td>
                    ";
               }
           }
             $html .= "</tr>";
            // printing table data rows
            $keys = array_keys($definitions);
            $definitions_values_size = count($definitions[$keys[0]]->value);
            for ($i = 0; $i < $definitions_values_size; $i++)
            {
                 $html .= "<tr>";
                foreach ($definitions as $definition)
                {
                   $html .= "<td>{$definition->value[$i]} </td> ";
                }
                $html .= "</tr>";
                 $index++;
            }
            $html .= "</table>";
        }

        return $html;
    }

    public function DeleteRow($sysTableName,$id)
    {
        return $this->database->deleteRow($sysTableName,$id);
    }

   public function get_definitions($systemTableName, $formType, $randomNumber, $FormNum=0)
   {
        $definitions=null;
        switch($formType)
        {
            case 'Insert':
                 $definitions = $this->database->get_InsertDefinitions($systemTableName,$formType,$randomNumber,"");
                break;
            case 'UpdateDetails':
                 $definitions = $this->database->get_UpdateDefinitions($systemTableName,$formType,$randomNumber,"");
                 break;
            case 'UpdateGroup':
                 $definitions = $this->database->get_UpdateGroupDefinitions($systemTableName,$formType,$randomNumber,"");
                 break;
            case 'Search_ReadOnly':
            case 'Search':
                 $definitions = $this->database->get_SearchDetailsDefinitions($systemTableName,'Search',$randomNumber,"");
                 break;
        }
        return $definitions;
   }


   public function CreateFormGeneral($SystemTableName,$formType,$randomNumber=0,$FormNum=0,$noAllForms='no', $readOnly=false)
   {
       $sysDetails=$this->database->getSysDetails($SystemTableName);
       $class = "";
       $style="";
       if(!empty($sysDetails['curentStyle']))
       {
           $style=" style='".$sysDetails['curentStyle']."'";
       }
       
        if(!empty($sysDetails['curentClassName']))
       {
           $class=" style='".$sysDetails['curentClassName']."'";
       }
       if ($randomNumber == 0)
    	{
    		//randomize some number => R
    		$randomNumber=rand(1,1000000);
    	}
         
        $dir =  DF_DBFuncs::getCurrentPluginURL();
        $hiddenField="<input type=\"hidden\" id=\"DF_path\" value=\"". $dir . "/\"/>";
         if($formType=="DefaultDisplay")
        {
            $readOnly=true;
             
            $this->CreateDefaultSearch($SystemTableName,"Search",$randomNumber,0,0,$readOnly);
            $formType= "UpdateGroup";
        }
        
       
        $singleDiv="<div id='singleForm".$randomNumber."'  ".$class."  ".$style." ></div><br/>";
        $resultDiv="<div id='newFormResult".$randomNumber."' ".$class."  ".$style." ></div><br/>";
        $groupDiv="<div id='groupForm".$randomNumber."'  ".$class."  ".$style." ></div><br/>";
        
      
        if ($formType == 'UpdateGroup')
        {
         
            $FormHtml2=$this->createForm($SystemTableName,"UpdateDetails",$randomNumber,$FormNum,$readOnly);
             $singleDiv="<div id='singleForm".$randomNumber."' ".$class."  ".$style." >".$FormHtml2."</div><br/>";   
            
             $FormHtml=$this->createForm($SystemTableName,$formType,$randomNumber,$FormNum,$readOnly,$singleDiv);
         
            
            
            $groupDiv="<div id='groupForm".$randomNumber."' ".$class."  ".$style." >".$FormHtml."</div><br/>";

           

        }
        else
        {
            $FormHtml=$this->createForm($SystemTableName,$formType,$randomNumber,$FormNum,$readOnly);
             $groupDiv=" <div id='singleForm".$randomNumber."' ".$class."  ".$style." >".$FormHtml."</div><br/>";
        }
         $extraStart = "<div id='allforms".$randomNumber."' ".$class."  ".$style." >";
         $extraEnd="</div>";

        if($noAllForms=='yes')
        {
            $extraStart = "";
            $extraEnd="";
        }
        return $extraStart.$hiddenField.$resultDiv.$groupDiv.$extraEnd;
   }

   // return html code for form
    public function createForm($SystemTableName,$formType,$randomNumber=0,$FormNum=0,$readOnly=false,$singleDiv="")
    {
      
         if($formType=="DefaultDisplay")
        {
            $readOnly=true;
            
            $this->CreateDefaultSearch($SystemTableName,"Search",$randomNumber,0,0,$readOnly);
            $formType= "UpdateGroup";
        }
        
    	$db = new DF_DataBase();
		$entityDetails= $this->database->getSysDetails($SystemTableName);
       $htmlRespondT="";

       $htmlRespond="";
    	if ($randomNumber == 0)
    	{
    		//randomize some number => R
    		$randomNumber=rand(1,1000000);
    	}

        //start form
        $myDF_Crypt=new DF_Crypt(DF_DBFuncs::getDecryptionKey());
        $formName="".$formType."_".$SystemTableName."_".$FormNum."_".$randomNumber;
        $formName=$myDF_Crypt->Encrypt($formName);
        $readOnlyToSession=$readOnly;
        //prevent hacker attacks
        $readOnlyS=DF_Session::LoadSession("{$randomNumber}_readOnly");
        
        if($readOnlyS!=null)
        {
           $readOnlyToSession=$readOnlyS;  
          
          
            //in this case there is a try to change read only value unathorized way,
            //therefore no results will be shown
            if($readOnlyToSession!= $myDF_Crypt->Encrypt($readOnly) )
            {
                $oldReadonly=$readOnly;
                $readOnly=true;
                return DF_DBFuncs::translate("Unathorized access </br>");
            }
        }
        else
        {
            $readOnlyToSession=$readOnly;
            if($formType=="Search_ReadOnly")
            {
                $readOnlyToSession=true;
            }
           
            $readOnlyToSession=$myDF_Crypt->Encrypt($readOnlyToSession);
           
            DF_Session::StoreSession("{$randomNumber}_readOnly",$readOnlyToSession);
        }

       // $htmlRespond .= DF_DBFuncs::translate($formType. "Form") .":";
        $htmlRespond .= "<form id=\"".$formName."\"
        name=\"".$formName."\">";

        if ($formType == 'UpdateGroup')
        {
        	$htmlRespond .= $this->get_UpdateGroupHtml($SystemTableName, $formType, $randomNumber, $FormNum,$readOnly,$singleDiv);
        }else   if ($formType=="Export")
        {
            return $this->createExport($SystemTableName, $formType, $randomNumber, $FormNum,$readOnly);
        } else
        {


        	$definitions = array();

            $definitions = $this->get_definitions($SystemTableName, $formType, $randomNumber, $FormNum);

            $sysId=$this->database->getEntityId($SystemTableName);
            $formTemplate = $this->database->getFormDesign($sysId,$formType);
            if($formTemplate=="")
            {
                $formTemplate = $this->database->getFormDesign($sysId,'default');
            }



	        //go over definitions
	        if (is_array($definitions))
	        {
		        foreach($definitions as $definition)
		        {
		            //build control
                    $insertValue=$definition->get_Html($formType);
                    if ($readOnly)
                    {
                        $insertValue=html_entity_decode(stripslashes($definition->value[0]));
                         $insertValue = $this->ReloadValueIfFile($insertValue,$definition);
                    }

		        	 $htmlRespondT.= $insertValue;

                     //If definition is must or id and not fount in template, add this field to the template at the end with <br /> tag
                    $pos=strrpos($formTemplate,"[@".$definition->system_name."@]");
                    if(!$readOnly && $pos==false && $formType!="Search" && $formType!="Search_ReadOnly")
                    {
                        $required="";
                        if($definition->is_must)
                        {
                             $required="<font color='red'>*</font>";
                        }
                        $label="";
                        if($definition->input_type!='hidden')
                        {
                            $label="<label for='".$definition->getId("",true)."'>".$definition->name."</label> ";
                        }
                        
                            $formTemplate .="<p> [@".$definition->system_name."@]</p>" ;
                            $insertValue=$definition->getHidden($formType);
                    }
                    $formTemplate=str_replace("[@".$definition->system_name."@]",$insertValue,$formTemplate);
                    $formTemplate=str_replace("[@id|".$definition->system_name."@]",$definition->getId("",true),$formTemplate);
		        }
	        }else{
	        	return DF_DBFuncs::translate("No results</br>");
	        }
            $htmlRespond.=$formTemplate;
        }
        //submit button and end form
        // build call for javascript function using Random number
        if ($formType != 'UpdateGroup' && $formType!="Export")
        {
           //DEBUG MODE:
          // $htmlRespond .=$SystemTableName." " .$formType. " random number is ".$randomNumber."<br />";

           $textToDisplay=$formType;
           if($formType=="Search_ReadOnly")
           {
               $textToDisplay="Search";
           }
        	switch($formType)
        	{
        		case "Insert":
        			$textToDisplay=$entityDetails['inserttxt'];
        			break;
        		case "Search_ReadOnly":
				case "Search":
        			$textToDisplay=$entityDetails['searchtxt'];
        			break;
				case "UpdateDetails":
					$textToDisplay=$entityDetails['updatetxt'];
					break;
        		default:
        			break;
        	}
           if(!$readOnly)
           {
               $captcha="";
               if($formType=="Insert" && !empty($entityDetails['enableCaptcha']) && $entityDetails['enableCaptcha']=="1")
               {
                   $dir =  plugin_dir_url(__FILE__);
                     $captcha.="<div id='".$randomNumber."_recaptcha'></div><img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_AddRecaptcha('".$randomNumber."_recaptcha','".$GLOBALS["ReCaptchaPublic"]."','clean')\" />";
               }
               // $button="<input type=\"button\" onclick=\"return {$SystemTableName}_{$formType}({$randomNumber}, '{$formName}', '{$formType}', '{$SystemTableName}')\"
        	    //name=\"submit\" id=\"submit\" value=\"". DF_DBFuncs::translate($textToDisplay). "\" />";
                if($formType=="Search"||$formType=="Search_ReadOnly" || $formType=="Insert" || $formType=="UpdateDetails")
                {
                    $button="<input type=\"button\" onclick=\"return DFMake_".$formType."(".$randomNumber.", '".$formName."','".$SystemTableName."')\"
        	    name=\"submit\" id=\"submit\" value=\"". DF_DBFuncs::translate($textToDisplay). "\" />";
                }
                $buttonReset="";
                 if($formType=="Insert")
                {
                    $buttonReset="<input type=\"button\" onclick=\"return DF_Reset(".$randomNumber.", '".$formName."','".$SystemTableName."')\"
                name=\"submit\" id=\"submit\" value=\"". DF_DBFuncs::translate("Reset"). "\" />";
                }
        	    $htmlRespond .=$captcha.$buttonReset.$button;
        	}

        }
        if ($formType!="Export") $htmlRespond .= "</form>";
        return $htmlRespond;
    }
    
    public function CheckCaptcha($server,$challenge,$responce)
    {
        
        $myCaptcha=new DF_Recaptcha();
        $resp=$myCaptcha->recaptcha_check_answer($GLOBALS["ReCaptchaPrivate"],$server,$challenge,$responce);
        if($resp->is_valid)
        {
            return "true";
        }
        else
        {
            return $resp->error;
        }
        
    }

		/*
	   *Creates Email form with filled values
	   *
	   */
	private function createSendMailForm($sysId,$formType,$definitionsWithValues)
	{

		$formTemplate = $this->database->utf8_urldecode($this->database->getFormDesign($sysId,"Insert"));
		if($formTemplate=="")
		{
			$formTemplate = $this->database->utf8_urldecode( $this->database->getFormDesign($sysId,'default'));
		}
		if($formTemplate!="")
		{

			foreach($definitionsWithValues as $definition)
			{
				$insertValue=$definition->value;
                if($definition->input_type=="textarea")
                {
                    
                    $insertValue= html_entity_decode(stripslashes( $definition->utf8_urldecode($definition->value))) ;   
                    
                }
                     $insertValue = $this->ReloadValueIfFile($insertValue,$definition); 
                            
				$formTemplate=str_replace("[@".$definition->system_name."@]",$insertValue,$formTemplate);
                $formTemplate=str_replace("[@id|".$definition->system_name."@]",$definition->getId("",true),$formTemplate);
                
                
                
			//	$formTemplate.=" Tests: definition sysName = ".$definition->system_name. " value = ".$definition->value;
			}

		}
        
		return $formTemplate;
	}



    private function processUpdateDetailForm($systemTableName, $formType, $randomNumber, $definitions)
    {
    		$entityDetails= $this->database->getSysDetails($systemTableName);
    	// get all the definitions
    	$definitionsForUpdate = $this->database->get_AllDefinitions(
    			$systemTableName, $formType, $randomNumber,"","");
    	$currId=0;
        // fill the definitions with values from user
    	foreach ($definitionsForUpdate as $system_name=>$definitionForUpdate)
    	{
            if($system_name=="id")
            {
                $currId=$definitions[$system_name]->previous_value;
                if(!is_numeric($currId))
                         return "<font color='red'>". $entityDetails['failUpdatetxt']."<br />".DF_DBFuncs::translate("some unvalid parameters")."</font>";
            }
    		$definitionForUpdate->value = $definitions[$system_name]->value;
    		$definitionForUpdate->previous_value = $definitions[$system_name]->previous_value;
    	}


        //check if there is no row with the same id values!
        $check1 = "select * from ".$systemTableName;
        $where = $this->getWhereClause($definitionsForUpdate);

        if($where!="")
        {
            $where2="";
            if(isset($currId))
            {
                $where2=" and (not id=".mysql_real_escape_string($currId).")";
            }
            $results = DF_DBFuncs::sqlGetResults($check1.$where.$where2);
        }
        if ( $results["boolean"] == true && count($results["data"])>0)
        {

        	return "<font color='red'>".$entityDetails['failUpdatetxt']."<br />".DF_DBFuncs::translate("There is already item with the same ids in database")."</font>";
        }





        $query = "UPDATE $systemTableName SET ";
        foreach ($definitionsForUpdate as $definition) {
            if($definition->system_name=="id") continue;
            if(!$definition->isPossibleValue($definition->value))
                return DF_DBFuncs::translate("some unvalid parameters");
        	$query .= "{$definition->system_name}=".$definition->getFormattedValue($definition->value)."," ;
        }
        // remove the last unneeded comma from $query
        $query = substr ($query, 0, strlen($query)-1);

        $where="";
        if(isset($currId))
        {
            $where =" where id=".$currId;
        }
        if($where!="")
        {
           // echo $query.$where;
            $results = DF_DBFuncs::sqlExecute($query.$where,true);
        }
        if ( $results["boolean"] != true)
        {
        	return "<font color='red'>".$entityDetails['failUpdatetxt']."</font>";
        }
        else
        {
            return "success"."@@"."<font color='green'>".$entityDetails['successUpdatetxt']."</font>";
        }
    }

    public function getWhereClause($definitionsForUpdate,$update=false)
    {
        $query .= "";
        $count=0;
        foreach ($definitionsForUpdate as $definition) {
        	if ($definition->is_id == '1')
        	{

                if($update==false && $definition->system_name=="id")
                {
                    continue;
                }
                if($count>0)
                {
                    $query .= " and ";
                }


                if($update)
                {
                    $value = $definitionsForUpdate[$definition->system_name]->previous_value;
                }else
                {
                        $value = $definitionsForUpdate[$definition->system_name]->value;
                }
                if(!$definition->isPossibleValue($value))
                    return DF_DBFuncs::translate("some unvalid parameters");
        		$query .= "{$definition->system_name}=
        		'".mysql_real_escape_string($value)."' ";

                $count++;
        	}
        }
        if($count>0)
            $query = " WHERE ".$query;
        return $query;
    }

    public function returnValue()
    {
        return "test1";
    }


    public function fillDefinitionsWithPostValues($definitionsToFill,$definitionsFromPost)
    {
          foreach($definitionsFromPost as $definitionPost)
                {
                    foreach($definitionsToFill as $definitionSys)
                    {
                       if($definitionSys->system_name==$definitionPost->system_name)
                       {
                            $definitionSys->value = $definitionPost->value;
                       }
                    }
                }

    }

    public function processInsertForm($systemTableName, $formType, $randomNumber, $pageNum,
    		$indexNum, $systemTableName, $definitions,$currUrl='')
            {
    			$entityDetails= $this->database->getSysDetails($systemTableName);
    			$definitionsForSelect = $this->database->get_AllDefinitions(
    					$systemTableName, $formType, $randomNumber,"","");

                $this->fillDefinitionsWithPostValues($definitionsForSelect,$definitions);

                $sysDetails=$this->database->getSysDetails($systemTableName);
                 $senderMail="";
                //check if there is no row with the same id values!
                $check1 = "select * from ".$systemTableName;
                $where = $this->getWhereClause($definitionsForSelect);

                if($where!="")
                {
                    $results = DF_DBFuncs::sqlGetResults($check1.$where);
                }
                if ( $results["boolean"] == true && count($results["data"])>0)
                {
        	        return "<font color='red'>".$entityDetails['failInserttxt']."<br/>".DF_DBFuncs::translate("There is already item with the same ids in database")."</font>";
                }

                $query = "Insert into  ".$systemTableName;
                $query .= " Set ";
                 $count=0;
    			foreach ($definitions as $definitionPost)
    			{
                    foreach ($definitionsForSelect as $definition)
    			    {
                        
                        if($definition->system_name=="id")
                        {
                            continue;
                        }
                        if($definition->system_name ==$definitionPost->system_name && !empty($definitionPost->value))
                        {
                            if(!empty($sysDetails['sendMailToAdder']) && !empty($sysDetails['emailField'])&& $sysDetails['sendMailToAdder']=="1" && $definition->system_name==$sysDetails['emailField'])
                            {
                                $senderMail=    $definitionPost->value;
                            }
                            if($count>0)
                            {
                                $query .=",";
                            }
                            if(!$definition->isPossibleValue($definitionPost->value))
                                return "<font color='red'>".$entityDetails['failInserttxt']."<br/>".DF_DBFuncs::translate("some invalid parameters")."</font>";
                            $query .= $definition->system_name."=".$definition->getFormattedValue($definitionPost->value)." ";
                            $count++;
                        }
                    }

    			}

                //write query to the test file
                 if($count>0)
                {
                    echo $query."<br/>";
                    $results = DF_DBFuncs::sqlExecute($query);
                    
                }
                if ( $results["boolean"] != true)
                {
                    return $entityDetails['failInserttxt']."<br/>". DF_DBFuncs::translate(" : Database update was not successfull");
                }

                 $newId=$results['affected_id'];
				try
		    	{
	    			

					$email=$sysDetails['email'];

					if(isset($email) && !empty($email))
	    			{

                        
	    					//Send Mail
                            $generatedOn="";
	    					$htmlText = $this->createSendMailForm($sysDetails['entity_id'],$formType,$definitionsForSelect);
                            if($currUrl!="")
                            {
                                $generatedOn=DF_DBFuncs::translate("Generated on")." ".$currUrl;
                            }
							
                            if(!empty($sysDetails['sendMailToAdder']) && $sysDetails['sendMailToAdder']=="1" && !empty($senderMail))
                            {
                                $url="";
                                 if(!empty($sysDetails['validateAction']) && $sysDetails['validateAction']=="1" )
                                {
                                    $validationNum=rand(1,10000000000);
                                    $this->database->InsertNewValidation($systemTableName,$newId,$validationNum);
                                    $url ="<br/> <a href='".site_url()."?DF_ValidateMail=true&DF_MailNum=".$validationNum."' >". DF_DBFuncs::translate("Please validate your mail")."</a>"."";
                                        
                                }
                                //sendMailToAdder
                                DF_DBFuncs::sendMail($senderMail,$sysDetails['emailTitle'] ,$sysDetails['emailText'].$url."<br />".$htmlText );
                                DF_DBFuncs::sendMail($email,$sysDetails['emailTitle'] ,$sysDetails['emailText']."<br />".$htmlText."<br />".$generatedOn );
                            }
                            else
                            {
                                DF_DBFuncs::sendMail($email,$sysDetails['emailTitle'] ,$sysDetails['emailText']."<br />".$htmlText."<br />".$generatedOn );
                            }
                            
                            


	    			}

		    	}
	    		catch (Exception $e)
	    		{

	    		}
    	//add second parameter sycess message
                return "success"."@@"."<font color='green'>".$entityDetails['successInserttxt']."</font>";

            }


    public function processSearchForm($systemTableName, $formType, $randomNumber, $pageNum,
    		$indexNum,  $definitions,$readOnly=false)
    {

        	$query = "SELECT ";
    			$definitionsForSelect = $this->database->get_AllDefinitions(
    					$systemTableName, $formType, $randomNumber,"","");
                $count=0;
    			foreach ($definitionsForSelect as $definition)
    			{
                    if($count>0)
                    {
                        $query .=",";
                    }
    				$query .= "$definition->system_name";
                    $count++;
    			}
    			$query .= " FROM $systemTableName WHERE 1=1 ";
    			foreach ($definitions as $definitionPost)
    			{
                    foreach ($definitionsForSelect as $definition)
    			    {
                        if($definition->system_name ==$definitionPost->system_name && ! empty($definitionPost->value) &&
                        !($definition->input_type=="dropdownlist" && $definitionPost->value=="-") )
                        {
                            if(!$definition->isPossibleValue($definitionPost->value))
                                return DF_DBFuncs::translate("some unvalid parameters");
                            $query .= " and ".$definition->system_name." ".$definition->getSQLWhereFormat($definitionPost->value)." ";
                        }
                    }

    			}

    			//();
                $dfcrypt = new DF_Crypt(DF_DBFuncs::getDecryptionKey());
                $query=$dfcrypt->Encrypt($query);
                
                DF_Session::StoreSession("{$randomNumber}_query",$query);
                DF_Session::StoreSession("{$randomNumber}_pageNum",$pageNum);
                DF_Session::StoreSession("{$randomNumber}_indexNum",$indexNum);
                
                DF_Session::StoreSession("{$randomNumber}_readOnly",$dfcrypt->Encrypt($readOnly));

    }
    
    
    public function CreateDefaultSearch($systemTableName, $formType, $randomNumber, $pageNum,
            $indexNum,$readOnly=false)
    {
        
      
        $query = "SELECT ";
                $definitionsForSelect = $this->database->get_AllDefinitions(
                        $systemTableName, $formType, $randomNumber,"","");
                $count=0;
                foreach ($definitionsForSelect as $definition)
                {
                    if($count>0)
                    {
                        $query .=",";
                    }
                    $query .= "$definition->system_name";
                    $count++;
                }
                $query .= " FROM ".$systemTableName." WHERE 1=1 ";
                
                $orderBy="";
                $cntOrd=0;
                foreach ($definitionsForSelect as $definition)
                {
                    if(!empty($definition->defaultValue) )
                    {
                        $query .= " and ".$definition->system_name." ".$definition->getSQLWhereFormat($definition->defaultValue)." ";
                    }
                    
                      if(!empty($definition->orderType) && $definition->orderType!="None" )
                    {
                        $extra="";
                        if($cntOrd>0)
                        {
                            $extra=",";
                        }
                        $orderBys.=$extra.$definition->definition_id;
                        $orderTypes.=$extra.$definition->orderType;
                        
                         $cntOrd++;
                    }
                    
                }

                
                
                
                $dfcrypt = new DF_Crypt(DF_DBFuncs::getDecryptionKey());
                $query=$dfcrypt->Encrypt($query);
                
                 DF_Session::StoreSession("{$randomNumber}_orderby",$orderBys);
                 DF_Session::StoreSession("{$randomNumber}_ordertype",$orderTypes);
                 
                DF_Session::StoreSession("{$randomNumber}_query",$query);
                DF_Session::StoreSession("{$randomNumber}_pageNum",$pageNum);
                DF_Session::StoreSession("{$randomNumber}_indexNum",$indexNum);
               
                DF_Session::StoreSession("{$randomNumber}_readOnly",$dfcrypt->Encrypt($readOnly));
                
                
                
                
                
        
    }
    //for each definition get value
    //make an action with definitions
    //according to result renew form
    public function processForm($systemTableName, $formType, $randomNumber, $pageNum,
    		$indexNum, $systemTableName, $definitions,$noAllForms='no',$currUrl='')
    {
        $result= DF_DBFuncs::translate("success");
            $query='';
    	switch($formType)
    	{
    		case 'Insert':
    		    return $this->processInsertForm($systemTableName, $formType, $randomNumber, $pageNum,
    		$indexNum, $systemTableName, $definitions,$currUrl);
    			break;
    		case 'UpdateDetails':
    			return $this->processUpdateDetailForm($systemTableName, $formType, $randomNumber,$definitions);
    			break;
    		case 'UpdateGroup':

    			break;
            case 'Search_ReadOnly':
    		case 'Search':
    			 $readOnly=false;
                if($formType=='Search_ReadOnly') $readOnly=true;
                $this->processSearchForm($systemTableName, 'Search', $randomNumber, $pageNum,
    		$indexNum,$definitions,$readOnly);
                $result .= $this->CreateFormGeneral($systemTableName,"UpdateGroup",$randomNumber,$FormNum,$noAllForms,$readOnly);
    			break;
    	}

       // file_put_contents("tests\\{$_POST['random_number']}.txt", $keys);
       //return $query;
       return $result;
	}
    
    public function getHtmlOfForm($entity,$type,$randomNumber)
    {
            $myDynamic=$this;
            $accesstype=$type;
            
            $myDynamic->SetAuthorization($entity,$type,$randomNumber,"process");
            $myDynamic->SetAuthorization($entity,$type,$randomNumber,"build");
            if($type=="DefaultDisplay")
            {
                $accesstype="Search_ReadOnly";
            }
            $myDynamic->SetAuthorization($entity,$accesstype,$randomNumber,"build");
            $myDynamic->SetAuthorization($entity,$accesstype,$randomNumber,"process");
            if($type=="Search" || $type=="Search_ReadOnly" || $type=="DefaultDisplay")
            {
                
                
                $myDynamic->SetAuthorization($entity,"UpdateDetails",$randomNumber,"build");
                $myDynamic->SetAuthorization($entity,"UpdateDetails",$randomNumber,"process");
                $myDynamic->SetAuthorization($entity,"UpdateGroup",$randomNumber,"build");
                $myDynamic->SetAuthorization($entity,"UpdateGroup",$randomNumber,"process");
                $myDynamic->SetAuthorization($entity,"Export",$randomNumber,"build");
              
            }
            $authorized="";
            if($type=="Insert" || $type=="UpdateDetails")
            {
                //$authorized = " set load file authorization for ".$entity.$type.$randomNumber."loadfile";
                $myDynamic->SetAuthorization($entity,$type,$randomNumber,"loadfile");
            }
             if($type=="Search" || $type=="UpdateDetails")
            {
                $myDynamic->SetAuthorization($entity,"UpdateDetails",$randomNumber,"loadfile");
            }
            
            $htmlToAdd=$myDynamic->CreateFormGeneral($entity,$type,$randomNumber);
            return   $authorized.$htmlToAdd;
    }
    
    
    public function validateMail($mailNum)
    {
            return $this->database->Validate($mailNum);
    }
    
    public function RemoveEmail($email)
    {
        return $this->database->RemoveEmail($email);
    }

    
    public function sendMailToList($post_id,$sysid)
    {
        if ( is_admin() ){
            
            if(($mailSent=$this->database->IsMailSent($sysid,$post_id))==null)
            {
                $mailsList= $this->database->GetMailsList($sysid);
                 $sentMails=DF_DBFuncs::translate(" Send mails to list of ").count($mailsList).DF_DBFuncs::translate(" recipients ");
                 $count=1;
              $content_post = get_post($currid);
                 $html =  get_post_field('post_content', $post_id);
                 $title = get_post_field('post_title', $post_id);
                 
                 foreach($mailsList as $email)
                 {
                    $removeUrl="<hr><br/><a href='".site_url()."?DF_RemoveEmail=true&email=".$email."'>".DF_DBFuncs::translate(" Remove this email from your mailing lists ")."</a>"; 
                    DF_DBFuncs::sendMail($email,$title ,$html.$removeUrl );
                    $sentMails.=" ".$count. " ".$email;
                    $count++;
                 }
                 $this->database->SetSentMail($sysid,$post_id);
                 return $sentMails;
            }
            else
            {
                return   DF_DBFuncs::translate(" Post already sent on  ").$mailSent;
            }
        }
        else
        {
          return   DF_DBFuncs::translate(" You are not allowed to make this action ");
        }
    }
    
      public function getActionType($fieldId)
    {
       $action = " unknown";
       $actions =  array('Insert','Search','Search_ReadOnly','DefaultDisplay');
       foreach($actions as $actionType)
       {
           $pos= strpos($fieldId,$actionType);  
           if($pos)
           {
               $action = $actionType;
           }
       }
       return $action;
    }
}
?>