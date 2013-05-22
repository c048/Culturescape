<?php

	include_once("classes/User.class.php");
	
	//Als de cookie is geplaatst, inloggen
	if(isset($_COOKIE["65qs4f898"]) && isset($_COOKIE["qs9df7856"])){
		$user = new User();
		$user->email=$_COOKIE["65qs4f898"];
		$user->cookie=$_COOKIE["qs9df7856"];
		
		try
		{
			$userpk=$user->confirm();
			if($userpk)
			{
				session_start();
				$_SESSION['user']=$userpk;
				header('Location: stad.php');
			}
		} 
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	//Controleren ofdat de login gegevens kloppen
	if(isset($_POST['bLogin']))
	{
		
		if(!empty($_POST['mEmail']) && !empty($_POST['mPasswd']))
		{
			$user = new User();
			$user->email=$_POST['mEmail'];
			$user->password=$_POST['mPasswd'];
			
			try
			{
				if(isset($_POST["mRemember"])){
					$time = time() + 60*60*24*7;
					setcookie('65qs4f898', $user->email, $time);
					setcookie('qs9df7856', $user->password, $time);
				}
				try
				{
					$userpk=$user->confirm();
				} 
				catch (Exception $e)
				{
					echo $e->getMessage();
				}
				if($userpk)
				{
					session_start();
					$_SESSION['user']=$userpk;
					header('Location: stad.php');
				}
				else
				{
					$feedback = "Incorrecte gegevens";
				}
			} 
			catch (Exception $e)
			{
				echo $e->getMessage();
			}	
		}
		else 
		{
			$feedback = "Gelieve all velden in te vullen";
		}	
	}
	
	if(isset($_POST['bSearch']))
	{
		header('Location: bezoeken.php?city='.$_POST['search']);
	}
	
	//Registeren, maar eerst controleren ofdat de naam niet bezet is
	if(isset($_POST['bRegister']))
	{
		
		if(!empty($_POST['mEmail']) && !empty($_POST['mPass']))
		{
			
			$user = new User();
			$user->email=$_POST['mEmail'];
			$user->password=$_POST['mPass'];
			
			try
			{
				$exists=$user->availability();
			} 
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
			if($exists)
			{
				$regfeedback = "Deze gebruikersnaam is al gebruikt";
			}
			else 
			{
				try
				{
					$user->register();
					if($user){
						try
						{
							$userpk=$user->confirm();
						} 
						catch (Exception $e)
						{
							echo $e->getMessage();
						}
						if($userpk)
						{
							session_start();
							$_SESSION['user']=$userpk;
							header('Location: stad.php');
						}
					}
				} 
				catch (Exception $e)
				{
					echo $e->getMessage();
				}
			}
			
		}
		else 
		{
			$regfeedback = "Gelieve alle velden in te vullen";
		}
		
	}

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Culturscape - Home</title>
		<meta name="author" content="Jorik" />
		<link rel="stylesheet" href="css/style.css" />
	</head>

	<body>
		<div id="cMain">
			
			<nav id="cTopnav">
				<form action="#" method="post" enctype="multipart/form-data">
						<input id="searchtext" name="search" type="search" placeholder="zoeken naar een stad" />
						<input id="bSearch" name="bSearch" value="Zoeken" type="submit" />
				</form>
			</nav>
			
			<figure id="cScape">
				<p id="cMessage">Culturescape</p>
				<img src="assets/images/site/main.png" alt="culturescape image" />
			</figure>
			
			<div id="cTekst">
				<h1>Welkom bij culturescape.</h1><br/><p>Zoek en participeer samen met duizenden andere in cultuur evenementen, en creeër zo je unieke digitale avatar.</p>
			</div>
			
			<div id="cForm">
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
					<input id="mEmail" name="mEmail" type="text" placeholder="gebruikersnaam" /><br/>
					<input id="mPasswd" name="mPasswd" type="password" placeholder="wachtwoord" /><br/>
					<input class="bMain" name="bLogin" value="Login" type="submit" /><br/>
					<label><input type="checkbox" name="mRemember" /> Mij onthouden</label>
					<h3 class="<?php if(!isset($feedback)){echo "hidden";} ?>"><?php if(isset($feedback)){echo $feedback;} ?></h3>
				</form>
			</div>
			
			<div id="rForm">
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<h1>Nog geen account? Registreer!</h1>
					<input id="regname" type="text" name="mEmail" placeholder="Gebruikersnaam" />
					<input type="password" name="mPass" placeholder="Wachtwoord" />
					<input class="bReg" type="submit" name="bRegister" value="Registreren" />
					<h3 class="<?php if(!isset($regfeedback)){echo "hidden";} ?>"><?php if(isset($regfeedback)){echo $regfeedback;} ?></h3>
				</form>
			</div>
		</div>
	</body>
</html>
