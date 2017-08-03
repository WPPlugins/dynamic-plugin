<?php
include_once '..\DF_CMSFunctions.php';


//TODO: Add default html for class instance
//TODO: Think how to create entity and fields definition from table or class
  class DF_Reflection
  {
     
          
          /*  Returns example:
          Field      | Type     | Null | Key | Default | Extra          |
+------------+----------+------+-----+---------+----------------+
| Id         | int(11)  | NO   | PRI | NULL    | auto_increment |
| Name       | char(35) | NO   |     |         |                |
| Country    | char(3)  | NO   | UNI |         |                |
| District   | char(20) | YES  | MUL |         |                |
| Population | int(11)  | NO   |     | 0       |             
    
    */
     public function GetRowsProperties($tableName)
      {
          $rows=array();
          $query="SHOW COLUMNS FROM ".$tableName;
           $results = DF_DBFuncs::sqlGetResults($query);
            if ( $results["boolean"] == true)
            {
                $rows = $results["data"];
            }
            return $rows;
    
      }
     
     
     public function GetTableName($className)
     {
        return $this->GetTabPrefix(). $className    ;
     } 
     
      public function GetTabPrefix()
    {
         $tabPrefix="";
         global $wpdb;
         if (function_exists('is_multisite') && is_multisite())
         {
                $tabPrefix="";
                $tabPrefix = $wpdb->prefix ;
         }
         return $tabPrefix;
    }
    
    
   
    
    public function GetFormattedValue($propety,$classInstance,$dbProperty) 
    {
        
        $res= "'".   $classInstance->{$property->getName()}."'";
        if(isset($dbProperty["Type"]))
        {
             $pos= strrpos($dbProperty["Type"],"int");
             $pos2= strrpos($dbProperty["Type"],"integer");
             $pos3= strrpos($dbProperty["Type"],"double");
             if($pos || $pos2 || $pos3)
             {
                $res=   $classInstance->{$property->getName()};
             }
        }
        
        return $res;
    }
   
   public function SaveClassInstance($className, $classInstance)
   {
        $reflector = new ReflectionClass($className);
        if(!$this->InstanceExistsOnDatabase($className,$classInstance))
        {
            $this->InsertNewClassInstance($className, $classInstance);
        }
        else
        {
        
            $properties = $reflector->getProperties();
              $tableName = $this->GetTableName($className)  ;
             
              $query = " update ".$tableName. " set ";
           
           $dbproperties =GetRowsProperties($tableName);
          
          
            $count=0;
            foreach($properties as $property)
            {
                if($count>0)
                {
                    $query.=",";
                }
                foreach($dbproperties as $dbpropertyS)
                {
                    if   ($dbpropertyS["Field"]==$property->getName())
                        $dbproperty = $dbpropertyS;
                }
                if(isset($dbproperty))
                {
                    $query.= $property->getName() ." = ". $this->GetFormattedValue($property,$classInstance,$dbproperty);
                    $count++;
                }
            } 
            
           
            $query.=" where id =".$classId;
            $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                 return "fail";
            }
            return "success";
        }
          
   }
   
   
   public function InstanceExistsOnDatabase($className, $classInstance)
   {
       if (!isset($classInstance->{"id"}))
            return false;
            
        $tableName = $this->GetTableName($className)  ;
        $query = " select * from ".$tableName  ." where id=". $classInstance->{"id"};
        $results = DF_DBFuncs::sqlGetResults($query);

        return $results["boolean"] ;
   }
   
   public function InsertNewClassInstance($className, $classInstance)
   {
          $reflector = new ReflectionClass($className);
       
        
        
        $properties = $reflector->getProperties();
          $tableName = $this->GetTableName($className)  ;
         
          $query = " insert into ".$tableName. " ";
         $queryPartFieldNames = "(";
         $queryPartValues = "(";
       $dbproperties =GetRowsProperties($tableName);
      
      
        $count=0;
        foreach($properties as $property)
        {
            if($property->getName()=="id")
                continue;
            if($count>0)
            {
                $queryPartFieldNames.=",";
                $queryPartValues.=",";
            }
            foreach($dbproperties as $dbpropertyS)
            {
                if   ($dbpropertyS["Field"]==$property->getName())
                    $dbproperty = $dbpropertyS;
            }
            if(isset($dbproperty))
            {
                 $queryPartFieldNames.=$property->getName();
                $queryPartValues.= $this->GetFormattedValue($property,$classInstance,$dbproperty);
               
                $count++;
            }
        } 
        
        if($count>0)
        {
            $queryPartFieldNames .= ")";
            $queryPartValues .= ")";
           
            $query.=  $queryPartFieldNames . " values ". $queryPartValues;
            $results=DF_DBFuncs::sqlExecute($query);
            if($results['boolean']!=true)
            {
                 return "fail";
            }
            
            return "success";
        }
        else
        {
            return "fail to build query";
        }
   }
   
   
  
     
    public function LoadFromDatabase($className, $classId)
    {
        $reflector = new ReflectionClass($className);
        $newInstance=  $reflector->newInstanceWithoutConstructor();
       
       
       
        //Now get all the properties from class A in to $properties array
        $properties = $reflector->getProperties();
          $query = " select ";
       
        $count=0;
        //Now go through the $properties array and populate each property
        foreach($properties as $property)
        {
            if($count>0)
            {
                $query.=",";
            }
            $query.= $property->getName();
            $count++;
        } 
        
        $tableName = $this->GetTableName($className)  ;
        $query.=" from ".$tableName." where id =".$classId;
         $results = DF_DBFuncs::sqlGetResults($query);
         
         if ( $results["boolean"] == true)
        {

            $row = $results["data"][0];
            //asignment
            foreach($properties as $property)
            {
                if(isset($row[$property->getName()]))
                {
                    $newInstance->{$property->getName()}=$row[$property->getName()];
                }
            } 
        }
        return $newInstance;
    }
    
    
    
    public function IsExistsForTableEntityAndFieldsDefinitions($tableName)
    {
          $query = " select * from df_sys_entities where sysname='".$tableName."'";
          $results = DF_DBFuncs::sqlGetResults($query);
          return  $results["boolean"] ;
    }
  
  
    //TODO:  
    public  function isTableForClassExists()
    {
        
    }
    
    public function updateTableFromClass()
    {
        //go over properties and update table according to properties names
    }
    
    public function createTableFromClass()
    {
        //go over properties and createTable according to properties names
    }
    
    public function CreateEntityAndDefinitionFromTable($tableName)
    {
        //create entity details
        
        //create fields
        
        //create default forms    
    }
    
   
    
  }
?>
