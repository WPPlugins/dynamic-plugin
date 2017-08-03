<?php
     include_once 'DF_DataBase.php';
 class DF_TestPage
 {

     public function getHtml()
     {
?>
 <input type="hidden" id="DF_pathTests" value="<?php echo  DF_DBFuncs::getCurrentPluginURL();  ?>"/>
<h1><?php echo DF_DBFuncs::translate("Stage")." 3: ".DF_DBFuncs::translate("Test Forms"); ?></h1>
<form method="post" name="test_form">
  <p>

  	<label for="system_table_name"><?php echo DF_DBFuncs::translate("Form Name"); ?></label>

    <?php

        $mydatabase = new DF_DataBase();
        echo $mydatabase->getEntitiesList('system_table_name');


    ?>

  </p>
  <p>
    <label for="form_type"><?php echo DF_DBFuncs::translate("View Type To View"); ?></label>
     <select name="form_type" id="form_type">
            <option value="Insert" selected="selected"><?php echo DF_DBFuncs::translate("Insert"); ?></option>
            <option value="Search"><?php echo DF_DBFuncs::translate("Search"); ?></option>
            <option value="Search_ReadOnly"><?php echo DF_DBFuncs::translate("Search - Read Only"); ?></option>
             <option value="DefaultDisplay"><?php echo DF_DBFuncs::translate("Default Display"); ?></option>
          </select>
  </p>
   <p>
    <input type="button" class="button-primary"  onclick="return DF_ajax_post()" name="submit" id="submit" value="<?php echo DF_DBFuncs::translate("View"); ?>" />

  </p>
</form>

    <div id="testField">

    </div>

<?php

 }
 }

?>