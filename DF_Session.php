<?php

class DF_Session
{
    
    public static function StoreSession($key,$value)
    {  
       $_SESSION[$key]=$value;
        
    }

    public static function LoadSession($key)
    {
       
        if(isset($_SESSION[$key]))
        {
           return $_SESSION[$key];
        }
        return null;
       
    }
    
    

}


?>