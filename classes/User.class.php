<?php

	include_once("Db.class.php");

	class User
	{
		protected $m_iId;
		protected $m_sEmail;
		protected $m_sPasswd;
		protected $salt="IOEjulalkz987ZAElm87A96";
		
		public function __set($p_sProperty, $p_vValue)
		{
			switch($p_sProperty)
			{					
				case 'email':
					$this->m_sEmail = $p_vValue;
					break;
					
				case 'password':
					$this->m_sPasswd = md5($p_vValue).$this->salt;
					break;
					
				case 'cookie':
					$this->m_sPasswd = $p_vValue;
					break;
				
				case 'id':
					$this->m_iId = $p_vValue;
					break;
					
				default: 
					echo("Niet bestaande set property : ".$p_sProperty."<br/>");
			}
		}
		
		public function __get($p_sProperty)
		{
			switch($p_sProperty)
			{					
				case 'email':
					return($this->m_sEmail);
					break;
					
				case 'password':
					return($this->m_sPasswd);
					break;
				
				case 'id':
					return($this->m_iId);
					break;
					
				default: 
					echo("Niet bestaande property get: ".$p_sProperty."<br/>");
			}
		}
		
		public function register()
		{
			//een gebruiker aanmaken
			try
			{
				$db=new Db();
				$uTbl="users";
				$uCols=array("user_email","user_password");
				$uVals=array($this->m_sEmail,$this->m_sPasswd);
				$db->insert($uTbl,$uCols,$uVals);
				$db->close();
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());	
			}
		}
		
		public function availability()
		{
			//controleren ofdat de naam al bestaat
			try{
				$db=new Db();
				$uTbl="users";
				$uCols=array("user_id");
				$uWhere=array("user_email");
				$uVals=array($this->m_sEmail);
				$userpk=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				$db->Close();
				if(count($userpk)>0)
				{
					return(1);
				}
				else {
					return(0);
				}
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		
		public function confirm()
		{
			//controleren ofdat de gebruiker wel bestaat
			try{
				$db=new Db();
				$uTbl="users";
				$uCols=array("user_id");
				$uWhere=array("user_password","user_email");
				$uVals=array($this->m_sPasswd,$this->m_sEmail);
				$userpk=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				$db->Close();
				if(count($userpk)>0)
				{
					return($userpk[0]['user_id']);
				}
				else {
					return(0);
				}
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		
		public function vistordata()
		{
			//Informatie ophalen voor een bezoeker van een stad
			try{
				$db=new Db();
				$uTbl="users";
				$uCols=array("*");
				$uWhere=array("user_email");
				$uVals=array($this->m_sEmail);
				$retrieved=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
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
	}
?>