<?php
require("/etc/apache2/capstone-mysql/przm.php");
require("../class/user.php");
require("../class/profile.php");
include("../../lib/csrf.php");

try {
	session_start();
	$savedName = $_POST["csrfName"];
	$savedToken =$_POST["csrfToken"];

	if(verifyCsrf($_POST["csrfName"], $_POST["csrfToken"]) === false) {
		throw(new RuntimeException("Make sure cookies are enabled"));
	}
	$mysqli = MysqliConfiguration::getMysqli();
	$user = User::getUserByUserId($mysqli, $_SESSION['userId']);
	$profile = Profile::getProfileByUserId($mysqli, $user->getUserId());

	$profile->setFirstName($newFirstName = filter_input(INPUT_POST, "first", FILTER_SANITIZE_STRING));
	$newMiddleName = filter_input(INPUT_POST, "middle", FILTER_SANITIZE_STRING);
	if($newMiddleName !== "" || $newMiddleName !== " " || $newMiddleName !== null) {
		$profile->setMiddleName($newMiddleName);
	}
	$profile->setLastName($newLastName = filter_input(INPUT_POST, "last", FILTER_SANITIZE_STRING));
	$newDOB = filter_input(INPUT_POST, "dob", FILTER_SANITIZE_STRING);
	$newDOB = DateTime::createFromFormat("m/d/Y", $newDOB);
	$profile->setDateOfBirth($newDOB->format("Y-m-d H:i:s"));
	$user->setEmail($newEmail = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL));

	$profile->update($mysqli);
	$user->update($mysqli);
	echo "<div class='alert alert-success' role='alert'>
  			Your profile has been updated with your changes</div>
			<script>
						$(document).ready(function() {
							$(':input').attr('disabled', true);
						});
			</script>";
}catch (Exception $e){
	$_SESSION[$savedName] = $savedToken;
	echo "<div class='alert alert-danger' role='alert'>"
  			.$e->getMessage."</div>";
}
?>
