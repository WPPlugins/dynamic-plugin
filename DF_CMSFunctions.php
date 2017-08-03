<?php
/*
This module will be differnet for each cms module.
Those functions will be use in each part of general module.
This module suppose to be replace d in dependence of the cms platform chosen.


*/
//include ABSPATH."/wp-config.php";
include 'DF_Session.php';
class DF_DBFuncs
{
	private static $mysqli = null;

	public static function sendMail($to,$subject,$message)
	{
		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		wp_mail($to,$subject,$message);
	}
	// performs $query on mysql database
	// returns $results that contains boolean true or false and error_number in the case of false
    public static function sqlExecute($query)
    {
    	//echo "query : $query <br/>";

    	$results = array();

        $mysqli = self::connectToDataBase();
    	mysql_set_charset('utf8');
        $mysqli->query("SET CHARACTER SET  'utf8'");
        $mysqli->query("SET NAMES  'utf8'");

        
        $results["boolean"] = $mysqli->query($query);
		if ($results["boolean"] == false)
		{
			//echo " For sql query " . $query ." Database query error: {$mysqli->error}, error number : {$mysqli->errno}<br/>";
			$results["error_number"] = $mysqli->errno;
            
            
		}
       
        if(isset($mysqli->insert_id ))
            {
                 $results["affected_id"]=  $mysqli->insert_id;
            }

	   	$mysqli->close();
    	return $results;
    }

    // performs $query on mysql database
    // returns $results array that contains boolean true or false and data retrieved from the query
     public static function sqlGetResults($query)
    {

    	
        $mysqli = self::connectToDataBase();
        $mysqli->query("SET CHARACTER SET  'utf8'");
        $mysqli->query("SET NAMES  'utf8'");
    	$result = $mysqli->query($query);
       // echo "query : $query $result->num_rows<br/>";
        $results["data"] = array();
    	if ($result == false || !$result->num_rows)
    	{
    		//echo self::translate("Database query error: "). $mysqli->error . self::translate(", error number :"). $mysqli->errno."<br/>";
    		$results["boolean"] = false;
            return $results;
    	}
    	$results["boolean"] = true;

    	// building results data array
    	
    	while ($data_row = $result->fetch_array(MYSQLI_ASSOC)) {
    		foreach($data_row as $key=>$value)
            {
                  $data_row[$key]=   self::utf8_urldecode($value);
            }
            $results["data"][] = $data_row;
            
    	}
		$result->close();
   		$mysqli->close();

        return $results;
    }

    public static function utf8_urldecode($str) {
        $str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
        return html_entity_decode($str,null,'UTF-8');;
    }
    // connects to database if no connection was established before and returns the connection
    private function connectToDataBase()
    {
    	if (self::$mysqli == null)
    	{
            //();
            $myFile =self::getCurrentPluginURL(). "files/DF_Connect.txt";
            $theData="";
            if(ABSPATH!="ABSPATH")
            {
                require_once(ABSPATH."/wp-config.php");

                $dbHost = DB_HOST;
                $dbName = DB_NAME;
                $dbUser = DB_USER;
                $dbPsw  = DB_PASSWORD;
            }

            
            //$mysqli = new mysqli('localhost', 'root', 'cond#@59', 'wordpress');
            $mysqli = new mysqli($dbHost,$dbUser,$dbPsw,$dbName);
            if ($mysqli->connect_error)
	    	{
	    		die(self::translate("Database Connection Error"). $mysqli->connect_errno.",". $mysqli->connect_error.$theData);
	    	}
    	}
    	return $mysqli;
    }

    public static function loadTranslation($language)
    {


        //DF_Session::StoreSession('translate_'.$GLOBALS['language'],"true");
          wp_cache_set('translate_'.$language,"true");
        $query = " select * from df_sys_translations where language='".$language."'";

        $results=self::sqlGetResults($query);

        if($results['boolean']==true)
        {

            for($i=0;$i<count($results['data']);$i++)
            {
                $row = $results['data'][$i];
              //  DF_Session::StoreSession('translate_'.$row['keyVal'].'_'.$language,$row['translation']);
                wp_cache_set('translate_'. trim(str_replace(array('\'','\"'), "", $row['keyVal'])).'_'.$language,$row['translation']);

            }
        }
    }

    public static function translate($value)
    {
        $result=$value;
        $valueWithNoTags = trim(str_replace(array('\'','\"'), "", $value));
        $language= get_bloginfo("language");
         
        //self::loadTranslation($language);
        $translateLanguage = wp_cache_get('translate_'.$language);  //DF_Session::LoadSession('translate_'.$language);
        
         
        if($translateLanguage==null || $translateLanguage==false || !(isset($translateLanguage)))
        {
              self::loadTranslation($language);
        }
        $translateLanguageValue = wp_cache_get('translate_'.$valueWithNoTags.'_'.$language);//DF_Session::LoadSession('translate_'.$value.'_'.$language);
         if($translateLanguageValue!=null && $translateLanguageValue!=false && isset($translateLanguageValue))
        {
            $result = $translateLanguageValue;
        }
        else
        {
            // self::WriteNotTranslated($value,$language);
        }
        
       
       
       
        return  $result;
    }

    public static function WriteNotTranslated($value,$language)
    {
        $file = @fopen(dirname(__FILE__).'/NotTranslated.txt', "a") or die("can't open file") ;
        
        $stringData = $value." | ".$language."\n";
        fwrite($file, $stringData);
        fclose($file);
    }
    public static function getCurrentPluginURL()
    {
        
         
          $WPPluginUrl=DF_Session::LoadSession("WP_PLUGIN_URL");
        if($WPPluginUrl==null || $WPPluginUrl=="WP_PLUGIN_URL/dynamic_plugin/"  )
        {
            $url=plugin_dir_url(__FILE__);
            DF_Session::StoreSession("WP_PLUGIN_URL",$url);
       }
       
        $WPPluginUrl=DF_Session::LoadSession("WP_PLUGIN_URL");
        $dir = $WPPluginUrl;
        return $dir;
         
    }

      public static function getDecryptionKey()
    {
        $query = " select sec_key from df_sys_general";
        $results=DF_DBFuncs::sqlGetResults($query);
        if($results['boolean']==true && count($results['data'])>0)
        {
            return  $results['data'][0]['sec_key'];
        }
        return $GLOBALS["EncriptionKey"];
    }
    
    
    public static function WriteError($error)
    {
         $myFile = "logError.txt";
         $fh = fopen($myFile, 'w') or die("can't open file");
         fwrite($fh, $error);
         fclose($fh);
    }
}
?>