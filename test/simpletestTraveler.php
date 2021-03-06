<?php
// first require the SimpleTest framework
require_once("/usr/lib/php5/simpletest/autorun.php");
require_once("/var/www/html/przm.php");

// then require the class under scrutiny
require_once("../php/class/traveler.php");
require_once("../php/class/profile.php");
require_once("../php/class/user.php");
// the UserTest is a container for all our tests
class TravelerTest extends UnitTestCase {
	// variable to hold the mySQL connection
	private $mysqli = null;
	// variable to hold the test TRAVELER object
	private $traveler  = null;
	private $traveler2  = null;
	private $traveler3  = null;
	private $traveler4  = null;
	// variable to hold the test Profile object
	private $profile = null;
	// variable to hold the test TRAVELER object
	private $user  = null;
	// a few "global" variables for creating test data
	private $TRAVELERFIRSTNAME	= "jacob";
	private $TRAVELERMIDDLENAME = "taylor";
	private $TRAVELERLASTNAME   = "white";
	private $TRAVELERDATEOFBIRTH = null;

	// setUp() is a method that is run before each test
	// here, we use it to connect to mySQL
	/*
	 * create test TRAVELER and test Profile
	 * */
	public function setUp() {
		// connect to mySQL

		$this->mysqli = MysqliConfiguration::getMysqli();
		// randomize the salt, hash, and authentication token for the profile
		$i = rand(1,10000); /*to randomize (must be unique) the email for correct insertion*/
		$testEmail       = "johnmax".++$i."@test.com";
		$testSalt        = bin2hex(openssl_random_pseudo_bytes(32));
		$testAuthToken   = bin2hex(openssl_random_pseudo_bytes(16));
		$testHash        = hash_pbkdf2("sha512", "tEsTpASs", $testSalt, 2048, 128);
		$testCustomerToken   = Stripe_Customer::create(array("description" => "testCustomer"));
		$testFirstName = "john";
		$testMiddleName = "maxwell";
		$testLastName = "green";
		$testDateOfBirth = DateTime::createFromFormat("Y-m-d H:i:s" ,"2010-11-12 12:11:10");
		$this->TRAVELERDATEOFBIRTH = $testDateOfBirth;

		try {
			$this->user = new User(null, $testEmail , $testHash, $testSalt, $testAuthToken);
			$this->user->insert($this->mysqli);

			$this->profile = new Profile (null,$this->user->getUserId(),$testFirstName, $testMiddleName, $testLastName,
															$testDateOfBirth, $testCustomerToken, $this->user);
			$this->profile->insert($this->mysqli);

		} catch (Exception $exception) {
			$exception->getMessage();
		}


	}

	// tearDown() is a method that is run after each test
	// here, we use it to delete the test record and disconnect from mySQL
	public function tearDown()
	{

		// delete the traveler/profile/user if we can
		if($this->traveler != null) {

			$this->traveler->delete($this->mysqli);
			$this->traveler = null;
		}
			if($this->profile !== null) {

				$this->profile->delete($this->mysqli);
				$this->profile = null;
			}
				if($this->user !== null) {

					$this->user->delete($this->mysqli);
					$this->user = null;
				}



		/* disconnect from mySQL
		if($this->mysqli !== null) {
			$this->mysqli->close();
		}*/
	}

	// test creating a new Traveler and inserting it to mySQL
	public function testInsertNewTraveler() {
		// first, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		try {
			// second, create a traveler to post to mySQL
			$this->traveler = new Traveler (null, $this->profile->__get("profileId"), $this->TRAVELERFIRSTNAME,
				$this->TRAVELERMIDDLENAME,$this->TRAVELERLASTNAME, $this->TRAVELERDATEOFBIRTH, $this->profile);
			// third, insert the traveler to mySQL
			$this->traveler->insert($this->mysqli);

		} catch (Exception $exception){
			$exception->getMessage();
		}
				// compare the fields
		$this->assertNotNull($this->traveler->__get("travelerId"));
		$this->assertTrue($this->traveler->__get("travelerId") > 0);
		$this->assertNotNull($this->traveler->__get("profileId"));
		$this->assertTrue($this->traveler->__get("profileId") > 0);
		$this->assertIdentical($this->traveler->__get("travelerFirstName"),   $this->TRAVELERFIRSTNAME);
		$this->assertIdentical($this->traveler->__get("travelerMiddleName"),  $this->TRAVELERMIDDLENAME);
		$this->assertIdentical($this->traveler->__get("travelerLastName"),    $this->TRAVELERLASTNAME);
		$this->assertIdentical($this->traveler->__get("travelerDateOfBirth"),     $this->TRAVELERDATEOFBIRTH);

		//-----------------------------------------------------------------------------------------------------------
		// compare the traveler object data against the data in the database
		//pull the data from the update to compare against it
		$row = $this->selectRow();
		$dateString = $row['travelerDateOfBirth'];
		$date = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);

		// finally, compare the fields against the row data pulled from the database
		$this->assertIdentical($this->traveler->__get('travelerId'),						   $row['travelerId']);
		$this->assertIdentical($this->traveler->__get('profileId'),							   $row['profileId']);
		$this->assertIdentical($this->traveler->__get("travelerFirstName"),              $row['travelerFirstName']);
		$this->assertIdentical($this->traveler->__get("travelerMiddleName"),             $row['travelerMiddleName']);
		$this->assertIdentical($this->traveler->__get("travelerLastName"),               $row['travelerLastName']);
		$this->assertIdentical($this->traveler->__get("travelerDateOfBirth"),           	$date);

	}

	// test updating a traveler in mySQL
	public function testUpdateTraveler()
	{
		// first, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);
		try{
		// second, create a traveler to post to mySQL
		$this->traveler = new Traveler(null, $this->profile->__get("profileId"), $this->TRAVELERFIRSTNAME,
								$this->TRAVELERMIDDLENAME, $this->TRAVELERLASTNAME, $this->TRAVELERDATEOFBIRTH/*Obj*/,
								$this->profile);
		// third, insert the Traveler to mySQL
		$this->traveler->insert($this->mysqli);

		}catch(Exception $ex){
			$ex->getMessage();
		}
		// fourth, update the traveler and post the changes to mySQL
		$newFirstName = "daniel";
		$newMiddleName = "lisco";
		$newLastName = "belahassi";
		$newDateOfBirth = DateTime::createFromFormat("Y-m-d H:i:s","2011-01-02 03:04:05");

		$this->traveler->setFirstName($newFirstName);
		$this->traveler->setMiddleName($newMiddleName);
		$this->traveler->setLastName($newLastName);
		$this->traveler->setDateOfBirth($newDateOfBirth);

		$this->traveler->update($this->mysqli);

		//compare testClass values against traveler object values
		$this->assertNotNull($this->traveler->__get("travelerId"));
		$this->assertTrue($this->traveler->__get("travelerId") > 0);
		$this->assertNotNull($this->traveler->__get("profileId"));
		$this->assertTrue($this->traveler->__get("profileId") > 0);

		$this->assertIdentical($this->traveler->__get("travelerFirstName"),   $newFirstName);
		$this->assertIdentical($this->traveler->__get("travelerMiddleName"),  $newMiddleName);
		$this->assertIdentical($this->traveler->__get("travelerLastName"),    $newLastName);
		$this->assertIdentical($this->traveler->__get("travelerDateOfBirth"),     $newDateOfBirth);

		//---------------------------------------------------------------------------------------------------------
		$row = $this->selectRow();
		$dateString = $row['travelerDateOfBirth'];
		$date = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);

		// finally, compare the fields against the row data pulled from the database
		$this->assertIdentical($this->traveler->__get("travelerId"),						  $row['travelerId']);
		$this->assertIdentical($this->traveler->__get("profileId"),							  $row['profileId']);
		$this->assertIdentical($this->traveler->__get("travelerFirstName"),               $row['travelerFirstName']);
		$this->assertIdentical($this->traveler->__get("travelerMiddleName"),              $row['travelerMiddleName']);
		$this->assertIdentical($this->traveler->__get("travelerLastName"),                $row['travelerLastName']);
		$this->assertIdentical($this->traveler->__get("travelerDateOfBirth"),             $date);

	}

	// test deleting a User
	public function testDeleteTraveler() {
		// first, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);

		// second, create a user to post to mySQL
		$this->traveler = new Traveler (null, $this->profile->__get("profileId"), $this->TRAVELERFIRSTNAME,
			$this->TRAVELERMIDDLENAME, $this->TRAVELERLASTNAME, $this->TRAVELERDATEOFBIRTH, $this->profile);

		// third, insert the user to mySQL
		$this->traveler->insert($this->mysqli);

		$travelerId = $this->traveler->__get("travelerId");
		// fourth, verify the User was inserted
		$this->assertNotNull($this->traveler->__get("travelerId"));
		$this->assertTrue($this->traveler->__get("travelerId") > 0);

		// fifth, delete the user
		$this->traveler->delete($this->mysqli);
		$this->traveler = null;

		// finally, try to get the user and assert we didn't get a thing
		$hopefulTraveler = Traveler::getTravelerByTravelerId($this->mysqli, $travelerId);
		$this->assertNull($hopefulTraveler);
	}

	// test grabbing a User from mySQL
	public function testGetTravelerByProfileId() {
		// first, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);

		// second, create a user to post to mySQL
		$this->traveler = new Traveler(null, $this->profile->__get("profileId"), $this->TRAVELERFIRSTNAME,
		$this->TRAVELERMIDDLENAME,$this->TRAVELERLASTNAME, $this->TRAVELERDATEOFBIRTH);

		// third, insert the user to mySQL
		$this->traveler->insert($this->mysqli);

		$this->traveler2 = new Traveler(null, $this->profile->__get("profileId"), "Ryan",
			"Pace","Sloan", "2014-08-10 10:10:10");
		$this->traveler2->insert($this->mysqli);

		$this->traveler3 = new Traveler(null, $this->profile->__get("profileId"), "Joseph",
			"J","Bottone", "2009-10-11 12:11:10");
		$this->traveler3->insert($this->mysqli);

		$this->traveler4 = new Traveler(null, $this->profile->__get("profileId"), "Christian",
			"","Slater", "1997-10-11 12:11:10");
		$this->traveler4->insert($this->mysqli);

		// fourth, get the user using the static method
		$staticTraveler = Traveler::getTravelerByProfileId($this->mysqli, $this->profile->__get("profileId"));
		echo "<p>staticTraveler line 228 simpletestTraveler.php</p>";
		var_dump($staticTraveler);
		// finally, compare the fields
		$this->assertNotNull($staticTraveler[0]->__get('travelerId'));
		$this->assertTrue($staticTraveler[0]->__get('travelerId') > 0);
		$this->assertNotNull($staticTraveler[0]->__get('profileId'));
		$this->assertTrue($staticTraveler[0]->__get('profileId') > 0);
		$this->assertIdentical($staticTraveler[0]->__get('travelerFirstName'),   $this->TRAVELERFIRSTNAME);
		$this->assertIdentical($staticTraveler[0]->__get('travelerMiddleName'),  $this->TRAVELERMIDDLENAME);
		$this->assertIdentical($staticTraveler[0]->__get('travelerLastName'),    $this->TRAVELERLASTNAME);
		$this->assertIdentical($staticTraveler[0]->__get('travelerDateOfBirth'), $this->TRAVELERDATEOFBIRTH);

		$this->assertNotNull($staticTraveler[1]->__get('travelerId'));
		$this->assertTrue($staticTraveler[1]->__get('travelerId') > 0);
		$this->assertNotNull($staticTraveler[1]->__get('profileId'));
		$this->assertTrue($staticTraveler[1]->__get('profileId') > 0);
		$this->assertIdentical($staticTraveler[1]->__get('travelerFirstName'),   "ryan");
		$this->assertIdentical($staticTraveler[1]->__get('travelerMiddleName'),  "pace");
		$this->assertIdentical($staticTraveler[1]->__get('travelerLastName'),    "sloan");
		$this->assertIdentical($staticTraveler[1]->__get('travelerDateOfBirth'),
			DateTime::createFromFormat("Y-m-d H:i:s","2014-08-10 10:10:10"));

		$this->assertNotNull($staticTraveler[2]->__get('travelerId'));
		$this->assertTrue($staticTraveler[2]->__get('travelerId') > 0);
		$this->assertNotNull($staticTraveler[2]->__get('profileId'));
		$this->assertTrue($staticTraveler[2]->__get('profileId') > 0);
		$this->assertIdentical($staticTraveler[2]->__get('travelerFirstName'),   "joseph");
		$this->assertIdentical($staticTraveler[2]->__get('travelerMiddleName'),  "j");
		$this->assertIdentical($staticTraveler[2]->__get('travelerLastName'),    "bottone");
		$this->assertIdentical($staticTraveler[2]->__get('travelerDateOfBirth'),
			DateTime::createFromFormat("Y-m-d H:i:s", "2009-10-11 12:11:10"));

		$this->assertNotNull($staticTraveler[3]->__get('travelerId'));
		$this->assertTrue($staticTraveler[3]->__get('travelerId') > 0);
		$this->assertNotNull($staticTraveler[3]->__get('profileId'));
		$this->assertTrue($staticTraveler[3]->__get('profileId') > 0);
		$this->assertIdentical($staticTraveler[3]->__get('travelerFirstName'),   "christian");
		$this->assertIdentical($staticTraveler[3]->__get('travelerMiddleName'),  "");
		$this->assertIdentical($staticTraveler[3]->__get('travelerLastName'),    "slater");
		$this->assertIdentical($staticTraveler[3]->__get('travelerDateOfBirth'),
			DateTime::createFromFormat("Y-m-d H:i:s", "1997-10-11 12:11:10"));

		//-----------------------------------------------------------------------------------------
		$row = $this->selectRow();

		$dateString = $row['travelerDateOfBirth'];
		$date = DateTime::createFromFormat("Y-m-d H:i:s", $dateString);
		// finally, compare the fields against the row data pulled from the database
		$this->assertIdentical($this->traveler->__get('travelerId'),			 $row['travelerId']);
		$this->assertIdentical($this->traveler->__get('profileId'),				 $row['profileId']);
		$this->assertIdentical($this->traveler->__get("travelerFirstName"),   $row['travelerFirstName']);
		$this->assertIdentical($this->traveler->__get("travelerMiddleName"),  $row['travelerMiddleName']);
		$this->assertIdentical($this->traveler->__get("travelerLastName"),    $row['travelerLastName']);
		$this->assertIdentical($this->traveler->__get("travelerDateOfBirth"), $date);

		$this->traveler4->delete($this->mysqli);
		$this->traveler3->delete($this->mysqli);
		$this->traveler2->delete($this->mysqli);
	}

	private function selectRow(){

		//pull the data from the update to compare against it
		try {
			$query = "SELECT travelerId, profileId, travelerFirstName, travelerMiddleName, travelerLastName, travelerDateOfBirth
					 FROM traveler WHERE travelerId = ?";
			$statement = $this->mysqli->prepare($query);
			$statement->bind_param("i", $this->traveler->__get("travelerId"));
			$statement->execute();
			$result = $statement->get_result();
			$row = $result->fetch_assoc();
		}catch(mysqli_sql_exception $sqlException){
			$sqlException->getMessage();
		}
		return $row;
	}

}





?>