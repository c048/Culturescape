<?php

	include_once("classes/Event.class.php");
	
	session_start();
	
	if(isset($_SESSION["user"]))
	{
		$event = new Event();
		$event->id=$_SESSION["user"];
		//Score ophalen
		try
		{
			$score = $event->getinfo();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		//Alle event types ophalen
		try
		{
			$alltypes = $event->getlist();
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
		$citycheck=0;
		
		foreach ($score as $singlescore) {
			$citycheck+=$singlescore["level"];
		}
		
		if(isset($_POST['bSearch']))
		{
			header('Location: bezoeken.php?city='.$_POST['search']);
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

			<figure id="cScape">
				<p id="cMessage">Kies een categorie</p>
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
			
			<div id="mNavCenterSmall"><a href="stad.php">Terugkeren</a></div>
			
			<article id="cLists">
				<div class="cListScroll" >
					<label><a href="details.php?typeid=1.17.0.0.0;1.2.1.0.0;1.1.0.0.0;1.0.2.0.0;1.0.6.0.0;">Kunst</a></label><br/>
					<ul>
						<?php 	
							foreach($alltypes AS $singletype)
							{
								if($singletype['type_category']==1){
						?>
						<li><a href="details.php?typeid=<?php echo $singletype['type_serial'] ?>"><?php echo $singletype['type_omschrijving']; ?></a></li>
						<?php 
								}
							}
						?>	
					</ul><br>
				</div>
				<div class="cListScroll" >
					<label><a href="details.php?typeid=1.62.0.0.0;1.63.0.0.0;1.64.0.0.0;1.52.0.0.0;1.42.0.0.0;">Recreatie & Sport</a></label><br/>	
					<ul>
						<?php 	
							foreach($alltypes AS $singletype)
							{
								if($singletype['type_category']==2){
						?>
						<li><a href="details.php?typeid=<?php echo $singletype['type_serial'] ?>"><?php echo $singletype['type_omschrijving']; ?></a></li>
						<?php 
								}
							}
						?>	
					</ul><br>
				</div>
				<div class="cListScroll" >
					<label><a href="details.php?typeid=1.7.2.0.0;1.7.12.0.0;1.7.1.0.0;1.7.6.0.0;1.7.8.0.0;1.7.14.0.0;">Film</a></label><br/>
					<ul>
						<?php 	
							foreach($alltypes AS $singletype)
							{
								if($singletype['type_category']==3){
						?>
						<li><a href="details.php?typeid=<?php echo $singletype['type_serial'] ?>"><?php echo $singletype['type_omschrijving']; ?></a></li>
						<?php 
								}
							}
						?>	
					</ul><br>
				</div>
				<div class="cListScrollt" >
					<label><a href="details.php?typeid=1.10.0.0.0;1.10.5.0.0;1.10.11.0.0;1.10.12.0.0;1.10.8.0.0;">Theater & Poezie</a></label><br/>
					<ul>
						<?php 	
							foreach($alltypes AS $singletype)
							{
								if($singletype['type_category']==4){
						?>
						<li><a href="details.php?typeid=<?php echo $singletype['type_serial'] ?>"><?php echo $singletype['type_omschrijving']; ?></a></li>
						<?php 
								}
							}
						?>	
					</ul><br>
				</div>
				<div class="cListScrollm" >
					<label><a href="details.php?typeid=1.9.1.0.0;1.9.3.0.0;1.9.5.0.0;1.9.2.0.0;1.40.0.0.0;">Muziek</a></label><br/>
					<ul>
						<?php 	
							foreach($alltypes AS $singletype)
							{
								if($singletype['type_category']==5){
						?>
						<li><a href="details.php?typeid=<?php echo $singletype['type_serial'] ?>"><?php echo $singletype['type_omschrijving']; ?></a></li>
						<?php 
								}
							}
						?>	
					</ul><br/>
				</div>
			</article>
		</div>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
		<script>
		    (function($){
		        $(window).load(function(){
		            $(".cListScrollt ul").mCustomScrollbar({
		            	scrollButtons:{
						enable:true
						},
						theme:"dark-thin"
		            });
		         	$(".cListScrollm ul").mCustomScrollbar({
		         		scrollButtons:{
						enable:true
						},
						theme:"dark-thin"
		         	});
		         	$(".cListScroll ul").mCustomScrollbar({
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
