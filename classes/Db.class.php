<?php
	
	class Db
	{
		public $m_rConn;
		private static $instance = NULL;
		
		public function __construct()
		{
			$this->m_rConn = new mysqli("localhost", "thieriy103_cs", "gdpi2gt8", "thieriy103_cs");
			if($this->m_rConn->connect_errno)
			{
				throw new Exception("Sorry, no database was found.");
			}
		}
		
		public static function getInstance() {
		    if(!isset(self::$instance)) {
		      self::$instance = new Db();
		    }
		    return self::$instance;
  		}
		
		public function close()
		{
			$this->m_rConn->close();
		}
		
		public function sanitize($val)
		{
			return $this->m_rConn->real_escape_string($val);
		}
		
		public function insert($table, $cols, $values)
		{
			$sql = "INSERT INTO $table (";
			$sql_cols = "";

			foreach($cols as $key=>$val)
			{
				if($sql_cols == "")
				{
					$sql_cols .= $val;
				}
				else
				{
					$sql_cols .= ", " . $val;
				}
			}
			$sql.= $sql_cols. ") VALUES (";

			$sql_values = "";
			foreach($values as $key=>$val)
			{
				$val = $this->sanitize($val);

				if($sql_values == "")
				{
					if(!is_int($val))
						$sql_values .= "'";

					$sql_values .= $val;
				}
				else
				{
					$sql_values .= ", ";

					if(!is_int($val))
						$sql_values .= "'";

					$sql_values .= $val;
				}


				if(!is_int($val))
						$sql_values .= "'";

			}

			$sql.="$sql_values);";
			$this->m_rConn->query($sql);
		}
		
		public function select($table, $cols, $where, $values, $limit, $orderby, $orderHow)
	    {  
	    	$sql = "SELECT ";
	    	$sql_from = "";
			$sql_where = "";

	    	/* HANDLE COLUMNS */
	    	foreach($cols as $key=>$val)
	    	{
	    		if($val == "*")
	    		{
	    			$sql_from = "*";
	    		}
	    		else
	    		{
		    		if($sql_from == "")
		    		{
		    			$sql_from .= "(";
		    			$sql_from .= $val;	
		    		}
		    		else
		    		{
		    			$sql_from .= ", " . $key;
		    		}	    
	    		}		 
	    	}	 

	    	if($sql_from != "*")
	    	{
	    		$sql_from .= ")"; 
			}

			/* HANDLE FROM */
			$sql .= $sql_from;		
			$sql .= " FROM $table";
			
			/* HANDLE WHERE */
			if(!empty($where) && !empty($values))
			{
				foreach($where as $key=>$val)
		    	{
		    		if($sql_where == "")
		    		{
		    			$sql_where .= " WHERE ";
		    			$sql_where .= $val."='";	
		    			$sql_where .= $values[$key]."'";
		    		}
		    		else
		    		{
		    			$sql_where .= " AND " . $val . "='" . $values[$key]."'";
		    		}	    	 
		    	}
				$sql.= " ".$sql_where;
			}

			/* HANDLE ORDERBY */
			if(!empty($orderby))
			{
				$sql.= " ORDER BY $orderby";
			}

			/* HANDLE ORDERHOW */
			if(!empty($orderHow))
			{
				$sql.= " $orderHow";
			}

			/* HANDLE LIMIT */
			if(!empty($limit))
			{
				$sql.=" LIMIT $limit";
			}

			// RETURN THE RESULT AS AN ARRAY FOR EASY LOOPING IN TEMPLATES
	    	$result = $this->m_rConn->query($sql);
	    	$arrRecords = array();
	    	if($result)
	    	{
	    		while($row = $result->fetch_array())
	    		{
	    			// Note: If you use array_push() to add one element to the array it's better to use $array[] = because in that way there is no overhead of calling a function.
	    			// http://php.net/manual/en/function.array-push.php
	    			$arrRecords[] = $row;
	    		}
	    	}
			
	    	return($arrRecords);
	    }  

		public function update($table, $cols, $where, $values)
	    {  
	    	$sql = "UPDATE $table SET ";
	    	$sql_set = "";
			$sql_where = "";

	    	/* HANDLE COLUMNS */
	    	foreach($cols as $key=>$val)
	    	{
	    		if($val == "*")
	    		{
	    			$sql_set = "*";
	    		}
	    		else
	    		{
		    		if($sql_set == "")
		    		{
		    			$sql_set .= "";
		    			$sql_set .= $val;	
		    		}
		    		else
		    		{
		    			$sql_set .= ", " . $key;
		    		}	    
	    		}		 
	    	}	 

	    	if($sql_set != "*")
	    	{
	    		$sql_set .= ""; 
			}

			/* HANDLE FROM */
			$sql .= $sql_set;		
			$sql .= " ";
			
			/* HANDLE WHERE */
			if(!empty($where) && !empty($values))
			{
				foreach($where as $key=>$val)
		    	{
		    		if($sql_where == "")
		    		{
		    			$sql_where .= " WHERE ";
		    			$sql_where .= $val."='";	
		    			$sql_where .= $values[$key]."'";
		    		}
		    		else
		    		{
		    			$sql_where .= " AND " . $val . "='" . $values[$key]."'";
		    		}	    	 
		    	}
				$sql.= " ".$sql_where;
			}
			$this->m_rConn->query($sql);
	    }

		public function delete($table, $where, $values)
	    {
	    	$sql = "DELETE FROM $table";
			$sql_where = "";
			
			/* HANDLE WHERE */
			if(!empty($where) && !empty($values))
			{
				foreach($where as $key=>$val)
		    	{
		    		if($sql_where == "")
		    		{
		    			$sql_where .= " WHERE ";
		    			$sql_where .= $val."='";	
		    			$sql_where .= $values[$key]."'";
		    		}
		    		else
		    		{
		    			$sql_where .= " AND " . $val . "='" . $values[$key]."'";
		    		}	    	 
		    	}
				$sql.= " ".$sql_where;
			}
			$this->m_rConn->query($sql);
	    }
	}
	
?>