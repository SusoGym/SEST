<?php

/**
 *
 */
class Model
{
  private $connection;//Connection Object

  /**
  *Konstruktor
  */
  function __construct()
  {
    $this->connection=new Connect();
  }
  
  
  /**
   * Schülerexistenz prüfen
   * 
   * /
   public function checkPupilExist($name,$bday){
   $id=null;
   $name=$this->connection->mysqli_real_escape_string($name);
   $bday=$this->connection->mysqli_real_escape_string($bday);
   $data=$this->connection->selectValues('SELECT id FROM schueler WHERE Name="$name" and bday="$bday" AND EId=null');
   if(isset($datat[0][0])) $id=$data[0][0];
   return $id;
   }
  
  
}


?>
