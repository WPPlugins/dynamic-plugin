<?php
//can be used to store pairs of names and values 
class DF_Value
{
    public $value='';
    public $name='';
    
    function __construct($name='', $value='')
    {
        $this->value=$value;
        $this->name=$name;
    }
}

?>