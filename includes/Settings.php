<?php
class Settings {

    public $sql;
    private $values;
    public $keys;

    //
    public function __construct() {
        $this->sql = new sqlme();
        $this->sql->Query("SET NAMES utf8");
        $this->values = array();
        $this->keys = array();
        //
        $this->sql->Query("SELECT * FROM settings ORDER BY id");
        if($this->sql->size_of_result != 0){
           foreach($this->sql->GetAssoc() as $s){
               $this->values[$s['name']] = $s['value'];
               $this->keys[] = $s['name'];
           }
        }
    }

    public function get($name) {
        if ($this->values[$name]) {
            return $this->values[$name];
        }
        return false;
    }

    public function set($name,$value,$sort=false) {
        if(!isset($value)){return false;}
        $this->values[$name] = $value;
        if(strstr($name,"serialize")){
            $array=explode("\r\n",stripslashes(trim($value)));
            foreach(array_unique($array) as $v){if(trim($v)){$newarray[]=trim($v);}}
            if($sort){sort($newarray);}
            $value=serialize($newarray);
        }
        $this->sql->Query("SELECT value FROM settings WHERE name = '".$name."'");
	if($this->sql->size_of_result > 0){$this->sql->Query("UPDATE settings SET value = '".mysql_real_escape_string($value)."' WHERE name = '".$name."'");}
	else{$this->sql->Query("INSERT INTO settings (name, value) VALUES ('".$name."', '".mysql_real_escape_string($value)."') ");}
        $this->update();
    }

    public function update() {
        $this->sql->Query("SELECT * FROM settings ORDER BY id");
        if($this->sql->size_of_result != 0){
               foreach($this->sql->GetAssoc() as $s){
                   $this->values[$s['name']] = $s['value'];
               }
        }
    }


}

?>
