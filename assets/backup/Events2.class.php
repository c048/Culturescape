<?php

	include_once("Db.class.php");

	class Events
	{
		protected $e_sType;
		protected $e_sTitle;
		protected $e_sText;
		protected $e_sUitid;
		protected $e_sStad;
		protected $e_sZip;
		protected $e_sAdres;
		protected $e_sDate;
		protected $e_sUserid;
		protected $e_sImg;
		protected $e_sLoc;
		
		public function __set($p_sProperty, $p_vValue)
		{
			switch($p_sProperty)
			{					
				case 'type':
					$this->e_sType = $p_vValue;
					break;
					
				case 'title':
					$this->e_sTitle = $p_vValue;
					break;
					
				case 'text':
					$this->e_sText = $p_vValue;
					break;
				
				case 'uitid':
					$this->e_sUitid = $p_vValue;
					break;
					
				case 'stad':
					$this->e_sStad = $p_vValue;
					break;
					
				case 'zip':
					$this->e_sZip = $p_vValue;
					break;
					
				case 'adres':
					$this->e_sAdres = $p_vValue;
					break;
					
				case 'datum':
					$this->e_sDate = $p_vValue;
					break;
				
				case 'userid':
					$this->e_sUserid = $p_vValue;
					break;
					
				case 'url':
					$this->e_sImg = $p_vValue;
					break;
				
				case 'location':
					$this->e_sLoc = $p_vValue;
					break;
					
				default: 
					echo("Niet bestaande set property : ".$p_sProperty."<br/>");
			}
		}
		
		public function __get($p_sProperty)
		{
			switch($p_sProperty)
			{										
				case 'type':
					return ($this->e_sType);
					break;
					
				case 'title':
					return ($this->e_sTitle);
					break;
					
				case 'text':
					return ($this->e_sText);
					break;
				
				case 'uitid':
					return ($this->e_sUitid);
					break;
					
				case 'stad':
					return ($this->e_sStad);
					break;
					
				case 'zip':
					return ($this->e_sZip);
					break;
					
				case 'adres':
					return ($this->e_sAdres);
					break;
					
				case 'datum':
					return ($this->e_sDate);
					break;
				
				case 'userid':
					return ($this->e_sUserid);
					break;
					
				case 'url':
					return ($this->e_sImg);
					break;
					
				case 'location':
					return ($this->e_sLoc);
					break;
					
				default: 
					echo("Niet bestaande property get: ".$p_sProperty."<br/>");
			}
		}
		
		public function getlist()
		{
			//Lijst van event types ophalen
			try{
				$db=new Db();
				$uTbl="types";
				$uCols=array("*");
				$retrieved = $db->select($uTbl, $uCols, 0, 0, 0, "type_omschrijving", "ASC");
				if($retrieved)
				{
					$db->Close();
					return($retrieved);
				}
				else {
					$db->Close();
					return(0);
				}
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		
	
		
		public function save()
		{
			//Een event opslaan
			try
			{
				$db=new Db();
				$uTbl="events";
				$uCols=array("event_type","event_title","event_text","event_uitid","event_stad","event_zip","event_adres","event_date","event_userid","event_img","event_loc");
				$uVals=array($this->e_sType,$this->e_sTitle,$this->e_sText,$this->e_sUitid,$this->e_sStad,$this->e_sZip,$this->e_sAdres,$this->e_sDate,$this->e_sUserid,$this->e_sImg,$this->e_sLoc);
				$db->insert($uTbl,$uCols,$uVals);
				$db->close();
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());	
			}
		}
		
		public function check()
		{
			//Controleren ofdat een event voor een gebruker al bestaat
			try
			{
				$db=new Db();
				$uTbl="events";
				$uCols=array("event_id");
				$uWhere=array("event_uitid","event_zip","event_date","event_userid");
				$uVals=array($this->e_sUitid,$this->e_sZip,$this->e_sDate,$this->e_sUserid);
				$eventcheck=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				$db->close();
				if(count($eventcheck)==0)
				{
					return(0);
				}
				else {
					return($eventcheck[0][0]);
				}
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());	
			}
		}
	}
?>