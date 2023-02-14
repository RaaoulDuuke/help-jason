<?php

	$connect = mysqli_connect("localhost","root" ,"") or die("erreur de connexion au serveur");
	mysqli_select_db($connect, "expedition");
	
	$member_limit = 15;
	
	$action_rq = (!empty($_REQUEST["action"])) ? $_REQUEST["action"] : '';
	
	
	// CREATE MEMBER REQUEST
	if($action_rq=="createMember"){
		
		$name_rq = (!empty($_REQUEST["name"])) ? strtolower(str_replace("'", "’", $_REQUEST["name"])) : '';
		
		$error = 0;
	
		if($name_rq!=""){

			$member_rq = "SELECT id FROM members WHERE name='{$name_rq}'";
			$member_rs = mysqli_query($connect, $member_rq) or die();	
			if(mysqli_num_rows($member_rs)){
				
				$error = 1;
				$action_rs = "duplicatedMember";
				
			}
			
		}else{
			
			$error = 1;
			$action_rs = "emptyMember";
			
		}
		
		// CREATE MEMBER
		if(!$error){
			
			$memberCreate_rq = "INSERT INTO members(name) VALUES ('{$name_rq}')";
			$memberCreate_rs = mysqli_query($connect, $memberCreate_rq) or die();
			
			$action_rs = "createdMember";
			
		}
		
	}
	
	
	// DELETE MEMBER REQUEST
	if($action_rq=="deleteMember"){
		
		$memberID = (!empty($_REQUEST["memberID"])) ? $_REQUEST["memberID"] : 0;
		
		if($memberID){
			
			$member_rq = "SELECT name FROM members WHERE id='{$memberID}'";
			$member_rs = mysqli_query($connect, $member_rq) or die();	
			
			// DELETE MEMBER
			if(mysqli_num_rows($member_rs)){
				
				$member = mysqli_fetch_array($member_rs);
				
				$name_rq = $member['name'];
			
				$memberDelete_rq = "DELETE FROM members WHERE id={$memberID}";	
				$memberDelete_rs = mysqli_query($connect, $memberDelete_rq) or die();
				
				$action_rs = "deletedMember";
				
			}else{
				
				$action_rs = "missingMember";
				
			}
			
		}

	}
		
	
	// FORM SECTION
	function formSection(){
		
		global $connect;
		global $member_limit;
		
		$members_rq = "SELECT * FROM members";
		$members_rs = mysqli_query($connect, $members_rq);
		$members_nb=mysqli_num_rows($members_rs);
		
		$section = "
		<h2 class='text-center'>
			Ajouter un membre d'équipage
		</h2>";
		
		if($members_nb<$member_limit){
			
			$section .= " 
			
			<form class='new-member-form'>
			
				<label for='name' class='visually-hidden'>Nom de l&apos;Argonaute</label>
				<input id='name' name='name' type='text' placeholder='Nom de l&apos;Argonaute' class='form-control form-control-lg mb-2 text-center' />
				
				<input name='action' type='hidden' value='createMember'>
				
				<div class='d-grid'>
					<button type='submit' class='btn btn-primary'>Ajouter</button>
				</div>
				
			</form>";
			
		}else{
			
			$section .= "
			<div class='alert alert-success text-center' role='alert'>
				L'équipage est au complet !
			</div>";	
			
		}
		
		return $section;
		
	}
	
	
	// MEMBERS SECTION
	function membersSection(){
		
		global $connect;
		global $member_limit;
		
		$members_rq = "SELECT * FROM members ORDER BY name ASC";
		$members_rs = mysqli_query($connect, $members_rq);
		$members_nb = mysqli_num_rows($members_rs);
		
		$section = "
		<h2 class='text-center'>
			<span class='badge rounded-pill text-bg-primary'>{$members_nb}</span> Membres d'équipage 
		</h2>";		
		
		if(!$members_nb){
			
			$section .= "
			<div class='alert alert-warning text-center' role='alert'>
				Vous devez <strong>ajouter {$member_limit} Argonautes</strong> à l'équipage
			</div>";	
			
		}else{
			
			$members_list = "";
			
			while($members=mysqli_fetch_array($members_rs)){	
			
				$members_list .= "
				<div class='member-item col text-center py-1'>
				
					<span class='text-uppercase fw-bold'>".$members["name"]."</span> 
					<a href='?action=deleteMember&memberID=".$members["id"]."'><i class='bi bi-dash-circle-fill'></i> <span class='visually-hidden'> retirer</span></a>
					
				</div>";	
				
			}
			
			$section .= "
			<section class='container'>		
			
				<div class='member-list row row-cols-3'>{$members_list}</div>
				
			</section>";			
			
		}

		return $section;
		
	}
	
	
	// ALERT SECTION
	function alertSection(){
		
		global $action_rs;
		global $name_rq;
		
		if($action_rs!=""){
			
			$msg_name = "<strong class='text-capitalize'>{$name_rq}</strong>";

			switch($action_rs){
				case "createdMember":
					$alert_msg = "{$msg_name} a été <strong>ajouté(e)</strong> à l'équipage";
					$alert_class = "primary";
				break;
				case "deletedMember":
					$alert_msg = "{$msg_name} a été <strong>retiré(e)</strong> de l'équipage";
					$alert_class = "primary";
				break;
				case "duplicatedMember":
					$alert_msg = "{$msg_name} est <strong>déjà présent(e)</strong> dans l'équipage";
					$alert_class = "warning";
				break;
				case "emptyMember":
					$alert_msg = "<strong>Saisissez le nom d'un Argonaute</strong>";
					$alert_class = "danger";
				break;
				case "missingMember":
					$alert_msg = "<strong>L'Argonaute n'est pas dans l'équipage</strong>";
					$alert_class = "warning";
				break;
				
			}
			
			$section = "
			<div class='alert alert-{$alert_class} text-center' role='alert'>
				{$alert_msg}
			</div>";	

			return $section;
		}

	}
	
?>  


<html>

<head>

	<meta charset="utf-8" />
	
	<title>Mission Toison d'or</title>
	
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
	
	<style>
		body { margin: 0; background:#DDD;}
		main { max-width: 520px; margin: 0 auto;}
	</style>
	
</head>

<body>

	<!-- Header section -->
	<header class="p-2 mb-4 text-bg-primary text-center">
		<h1>Mission Toison d'Or</h1>
	</header>

	<!-- Main section -->
	<main>
	
		<!-- Form section -->
		<?php echo formSection(); ?>

		<!-- Alert section -->
		<?php echo alertSection(); ?>
		  
		<!-- Members section -->
		<?php echo membersSection(); ?>

	</main>
	
	<!-- Footer section -->
	<footer class='text-center mt-4'>
		<p>Consens à accomplir cet exploit, et je jure que je te céderai le sceptre et la royauté.</p>
	</footer>

</body>

</html>