<?php
	
	include_once("classes/Event.class.php");
	
	session_start();
	
	if(isset($_SESSION["user"]))
	{
		$event = new Event();
		$event->id=$_SESSION["user"];
		try
		{
			$score = $event->getinfo();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
		$citycheck=0;
		
		foreach ($score as $singlescore) {
			$citycheck+=$singlescore["level"];
		}
		
		if(!isset($_GET["typeid"]))
		{
			
			header('Location: stad.php');
			
		}
	
		if(isset($_POST["bZoek"]))
		{
			//Filter query samenstellen
			$location="&thema=".$_GET["typeid"];
			
			if(!empty($_POST["sNaam"])){
				$location.= "&q=".$_POST["sNaam"];
			}
			if(!empty($_POST["sProvincie"])){
				$location.= "&city=".$_POST["sProvincie"];
			}
			if(!empty($_POST["sZip"])){
				$location.= "&zip=".$_POST["sZip"];
			}
			if(!empty($_POST["sLeeftijd"])){
				$location.= "&age=".$_POST["sLeeftijd"];
			}
			if(!empty($_POST["sOrganisator"])){
				$location.= "&organiser=".$_POST["sOrganisator"];
			}
			if(!empty($_POST["sDatum"])){
				$location.= "&datetype=".$_POST["sDatum"];
			}
			if(!empty($_POST["sGratis"])){
				$location.= "&isfree=true";
			}
			
			header('Location: lijst.php?'.$location);

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
		<title>Culturescape - Zoeken</title>
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
				<p id="cMessage">Details</p>
				<img src="<?php if($citycheck){echo 'assets/images/users/564'.$_SESSION["user"].'789.png';}else{echo 'assets/images/site/default.png';} ?>" alt="your city" />
				<div id="cScore">
					<ul>
						<?php
							foreach ($score as $setlevel) {

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
									<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>_n.png' alt="category no score" />
									</li>
								<?php 
								}	
							}
						?>
					</ul>
				</div>
			</figure>
			
			<div id="mNavCenterSmall"><a href="type.php">Terugkeren</a></div>
			
			<article id="cSearch">
				<form action="#" method="post" enctype="multipart/form-data">
					<div class="cPart">
					<label for="sNaam">Titel</label><br/>
					<input id="sNaam" name="sNaam" type="text" placeholder="De titel van een event" /><br/>
					<label for="sProvincie">Provincie</label><br/>
					<div class="select_box">
						<select id="sProvincie" name="sProvincie">
							<option value=""></option>
							<option value="Antwerpen">Antwerpen</option>
							<option value="Limburg">Limburg</option>
							<option value="Oost-Vlaanderen">Oost-Vlaanderen</option>
							<option value="Vlaams-Brabant">Vlaams-Brabant</option>
							<option value="West-Vlaanderen">West-Vlaanderen</option>
						</select><br/>
					</div>
					<label for="sZip">Zip</label><br/>
					<input id="sZip" name="sZip" type="text" placeholder="bv. '2930'" /><br/>
					</div>
					<div class="cPart">
					<label for="sLeeftijd">Leeftijd</label><br/>
					<input id="sLeeftijd" name="sLeeftijd" type="text" placeholder="Minimum leeftijd"/><br/>
					<label for="sOrganisator">Organisator</label><br/>
					<input id="sOrganisator" name="sOrganisator" type="text" /><br/>
					<label for="sDatum">Datum</label><br/>
					<div class="select_box">
						<select id="sDatum" name="sDatum">
							<option value=""></option>
							<option value="today">Vandaag</option>
							<option value="tomorrow">Morgen</option>
							<option value="thisweek">Deze Week</option>
							<option value="thisweekend">Dit weekend</option>
							<option value="nextweekend">Volgend Weekend</option>
							<option value="next30days">Volgende 30 dagen</option>
						</select><br/>
					</div>
					</div>
					<div class="cPartSide">
					<label for="sGratis">Enkel Gratis?</label><br/>
					<input id="sGratis" name="sGratis" type="checkbox" /><br/>
					<input class="bZoek" name="bZoek" value="Zoeken" type="submit" /><br/>
					</div>
				</form>
			</article>
		</div>
	</body>
</html>
