<?php
session_start();
require_once("/etc/apache2/capstone-mysql/przm.php");
require_once("../php/class/flight.php");
require_once("../php/class/profile.php");

	if(isset($_SESSION['userId'])) {
		$mysqli = MysqliConfiguration::getMysqli();
		$profile = Profile::getProfileByUserId($mysqli,$_SESSION['userId']);
		$fullName =  ucfirst($profile->__get('userFirstName')).' '.ucfirst($profile->__get('userLastName'));
		$userName = <<<EOF
		<a><span	class="glyphicon glyphicon-user"></span> Welcome, $fullName  </a>
EOF;
		$status = <<< EOF
			<a href="../php/processors/signOut.php">Sign Out</a>
EOF;

	}

	$flightIds = $_SESSION['flightIds'];
	foreach($flightIds as $flightId){
		$flights[] = Flight::getFlightByFlightId($mysqli, $flightId);
	}
	$outboundFlightCount = $_SESSION['outboundFlightCount'];

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

	<style>

		#travelerContainer{
			border: 1px solid lightgrey;
			height: 35em;
			width: 27em;
			margin-left: 4.7em;
			margin-top: 2em;
			margin-bottom: 2em;

		}

		.travelerSelect{
			font-size: 1.2em;
			padding: .5em;
			background-color: white;

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
		.flightData td{
			padding: .5em;
			background-color: lightblue;
		}
		table{
			padding: 1em;
			margin-left: 4em;

		}
		table td, th{
			padding: .8em;

		}
		ul{
			list-style: none;
			text-align: left;
			padding-left: 0pt;
		}

	</style>
</head>
<body>
<?php
echo<<<HTML
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
				<a class="navbar-brand" href="../php/processors/clearSession.php"><span class="glyphicon glyphicon-cloud"
																												aria-hidden="true"></span> PRZM AIR</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li></li>
				</ul>

				<ul class="nav navbar-nav navbar-right">
					<li class="disabled"> </li>
					<li class="active"></li>
					<li><a href="#"></a></li>
				</ul>
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>
</header>
<!-- Display Flights -->
<section>
HTML;



echo <<<HTML

<h3 style="text-align: center">Outbound Flight Details</h3>
<div class="flightContainer">
HTML;


		foreach ($flights as $flight){

			$fltNum = $flight->getFlightNumber();
			$origin = $flight->getOrigin();
			$destination = $flight->getDestination();
			$duration = $flight->getDuration()->format("%H:%I");
			$depTime = $flight->getDepartureDateTime()->format('h:i:s a m/d/Y');
			$arrTime = $flight->getArrivalDateTime()->format("h:i:s a m/d/Y");
			if($outboundFlightCount-- === 0){
				echo <<<HTML
					<hr><h3 style="text-align: center">Inbound Flight Details</h3>
HTML;

			}
			echo <<<HTML
		<div class="displayFlt">
			<table class="flightData table">
				<thead>
					<tr>
						<th>Flight Number</th>
						<th>Origin</th>
						<th>Destination</th>
						<th>Duration</th>
						<th>Departure</th>
						<th>Arrival</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>$fltNum</td>
						<td>$origin</td>
						<td>$destination</td>
						<td>$duration</td>
						<td>$depTime</td>
						<td>$arrTime</td>

					</tr>
				</tbody>
			</table>
		</div>

HTML;
echo "</div>";
		}
echo <<<HTML
	</div>
</section>
<section>
HTML;
	$today = new DateTime('now');
	$today = $today->format("h:i:s a m/d/y");
	$outboundFltCount = $_SESSION['outboundFlightCount'];
echo <<<HTML
<div>
	<table>

		<tr><th colspan="16">Your Itinerary</th></tr>

		<tr>
			<td>
				<ul>

					<li>Today: $today</li>

				</ul>
			</td>
		</tr>


		<tr><td colspan="16"><b>Departure Details</b><hr></td></tr>
HTML;

		foreach($flights as $flight) {
			$fltNum = $flight->getFlightNumber();
			$origin = $flight->getOrigin();
			$destination = $flight->getDestination();
			$duration = $flight->getDuration()->format("%H:%I");
			$depTime = $flight->getDepartureDateTime()->format("h:i:s a");
			$depDate = $flight->getDepartureDateTime()->format("m/d/Y");
			$arrTime = $flight->getArrivalDateTime()->format("h:i:s a");
			$arrDate = $flight->getArrivalDateTime()->format("m/d/Y");
			if($outboundFltCount-- === 0) {
				echo "<tr><td colspan='16'><b>Return Details</b><hr></td></tr>";
			}
			echo <<<HTML
		<tr>
			<td>
				<ul>
					<li>Origin: $origin</li>
					<li>Destination: $destination</li>
				</ul>
			</td>
			<td colspan="10">
				<ul>
					<li>Depart: $depTime</li>
					<li>Arrive: $arrTime</li>
				</ul>
			</td>
			<td>
				Flight # $fltNum
			</td>
			<td colspan="5">
				<ul>
					<li>$depDate</li>
					<li>Duration</li>
					<li>$duration</li>
				</ul>
			</td>
		</tr>
HTML;
		}
echo "</table>
</div>

</section>";
?>
</body>
</html>

