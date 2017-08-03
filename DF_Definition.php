<?php
include_once 'IDF_DynamicControl.php';
include_once 'DF_Crypt.php';
//include_once 'DF_DBFuncs.php';

class DF_Definition implements IDF_Dynamic_Control
{
    public $definition_id;
    public $sysTableName;
    public $formType;
  //  public $validationFunc;
   // public $jsValueCall;
    public $randomNum;
    public $name;
    public $system_name;	// name in mysql
    public $type;
    public $input_type;
    public $field_order;	// field order to display on the page


    public $appear_on_group;	// should it appear on group seach
    public $is_id;		// if it is key
    public $is_must;	// if it is mandatory field
    public $search_type;


    public $value;	// the value of this definition
    public $previous_value; //used in case of UpdateDetails action in order to store previous value
    public $isReadOnly;
    
    public $helpTxt;
    public $classTxt;
    public $styleTxt;
    public $defaultValue;
    public $isFilterable;
    public $filterType;
    public $orderType;
    
    
   
   public function getHidden($formType)
   {
         $cssClass = "";
        $extra = "";
        if(isset($this->classTxt) && $this->classTxt!="")
        {
            $cssClass = " class = '".$this->classTxt."'" ;
        }
        
         if(isset($this->styleTxt) && $this->styleTxt!="")
        {
            $cssClass .= " style= '".$this->styleTxt."' " ;
        }
        
        
        if(isset($this->helpTxt) && $this->helpTxt!="")
        {
            $unigue_ID = $this->getId("",true);
            $dir =  plugin_dir_url(__FILE__);
            
            $extra.="<img style='border:transparent;' src='".$dir."images\helpEngine.png'  onmouseover=\"df_ShowHide('".$unigue_ID."_help')\"  onmouseout=\"df_ShowHide('".$unigue_ID."_help')\" />";
            $extra .= "<div id='".$unigue_ID."_help' style='height:0; visibility: hidden; 
            background-color: AliceBlue;
    border: 1px solid Blue;
    padding: 5px;
    visibility:   hidden;
    position:absolute;
    width:200px;
            ' >".$this->helpTxt."</div>";
        }
        
        $result=$this->getHiddenHtml($cssClass, $formType);
        return $result.$extra;
        
   }

    // returns html representation of the defionition
    public function get_Html($formType)
    {
    	$cssClass = "";
        $extra = "";
        if(isset($this->classTxt) && $this->classTxt!="")
        {
            $cssClass = " class = '".$this->classTxt."'" ;
        }
        
         if(isset($this->styleTxt) && $this->styleTxt!="")
        {
            $cssClass .= " style= '".$this->styleTxt."' " ;
        }
        
        
        if(isset($this->helpTxt) && $this->helpTxt!="")
        {
            $unigue_ID = $this->getId("",true);
            $dir =  plugin_dir_url(__FILE__);
            
            $extra.="<img style='border:transparent;' src='".$dir."images\helpEngine.png'  onmouseover=\"df_ShowHide('".$unigue_ID."_help')\"  onmouseout=\"df_ShowHide('".$unigue_ID."_help')\" />";
            $extra .= "<div id='".$unigue_ID."_help' style='height:0; visibility: hidden; 
            background-color: AliceBlue;
    border: 1px solid Blue;
    padding: 5px;
    visibility:   hidden;
    position:absolute;
    width:200px;
            ' >".$this->helpTxt."</div>";
        }
        
    	switch ($this->input_type)
    	{
            case "htmleditor":
            case "textarea":
                $result=$this->getTextAreaHtml($cssClass, $formType);
                break;
            case "file": 
            case "image":   
    		case "text":
    			$result=$this->getTextHtml($cssClass, $formType);
    			break;
    		case 'hidden':
    			$result=$this->getHiddenHtml($cssClass, $formType);
    			break;
    			/*
    			case 'file':
    			break;
    			*/
    		case 'checkbox':
    			$result = $this->getCheckBox($cssClass, "checkbox", $formType);
    			break;

    		case 'dropdownlist':
    			$result = $this->getDropDownList($cssClass, $formType);
    			break;
    			/*
    			 case 'image':
    			break;
    			case 'password':
    			break;
    			*/
    		case 'radio':
    			$result = $this->getCheckBoxOrRadioHtml($cssClass, "radio", $formType);
    			break;
           /* case 'fileupload':
                $result = $this->getFileupload($cssClass, $formType);
                break;*/
    			/*
    			 case 'reset':
    			//validate reset
    			break;
    			*/
    	}
    	return $result.$extra;
    }

  /*  private function getFileupload($cssClass, $formType,$label=false)
    {
        $unigue_ID = $this->getId("",true);
        if($label)
        {
            $result = "<label for=\"$unigue_ID\">{$this->name}</label>";
        }
      
        $result.="<input type=\"file\" name=\"$unigue_ID\" id=\"$unigue_ID\" >";
         
         if(!empty($this->value[0]))
         {
            //print link to existing file.
            //add possibility to remove file.
         }
         
        return $result; 
    }                  */
    
	// the function recieves $value and returns unique id for the definition
	// $value contains data for radio or checkbox element
	// and is empty string for all the other elements
    public function getId($value,$crypted=false)
    {
    	$id = "{$this->getIdNoRandom($crypted)}_{$this->randomNum}";
    	if (empty($value) == false)
    	{
    		$id .= "_$value";
    	}
    	return $id;
	}

    public function getIdNoRandom($crypted=false)
    {
        $result="{$this->system_name}_{$this->input_type}_{$this->sysTableName}_{$this->formType}";

        if($crypted)
        {
            $myDF_Crypt=new DF_Crypt(DF_DBFuncs::getDecryptionKey());
            $result=$myDF_Crypt->Encrypt($result);
        }
        return $result;
    }
	// every control needs a hidden filed with the same value to store the value
	// this is needed to updateDetails action to store the previous value
	// that will be changed to new one
	private function getHiddenFiledForStoringValue($unigue_ID)
	{
		$value = mysql_real_escape_String(htmlentities($this->value[0]));
		return  "<input name=\"{$unigue_ID}_hidden\" type=\"hidden\" id=\"{$unigue_ID}_hidden\" value=\"{$value}\">";
	}



    // the function recieves $cssClass
    // returnes html code for this text input definition
    private function getTextHtml($cssClass, $formType,$label=false)
    {


        $unigue_ID = $this->getId("",true);
        if($label)
        {
            $result = "<label for=\"$unigue_ID\">{$this->name}</label>";
        }

    	$value = html_entity_decode(stripslashes($this->value[0]));
    	$result .="<input $cssClass name=\"$unigue_ID\" type=\"text\"  id=\"$unigue_ID\" value=\"{$value}\"";
    	$result .= "/>";

    	if ($this->type=="date") {
    			$dir =  plugin_dir_url(__FILE__);
    		
			$result.="<img style='border:transparent;' src='".$dir."images/Empty.png'  onload=\"DF_DatePicker('".$unigue_ID."')\" />";

		}
        
        $isSearch= strrpos(" ".$formType,"Search");

        
        if ($this->input_type=="file" && ($formType=="Insert"||$formType=="UpdateDetails")) {
                $dir =  plugin_dir_url(__FILE__);
            
            $result.=" <input type='button' id='".$unigue_ID."_uploader' value='Upload'><img style='border:transparent;' src='".$dir."images/Empty.png' ";
             $result.="onload=\"DF_AddUpClick('".$unigue_ID."_uploader', '".$unigue_ID."','". trim(preg_replace('/\s\s+/', ' ',DF_DBFuncs::Translate("Uploading file:")))."','". trim(preg_replace('/\s\s+/',' ',DF_DBFuncs::Translate("There is no output field")))."')\" />";

        }
        
         if ($this->input_type=="image" && ($formType=="Insert"||$formType=="UpdateDetails")) {
                $dir =  plugin_dir_url(__FILE__);
            
            $result.=" <input type='button' id='".$unigue_ID."_uploader' value='Upload'><img style='border:transparent;' src='".$dir."images/Empty.png' ";
             $result.="onload=\"DF_AddImageClick('".$unigue_ID."_uploader', '".$unigue_ID."','". trim(preg_replace('/\s\s+/', ' ',DF_DBFuncs::Translate("Uploading file:")))."','". trim(preg_replace('/\s\s+/',' ',DF_DBFuncs::Translate("There is no output field")))."')\" />";

        }



    	if ($formType == "UpdateDetails")
    	{
	    	$result .= $this->getHiddenFiledForStoringValue($unigue_ID);
    	}
    	return $result;
    }

    public function getTextAreaHtml($cssClass, $formType,$label=false)
    {

        $unigue_ID = $this->getId("",true);
        if($label)
        {
            $result = "<label for=\"$unigue_ID\">{$this->name}</label>";
        }


    	// $cssClass
    	//class="tinymce_data"
    	$value = html_entity_decode(stripslashes($this->value[0]));
    	$result .="<textarea $cssClass name=\"$unigue_ID\" type=\"text\"  id=\"$unigue_ID\" cols=\"50\" rows=\"2\" >"
        . $value."</textarea>";


		$dir =  plugin_dir_url(__FILE__);
    	//if $definition has validation function add validation function

    	$isSearch= strrpos(" ".$formType,"Search");

		if(!$isSearch && $this->input_type=="htmleditor")
		{
			//add tinyMCE
			$result.="<img style='border:transparent;' src='".$dir."images\Empty.png'  onload=\"DF_TinyInit('".$unigue_ID."')\" />";

		}

		if ($formType == "UpdateDetails")
    	{
	    	$result .= $this->getHiddenFiledForStoringValue($unigue_ID);
    	}

    	return $result;
    }

        //<textarea name="values" id="values" cols="50" rows="2"></textarea>


    // the function recieves $cssClass
    // returnes html code for this hidden definition
    private function getHiddenHtml($cssClass, $formType,$label=false)
    {
    	$unigue_ID = $this->getId("",true);

    	$result = "<input $cssClass name=\"$unigue_ID\" type=\"hidden\" id=\"$unigue_ID\" value=\"{$this->value[0]}\"";;
    	//if $definition has validation function add validation function
    	$result=$result."/>";
    	if ($formType == "UpdateDetails")
    	{
	    	$result .= $this->getHiddenFiledForStoringValue($unigue_ID);
    	}
        return $result;
    }

    // the function recieves "checkbox" or "radio" as a $type and $cssClass
    // returnes html code for this definition
    private function getCheckBoxOrRadioHtml($cssClass, $type, $formType,$label=false)
    {
        $count=0;
    	foreach ($this->values as $value)
    	{
    		$unigue_ID=$this->getId($value,true);
    		if($count>0) $result .="<br />";
    		$result .= "<input $cssClass name=\"$unigue_ID\" type=\"$type\" id=\"$unigue_ID\" value=\"{$value["value"]}\"";
    		if ($value["value"] == $this->value[0])
    		{
    			if ($type == "radio")
    			{
    				$result .= " checked=\"checked\"";
    			}
    			if ($type == "checkbox")
    			{
    				$result .= " checked";
    			}
    		}
    		$result .= "/>";
             $result.="<label for=\"$unigue_ID\">{$value["name"]}</label>";
           $count++;
    	}
    	$result .= "";
    	if ($formType == "UpdateDetails")
    	{
			$result .= $this->getHiddenFiledForStoringValue($unigue_ID);
   		}
    	//if $definition has validation function add validation function
    	return $result;
    }
    
    private function getCheckBox($cssClass, $type, $formType,$label=false)
    { 
        $unigue_ID = $this->getId("",true);
        if($count>0) $result .="<br />";
        $result .= "<input $cssClass name=\"$unigue_ID\" type=\"$type\" id=\"$unigue_ID\" "; 
        if ($type == "checkbox" && $this->value[0]==1)
        {
            $result .= " checked";
        }
        $result .= "/>";
         $result.="<label for=\"$unigue_ID\">{$value["name"]}</label>";
     
    
        $result .= "";
        if ($formType == "UpdateDetails")
        {
            $result .= $this->getHiddenFiledForStoringValue($unigue_ID);
           }
        //if $definition has validation function add validation function
        return $result;
    }



    // returnes html code for dropdown list (html select)
    private function getDropDownList($cssClass, $formType,$label=false)
    {
    	$unigue_ID = $this->getId("",true);
        $result="";
    	if($label)$result = "<label for\"$unigue_ID\">$this->name </label>";
    	$result .= "<select $cssClass name=\"$unigue_ID\" id=\"$unigue_ID\" value=\"".$this->value[0]."\">";
        if($formType=="Search" ||$formType=="Search_ReadOnly")
        {
            $result .= "<option $cssClass
            value\"-\"";
            $result .= ">-</option>";
        }
    	foreach ($this->values as $value)
    	{
    		$valueC = html_entity_decode(stripslashes($value["value"]));
			$result .= "<option $cssClass value=\"{$valueC}\"";
    		// this option contains the value of this definition

    		if ($valueC ==$this->value[0])
    		{
    			$result .= " selected=\"selected\"";
    		}
    		$result .= ">{$value["name"]}</option>";
    	}
    	$result .= "</select>";

    	if ($formType == "UpdateDetails")
    	{
    		$result .= $this->getHiddenFiledForStoringValue($unigue_ID);
    	}
    	return $result;
    }


    public function isPossibleValue($TValue)
    {

       
        $result=true;
        switch($this->type)
        {
           case "double":
                 $result=is_numeric($TValue);
            break;
           case "integer":
                $result=is_numeric($TValue);
              break;
            case "boolean":
               if ($TValue==1 || $TValue==0)
               {
                    $result=true;
               }
               else
               {
                   $result=false;
               }
             break;
            case "date":
                $dateArr=split('-',$TValue);
                if(count($dateArr)==3)
                {
                    $result=checkdate($dateArr[1],$dateArr[2],$dateArr[0]);
                }
                else
                {
                    $result=false;
                }
               break;
            default:
               break;

        }
        return $result;
    }



    public function getSQLWhereFormat($Tvalue)
    {

             $equation = "";
             switch($this->search_type)
             {
                 case "equal":
                    $equation = "=".$this->getFormattedValue($Tvalue);
                 break;
                 case "contains":
                    $equation = " like ".$this->getFormattedValue($Tvalue,$this->search_type);
                 break;
                 case "more":
                    $equation = ">".$this->getFormattedValue($Tvalue);
                 break;
                 case "less":
                    $equation = "<".$this->getFormattedValue($Tvalue);
                 break;
                  case "more_equal":
                    $equation = ">=".$this->getFormattedValue($Tvalue);
                 break;
                 case "less_equal":
                    $equation = "<=".$this->getFormattedValue($Tvalue);
                 break;
                 case "between":

                    $values=split(',',$Tvalue);
                    if(count($values)==2)
                   {
                         $equation = " between ".$this->getFormattedValue($values[0])." and ".$this->getFormattedValue($values[1]);
                    }

                 break;
                 default:
                    $equation = "=".$this->getFormattedValue($this->value);
                 break;

             }
        return $equation;

    }


	public function replace_unicode_escape_sequence($match) {
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
	}


	public function utf8_urldecode($str) {
		$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
		return html_entity_decode($str,null,'UTF-8');;
	}

    public function getFormattedValue($Tvalue, $searchType="")
    {

		if($this->input_type=="textarea")
    	{

    		$Tvalue= $this->utf8_urldecode( $Tvalue);
    	//	echo $Tvalue;
    	}

    	
        $Tvalue = mysql_real_escape_string($Tvalue);

    	


        $result="";
         switch($this->type)
        {
        case "text": 
          case "string":
            if( $searchType=="contains")
            {
                $result.="'%".$Tvalue."%'";
            }
            else
            {
                $result.="'".$Tvalue."'";
            }
            break;
            case "double":
                $result.="".$Tvalue."";
            break;
            case "integer":
                $result.="".$Tvalue."";
            break;
            case "boolean":
                $result.="".$Tvalue."";
            break;
            case "date":
                $result.="'".$Tvalue."'";
            break;
            default :
                $result.="'".$Tvalue."'";
                break;

        }
        return $result;

    }



}

?>