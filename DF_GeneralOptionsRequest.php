<?php
include_once 'DF_CMSFunctions.php';
include_once 'DF_GeneralOptions.php';

class DF_GeneralOptionsRequest
{
    public function Manage($PostVariables)
    {
          if(isset($PostVariables['SaveOptions']))
          {
              $response = "";
              if($PostVariables['curraction']=="save")
              {
                      
                      //Checks
                    $newSecKey = $PostVariables['sec_key'];
                    if(strlen($newSecKey)<7)
                    {
                        $response.=DF_DBFuncs::translate("Security key is too short"); 
                    }

                    if($response=="")
                    {
                        $GLOBALS["EncriptionKey"] = $newSecKey;
                        $GLOBALS["EncriptionKeyLoaded"]="true";
                        $query="update df_sys_general set 
                        sec_key='{$newSecKey}'";  
                        $results=DF_DBFuncs::sqlExecute($query);
                        if($results['boolean']!=true) 
                        {
                             $response.=DF_DBFuncs::translate("Didn't success to save");
                        }
                        
                        
                    } 
              }
            if($response=="")
            {
                $response = DF_DBFuncs::translate("success"); 
                
                 
            }
            $mygo=new DF_GeneralOptions();
            $response .= $mygo->GetHtml(false);
            return $response;   
            
          }
    }
}
?>
