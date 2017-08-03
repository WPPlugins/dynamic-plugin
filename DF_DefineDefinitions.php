<?php
/*
Form for defining new entities and their definitions
Use javascript functions to update the system
Work with DF_DefineEntitiesFunc.php
*/
//test note
include_once 'DF_CMSFunctions.php';
include_once 'DF_DataBase.php';
include_once 'DF_Session.php';   


    class DF_DefineDefinitionsUI
    {
    public function getHtml() {
        $dir = plugin_dir_url(__FILE__);
        $dirImages=$dir."/images/";
        $mydatabase = new DF_DataBase();
        
        $force=true;
  
        echo $mydatabase->start_session_values();
        

?>

    
  <input type="hidden" id="DF_path" value="<?php echo DF_DBFuncs::getCurrentPluginURL();  ?>"/>
          
   <h1><?php echo DF_DBFuncs::translate("Stage")." 2 : ".DF_DBFuncs::translate("Create")." / ".DF_DBFuncs::translate("Edit Fields and Views"); ?></h1>
   
   
 
    <table >
    <tr><td>
      <?php
           $database = new DF_DataBase();
            
          echo $database->GetEntitiesChose();
          
         
       ?>    
    </td></tr>
    <tr>
    
    <td style="vertical-align: top;">
          <div id="definitionsDetails" class="DF_datagrid">
    <?php
  
    $database = new DF_DataBase();
    echo $database->getDefinitionsOfCurrentEntity(DF_Session::LoadSession('entity_id'),DF_Session::LoadSession('sysTableName'));
   
    
    ?>
   
</div>


 <label ><?php echo DF_DBFuncs::translate("Add/Edit Field");?></label><input type="button" id="ShowDefinitionDetails" style="background:url('<?php echo $dirImages."ZoomIn.png"?>') no-repeat; width:26px; height:26px; background-color: transparent; border: transparent;  "  onClick="return DF_ShowEntity('DefinitionsDetails',this)" />

<div id="DefinitionsDetails" style="visibility: hidden;position: relative;height:0;">
<table>
<tr>
    
    <td>
   
        <div class='DF_datagrid'>
<form name="defineDefinitionForm" >
    <table >
         <thead>
         <tr>
            <th  colspan="4"><label ><?php echo DF_DBFuncs::translate("Field Settings"); ?></label></th>
        </tr>
         </thead>
         <tbody>
        <tr>
            <td ><label for="definitionname"><?php echo DF_DBFuncs::translate("Name")?></label></td>
           
                  <td><label for="input_type"><?php echo DF_DBFuncs::translate("Input Type")?></label></td>
                </tr>
        
         

     

        <tr>
                
                     <td><input id="definitionname" name="definitionname" type="text" onchange="return DF_SetSystemName(this)" /> </td>  
                    <td>    
                        <select name="input_type" id="input_type" onchange=" return DF_ChangeTypeByInputType()">
                        <option value="text" selected="selected"><?php echo DF_DBFuncs::translate("text")?></option>
                        <option value="textarea"><?php echo DF_DBFuncs::translate("text area")?></option>
                        <option value="htmleditor"><?php echo DF_DBFuncs::translate("html editor")?></option>
                        <option value="dropdownlist"><?php echo DF_DBFuncs::translate("dropdownlist")?></option>             
                        <option value="radio"><?php echo DF_DBFuncs::translate("radio")?></option> 
                        <option value="checkbox"><?php echo DF_DBFuncs::translate("checkbox")?></option>
                        <option value="file"><?php echo DF_DBFuncs::translate("file")?></option>
                        <option value="image"><?php echo DF_DBFuncs::translate("image")?></option>
                     <!--   <option value="fileupload"><?php echo DF_DBFuncs::translate("fileupload")?></option>-->
                        </select>
               
                    </td>
       
           
            
        </tr>
          <tr>                   <td colspan="2"><label for="values"><?php echo DF_DBFuncs::translate("Values")?></label></td>
             </tr>
             
              <tr>
                    <td colspan="2"><textarea name="values" id="values" cols="70" rows="2"></textarea></td>
                </tr>
        
         
               <tr>
                <td><label for="is_must"><?php echo DF_DBFuncs::translate("Mandatory")?></label></td>
              
                    <td>
                    <input type="radio" name="is_must" id="is_must" value="1" /><?php echo DF_DBFuncs::translate("yes")?><br/>
                    <input type="radio" name="is_must" id="is_must" value="0" checked="checked" /><?php echo DF_DBFuncs::translate("no")?>
                </td>
                   </tr>

        <tr>
                    <td  colspan="4"><label ><?php echo DF_DBFuncs::translate("Advanced settings"); ?></label>
              <input type="button" id="definitionTechDetails" style="background:url('<?php echo $dirImages."ZoomIn.png"?>') no-repeat; width:26px; height:26px; background-color: transparent; border: transparent;   "  onClick='return DF_ShowHide("TechnicalDetails",this)' />
                
            </td>
             </tr>
             
       
       

        <tr>
        <td colspan="4">

              <div id="TechnicalDetails" style="position: relative;height:0; visibility: hidden; " >
        <table style="width: 100%; text-align: center;">
            
            <tr>
            <td><label for="system_name">
                <?php echo DF_DBFuncs::translate("System Name")?></label>
            </td>  
           
              <td> <label for="type"><?php echo DF_DBFuncs::translate("Type")?></label></td>
                  </tr>
             
           
            <tr>
                    <td><input id="system_name" name="system_name" type="text" onchange="return DF_CleanField(this)" /></td>
                 <td> <select name="type" id="type"  onchange="return DF_ChangeInputTypeByType()">
                        <option value="double"><?php echo DF_DBFuncs::translate("double")?></option>
                        <option value="integer"><?php echo DF_DBFuncs::translate("integer")?></option>
                        <option value="string" selected="selected"><?php echo DF_DBFuncs::translate("text")?></option>
                        <option value="text" ><?php echo DF_DBFuncs::translate("long text")?></option>
                        <option value="date"><?php echo DF_DBFuncs::translate("date")?></option>
                        <option value="boolean"><?php echo DF_DBFuncs::translate("boolean")?></option>
                        </select></td>  
                   
            </tr>
           
             
           
           
             
           
            <tr>
             <td><label for="search_type"><?php echo DF_DBFuncs::translate("Search Type")?></label></td>
                <td> <label for="field_order"><?php echo DF_DBFuncs::translate("Field Order")?></label></td>
            
                    
                     </tr>
              <tr>
                   
                    
                      <td>
             
                          <select name="search_type" id="search_type">
                        <option value="equal" selected="selected"><?php echo DF_DBFuncs::translate("equal")?></option>
                        <option value="contains"><?php echo DF_DBFuncs::translate("contains")?></option>
                        <option value="more"><?php echo DF_DBFuncs::translate("more")?></option>
                        <option value="less"><?php echo DF_DBFuncs::translate("less")?></option>
                        <option value="more_equal"><?php echo DF_DBFuncs::translate("more equal")?></option>
                        <option value="less_equal"><?php echo DF_DBFuncs::translate("less equal")?></option>
                        <option value="between"><?php echo DF_DBFuncs::translate("between")?></option>
                        </select>
              
                    </td>
                    <td> <input type="text" name="field_order" id="field_order" /></td>
            </tr>
              <tr>
                
          
               
                     <td><label for="is_id"><?php echo DF_DBFuncs::translate("Unique")?></label></td>
               
                        <td>
                    <input type="radio" name="is_id" id="is_id" value="1"  /><?php echo DF_DBFuncs::translate("yes")?><br/>
                    <input type="radio" name="is_id" id="is_id" value="0" checked="checked" /><?php echo DF_DBFuncs::translate("no")?>
                    </td>
            </tr>
             
           
              <tr>
               <td><label for="appear_on_group"><?php echo DF_DBFuncs::translate("Appear on Group?")?></label></td>
               
               <td>
                     <label for="isFilterable"><?php echo DF_DBFuncs::translate("Display filter by Field In Group (*Coming soon)")?>
                  </td>
                  
                 </tr>
              
              <tr> 
                <td>
                    <input type="radio" name="appear_on_group" id="appear_on_group" value="1" checked="checked" /><?php echo DF_DBFuncs::translate("yes")?><br/>
                    <input type="radio" name="appear_on_group" id="appear_on_group" value="0" /><?php echo DF_DBFuncs::translate("no")?>
                    </td>
                    
                      <td>
                              <input type="radio" name="isFilterable" id="isFilterable" value="1" checked="checked" /><?php echo DF_DBFuncs::translate("yes")?><br/>
                        <input type="radio" name="isFilterable" id="isFilterable" value="0" /><?php echo DF_DBFuncs::translate("no")?>
                  </td> 
              </tr>
              
              <tr>
                  <td colspan="2">
                        <label for="helpTxt"><?php echo DF_DBFuncs::translate("User Help Text")?>
                        
                  </td>  
                  
                  </tr>
              
              <tr> 
                  <td colspan="2">
                        <input id="helpTxt" name="helpTxt" type="text"  style="width:370px"  />
                  </td>
                 
              </tr>
              
              
                <tr>
              
                  <td colspan="2">
                         <label for="styleTxt"><?php echo DF_DBFuncs::translate("Style Free Text")?>
                  </td>
              
              </tr>
              
              <tr>
               <td colspan="2">
                       <input id="styleTxt" name="styleTxt" type="text" style="width:370px"  />
                  </td>
               
                 
              
              </tr>
              
              <tr>
                     
                  
                   <td>
                     <label for="classTxt"><?php echo DF_DBFuncs::translate("Style class name")?>
                  </td>
                     
                       <td>
                     <label for="defaultValue"><?php echo DF_DBFuncs::translate("Default Search Value")?>
                  </td>
                    
                 
                  </tr>
              
            
            <tr>
            
                <td>
                  <input id="classTxt" name="classTxt" type="text" style="width:150px" />
                     
                  </td>
                  
                    <td>
                             <input id="defaultValue" name="defaultValue" type="text"  />
                  </td>
            
            </tr>
            
              
               <tr>
                
                  <td>
                   <label for="filterType"><?php echo DF_DBFuncs::translate("Filter Type (*Coming soon)")?>
                      
                  </td> 
                    <td>
                  <label for="orderType"><?php echo DF_DBFuncs::translate("Order Type")?>
                      
                  </td>
              
              </tr>
              
               <tr>
                   
                    
                      <td>
             
                          <select name="filterType" id="filterType">
                        <option value="Default" selected="selected"><?php echo DF_DBFuncs::translate("Default")?></option>
                        <option value="FreeText"><?php echo DF_DBFuncs::translate("Free Text")?></option>
                        <option value="ExistingValues"><?php echo DF_DBFuncs::translate("Existing Values")?></option>
                        </select>
              
                    </td>
                    
                    <td>
             
                          <select name="orderType" id="orderType">
                        <option value="None" selected="selected"><?php echo DF_DBFuncs::translate("None")?></option>
                        <option value="ASC"><?php echo DF_DBFuncs::translate("Ascending")?></option>
                        <option value="DESC"><?php echo DF_DBFuncs::translate("Descending")?></option>
                        </select>
              
                    </td>
            </tr>
            
              
               
            
           
            <tr>
            <td colspan="2" >
              
            
                <input id="sysTableName" style="visibility:hidden;" name="sysTableName" type="text" readonly="readonly" class="Df-table-head-values" value="<?php echo DF_Session::LoadSession('sysTableName'); ?>" />
           
                <input id="entity_id" style="visibility:hidden;" name="entity_id" type="text" readonly="readonly" class="Df-table-head-values"  value="<?php echo DF_Session::LoadSession('entity_id'); ?>" />
            
                
                <input id="definition_id" style="visibility:hidden;" name="definition_id" class="Df-table-head-values"  type="text"  readonly="readonly" />
            </td>
           
        </tr>
        </table>
                  </div>
        </td>
        </tr>
           
        
        
         <tr>
            <td colspan="4" class="Df-Definitions-List">
                <input type="button"  class="button-primary" id="addNewButton" onClick="return DF_Define_New_Entity_Definition('defineDefinition')" value="<?php echo DF_DBFuncs::translate("Save")?>" />
                <input type="button"  class="button-primary"  onClick="return DF_emptyFields()" value="<?php echo DF_DBFuncs::translate("Clear")?>" />
                </td>
        </tr>
        <tr>
            <td colspan="4" class="Df-Definitions-List">
                 <div id="defineDefinitionResult"></div>
            </td>
            
        </tr>
      </tbody> 
       
    </table>
 
   
        <!-- Finish Definitions button initiates creation of javascript files -->
        <p>
        
        </p>
        
</form>
</div>

    </td>
</tr>
</table>

</div>
    </td>
    <td >
        
     
        <div class='DF_datagrid'>
        <table>
        <thead>
        <tr>
        <th>
           <?php echo DF_DBFuncs::translate("View Settings"); ?>
        </th>
        </tr>
        </thead>
        <tbody>
        <tr><td>
          <div id="TinyTextEditor" style="position: relative;height: fit-content; visibility: visible; ">
         
            <label id="formTypeLabel" ><?php echo DF_DBFuncs::translate("View Type"); ?></label>
         
            <select name='formType' id='formType'>
            <option value='Default'><?php echo DF_DBFuncs::translate("Default"); ?></option>
             <option value='Search'><?php echo DF_DBFuncs::translate("Search"); ?></option>
            <option value='Insert'><?php echo DF_DBFuncs::translate("Insert"); ?></option>
            <option value='UpdateDetails'><?php echo DF_DBFuncs::translate("Details Update"); ?></option>
            
        </select>

             <input type="button" class="button-primary" onClick="return DF_GetFormDesign()" value="<?php echo DF_DBFuncs::translate("Load"); ?>" />
             
              <div id="TinyTextEditor">
                         
                        <textarea id="TinyTxtArea" name="TinyTxtArea" rows="30" cols="100" style="width: 50%"></textarea>
                     
                     <input type="button" class="button-primary" onClick="return DF_SaveFormDesign()" value="<?php echo DF_DBFuncs::translate("Save"); ?>" />
                     <label  style=" color: green; font-weight: bold;"> * <?php echo DF_DBFuncs::translate("You can add this form using special admin box in page/post editor"); ?></label>
                     
                     
                   
                </div>
                <br/>
                 <input type="button" id="refreshForm" style="background:url('<?php echo $dirImages."refresh.png"?>') no-repeat; width:32px; height:32px; background-color: transparent; border: transparent;   "  onClick='return DF_Show_Form()' />
                 
               
                <div id='saveFormActionResult'></div> 
                 <div id='definitionsActionResult'></div> 
                   <h2><?php echo DF_DBFuncs::translate("Preview"); ?>:</h2>
                      <div id="testField">

                      </div>
                      
                    
            </div>
        </td></tr>
        
        
        </tbody>
        </table>
        </div>
       
     
         
     
      

        <div id="DefinitionDetails" style="position: relative;height:0; visibility: hidden; ">
        
           

    






 </div>
       
      
       

      
    
    </td>
    </tr>
    
    </table>

    <h3>
     <A href="http://dynamicplugin.com/survey/" target="_blank"><?php echo DF_DBFuncs::translate("Please, fill up our short survey");?></a> 
        </h3>
        
       
<?php
          
    }
 }
?>