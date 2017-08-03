<?php
/*
Plugin Name: Dynamic Wordpress Form Builder
Plugin URI: http://dynamicplugin.com/
Description: This plugin is not supported on wordpress version 3.6.1. No future upgrades will be available on wordpress repository and no support 
Version: 3.0.5
Author: BusyPlace
License: GPL
*/

include_once 'DF_DynamicRequest.php';
include_once 'DF_DefineEntitiesRequest.php';
include_once 'DF_DefineEntities.php';
include_once 'DF_DefineDefinitions.php';
include_once 'DF_TestPage.php';
include_once 'DF_DataBase.php';
include_once 'DF_GeneralOptions.php';
include_once 'DF_GeneralOptionsRequest.php';
include_once 'DF_SaveFile.php';
include_once 'DF_Crypt.php';


/* Runs when plugin is activated */
register_activation_hook(__FILE__,'df_install');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'df_remove' );

function df_install() {
/* Creates new database field */
   $mydb=new DF_DataBase();
   $mydb->createDatabaseSystem();

}

function df_remove() {

}

add_action('init', 'df_myStartSession', 1);
function df_myStartSession() {
    if(!session_id()) {
        session_start();
    }
} 

function my_scripts_method() {

	$dir =  plugin_dir_url(__FILE__);




	$pos= strrpos($_SERVER["REQUEST_URI"],"df_define_entities");
	if($pos)
	{
       
		wp_deregister_script( 'jqueryDF_DefineEntities' );
		wp_register_script( 'jqueryDF_DefineEntities', $dir.'jscripts/DF_DefineEntities.js');
		wp_enqueue_script( 'jqueryDF_DefineEntities' );

		wp_deregister_style( 'define_Definitions' );
		wp_register_style( 'define_Definitions', $dir.'/styles/DF_Style.css');
		wp_enqueue_style( 'define_Definitions' );
	}

	$pos2= strrpos($_SERVER["REQUEST_URI"],"df_define_definitions");
	if($pos2)
	{
		wp_deregister_script( 'jquery1_5' );
		wp_register_script( 'jquery1_5', 'http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		wp_enqueue_script( 'jquery1_5' );

		wp_deregister_script( 'jquery1_8' );
		wp_register_script( 'jquery1_8', 'http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
		wp_enqueue_script( 'jquery1_8' );
		wp_deregister_script( 'jqueryDF_DefineDefinitions' );
		wp_register_script( 'jqueryDF_DefineDefinitions', $dir.'jscripts/DF_DefineDefinitions.js');
		wp_enqueue_script( 'jqueryDF_DefineDefinitions' );
        
        wp_deregister_script( 'jqueryDF_DefineEntities' );
        wp_register_script( 'jqueryDF_DefineEntities', $dir.'jscripts/DF_DefineEntities.js');
        wp_enqueue_script( 'jqueryDF_DefineEntities' );

		wp_deregister_script( 'jquerytiny_mce' );
		wp_register_script( 'jquerytiny_mce', $dir.'jscripts/tiny_mce/tiny_mce.js');
		wp_enqueue_script( 'jquerytiny_mce' );

		wp_deregister_style( 'define_Definitions' );
		wp_register_style( 'define_Definitions', $dir.'/styles/DF_Style.css');
		wp_enqueue_style( 'define_Definitions' );
	}
	$pos3= strrpos($_SERVER["REQUEST_URI"],"df_GeneralOptions");
	if($pos3)
	{
		wp_deregister_script( 'jqueryDF_SaveOptions' );
		wp_register_script( 'jqueryDF_SaveOptions', $dir.'jscripts/DF_SaveOptions.js');
		wp_enqueue_script( 'jqueryDF_SaveOptions' );
	}



	$pos5= strrpos($_SERVER["REQUEST_URI"],"post-new.php");
	$pos8= strrpos($_SERVER["REQUEST_URI"],"post.php");
	if( $pos5 || $pos8)
	{
		wp_deregister_script( 'jqueryDF_SaveOptions' );
		wp_register_script( 'jqueryDF_SaveOptions', $dir.'jscripts/DF_MakeActions.js');
		wp_enqueue_script( 'jqueryDF_SaveOptions' );
	}
	$pos6= strrpos($_SERVER["REQUEST_URI"],"wp-admin");

	$pos4= strrpos($_SERVER["REQUEST_URI"],"df_test_page");
	if( $pos4 || (!$pos && !$pos2 && !$pos3 && !$pos4 && !$pos5 && !$pos6 && !$pos8))
	{


		wp_deregister_script( 'zaria' );
		wp_register_script( 'zaria', $dir.'jscripts/zaria/zaria.js');
		wp_enqueue_script( 'zaria' );

		wp_deregister_script( 'zaria_htmlparser' );
		wp_register_script( 'zaria_htmlparser', $dir.'jscripts/zaria/htmlparser.js');
		wp_enqueue_script( 'zaria_htmlparser' );

		wp_deregister_style( 'zaria_style' );
		wp_register_style( 'zaria_style', $dir.'jscripts/zaria/style.css');
		wp_enqueue_style( 'zaria_style' );

		wp_deregister_script( 'datepickr_1.0' );
		wp_register_script( 'datepickr_1.0', $dir.'jscripts/datepickr_1.0/datepickr.js');
		wp_enqueue_script( 'datepickr_1.0' );

		wp_deregister_style( 'DF_calendarStyle' );
		wp_register_style( 'DF_calendarStyle', $dir.'styles/DF_calendarStyle.css');
		wp_enqueue_style( 'DF_calendarStyle' );

			//	wp_enqueue_script( 'jquery' );
		wp_deregister_script( 'jqueryDF_MakeActions' );
		wp_register_script( 'jqueryDF_MakeActions', $dir.'jscripts/DF_MakeActions.js');
		wp_enqueue_script( 'jqueryDF_MakeActions' );
        
        wp_deregister_script( 'jqueryRecaptcha' );
        wp_register_script( 'jqueryRecaptcha', 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
        wp_enqueue_script( 'jqueryRecaptcha' );
        
        wp_deregister_script( 'jquerytiny_upclick' );
        wp_register_script( 'jquerytiny_upclick', $dir.'jscripts/upclick/upclick.js');
        wp_enqueue_script( 'jquerytiny_upclick' );
        
        //http://www.google.com/recaptcha/api/js/recaptcha_ajax.js
	}
}

add_action('wp_enqueue_scripts', 'my_scripts_method');
add_action('admin_enqueue_scripts', 'my_scripts_method');

        //Add to Post / Page possibility to add forms
         add_action( 'add_meta_boxes', 'df_add_custom_box' );

        function df_add_custom_box()
        {
	       add_meta_box( 'df_box',	 // Unique ID
          'Add dynamic forms',	 // Title
           'df_add_forms_box',	 // Callback function
           'page',	 // Admin page (or post type)
           'side',	 // Context
           'high'	 // Priority
           );

           add_meta_box( 'df_box',	 // Unique ID
          'Add dynamic forms',	 // Title
           'df_add_forms_box',	 // Callback function
           'post',	 // Admin page (or post type)
           'side',	 // Context
           'high'	 // Priority
           );

        }

        function df_add_forms_box(  ) {

            $myDef= new DF_DynamicForms();
            echo $myDef->GetAddPanelBoxToPost();
        }


       
        
        //filter post/pages and create needed form
        add_filter( 'the_content', 'df_content_filter', 20 );

        function df_content_filter( $content ) {

            $myDynamic =new DF_DynamicForms();

            $formTypes= array('Insert','Search','Search_ReadOnly','DefaultDisplay');
             $mydatabase = new DF_DataBase();
             $results =  $mydatabase->getEntitiesListRes();
             if($results['boolean']==true)
             {
                 foreach($results['data'] as $row)
                 {
                     foreach($formTypes as $formType)
                     {

                         if(strrpos($content,"[@".$row[sysname]."-".$formType."@]")>-1)
                         {
                            $randomNumber=rand(1,1000000);
                            
                            $htmlToAdd= $myDynamic->getHtmlOfForm($row[sysname],$formType,$randomNumber);
                           
                            $content=str_replace("[@".$row[sysname]."-".$formType."@]",$htmlToAdd, $content);
                         }

                     }
                 }
             }
            return $content;
        }




         //Ajax calls
         add_action('wp_ajax_saveOptions', 'prefix_ajax_saveOptions');
         function prefix_ajax_saveOptions()
         {
                $myDef=new DF_GeneralOptionsRequest();
                echo $myDef->Manage($_POST);
              
         }

         add_action('wp_ajax_defineDefinitions', 'prefix_ajax_defineDefinitions');
         function prefix_ajax_defineDefinitions()
         {
                $myDef=new DefineEntitiesRequest();
                echo $myDef->Manage($_POST);
         }

         add_action('wp_ajax_defineMyEntities', 'prefix_ajax_defineMyEntities');
         function prefix_ajax_defineMyEntities()
         {

                $myDef=new DefineEntitiesRequest();
                echo $myDef->Manage($_POST);
         }
         
         
       
         add_action('wp_ajax_nopriv_DFDynamicRequest', 'prefix_ajax_DFDynamicRequest');
         add_action('wp_ajax_DFDynamicRequest', 'prefix_ajax_DFDynamicRequest');
         function prefix_ajax_DFDynamicRequest()
         {
                $myDef=new DF_DynamicRequest();
                echo $myDef->Manage($_POST);
         }
         
         add_action('parse_request', 'df_load_file');
         add_action('admin_action_DFloadFile', 'df_load_file');
         function df_load_file()
         {
              $isLoadfile= strpos($_SERVER['REQUEST_URI'],"action=DFloadFile");
              if($isLoadfile){
                     $readFrom= strpos($_SERVER['REQUEST_URI'],"fieldId=");
                         if($readFrom)
                         {
                             $myDef=new DF_DynamicForms();
                                $fieldId= substr($_SERVER['REQUEST_URI'],$readFrom+8) ;
                                $fieldParams = explode("_",$fieldId);
                                $formParams = $fieldParams[0];
                                $randomNum=  $fieldParams[1];
                                $mydf=new DF_Crypt( DF_DBFuncs::getDecryptionKey());
                                
                                $formParamsEncrypted= $mydf->Decrypt($formParams);
                                
                                 $actionType = $myDef-> getActionType ($formParamsEncrypted);
                                  $readFrom= strpos($formParamsEncrypted,$actionType);
                                  $formParamsEncrypted= substr($formParamsEncrypted,1,$readFrom+strlen ($actionType)) ;
                                  
                                 
                                $formParamsEncryptedArray = explode("_",$formParamsEncrypted);
                                $sysname=$formParamsEncryptedArray[2];
                                $formType= $formParamsEncryptedArray[3];
                                $endformType= strpos($formType,";");
                                
                                $formType= trim(substr($formType,0,$endformType-1)) ;
                                  
                                  if($myDef->IsAuthorized($sysname,$formType,$randomNum,"loadfile"))
                                  {
                                        $myDef=new DF_SaveFile();
                                        echo  $myDef->Manage($_FILES);
                                  }
                                   else
                                 {
                                     echo DF_DBFuncs::translate($sysname.$formType.$randomNum. 'error: You have no permission to make this action' );
                                 }
                         }
                         else
                         {
                             echo DF_DBFuncs::translate( 'error: You have no permission to make this action' );
                         }
                         exit;
              }
            
             
         }
        

         //Admin only
if ( is_admin() ){


        add_action('admin_menu', 'df_admin_menu');


        //create menues
        function df_admin_menu() {

           //New:
           //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
           add_menu_page('General Options', DF_DBFuncs::translate('Dynamic Forms'), 'manage_options', 'df_GeneralOptions', 'df_GeneralOptions');
           //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
           add_submenu_page('df_GeneralOptions', DF_DBFuncs::translate('Forms Settings'), "1: ".DF_DBFuncs::translate('Forms Settings'), 'manage_options', 'df_define_entities', 'DefineEntitiesStart');
           add_submenu_page('df_GeneralOptions',DF_DBFuncs::translate('Fields and Views'),"2: ". DF_DBFuncs::translate('Fields and Views'), 'manage_options', 'df_define_definitions', 'DefineDefinitionsStart');
           add_submenu_page('df_GeneralOptions',DF_DBFuncs::translate('Form Testing'), "3: ".DF_DBFuncs::translate('Form Testing'), 'manage_options', 'df_test_page', 'DefineTestPage');

        }

        function df_GeneralOptions()
        {
           $myde=new DF_GeneralOptions();
           $myde->GetHtml();
        }

        function DefineEntitiesStart()
        {
           $myde=new DF_DefineEntities();
           $myde->getHtml();
        }

        function DefineDefinitionsStart()
        {
           $myde=new DF_DefineDefinitionsUI();
           $myde->getHtml();
        }

        function DefineTestPage()
        {
           $myde=new DF_TestPage();
           $myde->getHtml();
        }
               
}



/**
 * Adds DynamicPlugin_Widget widget.
 */
class DynamicPlugin_Widget extends WP_Widget {    

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
             'dynamicplugin_Widget', // Base ID
            'DynamicPlugin_Widget', // Name
            array( 'description' => __( 'Dynamic Forms Widget', 'text_domain' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
         $entity= $instance['entity'];
         $type= $instance['type'];
        
        echo $before_widget;
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;
            
              $myDynamic =new DF_DynamicForms();
             $randomNumber=rand(1,1000000);
            $htmlToAdd= $myDynamic->getHtmlOfForm($entity,$type,$randomNumber);
           
            
            
            
        echo __( $htmlToAdd, 'text_domain' );
        echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );
        
        $instance['entity'] = strip_tags( $new_instance['entity'] );
        $instance['type'] = strip_tags( $new_instance['type'] );

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
             $entity = $instance[ 'entity' ];
              $type = $instance[ 'type' ];
        }
        else {
            $title = __( 'New title', 'text_domain' );
            $entity = __( 'New entity', 'text_domain' );
            $type = __( 'New type', 'text_domain' );
        }
        
        
          $myDb=new DF_DataBase();
        $entityHtml = $myDb-> getEntitiesListWithIdSelected($this->get_field_name( 'entity' ),$this->get_field_id( 'entity' ),  esc_attr( $entity ),$this->get_field_id( 'entity' ));
         $typeHtml =  $myDb-> getTypesWithNameIdSelected($this->get_field_name( 'type' ),$this->get_field_id( 'type' ),  esc_attr( $type ),$this->get_field_id( 'type' ));
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php DF_DBFuncs::translate( 'Title' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        
        <p>
       <?php
           echo $entityHtml;
       ?>
        </p>
        
        <p>
        
       <?php
           echo $typeHtml;
       ?>
        
        </p>
        <?php 
        
      
        
    }

} // class Foo_Widget


// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "dynamicplugin_Widget" );' ) );
       
?>