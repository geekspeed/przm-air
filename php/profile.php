<?php

	/**
	 * mySQL enabled profile container for use with user authentication class
	 * @author Ryan Sloan <ryansdeal@hotmail.com>
	 * @see User
	 */
	class Profile {
		/**
		 * property for the profiles accompanying user Object NOTE: object will not interact with the database
		 */
		private $userObj;
		/**
		 * int profileId is the primary key - a profile identifier
		 */
		private $profileId;
		/**
		 * int userId is a foreign key from the user table
		 */
		private $userId;
		/**
		 * string userFirstName holds the users first name
		 */
		private $userFirstName;
		/**
		 * string userMiddleName holds the users middle name
		 */
		private $userMiddleName;
		/**
		 * string userLastName holds the users last name
		 */
		private $userLastName;
		/**
		 * date dateOfBirth holds the user's DOB as a date
		 */
		private $dateOfBirth;
		/*
		 * string customerToken - interface with Stripe Payment Platform - identifies the customer
		 */
		private $customerToken;
		/**
		 * constructor for Profile
		 *
		 * @param int $newProfileId (or null if new object)
		 * @param int $newUserId
		 * @param string $newFirstName
		 * @param string $newMiddleName
		 * @param string $newLastName
		 * @param string $newDateOfBirth
		 * @param string $newCustomerToken
		 * @throws UnexpectedValueException when a parameter is of the wrong type
		 * @throws RangeException when a parameter is invalid
		 **/
		public function __construct($newProfileId, $newUserId, $newFirstName, $newMiddleName,
											 $newLastName, $newDateOfBirth, $newCustomerToken, $newUserObj = null) {
			try {
				$this->setProfileId($newProfileId);
				$this->setUserId($newUserId);
				$this->setFirstName($newFirstName);
				$this->setMiddleName($newMiddleName);
				$this->setLastName($newLastName);
				$this->setDateOfBirth($newDateOfBirth);
				$this->setCustomerToken($newCustomerToken);
				$this->setUserObject($newUserObj);
			} catch(UnexpectedValueException $unexpectedValue) {
				// rethrow to the caller
				throw(new UnexpectedValueException("Unable to construct Profile Object. Check input formats.", 0,
					$unexpectedValue));
			} catch(RangeException $range) {
				// rethrow to the caller
				throw(new RangeException("Unable to construct Profile Object. Check input formats.", 0, $range));
			}
		}

		/**
		 * get function for all class properties and objects
		 * @param $name - a string containing the property name as a string
		 * @throws ErrorException when cannot match $name to array key
		 */
		public function __get($name){
			       $data = array("userObj" => $this->userObj,
						 				"profileId" => $this->profileId,
			 	                  "userId" => $this->userId,
										"userFirstName" => $this->userFirstName,
				 						"userMiddleName" => $this->userMiddleName,
			 						  	"userLastName" => $this->userLastName,
				 						"dateOfBirth" => $this->dateOfBirth,
				 						"customerToken" =>$this->customerToken);
			if (array_key_exists($name, $data)) {
				return $data[$name];
			}
			else {
				throw(new ErrorException("Unable to get $name. Check that the key exists."));
			}
		}

		/**
		 * sets the userObj property with the profiles corresponding userObj
		 * @param User object $newUserObj
		 */
		function setUserObject($newUserObj){
			// handle degenerate cases
			if(gettype($newUserObj) !== "object" || get_class($newUserObj) !== "User") {
				throw(new UnexpectedValueException("input is not a User object"));
			}
			//test if objectId is null
			if($newUserObj->userId === null) {
				throw(new UnexpectedValueException("Unable to set a user that is null"));
			}
			//set new object into class
			$this->userObj = $newUserObj;

		}
		/**
		 * Test input to determine if is correct type format and within range
		 * if passes is set into the class property $profileId
		 * @param $newProfileId should be a + integer not -
		 */
		public function setProfileId($newProfileId){
			// zeroth, set allow the profile id to be null if a new object
			if($newProfileId === null) {
				$this->profileId = null;
				return;
			}

			// first, ensure the profile id is an integer
			if(filter_var($newProfileId, FILTER_VALIDATE_INT) === false) {
				throw(new UnexpectedValueException("profile id $newProfileId is not numeric"));
			}

			// second, convert the profile id to an integer and enforce it's positive
			$newProfileId = intval($newProfileId);
			if($newProfileId <= 0) {
				throw(new RangeException("profile id $newProfileId is not positive"));
			}

			// finally, take the profile id out of quarantine and assign it
			$this->profileId = $newProfileId;
		}

		/**
		 * tests input to determine if it is within range, the correct type and the correct format
		 * if input passes inspection it is set into the class property $userId
		 * @param $newUserId
		 */
		public function setUserId($newUserId){
			// zeroth, set allow the user id to be null if a new object
			if($newUserId === null) {
				$this->userId = null;
				return;
			}

			// first, ensure the user id is an integer
			if(filter_var($newUserId, FILTER_VALIDATE_INT) === false) {
				throw(new UnexpectedValueException("user id $newUserId is not numeric"));
			}

			// second, convert the user id to an integer and enforce it's positive
			$newUserId = intval($newUserId);
			if($newUserId <= 0) {
				throw(new RangeException("user id $newUserId is not positive"));
			}

			// finally, take the user id out of quarantine and assign it
			$this->userId = $newUserId;
		}

		/**
		 * trims and validates input to determine if it is a string and that the string meets
		 * specific parameters [a-zA-Z] using REGEX if it clears it is set into the class property $userFirstName
		 * @param $newFirstName
		 */
		public function setFirstName($newFirstName){
			//trims whitespace
			$newFirstName = trim($newFirstName);
			//sets all letter cases to lower
			$newFirstName = strtolower($newFirstName);
			//sets up the options for filter_var validation
			$filterOptions = array("options" => array("regexp" => "/^[a-zA-Z]+$/"));
			//validates $newFirstName with REGEX
			if(filter_var($newFirstName, FILTER_VALIDATE_REGEXP, $filterOptions)){
				throw(new RangeException("First name cannot contain spaces numbers or special characters."));
			}
			//sets $newFirstName into class property userFirstName
			$this->userFirstName = $newFirstName;
		}

		/**
		 * trims and validates input to determine if it is a string and matches the REGEX format of [a-zA-Z] only
		 * @param $newMiddleName
		 */
		public function setMiddleName($newMiddleName){
			//trims whitespace
			$newMiddleName = trim($newMiddleName);
			//sets all letter cases to lower
			$newMiddleName = strtolower($newMiddleName);
			//sets up the options for filter_var validation
			$filterOptions = array("options" => array("regexp" => "/^[a-zA-Z]+$/"));
			//validates $newMiddleName with REGEX
			if(filter_var($newMiddleName, FILTER_VALIDATE_REGEXP, $filterOptions)){
				throw(new RangeException("Middle name cannot contain spaces numbers or special characters."));
			}
			//sets $newMiddleName into class property userFirstName
			$this->userMiddleName = $newMiddleName;
		}
		/**
		 * trims and validates input to determine if it is a string and matches the REGEX format of [a-zA-Z] only
		 * @param $newLastName
		 */
		public function setLastName($newLastName){
			//trims whitespace
			$newLastName = trim($newLastName);
			//sets all letter cases to lower
			$newLastName = strtolower($newLastName);
			//sets up the options for filter_var validation
			$filterOptions = array("options" => array("regexp" => "/^[a-zA-Z]+$/"));
			//validates $newLastName with REGEX
			if(filter_var($newLastName, FILTER_VALIDATE_REGEXP, $filterOptions)){
				throw(new RangeException("Middle name cannot contain spaces numbers or special characters."));
			}
			//sets $newLastName into class property userFirstName
			$this->userLastName = $newLastName;
		}

		/**
		 * string in format mm/dd/yyyy comes in is validated and translated to Y-m-d
		 * @param $newDateOfBirth
		 *
		 */
		public function setDateOfBirth($newDateOfBirth){
			$newDateOfBirth = trim($newDateOfBirth);
			$filterOptions = array("options" => array("regexp" => "/^(0[1-9]|1[0-2])\/([0-2][1-9]|3[0-1])\/(19|20)\d{2}$/"));
			if(filter_var($newDateOfBirth, FILTER_VALIDATE_REGEXP, $filterOptions)){
				throw(new RangeException("Date entered doesn't match mm/dd/yyyy format or is out of range"));
			}

			$dateArray = array('month' => strtok($newDateOfBirth , "\\"), 'day' => strtok("\\"), 'year' => strtok("\\"));
			$reformattedDate = $dateArray['year']."\\".$dateArray['month']."\\".$dateArray['day'];
			$format = 'Y-m-d';
			$this->dateOfBirth = DateTime::createFromFormat($format, $reformattedDate);
		}

		public function setCustomerToken($newCustomerToken){
			// Create a Customer
			$customer = Stripe_Customer::create(
					array(
						"card" => $newCustomerToken,
						"userId" => $this->userId,
						"profileId" =>$this->profileId,
						"userLegalName" =>$this->userFirstName." ".$this->userMiddleName." ".$this->userLastName,
						"userEmail" =>$this->$userObj->email
					)
			);

			// Save the customer ID in your database so you can use it later
			$this->customerToken = $customer;
		}

		/**
		 * mySQL enabled class which inserts current class property values into the database
		 * with the exculstion of userObj
		 * @param $mysqli
		 * @throws my_mysqli_exception when profileId is not null or mysqli is not a mysqli object
		 */
		public function insert(&$mysqli) {
			// handle degenerate cases
			if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
				throw(new mysqli_sql_exception("input is not a mysqli object"));
			}

			// enforce the profileId is null (i.e., don't insert a profile that already exists)
			if($this->profileId !== null) {
				throw(new mysqli_sql_exception("not a new profile"));
			}

			// create query template
			$query     = "INSERT INTO profile (profileId, userId, userFirstName, userMiddleName, userLastName, dateOfBirth,
 								customerToken)VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
			$statement = $mysqli->prepare($query);

			if($statement === false) {
				throw(new mysqli_sql_exception("Unable to prepare statement"));
			}

			// bind the member variables to the place holders in the template
			$wasClean = $statement->bind_param("iisssss", $this->profileId, $this->userId, $this->userFirstName,
				$this->userMiddleName, $this->userLastName, $this->dateOfBirth, $this->customerToken);
			if($wasClean === false) {
				throw(new mysqli_sql_exception("Unable to bind parameters"));
			}

			// execute the statement
			if($statement->execute() === false) {
				throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
			}

			// update the null profileId with what mySQL just gave us
			$this->profileId = $mysqli->insert_id;
		}

		/**
		 * deletes this Profile from mySQL
		 *
		 * @param resource $mysqli pointer to mySQL connection, by reference
		 * @throws mysqli_sql_exception when mySQL related errors occur
		 **/
		public function delete(&$mysqli) {
			// handle degenerate cases
			if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
				throw(new mysqli_sql_exception("input is not a mysqli object"));
			}

			// enforce the profileId is not null (i.e., don't delete a profile that hasn't been inserted)
			if($this->profileId === null) {
				throw(new mysqli_sql_exception("Unable to delete a profile that does not exist"));
			}

			// create query template
			$query     = "DELETE FROM profile WHERE profileId = ?";
			$statement = $mysqli->prepare($query);
			if($statement === false) {
				throw(new mysqli_sql_exception("Unable to prepare statement"));
			}

			// bind the member variables to the place holder in the template
			$wasClean = $statement->bind_param("i", $this->profileId);
			if($wasClean === false) {
				throw(new mysqli_sql_exception("Unable to bind parameters"));
			}

			// execute the statement
			if($statement->execute() === false) {
				throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
			}
		}

		/**
		 * updates this Profile in mySQL
		 *
		 * @param resource $mysqli pointer to mySQL connection, by reference
		 * @throws mysqli_sql_exception when mySQL related errors occur
		 **/
		public function update(&$mysqli) {
			// handle degenerate cases
			if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
				throw(new mysqli_sql_exception("input is not a mysqli object"));
			}

			// enforce the profileId is not null (i.e., don't update a profile that hasn't been inserted)
			if($this->profileId === null) {
				throw(new mysqli_sql_exception("Unable to update a profile that does not exist"));
			}

			// create query template
			$query     = "UPDATE profile SET userFirstName = ?, userMiddleName = ?, userLastName = ?, dateOfBirth = ?,
				             customerToken = ? WHERE profileId = ?";
			$statement = $mysqli->prepare($query);
			if($statement === false) {
				throw(new mysqli_sql_exception("Unable to prepare statement"));
			}

			// bind the member variables to the place holders in the template
			$wasClean = $statement->bind_param("sssssi", $this->userFirstName, $this->userMiddleName,
				$this->userLastName, $this->dateOfBirth, $this->customerToken,$this->profileId);
			if($wasClean === false) {
				throw(new mysqli_sql_exception("Unable to bind parameters"));
			}

			// execute the statement
			if($statement->execute() === false) {
				throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
			}
		}

		/**
		 * gets the Profile by profileId the Primary Key
		 *
		 * @param resource $mysqli pointer to mySQL connection, by reference
		 * @param int $profileId primary key to search for
		 * @return mixed Profile found or null if not found
		 * @throws mysqli_sql_exception when mySQL related errors occur
		 **/
		public static function getProfileByProfileId(&$mysqli, $profileId)
		{
			//handle degenerate cases
			if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
				throw(new mysqli_sql_exception("Input is not a mysqli object"));
			}

			// first, ensure the profileId is an integer
			if(filter_var($profileId, FILTER_VALIDATE_INT) === false) {
				throw(new UnexpectedValueException("profile id $profileId is not numeric"));
			}

			// second, convert the user id to an integer and enforce it's positive
			$profileId = intval($profileId);
			if($profileId <= 0) {
				throw(new RangeException("profile id $profileId is not positive"));
			}
			//CREATE QUERY TEMPLATE
			$query = "SELECT profileId, userId, userFirstName, userMiddleName, userLastName, dateOfBirth,
					customerToken
					FROM profile WHERE profileId = ? ";
			$statement = $mysqli->prepare($query);
			if($statement === false) {
				throw(new mysqli_sql_exception("Unable to prepare statement"));
			}

			//bind the profileId to the place holder in the template
			$wasClean = $statement->bind_param("i", $profileId);
			if($wasClean === false) {
				throw(new mysqli_sql_exception("Unable to bind parameters"));
			}

			//execute statement
			if($statement->execute() === false) {
				throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
			}

			//get result from the SELECT query
			$result = $statement->get_result();
			if($result === false) {
				throw(new mysqli_sql_exception("Unable to get result set"));
			}

			/* since this is a unique field, this will only return 0 or 1 results so
			 * 1) if there's a result, we can make it into a channel object normally
			 * 2) if there's no result, we can just return null
			 * */
			$row = $result->fetch_assoc(); //fetch_assoc() returns a row as an associative array

			//convert the associate array to user
			if($row !== null) {
				try {
					$profile = new Profile ($row['profileId'], $row['userId'],$row['userFirstName'],
						$row['userMiddleName'],$row['userLastName'], $row['dateOfBirth'], $row['customerToken']);
				} catch(Exception $exception) {
					//if row can't be converted rethrow
					throw(new mysqli_sql_exception("Unable to convert row to Profile Object", 0, $exception));
				}

				//if we got here, the Profile Object is good - return it
				return ($profile);
			} else {
				//404 profile not found
				return (null);
			}
		}

		/**
		 * gets the Profile by userId the Foreign Key
		 *
		 * @param resource $mysqli pointer to mySQL connection, by reference
		 * @param int $userId primary key to search for
		 * @return mixed Profile found or null if not found
		 * @throws mysqli_sql_exception when mySQL related errors occur
		 **/
		public static function getProfileByUserId(&$mysqli, $userId)
		{
			//handle degenerate cases
			if(gettype($mysqli) !== "object" || get_class($mysqli) !== "mysqli") {
				throw(new mysqli_sql_exception("Input is not a mysqli object"));
			}

			// first, ensure the profileId is an integer
			if(filter_var($userId, FILTER_VALIDATE_INT) === false) {
				throw(new UnexpectedValueException("user id $userId is not numeric"));
			}

			// second, convert the user id to an integer and enforce it's positive
			$userId = intval($userId);
			if($userId <= 0) {
				throw(new RangeException("user id $userId is not positive"));
			}
			//CREATE QUERY TEMPLATE
			$query = "SELECT profileId, userId, userFirstName, userMiddleName, userLastName, dateOfBirth,
					customerToken
					FROM profile WHERE userId = ? ";
			$statement = $mysqli->prepare($query);
			if($statement === false) {
				throw(new mysqli_sql_exception("Unable to prepare statement"));
			}

			//bind the profileId to the place holder in the template
			$wasClean = $statement->bind_param("i", $userId);
			if($wasClean === false) {
				throw(new mysqli_sql_exception("Unable to bind parameters"));
			}

			//execute statement
			if($statement->execute() === false) {
				throw(new mysqli_sql_exception("Unable to execute mySQL statement"));
			}

			//get result from the SELECT query
			$result = $statement->get_result();
			if($result === false) {
				throw(new mysqli_sql_exception("Unable to get result set"));
			}

			/* since this is a unique field, this will only return 0 or 1 results so
			 * 1) if there's a result, we can make it into a channel object normally
			 * 2) if there's no result, we can just return null
			 * */
			$row = $result->fetch_assoc(); //fetch_assoc() returns a row as an associative array

			//convert the associate array to user
			if($row !== null) {
				try {
					$profile = new Profile ($row['profileId'], $row['userId'],$row['userFirstName'],
						$row['userMiddleName'],$row['userLastName'], $row['dateOfBirth'], $row['customerToken']);
				} catch(Exception $exception) {
					//if row can't be converted rethrow
					throw(new mysqli_sql_exception("Unable to convert row to Profile Object", 0, $exception));
				}

				//if we got here, the Profile Object is good - return it
				return ($profile);
			} else {
				//404 profile not found
				return (null);
			}
		}

	}
?>