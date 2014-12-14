<?php
session_start();
require("/etc/apache2/capstone-mysql/przm.php");
require("../php/class/profile.php");
require("../php/class/traveler.php");
require('../lib/csrf.php');

try{

	if(isset($_SESSION['userId'])) {
		$mysqli = MysqliConfiguration::getMysqli();
		$profile = Profile::getProfileByUserId($mysqli,$_SESSION['userId']);
		$fullName =  ucfirst($profile->__get('userFirstName')).' '.ucfirst($profile->__get('userLastName'));
		$userName = <<<EOF
		<a><span	class="glyphicon glyphicon-user"></span> Welcome, $fullName  </a>
EOF;
		$status = <<< EOF
			<a href="signOut.php">Sign Out</a>
EOF;
		$account = <<< EOF
		<li role="presentation">
			<a href="#account" id="account-tab" role="tab" data-toggle="tab" aria-controls="account"
				aria-expanded="true">
				Account</a>
		</li>
EOF;
	}

	if(isset($_SESSION['travelerIds'])){
		echo<<<HTML
		<script>
			$(function(){
					var array = @Session['travelerIds'];
				$(':checkbox').each(function() {
					for(var i =0; i <; ++i){
						if(this.val = array[i]){
							this.checked = true;
						}
					}
      		});
		   });
		</script>
HTML;

	}

	$staticTravelers = Traveler::getTravelerByProfileId($mysqli, $profile->__get("profileId"));

}catch(Exception $e){
	echo "<div class='alert alert-danger' role='alert'>".$e->getMessage()."</div>";
}

?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Travelers</title>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/3.51/jquery.form.min.js"></script>
	<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.12.0/jquery.validate.min.js"></script>
	<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.12.0/additional-methods.min.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

	<script type="text/javascript" src="../js/selectTravelers.js"></script>

	<script>
		$(function() {
			$( ".datepicker" ).datepicker({
				changeMonth: true,
				changeYear: true,
				maxDate: "0d",
				minDate: "-100y"
			});
		});
	</script>
	<style>
		#formDiv{
			position: relative;
			top: 52%;
			left: 33%;
			display: inline;
		}
		#travelerContainer{
			border: 2px solid lightgrey;
			border-radius: 5%;
			height: 35em;
			width: 27em;
			margin-left: 4.7em;
			margin-top: 2em;
			margin-bottom: 2em;

		}
		#bookFltDiv{
			border: 2px solid lightgrey;
			height: 4em;
			width: 36em;
			border-radius: 15%;
			margin-bottom: 1em;

		}
		#bookFltDiv button{
			padding: .5em;
			margin-left: 14em;
			margin-top: .5em;
			background-color: lightblue;

		}
		.buttonDiv{
			margin-bottom: 2em;
			height: 4em;
			width: 36em;
			border-radius: 15%;
			border: 2px solid lightgrey;
		}
		.innerBtnDiv{
			margin-left: 3.7em;
			margin-top: .4em;
		}
		#A{
			margin-left: 3em;
		}
		#B{
			margin-left: 2em;
		}
		#addTravelerDiv{
			height: 30em;
			width: 30em;
			border: 2px solid lightgrey;
			border-radius: 5%;
		}
		#addTInnerDiv input{
			margin-left: 3.5em;
		}
		#addTInnerDiv label{
			margin-left: 3.5em;
		}
		.travelerSelect{
			font-size: 1.2em;
			padding: .5em;
			background-color: white;
			border-radius: 4%;
		}
		.nameSpan{
			margin-left: .4em;
			padding: .5em;
			font-weight: bold;
		}
		#travelerList{
			background-color: white;
			height: 20em;
		}
		#ckBoxes input{
			margin-left: 4.2em;
		}
		#confirmBtn{
			margin-left: 8.2em;
		}
		.flightData td{
			padding: .5em;
		}
		.si{
			margin-top: 7em;
		}
		#selectAll{
			margin-left: 5.7em;
		}

	</style>
</head>
<body>
<header>
	<nav class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="../index.php"><span class="glyphicon glyphicon-cloud"
																				  aria-hidden="true"></span> PRZM AIR</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li></li>
				</ul>

				<ul class="nav navbar-nav navbar-right">
					<li class="disabled"><?php echo $userName?> </li>
					<li class="active"><?php echo $status?></li>
					<li><a href="#"></a></li>
				</ul>
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>
</header>
<section>
	<div id="formDiv">
		<form id="selectTravelersForm" action="../php/processors/editTravelersProcessor.php" method="post">
			<?php echo generateInputTags(); ?>
			<div class="buttonDiv">
				<div class="innerBtnDiv">
					<button id="A" type="submit" name="action" class="btn" value="Remove">Remove Travelers</button>
					<button id="B" type="button" class="btn" data-toggle="modal" data-target="#myModal">
						Add New Travelers</button>

				</div>
			</div>

			<div id="travelerContainer">
				<h3 style="text-align: center"><span style="color: lightgrey">Travelers</span></h3>


				<hr>
				<div id="travelerList">
					<div id="ckBoxes">
						<h4 style="text-align: center">Select Traveler To Edit</h4>
						<?php
						$travelerArray = array();
						if(count($staticTravelers) > 0) {
							foreach($staticTravelers as $traveler) {
								$name = $traveler->__get("travelerFirstName") . " " . $traveler->__get("travelerLastName");
								$name = ucwords($name);
								$uID = $traveler->__get("travelerId");
								echo <<<HTML
					<div class="travelerSelect"><input class="chkbox" type="checkbox" name="travelerArray[]"
					value="$uID"><span class="nameSpan">$name</span></div>
HTML;
							}
							echo <<<HTML
				<script>
					jQuery(function(){
  						var max = 1;
  						var checkboxes = $('input[class="chkbox"]');

						checkboxes.change(function(){
        					var current = checkboxes.filter(':checked').length;
        					checkboxes.filter(':not(:checked)').prop('disabled', current >= max);
    					});
					});


				</script>
HTML;


						}
						else{
							echo "<p style='text-align: center'>You have not added any travelers</p>";
						}
						?>
						<div id="selectOutput"></div>
					</div>
				</div>
				<hr>

			</div>

			<div id="addTDiv"">
			<!-- Modal -->
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
							<h4 class="modal-title" id="myModalLabel">Modal title</h4>
						</div>
						<div class="modal-body">
							<div id="addTravelerDiv">
								<div id="addTInnerDiv">


									<h4 style="text-align: center">You can have up to 8 travelers</h4><br>
									<h5 style="text-align: center">Traveler Count:
										<?php echo count($staticTravelers);
										if(count($staticTravelers) > 7){
											echo <<<HTML
													<script>
														$(function() {
															$('#first').attr('disabled', true);
															$('#middle').attr('disabled', true);
															$('#last').attr('disabled', true);
															$('#dob').attr('disabled', true);
															$('#addBtn').attr('disabled', true);
														});
													</script>
HTML;

										}

										?></h5><br>
									<label for="tFirst">First Name:</label><br><input type="text" id="first" name="tFirst" size="30"
																									  autocomplete="off"><br>
									<label for="tMiddle">Middle Name:</label><br><input type="text" id="middle" name="tMiddle" size="30"
																										 autocomplete="off"><br>
									<label for="tLast">Last Name:</label><br><input type="text" id="last" name="tLast" size="30"
																									autocomplete="off"><br>
									<label for="tDOB">Date of Birth:</label><br><input type="text" class="datepicker" id="dob" name="tDOB"
																										size="10">

								</div>
								<div id="modalOutput"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button id="addBtn" type="submit" class="btn btn-primary" name="action" value="Add">Add</button>
						</div>
					</div>
				</div>
			</div>
	</div>
	<div class="buttonDiv">
		<div class="innerBtnDiv">
			<div id="confirmBtn"><button type="submit" name="action" class="btn" value="Confirm">Confirm
					Travelers</button></div>
		</div>
	</div>
	</form>
	</div>
</section>
</body>
</html>


