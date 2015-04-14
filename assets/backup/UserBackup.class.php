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
		
		public function getname()
		{
			//controleren ofdat de gebruiker wel bestaat
			try{
				$db=new Db();
				$uTbl="users";
				$uCols=array("user_email");
				$uWhere=array("user_id");
				$uVals=array($this->m_iId);
				$userpk=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				$db->Close();
				if(count($userpk)>0)
				{
					return($userpk[0]['user_email']);
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
		
		public function update()
		{
			try{
				//Controleren ofdat er events in aanmerking komen om geincasseerd te worden
				$db=new Db();
				$uTbl="events";
				$uCols=array("*");
				$uWhere=array("event_checked","event_userid");
				$uVals=array("0",$this->m_iId);
				$retrieved = $db->select($uTbl, $uCols, $uWhere, $uVals, 0, "event_id", "ASC");
				
				//Events die in aanmerking komen hun datums vergelijken met de huidige datum
				if($retrieved)
				{
					$u=1;
					$today=date("Y-m-d");
					
					foreach ($retrieved as $singleretrieved) 
					{
						if($singleretrieved['event_date']<$today)
						{
							//Registreren dat er een update is gebeurt
							$u=2;
							
							//Controleren hoeveel keer hetzelfde type al voorkomt
							$uTbl="events";
							$uCols=array("event_type");
							$uWhere=array("event_type","event_checked","event_userid");
							$uVals=array($singleretrieved['event_type'],"1",$this->m_iId);
							$aantal = count($db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0));
							
							//XP toewijzen aan de hand van hoeveel keer het voorkomt
							switch ($aantal) 
							{
								case '0':
									$xp = "150";
									break;
									
								case '1':
									$xp = "120";
									break;
								
								case '2':
									$xp = "100";
									break;
								
								case '3':
									$xp = "80";
									break;
											
								default:
									$xp = "70";
									break;
							}
							
							//Categorie nummer ophalen
							$uTbl="types";
							$uCols=array("type_category");
							$uWhere=array("type_serial");
							$uVals=array($singleretrieved['event_type']);
							$categorie = $db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
							
							//Huidige user score voor de categorie ophalen
							$uTbl="users";
							$uCols=array("cat_".$categorie[0]['type_category']);
							$uWhere=array("user_id");
							$uVals=array($singleretrieved['event_userid']);
							$currentxp = $db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
							
							//User score updaten
							$uTbl="users";
							$uCols=array("cat_".$categorie[0]['type_category']."=".($currentxp[0][0]+$xp));
							$uWhere=array("user_id");
							$uVals=array($singleretrieved['event_userid']);
							$db->update($uTbl, $uCols, $uWhere, $uVals);
							
							//Event registreren als voorbij
							$uTbl="events";
							$uCols=array("event_checked=1");
							$uWhere=array("event_id");
							$uVals=array($singleretrieved['event_id']);
							$db->update($uTbl, $uCols, $uWhere, $uVals);
						}
					}
					
					//Level & XP informatie ophalen
					$sortedinfo = $this->getinfo();
					
					//Sorteren (om zo makkelijker de lagen van de afbeelding op te bouwen: laagste-> eerste(=onderaan), hoogste -> laatste(=bovenaan))
					foreach($sortedinfo as $key => $row){
						if(empty($row['level'])){
							unset($sortedinfo[$key]);
						}
						else {
							$lvl[$key] = $row['level'];
						}
					}
					
					//Berekenen hoeveel niveaus de afbeelding moet hebben (van 1 tot max 3)
					array_multisort($lvl, SORT_ASC, $sortedinfo);
					$imagelvls=count($sortedinfo);
					$storage=array();
					
					for ($j=0; $j < $imagelvls; $j++) {
						
						switch ($imagelvls-$j) {
							case '1':
								$imagedepth=1;
								break;
							case '2':
								$imagedepth=2;
								break;
							case '3':
								$imagedepth=2;
								break;
							default:
								$imagedepth=3;
								break;
						} 
						
						$storage[]=array("img"=>imagecreatefrompng("assets/images/city/".$imagedepth."/".$sortedinfo[$j]['cat']."/".$sortedinfo[$j]['level'].".png"),"level"=>$sortedinfo[$j]['level'],"depth"=>$imagedepth,"ruler"=>imagecreatefrompng("assets/images/bar/".$imagedepth."/".$sortedinfo[$j]['cat']."/1.png"));
					}
					
					//Beginnnen met het opbouwen van de afbeelding met een blanko file, imagebreak dient voor de positionering van de afbeeldingen te sturen
					$imagebreak=680/7;
					$background=imagecreatetruecolor(680,280);
					imagesavealpha($background, true);
					
					$color = imagecolorallocatealpha($background,0x00,0x00,0x00,127); 
					imagefill($background, 0, 0, $color); 
					
					$check="0";
					
					foreach ($storage as $singlelayer) 
					{
						//Om te voorkomen dat de kleine elementen in de achtergrond worden eclipsed door de grotere elementen vooraan, krijgen deze een bonus naar links of rechtse uitlijning afhankelijk van hun grootte
						switch ($singlelayer['level'])
						{
							case '1':
								$bonus=55;
								break;
							case '2':
								$bonus=45;
								break;
							case '3':
								$bonus=25;
								break;
							case '4':
								$bonus=15;
								break;
							default:
								$bonus=0;
								break;
						}
						
						//Het opbouwen van de afbeelding, niveau per niveau
						switch (true) {
							case ($singlelayer['depth']==3 && $check==$singlelayer['depth']):
								$loc=(-$imagebreak*2)+(25-($bonus/2));
								$ruler=(-$imagebreak)-2;
								break;
							case ($singlelayer['depth']==3):
								$check=3;
								$loc=($imagebreak*2)-(28-($bonus/2));
								$ruler=($imagebreak)+5;
								break;
							case ($singlelayer['depth']==2 && $check==$singlelayer['depth']):
								$loc=($imagebreak)+(-20+$bonus);
								$ruler=($imagebreak)+32;
								break;
							case ($singlelayer['depth']==2):
								$check=2;
								$loc=(-$imagebreak)+(20-$bonus);
								$ruler=(-$imagebreak)-30;
								break;
							default:
								$loc=0;
								$ruler=0;
								break;
						}
						
						//Samenvoegen en opruimen
						imagecopy($background, $singlelayer['img'], $loc, 0, 0, 0, 680, 280);
						imagecopy($background, $singlelayer['ruler'], $ruler, 0, 0, 0, 680, 280);
						imagedestroy($singlelayer['img']);
						imagedestroy($singlelayer['ruler']);
					}
					//Afbeedling opslagen en opruimen
					imagepng($background,'assets/images/users/564'.$_SESSION["user"].'789.png');
					imagedestroy($background);
					
					$db->Close();
					return($u);
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

		public function getinfo()
		{
			//Aan de hand van de totale score per categorie, een level en xp progression opbouwen voor iedere categorie
			try{
				//Score van de gebruiker ophalen
				$db=new Db();
				$uTbl="users";
				$uCols=array("*");
				$uWhere=array("user_id");
				$uVals=array($this->m_iId);
				$retrieved=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				
				if($retrieved)
				{
					$db->Close();
					
					//Score controleren en een level + xp tot volgende level hiervan afleiden
					$lvlinfo=array();	
					for ($i=1; $i < 6; $i++) {
						$xp=$retrieved[0]['cat_'.$i]; 	
						switch (true) {
							case $xp==0:
								$level="0";
								$max="0";
								break;
							case $xp<400:
								$level="1";
								$max="400";
								break;
							case $xp<900:
								$level="2";
								$xp=$xp-"400";
								$max="500";
								break;
							case $xp<1500:
								$level="3";
								$xp=$xp-"900";
								$max="600";
								break;
							case $xp<2200:
								$level="4";
								$xp=$xp-"1500";
								$max="700";
								break;
							default:
								$level="5";
								$max="0";
								break;
						}
						$lvlinfo[]=array("level"=>$level,"score"=>$xp,"max"=>$max,"cat"=>$i);
					}
					
					return($lvlinfo);
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
		
		public function getrare()
		{
			//Zoeken achter de minder populaire categorieën
			try{
				//Categorie scores ophalen
				$db=new Db();
				$uTbl="users";
				$uCols=array("*");
				$uWhere=array("user_id");
				$uVals=array($this->m_iId);
				
				$retrieved=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				if($retrieved)
				{
					//Uitzoeken wat de populairste categorie is
					$topscore="1";
					$topcat="1";
					for ($i=1; $i < 6; $i++) {
						if($retrieved[0]["cat_".$i]>$topscore){
							$topscore=$retrieved[0]["cat_".$i];
							$topcat=$i;
						}
					}
					
					//Randomize welke minder populaire categorie gekozen gaat worden
					$r=$topcat;
					while($r==$topcat){
						$r=rand(1,5);
					}
					
					//Type-id's van de categorie ophalen
					$db=new Db();
					$uTbl="types";
					$uCols=array("type_serial");
					$uWhere=array("type_category");
					$uVals=array($r);
					
					$catids=$db->select($uTbl, $uCols, $uWhere, $uVals, 5, 0, 0);
					
					$db->Close();
					return($catids);
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
		
		public function getcommon()
		{
			//De meest populaire categorie opzoeken
			try{
				$db=new Db();
				$uTbl="users";
				$uCols=array("*");
				$uWhere=array("user_id");
				$uVals=array($this->m_iId);
				
				$retrieved=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				if($retrieved)
				{
					//De populairste categorie uitzoeken door de hoogste score te achterhalen
					$topscore="1";
					$topcat="1";
					for ($i=1; $i < 6; $i++) {
						if($retrieved[0]["cat_".$i]>$topscore){
							$topscore=$retrieved[0]["cat_".$i];
							$topcat=$i;
						}
					}
					
					//De type-id's ophalen van de populairste categorie en terugsturen
					$db=new Db();
					$uTbl="types";
					$uCols=array("type_serial");
					$uWhere=array("type_category");
					$uVals=array($topcat);
					
					$catids=$db->select($uTbl, $uCols, $uWhere, $uVals, 5, 0, 0);
					
					$db->Close();
					return($catids);
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
		
		public function getupcomming($p_vValue,$p_vLimit)
		{
			//Events waarvoor de gebruiker geregistreerd is ophalen
			try{
				$db=new Db();
				$uTbl="events";
				$uCols=array("*");
				$uWhere=array("event_userid","event_checked");
				$uVals=array($this->m_iId,"0");
				$limit=($p_vLimit*$p_vValue).",".$p_vLimit;
				
				$retrieved=$db->select($uTbl, $uCols, $uWhere, $uVals, $limit, "event_date", "DESC");
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
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
		
		public function getsingle($p_vValue)
		{
			//Informatie van één bepaald event ophalen
			try{
				$db=new Db();
				$uTbl="events";
				$uCols=array("*");
				$uWhere=array("event_userid","event_id","event_checked");
				$uVals=array($this->m_iId,"$p_vValue","0");
				
				$retrieved=$db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
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
		
		public function remove($p_vValue)
		{
			//Event verwijderen
			try{
				$db=new Db();
				$uTbl="events";
				$uWhere=array("event_userid","event_id");
				$uVals=array($this->m_iId,$p_vValue);
				
				$db->delete($uTbl, $uWhere, $uVals);
				$db->Close();
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		
				
		public function getsingleinfo($p_vValue)
		{
			try{
				//Categorie nummer ophalen
				$db=new Db();
				$uTbl="types";
				$uCols=array("type_category");
				$uWhere=array("type_serial");
				$uVals=array($p_vValue);
				$catinfo = $db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
			if($catinfo){
				
				//Controleren hoeveel keer hetzelfde type al voorkomt
				try
				{
					$uTbl="events";
					$uCols=array("event_type");
					$uWhere=array("event_type","event_checked","event_userid");
					$uVals=array($p_vValue,"1",$this->m_iId);
					$aantal = count($db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0));
				}
				catch(Exception $e)
				{
					throw new Exception($e->getMessage());
				}
					
				//XP toewijzen aan de hand van hoeveel keer het voorkomt
				switch ($aantal) 
				{
					case '0':
						$xp = "150";
						break;
						
					case '1':
						$xp = "120";
						break;
					
					case '2':
						$xp = "100";
						break;
					
					case '3':
						$xp = "80";
						break;
								
					default:
						$xp = "70";
						break;
				}
				if($xp){
					return(array($xp,$catinfo[0][0]));
					$db->Close();
				}
				else{
					return 0;
					$db->Close();
				}
			}
			else 
			{
				return 0;
				$db->Close();
			}
		}

		public function getsingleid($p_vValue)
		{
			try{
				//Nummer van het type ophalen ophalen
				$db=new Db();
				$uTbl="events";
				$uCols=array("event_type");
				$uWhere=array("event_id");
				$uVals=array($p_vValue);
				$typeinfo = $db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
			}
			catch(Exception $e)
			{
				throw new Exception($e->getMessage());
			}
			if($typeinfo){
				try{
					//Categorie nummer ophalen
					$db=new Db();
					$uTbl="types";
					$uCols=array("type_category");
					$uWhere=array("type_serial");
					$uVals=array($typeinfo[0][0]);
					$catinfo = $db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0);
				}
				catch(Exception $e)
				{
					throw new Exception($e->getMessage());
				}
				if($catinfo)
				{
					//Ophalen hoeveelkeer het al voor komt
					try
					{
						$uTbl="events";
						$uCols=array("event_type");
						$uWhere=array("event_type","event_checked","event_userid");
						$uVals=array($p_vValue,"1",$this->m_iId);
						$aantal = count($db->select($uTbl, $uCols, $uWhere, $uVals, 0, 0, 0));
					}
					catch(Exception $e)
					{
						throw new Exception($e->getMessage());
					}
						
					//XP toewijzen aan de hand van de hoeveelheid
					switch ($aantal) 
					{
						case '0':
							$xp = "150";
							break;
							
						case '1':
							$xp = "120";
							break;
						
						case '2':
							$xp = "100";
							break;
						
						case '3':
							$xp = "80";
							break;
									
						default:
							$xp = "70";
							break;
					}
					if($xp){
						return(array($xp,$catinfo[0][0]));
						$db->Close();
					}
					else{
						return 0;
						$db->Close();
					}
				}
			}
			else 
			{
				return 0;
				$db->Close();
			}
		}
	}

?>