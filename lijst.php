<?php

	include_once("classes/Event.class.php");
	
	session_start();
	
	if(isset($_SESSION["user"]))
	{
		$today=date("Y-m-d");
		$location="";
		$i="";
		
		//Filters ophalen
		if(!empty($_GET["q"])){
			$location.= "&q=".$_GET["q"];
			$i=1;
		}
		
		if(!empty($_GET["city"])){
			$location.= "&city=".$_GET["city"];
			$i=1;
		}
		
		if(!empty($_GET["zip"])){
			$location.= "&zip=".$_GET["zip"];
			$i=1;
		}
		
		if(!empty($_GET["age"])){
			$location.= "&age=".$_GET["age"];
			$i=1;
		}
		
		if(!empty($_GET["organiser"])){
			$location.= "&organiser=".$_GET["organiser"];
			$i=1;
		}
		
		if(!empty($_GET["datetype"])){
			$location.= "&datetype=".$_GET["datetype"];
			$i=1;
		}
		
		if(!empty($_GET["isfree"])){
			$location.= "&isfree=true";
			$i=1;
		}
		
		if(!empty($_GET["thema"])){
			$location.= "&thema=".$_GET["thema"];
			$i=1;
		}
		
		if(!isset($_GET["page"]) || (($_GET["page"])<0)){
			$page="1";
		}
		else {
			$page=$_GET["page"];
		}
		
		//Als er 1 is getzet
		if($i==""){
			header('Location: type.php');
		}
		else{
			//Informatie gebaseerd op filter ophalen
			$url="http://build.uitdatabank.be/api/events/search?key=AEBA59E1-F80E-4EE2-AE7E-CEDD6A589CA9".$location."&format=json&pagelength=5&page=";
			$events = json_decode(file_get_contents(str_replace(' ', '%20', $url.$page)));
			
			//Als een gebruiker een event wil opslagen
			if(isset($_POST['bToevoegen']) && isset($_GET["id"]))
			{
				$urlsingle="http://build.uitdatabank.be/api/event/".$_GET["id"]."?key=AEBA59E1-F80E-4EE2-AE7E-CEDD6A589CA9&format=json";
				$event = json_decode(file_get_contents($urlsingle));
				
				//Gegevens instellen
				$saved = new Event();
				$arr = explode(";", $_GET["thema"], 2); 
				$saved->type=$arr[0];
				if(isset($event->event->eventdetails->eventdetail->title)){
					$saved->title=$event->event->eventdetails->eventdetail->title;
				}
				if(isset($event->event->eventdetails->eventdetail->shortdescription)){
					$saved->text=$event->event->eventdetails->eventdetail->shortdescription;
				}
				$saved->id=$_GET["id"];
				if(isset($event->event->contactinfo->address->physical->city)){
					$saved->stad=$event->event->contactinfo->address->physical->city;
				}
				if(isset($event->event->contactinfo->address->physical->zipcode)){
					$saved->zip=$event->event->contactinfo->address->physical->zipcode;
				}
				if(isset($event->event->contactinfo->address->physical->street) && isset($event->event->contactinfo->address->physical->housenr)){
					$saved->adres=$event->event->contactinfo->address->physical->street." ".$event->event->contactinfo->address->physical->housenr;
				}
				//Indien een datum is opgegeven, de datum doorsturen. Indien geen datum te vinden is, de einddatum over een week instellen
				if(isset($_POST['sDatum'])){
					$saved->datum=$_POST['sDatum'];
				}else{
					$saved->datum=($today=date('Y-m-d', strtotime('+1 week')));
				}
				$saved->id=$_SESSION["user"];
				if(isset($event->event->location->actor->actordetails->actordetail->title)){
					$saved->location=$event->event->location->actor->actordetails->actordetail->title;
				}
				$images= $event->event->eventdetails->eventdetail->media->file;
				
				//Controleren ofdat een afbeelding bestaat		
				$imgempty=0;			
				foreach($images as $image)
				{
					if(isset($image->mediatype) && $image->mediatype=="photo")
					{
						$saved->url=$image->hlink."?height=500&crop=auto";
						$imgempty=1;
					}
				}
				//Als er geen image bestaat, default opslagen
				if(empty($imgempty))
				{
					$saved->url="assets/images/site/leeg-3.png";
				}
				
				//Controleren ofdat de gebruiker al is geregistreerd
				try
				{
					$exists=$saved->check();
				} 
				catch (Exception $e)
				{
					echo $e->getMessage();
				}
				
				//Opslagen en/of boodschap weergeven
				if(!empty($exists))
				{
					$alert = "U bent al geregistreerd voor dit event";
				}
				else 
				{
					try
					{
						$saved->save();
						$confirm = "Opgeslaagd";
					} 
					catch (Exception $e)
					{
						echo $e->getMessage();
					}
				}
			}
		}

		//Als een gebruiker wil verwijderen
		if(isset($_POST['bVerwijderen']) && isset($_GET["id"]))
		{
			//extra gegevens ophalen ter controle
			$urlsingle="http://build.uitdatabank.be/api/event/".$_GET["id"]."?key=AEBA59E1-F80E-4EE2-AE7E-CEDD6A589CA9&format=json";
			$event = json_decode(file_get_contents($urlsingle));
			
			$saved = new Event();
			$saved->id=$_GET["id"];
			$saved->zip=$event->event->contactinfo->address->physical->zipcode;
			if(isset($_POST['sDatum'])){
				$saved->datum=$_POST['sDatum'];
			}else{
					$saved->datum=($today=date('Y-m-d', strtotime('+1 week')));
			}
			$saved->id=$_SESSION["user"];
			$exists=$saved->check();
			
			//Als het gevonden is, verwijderen
			if(!empty($exists)){
				try
				{
					$saved->remove($exists);
				} 
				catch (Exception $e)
				{
					echo $e->getMessage();
				}
			}
		}
		
		//Zoeken naar een stad
		if(isset($_POST['bSearch']))
		{
			header('Location: bezoeken.php?city='.$_POST['search']);
		}
		
		//Als een ID opgegeven is, het artikel tonen
		if(isset($_GET["id"]))
		{
			$usert=new Event();
			$usert->id=$_SESSION["user"];
			
			try
			{
				$arr = explode(";", $_GET["thema"], 2); 
				$catinfo=$usert->getsingleinfo($arr[0]);
			} 
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
			
			//Gegevens ophalen en toewijzen aan variabelen
			$urlsingle="http://build.uitdatabank.be/api/event/".$_GET["id"]."?key=AEBA59E1-F80E-4EE2-AE7E-CEDD6A589CA9&format=json";
			$event = json_decode(file_get_contents($urlsingle));	
			
			$title=$event->event->location->actor->actordetails->actordetail->title;
			
			if(isset($event->event->contactinfo->address->physical->zipcode)){
				$zip=$event->event->contactinfo->address->physical->zipcode;
			}
			else {
				$zip="";
			}
			if(isset($event->event->contactinfo->address->physical->city)){
				$city=$event->event->contactinfo->address->physical->city;
			}
			else {
				$city="";
			}
			if(isset($event->event->contactinfo->address->physical->street)){
				$street=$event->event->contactinfo->address->physical->street;
			}
			else {
				$street="";
			}
			if(isset($event->event->contactinfo->address->physical->housenr)){
				$housenr=$event->event->contactinfo->address->physical->housenr;
			}
			else {
				$housenr="";
			}
			if(isset($event->event->contactinfo->address->physical->file)){
				$images= $event->event->eventdetails->eventdetail->media->file;
			}
			else {
				$images="";
			}
			
			if(strlen($event->event->eventdetails->eventdetail->title)>41){
				$shorttitle=substr($event->event->eventdetails->eventdetail->title,0,41)." ...";
			}
			else{
				$shorttitle=$event->event->eventdetails->eventdetail->title;
			} 
			
			$dates="";
			if(isset($event->event->calendar->timestamps->timestamp)){
				$dates= $event->event->calendar->timestamps->timestamp;
			}
			else
			{
				if(isset($event->event->calendar->periods->period))
				{
					$startdate=$event->event->calendar->periods->period->datefrom;
					$enddate=$event->event->calendar->periods->period->dateto;
					
					$weekdays=array(array("monday","ma"),
									array("tuesday","di"),
									array("wednesday","wo"),
									array("thursday","do"),
									array("friday","vr"),
									array("saturday","za"),
									array("sunday","zo")
					);
					
					$daysopen="";
					foreach ($weekdays as $weekday) 
					{
						if(isset($event->event->calendar->periods->period->weekscheme->$weekday[0]->opentype) && ($event->event->calendar->periods->period->weekscheme->$weekday[0]->opentype)=="open")
						{
							$daysopen.=$weekday[1]." ";
						}
					}				
				}
			}
			
			//Event descriptie inkorten
			if(strlen($event->event->eventdetails->eventdetail->shortdescription)>600)
			{
				$shortdescr=substr($event->event->eventdetails->eventdetail->shortdescription,0,600)." ...";
			}
			else{
				$shortdescr=$event->event->eventdetails->eventdetail->shortdescription;
			} 
			
			//Naam & waarde van de knop van het artikel instellen
			$btnname="bToevoegen";
			$btnval="Inschrijven";
			
			if(isset($alert) || isset($confirm)){
				$btnname="bVerwijderen";
				$btnval="Uitschrijven";
			}
		}
		else
		{
			$user = new Event();
			$user->id=$_SESSION["user"];
			$score = $user->getinfo();
			
			$citycheck=0;

			foreach ($score as $singlescore) 
			{
				$citycheck+=$singlescore["level"];
			}
		}
	}	
	else {
		header('Location: loguit.php');
	}
	
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Culturescape - Zoeken</title>
		<meta name="author" content="Jorik" />
		<link rel="stylesheet" href="css/style.css" />
		<link href="css/jquery.mCustomScrollbar.css" rel="stylesheet" />
	</head>

	<body>
		<div id="cMain">
			
			<nav id="cTopnav">
				<a href="loguit.php">uitloggen</a>
				<form action="#" method="post" enctype="multipart/form-data">
						<input id="searchtext" name="search" type="search" placeholder="zoeken naar een stad" />
						<input id="bSearch" name="bSearch" value="Zoeken" type="submit" />
				</form>
			</nav>
			
					
			<?php 
			//Als er een artikel ID is gegeven, tonen
			if(isset($_GET["id"]))
				{
					
			?>
			<article id="cHero">
				<form action="#" method="post" enctype="multipart/form-data">
					<h1>
					<?php echo $shorttitle."<br/>"; ?>
					</h1>
					<?php 
						if(!empty($dates))
						{
							$i=0;
							foreach($dates as $date)
							{
								if(!empty($date->date) && (($date->date)>=$today) ){
									$i++;
								}
							}
							if(!empty($i))
							{
					?>
					<div class="select_box">
						<select id="sDatum" name="sDatum">
								<?php
									foreach($dates as $date)
									{
										if(!empty($date->date) && (($date->date)>=$today) ){
											echo "<option value='".$date->date."'>".$date->date;
											if(!empty($date->timestart))
												echo " ".$date->timestart;
											if(!empty($date->timeend))
												echo "-".$date->timeend;
											echo "</option>";
										}
									}
								?>
						</select><br/>
					</div>
					<?php 
							}	
							else
							{
								if(isset($event->event->eventdetails->eventdetail->calendarsummary))
								$dates= $event->event->eventdetails->eventdetail->calendarsummary;	
								echo "<h4>".$dates."</h4>";
							}
						}
						else
						{
							if(!empty($daysopen)){
								echo "<h6>Van ".$startdate." tot ".$enddate." open: ".$daysopen."<h6>";
							}
						}
					?>
					<h2><?php echo $title; ?><br/><br/><?php echo $zip." ".$city; ?><br/><?php echo $street." ".$housenr; ?></h2><br/>
					<div id="HeroImg">
					<?php
					$imgempty=0;
					if(!empty($images))
					{
						foreach($images as $image)
						{
							if(is_object($image) && $image->mediatype=="photo")
							{
								echo "<img src='".str_replace('&', '&amp;', $image->hlink)."?height=200&amp;crop=auto' alt='event photo' />";
								$imgempty=1;
							}
						}
					}
					if(empty($imgempty))
					{
						echo "<img class='imgempty' src='assets/images/site/leeg-3.png' alt='no picture available' />";
					}
					?>
					</div>
					<p><?php echo $shortdescr; ?></p>
					<input class="bHero" name="<?php echo $btnname ?>" value="<?php echo $btnval ?>" type="submit" /><br/>
					<?php
						if(isset($alert)){
							echo "<h1 class='alert'>".$alert."</h1>";
							$btnname="bVerwijderen";
							$btnval="Uitschrijven";
						}
						if(isset($confirm)){
							echo "<h1 class='confirm'>".$confirm."</h1>";
						}
					?>
					</form>
					<div id="cScore">
						<ul>
							<?php
								for ($i=1; $i < 6; $i++) 
								{
									//Score uitlussen, zowel de image als XP & level
									if($i==($catinfo[1]))
									{
									?>
										<li class='cScore_<?php echo $catinfo[1]; ?>'>
										<img src='assets/images/site/cat_<?php echo $catinfo[1] ?>.png' alt="category image" />
										<br/><h3>+ <?php echo $catinfo[0]; ?> XP</h3>
										</li>
									<?php 
									}
									else{
									?>
										<li class='cScore_<?php echo $i ?>'>
										<img src='assets/images/site/cat_<?php echo $i ?>_n.png' alt="category no score" />
										</li>
									<?php 
									}	
								}
							?>
						</ul>
					</div>
			</article>
			<?php 
				}
				else
				{	
			?>
				<figure id="cScape">
					<p id="cMessage">Selecteer een event</p>
					<img src="<?php if($citycheck){echo 'assets/images/users/564'.$_SESSION["user"].'789.png';}else{echo 'assets/images/site/default.png';} ?>" alt="your city" />
					<div id="cScore">
						<ul>
							<?php
								foreach ($score as $setlevel) {
	
									if(!empty($setlevel['level']))
									{
									//Score uitlussen, zowel de image als XP & level
									?>
										<li class='cScore_<?php echo $setlevel['cat'] ?>'>
										<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>.png' alt="category_score" />
											<h3>Level <?php echo $setlevel['level']; ?></h3>
											<h2>XP <?php echo $setlevel['score'];
											if(!empty($setlevel['max']))
												echo "/".$setlevel['max'];
											?>
											</h2>
										</li>
									<?php 
									}
									else{
									?>
										<li class='cScore_<?php echo $setlevel['cat'] ?>'>
										<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>_n.png' alt="category no score" />
										</li>
									<?php 
									}	
								}
							?>
						</ul>
					</div>
				</figure>
			<?php 
				}
			?>
				
			
			<div id="mNavCenter"><a href="stad.php">Hoofdpagina</a><a href="type.php">Opnieuw Zoeken</a></div>
			
			<article id="cArticles">
				<ul>
				<?php
				
					//De lijst afdrukken
					foreach ($events as $e) 
					{
						if(strlen($e->title)>30){
						$subtitle=substr($e->title,0,30)."...";
						}
						else{
							$subtitle=$e->title;
						} 
						$date=str_replace(',', '', $e->calendarsummary);  
						
				?>
						<li>
							<div><img src='<?php echo str_replace('&', '&amp;', $e->thumbnail) ?>' alt="event image" /></div><a href='lijst.php?<?php echo str_replace('&', '&amp;', $location) ?>&amp;page=<?php echo $page ?>&amp;id=<?php echo $e->cdbid ?>'><?php echo $subtitle ?></a>
							<h2><?php echo $e->location ?></h2>
							<h3><?php echo substr($date,0,12)?></h3>
							<p><?php echo substr($e->shortdescription,0,250)." ..." ?></p>
						</li>
				<?php
				
					}
					
				?>
				</ul>
			</article>
			
			<nav id="cBottomnav">
				
				<div id="mNavBack"><?php if($page>1){ ?><a class="bottomnavbutton" href="lijst.php?<?php echo str_replace('&', '&amp;', $location)."&amp;page=".($page-1); if(isset($_GET["id"])){echo "&amp;id=".$_GET["id"];}  ?>" ><img src="assets/images/site/arrow_left.png" alt="imagage back" /> Vorige</a><?php } ?></div>
				<div id="mNavForward">
				<?php
				$nextpage = json_decode(file_get_contents(str_replace(' ', '%20', $url.($page+1))));
				if(!empty($nextpage)){?>
				<a class="bottomnavbutton" href="lijst.php?<?php echo str_replace('&', '&amp;', $location)."&amp;page=".($page+1); if(isset($_GET["id"])){echo "&amp;id=".$_GET["id"];}  ?>" >Volgende <img src="assets/images/site/arrow_right.png" alt="image more" /></a><?php }?>
				</div>
				
			</nav>	
		</div>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
		<script src="js/app.js"></script>
		<script>
		    (function($){
		        $(window).load(function(){
		            $("#cArticles").mCustomScrollbar({
		            	scrollButtons:{
						enable:true
						},
						theme:"dark-thin"
		            });
		            
		             
		        });
		    })(jQuery);
		</script>
	</body>
</html>
