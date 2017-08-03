<?php
// the class holds connection to mysql database
// will be probably unneeded later, when the plugin will work with CMS

class DF_DBConnection
{
    static $connection = false;
    static function get_connection()
    {
        if(self::$connection == true)
        {
        	return self::$connection;
        }
        else
        {
        	self::$connection = new DF_DBConnection;
        }
    }
    function __construct () {
		    	
    }
}
?>