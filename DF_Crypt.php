<?php


class DF_Crypt
{
    protected $key;

    public function __construct($ckey="0")
    {
        if(!isset($ckey) || $ckey=="0")
        {
            /*if(!isset(get_option('df_sec_key')))
            {
               add_option('df_sec_key', $GLOBALS["EncriptionKey"]); 
            }
            $this->key=get_option('df_sec_key');
            */
            $this->key=$GLOBALS["EncriptionKey"];
        }
        else{
            $this->key=$ckey;
        }
    }

    public function Encrypt($value)
    {
        
        return  base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->key), $value, MCRYPT_MODE_CBC, md5(md5($this->key))));
    }

    public function Decrypt($value)
    {
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->key), base64_decode($value), MCRYPT_MODE_CBC, md5(md5($this->key))), "\0");
    }
}

?>