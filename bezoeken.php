<?php

	include_once("classes/User.class.php");
	include_once("classes/Event.class.php");
	
	session_start();
	
	$user = new User();
	$user->email=$_GET["city"];
	//Gegevens ophalen op basis van de naam ipv id
	$userid = $user->vistordata();
	
	$event = new Event();
	//ID nr ophalen
	$event->id= $userid[0]["user_id"];
	$score = $event->getinfo();
	
	$citycheck=0;
	
	if(!empty($score))
	{	
		foreach ($score as $singlescore) 
		{
			$citycheck+=$singlescore["level"];
		}
	}

	if(isset($_POST['bSearch']))
	{
		header('Location: bezoeken.php?city='.$_POST['search']);
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
				<?php if(isset($_SESSION["user"])){ ?><a href="loguit.php">uitloggen</a><?php } ?>
				<form action="#" method="post" enctype="multipart/form-data">
						<input id="searchtext" name="search" type="search" placeholder="zoeken naar een stad" />
						<input id="bSearch" name="bSearch" value="Zoeken" type="submit" />
				</form>
			</nav>
			
			<figure id="cScape">
				<p id="cMessage"><?php if(!empty($score)){ echo "Stad van ".$_GET["city"]; } else { echo "Geen stad gevonden "; } ?></p>
				<img src="<?php if(!empty($citycheck) && !empty($score)){echo 'assets/images/users/564'.$userid[0]["user_id"].'789.png';}else{echo 'assets/images/site/default.png';} ?>" alt="your city" />
				<?php if(!empty($score)){ ?>
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
										<br/><h3>Level <?php echo $setlevel['level']; ?></h3>
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
				<?php } ?>
			</figure>
			
			<div id="mNavCenterSmall"><a href="<?php if(isset($_SESSION["user"])){ echo "stad.php";}else{echo "index.php";} ?>"><?php if(isset($_SESSION["user"])){ echo "Terugkeren";}else{echo "Naar culturescape";} ?></a></div>
			
		</div>
	</body>
</html>
