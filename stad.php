<?php

	include_once("classes/Event.class.php");
	include_once("core/core.php");
	
	session_start();
	
	if(isset($_SESSION["user"]))
	{
		$event = new Event();
		$event->id=$_SESSION["user"];
		//Controleren op een update
		try
		{
			$status=$event->update();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		//Score ophalen
		try
		{
			$score = $event->getinfo();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		//Naam van de gebruiker ophalen voor facebook link
		try
		{
			$username= $event->getname();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
		$diffstring="";
		try
		{
			$different=$event->getrare();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		$random=rand(1,5);
		
		//Links uit de laagste cat tonen
		foreach ($different as $catkeys) {
			$diffstring.=$catkeys["type_serial"].";";
		}
		$diffurl="http://build.uitdatabank.be/api/events/search?key=".$g_uitdb_key."&thema=".$diffstring."&format=json&pagelength=3&page=".$random;
		$diffevents = json_decode(file_get_contents(str_replace(' ', '%20', $diffurl)));
		
		//Links uit de hoogste cat tonen
		$comstring="";
		try
		{
			$common=$event->getcommon();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		foreach ($common as $commonkeys) {
			$comstring.=$commonkeys["type_serial"].";";
		}
		$comurl="http://build.uitdatabank.be/api/events/search?key=".$g_uitdb_key."&thema=".$comstring."&format=json&pagelength=3&page=".$random;
		$comevents = json_decode(file_get_contents(str_replace(' ', '%20', $comurl)));
		
		//Agenda gegevens tonen
		$upcomming=$event->getupcomming(0,3);
		
		$citycheck=0;
		//Controleren ofdat er wel degelijk een eigen stad is
		foreach ($score as $singlescore) {
			$citycheck+=$singlescore["level"];
		}

		if(isset($_POST['bSearch']))
		{
			header('Location: bezoeken.php?city='.$_POST['search']);
		}
	}	
	else 
	{
		header('Location: loguit.php');
	}

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Culturescape - Home</title>
		<meta name="author" content="Jorik" />
		<link rel="stylesheet" href="css/style.css" />
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

			<figure id="cScape">
				<p id="cMessage">Welkom Terug!</p>
				<img src="<?php if($citycheck){echo 'assets/images/users/564'.$_SESSION["user"].'789.png';}else{echo 'assets/images/site/default.png';} ?>" alt="your city" />

				<div id="cScore">
					<ul>
						<?php
							foreach ($score as $setlevel) {
								
								//Score uitlussen
								if(!empty($setlevel['level']))
								{
								?>
									<li class='cScore_<?php echo $setlevel['cat'] ?>'>
									<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>.png' alt="category image" />
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
									<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>_n.png' "category no score" />
									</li>
								<?php 
								}	
							}
						?>
					</ul>
					<div id="fbbutton"><a href='https://www.facebook.com/dialog/feed?app_id=128683670664868&amp;link=http://thieriy103.onehundredandthree.axc.nl/culturescape/bezoeken.php?city=<?php echo $username ?>&amp;picture=http://thieriy103.onehundredandthree.axc.nl/culturescape/<?php if($citycheck){echo 'assets/images/users/564'.$_SESSION["user"].'789.png';}else{echo 'assets/images/site/default.png';} ?>&amp;name=Culturescape&amp;caption=Zoek%20en%20participeer%20samen%20met%20duizenden%20andere%20in%20cultuur%20evenementen,%20en%20creeër%20zo%20je%20unieke%20digitale%20avatar.&amp;redirect_uri=http://thieriy103.onehundredandthree.axc.nl/culturescape/'></a></div>
				</div>
			</figure>
			
			<article id="cListsM">
				<div id="cNav"><a class="cButton" href="type.php">Naar events zoeken</a></div>
				<div class="cListCat">
					<label>Probeer eens iets anders</label>
					<ul>
						<?php
							foreach ($diffevents as $d) 
							{
								if(strlen($d->title)>20){
									$subtitle=substr($d->title,0,20)."...";
								}
								else{
									$subtitle=$d->title;
								} 
								$date=str_replace(',', '', $d->calendarsummary);
						?>
								<li>
									<p><a href='lijst.php?thema=<?php echo $diffstring ?>&amp;id=<?php echo $d->cdbid ?>'><?php echo $subtitle ?></a> - <?php echo substr($date,0,12) ?></p>
								</li>
						<?php
							}	
						?>
						
					</ul>
					<a class="ListLink" href="lijst.php?thema=<?php echo $diffstring ?>">Meer >></a><br/>
				</div>
				<div class="cListCat">
					<label>Gelijkaardig aan je vorige keuzes</label>
					<ul id="tGelijk">
						<?php
							foreach ($comevents as $c) 
							{
								if(strlen($c->title)>20){
									$subtitle=substr($c->title,0,20)."...";
								}
								else{
									$subtitle=$c->title;
								} 
								$date=str_replace(',', '', $c->calendarsummary); 
						?>
								<li>
									<p><a href='lijst.php?thema=<?php echo $comstring ?>&amp;id=<?php echo $c->cdbid ?>'><?php echo $subtitle ?></a> - <?php echo substr($date,0,12) ?></p>
								</li>
						<?php
							}	
						?>	
					</ul>
					<a class="ListLink" href="lijst.php?thema=<?php echo $comstring ?>">Meer >></a><br/>
				</div>
				<div class="cListCat" >
					<label>Je opkomende events</label>
					<ul id="agenda">
						<?php
							if(!empty($upcomming))
							{
								foreach ($upcomming as $u) 
								{
									if(strlen($u['event_title'])>24){
									$subtitle=substr($u['event_title'],0,24)."...";
									}
									else{
										$subtitle=$u['event_title'];
									} 
									$date=str_replace(',', '', $u['event_date']);  
						?>
								<li>
									<p><a href='agenda.php?id=<?php echo $u['event_id'] ?>'><?php echo $subtitle ?></a> - <?php echo substr($date,0,12) ?></p>
								</li>
						<?php
								}
						?>
							
						<?php
							}	
							else {
						?>
								<li>
									<p>Klik op "naar events zoeken" om te beginnen!</p>
								</li>
						<?php
							}
						?>	
					</ul>
					<?php if(!empty($upcomming)){ ?><a class="ListLink" href="agenda.php">Alle opkomende events >></a><br/><?php } ?>
				</div>
			</article>
		</div>
	</body>
</html>
