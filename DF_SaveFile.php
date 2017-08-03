<?php
 require_once( ABSPATH  . 'wp-admin/includes/file.php' );
class DF_SaveFile
{
      public function Manage($PostFILES)
    {
        
        
       
            
          $upload_overrides = array( 'test_form' => false );  
          $uploadedfile = $PostFILES['Filedata'];
        $upload = wp_handle_upload($uploadedfile,$upload_overrides);

        // This message will be passed to 'oncomplete' function
        return ($upload['error'])?" error: ".$upload['error']:$upload['url']." | ".$upload['file'];
    }
}
?>
