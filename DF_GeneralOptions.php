<?php
include_once 'DF_CMSFunctions.php';

      class DF_GeneralOptions
      {
          public function GetHtml($withDiv=true)
          {


             if($withDiv)
             {
                 ?>
                 <div id="OptionsDiv">
                 <?php
             }
             ?>
             <h1><?php echo DF_DBFuncs::translate("Dynamic Forms General Options"); ?></h1>
               <form id="SaveOptions" name="SaveOptions" >
               <table>
               <tr>
               <td><label for="sec_key"><?php echo DF_DBFuncs::translate("Security Key"); ?></td>
               </tr>
                <tr>
               <td><input type="text" id="sec_key" name="sec_key" value="<?php echo DF_DBFuncs::getDecryptionKey(); ?>"></td>
               </tr>
              
               <tr>
               <td>
                    <input type="button" onClick="return DF_SaveOptions()" value="<?php echo DF_DBFuncs::translate("Save"); ?>">
               </td>
               </tr>
               </table>
               </form>
               
               <p>
               * 
               <?php echo DF_DBFuncs::translate("This key is used to secure your session values. It is generated once you are installing your plugin."); ?>
               
               </p>  
               <br/>
				<hr/>
				<h2><?php echo DF_DBFuncs::translate("Support Our Project"); ?></h2>
                <p>  <?php echo DF_DBFuncs::translate("Dear User").","; ?>
                  </p>
<p>
 <?php echo DF_DBFuncs::translate("We are a couple of young, enthusiastic plugin developers from Israel. We have started up our own business without any investment whatsoever."); ?>
   </p>
<p>
<?php echo DF_DBFuncs::translate("We are in the process of developing a powerful, new plugin that will save users a lot of time and money in coding costs."); ?>
    </p>
<p><?php echo DF_DBFuncs::translate("While we have plans for adding lots of great features in the near future, we need your help in spreading the word about our new plugin, so that lots of people who will find this plugin useful get the opportunity to use and enjoy it."); ?></p>
<p><?php echo DF_DBFuncs::translate("It is absolutely free and anyone who is willing and able to help us promote our plugin will be appreciated and not forgotten."); ?></p>
<p><?php echo DF_DBFuncs::translate("If you willing to help us, you can do that in several ways"); ?>:</p>
<ol>
<li><a href="http://dynamicplugin.com/survey/" target="_blank"><?php echo DF_DBFuncs::translate("Send us your feedback"); ?></a></li>
<li><?php echo DF_DBFuncs::translate("Tell your friends about our plugin, giving this link"); ?> <a href="http://wordpress.org/plugins/dynamic-plugin/" target="_blank">wordpress.org/plugins/dynamic-plugin/</a></li>
<li><?php echo DF_DBFuncs::translate("Give us some good review at"); ?> <a href="http://wordpress.org/plugins/dynamic-plugin/" target="_blank">WordPress</a> <?php echo DF_DBFuncs::translate("repository"); ?> </li>
<li><?php echo DF_DBFuncs::translate("Consider making a"); ?>  <a href="http://dynamicplugin.com/support-us/" target="_blank"><?php echo DF_DBFuncs::translate("donation"); ?></a></li>
</ol>
 <p> <?php echo DF_DBFuncs::translate("Thank you in advance for your assistance"); ?> </p>   
 <p> <?php echo DF_DBFuncs::translate("Luba and Avishay"); ?></p>           
    

  
  
    <a href="http://dynamicplugin.com/how-to-use/" target="_blank"> 
    <h2>
      <?php
       echo DF_DBFuncs::translate("Read user guide");    
       ?> </h2>
    </a>
    


               <?php

                 if($withDiv)
                 {
                     ?>
                     </div>
                     <?php
                 }


          }

      }
?>