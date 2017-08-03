<?php
/*
Form for defining new entities and their definitions
Use javascript functions to update the system
Work with DF_DefineEntitiesFunc.php
*/
//test note
include_once 'DF_CMSFunctions.php';
include_once 'DF_DataBase.php';


class DF_DefineEntities
{
    public function getHtml()
    {

        $mydatabase = new DF_DataBase();
            //();
           $force=true;
  
           echo $mydatabase->start_session_values();
   
   ?>
   <h1><?php echo DF_DBFuncs::translate("Stage")." 1: ".DF_DBFuncs::translate("Create")." / ". DF_DBFuncs::translate("Edit Forms"); ?></h1>
   
   <?php
         
          echo $mydatabase->GetEntitiesChose();
        ?>

   
         <div id="DefineNewEntity" style="width:40%"  >
             <input type="hidden" id="DF_Entity_path" value="<?php echo  DF_DBFuncs::getCurrentPluginURL();  ?>"/>
 	

             <?php
                echo $mydatabase->GetEntityDefinitionHtml();

             ?>
         </div>


          <div id="defineEntityResult"></div> 
   

        <?php
     }
}
?>