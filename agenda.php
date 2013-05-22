<?php

	include_once("classes/Event.class.php");
	
	session_start();
	
	if(isset($_SESSION["user"]))
	{
		if(isset($_GET['page']))
		{
			$page=$_GET['page'];
		}
		else {
			$page=0;
		}
		
		$info = new Event();
		$info->id=$_SESSION["user"];
		
		try
		{
			$upcomming = $info->getupcomming($page,5);
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
		if(isset($_GET["event"]))
		{
			try
			{
				$upcommingdetails = $info->getsingle($_GET["event"]);
			} 
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
			
				if(!empty($upcommingdetails))
				{
					try
					{
						$catinfo=$info->getsingleid($_GET["event"]);
					} 
					catch (Exception $e)
					{
						echo $e->getMessage();
					}
					
					if(isset($_POST['bVerwijderen']))
					{
						try
						{
							$info->remove($_GET["event"]);
							header('Location: agenda.php');
						} 
						catch (Exception $e)
						{
							echo $e->getMessage();
						}
					}
				}
		}
		if(isset($_POST['bSearch']))
		{
			header('Location: bezoeken.php?city='.$_POST['search']);
		}
		
		if(isset($_GET["event"]) && !empty($upcommingdetails))
		{
			if(strlen($upcommingdetails[0]['event_title'])>41){
				$shorttitle=substr($upcommingdetails[0]['event_title'],0,41)." ...";
			}
			else{
				$shorttitle=$upcommingdetails[0]['event_title'];
			} 	

			$loc=$upcommingdetails[0]['event_loc'];
			$zip=$upcommingdetails[0]['event_zip'];
			$stad=$upcommingdetails[0]['event_stad'];
			$adres=$upcommingdetails[0]['event_adres'];
			
			if(strlen($upcommingdetails[0]['event_text'])>700)
			{
				$shortdescr=substr($upcommingdetails[0]['event_text'],0,700)." ...";
			}
			else{
				$shortdescr=$upcommingdetails[0]['event_text'];
			} 
		}
		else{
			try
			{
				$score = $info->getinfo();
			} 
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
			$citycheck=0;

			foreach ($score as $singlescore) {
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
		<title>Culturescape - Agenda</title>
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
			if(isset($_GET["event"]) && !empty($upcommingdetails))
				{
					
			?>
			<article id="cHero">	
				<form action="#" method="post" enctype="multipart/form-data">
					<h1><?php echo $shorttitle; ?></h1>
					<h4><?php echo $upcommingdetails[0]['event_date'] ?></h4>
					<h2><?php echo $loc ?><br/><br/><?php echo $zip." ".$stad; ?><br/><?php echo $adres ?></h2>
					<?php
						if(empty($upcommingdetails[0]['event_img'])){
							echo "<br/><div id='HeroImg'><img class='imgempty' src='assets/images/site/leeg-3.png' alt='no image available' /></div>";
						}
						else{
							echo "<br/><div id='HeroImg'><img src='".str_replace('&', '&amp;', $upcommingdetails[0]['event_img'])."' alt='event image' /></div>";
						}	
					?>	
						<p><?php echo $shortdescr; ?></p>
					<?php	
						if(isset($alert)){
							echo "<h1>".$alert."</h2>";
						}
						if(isset($confirm)){
							echo "<h1>".$confirm."</h2>";
						}
					?>
					<input class="bHero" name="bVerwijderen" value="Verwijderen" type="submit" /><br/>
				</form>
				<div id="cScore">
					<ul>
						<?php
							for ($i=1; $i < 6; $i++) 
							{
								 
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
					<img src="<?php if($citycheck){echo 'assets/images/users/564'.$_SESSION["user"].'789.png';}else{echo 'assets/images/site/default.png';} ?>" alt='your city' />
					<div id="cScore">
						<ul>
							<?php
								foreach ($score as $setlevel) {
	
									if(!empty($setlevel['level']))
									{
									?>
										<li class='cScore_<?php echo $setlevel['cat'] ?>'>
										<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>.png' alt='category image' />
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
										<img src='assets/images/site/cat_<?php echo $setlevel['cat'] ?>_n.png' alt='category no score' />
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
			
			
			<div id="mNavCenterSmall2"><a href="stad.php">Terugkeren</a></div>
					
			<article id="cArticles">
				<ul>
				<?php
					if(!empty($upcomming)){
						foreach ($upcomming as $u) 
						{ 
				?>
					<li>
						<div><img src='<?php echo str_replace('&', '&amp;', $u['event_img']) ?>' alt="event image" /></div>
						<a href='agenda.php?event=<?php echo $u['event_id'] ?>&amp;page=<?php echo $page ?>'><?php echo $u['event_title'] ?></a>
						<h3><?php echo $u['event_date'] ?></h3>
						<p><?php echo substr($u['event_text'],0,250)." ..." ?></p>
					</li>
				<?php
						}
					}
					else{

				?>	
					<li>
						<h3>Geen events gevonden</h3>
					</li>
				<?php
					}	
				?>		
				</ul>
			</article>
			
			<nav id="cBottomnav">
				
				<div id="mNavBack"><?php if($page>0){ ?><a href="agenda.php?<?php echo "&amp;page=".($page-1); if(isset($_GET["event"])){echo "&amp;event=".$_GET["event"];}  ?>"><img src="assets/images/site/arrow_left.png" alt="page back" /> Vorige</a><?php } ?></div>
				<div id="mNavForward">
				<?php
				$nextpage = $info->getupcomming($page+1,5);
				if(!empty($nextpage)){?>
				<a href="agenda.php?<?php echo "&amp;page=".($page+1); if(isset($_GET["event"])){echo "&amp;event=".$_GET["event"];}  ?>">Volgende <img src="assets/images/site/arrow_right.png" alt="page forward" /></a><?php }?>
				</div>
				
			</nav>	
					
			
		</div>
		
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
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
