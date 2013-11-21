<?php
  class sqlme
    {
        var $cid; //connection id
        var $result; //query result
        var $size_of_result = 0; //size of query result
        var $last_id;
        var $lquery;
        var $db; //current database

        function connect($host = false, $user = false , $name = false, $pass = false )
		{
                if(!$host){$host = SQL_HOST;}
                if(!$user){$user = SQL_USER;}
                if(!$name){$name = SQL_DB;}
                if(!$pass){$pass = SQL_PWD;}
                $this->cid = mysql_connect($host, $user, $pass) or $this->Error();
                $this->db = $name;
		}

        function Error($query = '')
        {
            echo "<div style='font-family: verdana; font-size: 11px; color: #ff0000'><b>MySQL Error</b> ".mysql_error()."<br /><font color='#000000'>".$query."</font></div>";
        }

        function Query($query = '', $flag = 0)
        {
            if (null === $this->cid) {$this->connect();}
			$query = str_replace(array("\r","\n","\t"),'',$query);
			if (strstr(strtolower($query),'select'))
            {
            	$this->lquery = $query;
                $this->result = mysql_db_query($this->db, $query, $this->cid) or $this->Error($query);
                $this->size_of_result = mysql_num_rows($this->result);
                if ($flag == 1) return $this->size_of_result;
				return $this->result;
            }
            else
            {
                mysql_db_query($this->db, $query, $this->cid) or $this->Error($query);
                $this->size_of_result = mysql_affected_rows($this->cid);
                if (eregi('^insert', $query)) $this->last_id = mysql_insert_id($this->cid);
                return $this->size_of_result;
            }
        }
        function TableStatus()
        {
            if (null === $this->cid) {$this->connect();}
        	mysql_select_db($this->db,$this->cid);
        	$table_res = mysql_query ('show table status');
        	return $table_res;
		}
        function GetAssoc()
        {
            if (null === $this->cid) {$this->connect();}
        	if($this->size_of_result != 0){
        		while($row = mysql_fetch_assoc($this->result)){
        			$data[] = $row;
				}
				return $data;
			}else{
				return false;
			}
		}
        function GetAssocFirst()
        {
            if (null === $this->cid) {$this->connect();}
        	if($this->size_of_result != 0)
			  {
        	  return mysql_fetch_assoc($this->result);
			  }
			else{
				return false;
			}
		}

        function GetFirst()
        {
            if (null === $this->cid) {$this->connect();}
        	if($this->size_of_result != 0){
                return mysql_result($this->result,0);
			}else{
				return false;
			}
		}

        function Close()
        {
            mysql_close($this->cid);
        }
    }
?>