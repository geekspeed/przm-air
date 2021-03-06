<?php
 /**
  * test to verify the Transaction
  *
  * @author Paul Morbitzer <pmorbitz@gamil.com>
  */


// first require the SimpleTest framework
require_once("/usr/lib/php5/simpletest/autorun.php");

//then require the class under scrutiny
require_once("../php/transaction.php");

// require the mysqli
require_once("/var/www/html/przm.php");

// require the classes for foreign key
require_once("../php/user.php");
require_once("../php/profile.php");


// the TransactionTest is a container for all our tests
class TransactionTest extends UnitTestCase {
	// variable to hold the mySQL connection
	private $mysqli = null;
	// variable to hold the test database row
	private $transaction = null;

	// a few "global" variables for creating test data
	private $AMOUNT 		  = 100.00;
	private $DATE_APPROVED = null;
	private $CARD_TOKEN 	  = "card_14oo18o9fh39";
	private $STRIPE_TOKEN  = "transaction_1532o45ipo4";
	private $USER 			  = null;
	private $PROFILE 		  = null;

	// setUp () is a method that is run before each test
	// here, we use it to connect to my SQL
	public function setUp()
	{
		//not $mysqli:  $this->mysqli   you want to set the object into the class
		$this->mysqli = MysqliConfiguration::getMysqli();

		$salt = bin2hex(openssl_random_pseudo_bytes(32));
		$authenticationToken = bin2hex(openssl_random_pseudo_bytes(16));
		$hash = hash_pbkdf2("sha512", "password", $salt, 2048, 128);
		$i = rand(1,1000);//I added this to ensure the uniqueness of user emails to prevent collisions
		$this->USER = new User(null, "a".$i."@b.net", $hash, $salt, $authenticationToken);
		$this->USER->insert($this->mysqli);
								//not $mysqli: $this->mysqli you want to set the object into the class


		$this->PROFILE = new Profile(null, $this->USER->getUserId(), "Homer", "J", "Simpson", "1956-03-15 12:34:56",
			"Token", $this->USER);
		$this->PROFILE->insert($this->mysqli);

		$this->DATE_APPROVED = DateTime::createFromFormat("Y-m-d H:i:s", "2014-11-11 12:00:00");
	}									//not $mysqli: $this->mysqli setting into the class means you can access it
										//creating it locally makes it inaccessible

	// tearDown () is a method that is run after each test
	// here, we use it to delete the test record and disconnect from mySQL
	public function tearDown()
	{
		// delete the transaction if we can
		if($this->transaction !== null) {
			$this->transaction->delete($this->mysqli);
			$this->transaction = null;
		}

		if($this->PROFILE !== null) {
			$this->PROFILE->delete($this->mysqli);
			$this->PROFILE = null;
		}

		if($this->USER !== null) {
			$this->USER->delete($this->mysqli);
			$this->USER = null;
		}

		// disconnect from mySQL
		// if($this->mysqli !== null) {
		// $this->mysqli->close();
		// }
	}

	// test creating a new Transaction and inserting it to mySQL
	public function testInsertNewTransaction() {
		// first, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);

		// second, create a transaction to post to mySQL
		$this->transaction = new Transaction(null, $this->PROFILE->__get("profileId"), $this->AMOUNT,
																 $this->DATE_APPROVED, 					 $this->CARD_TOKEN,
																 $this->STRIPE_TOKEN);

		//third, insert the transaction to mySQL
		$this->transaction->insert($this->mysqli);


		// finally, compare the fields
		$this->assertNotNull($this->transaction->getTransactionId());
		$this->assertTrue($this->transaction->getTransactionId() > 0);
		$this->assertIdentical($this->transaction->getProfileId(),    $this->PROFILE->__get("profileId"));
		$this->assertIdentical($this->transaction->getAmount(),  	  $this->AMOUNT);
		$this->assertIdentical($this->transaction->getDateApproved(), $this->DATE_APPROVED);
		$this->assertIdentical($this->transaction->getCardToken(), 	  $this->CARD_TOKEN);
		$this->assertIdentical($this->transaction->getStripeToken(),  $this->STRIPE_TOKEN);
	}

	// test updating a Transaction
	public function testUpdateTransaction() {
		// first, verify mySQL connected OK
		$this->assertNotNull($this->mysqli);

		// second, create a transaction to post to mySQL
		$this->transaction = new Transaction(null, $this->PROFILE->__get("profileId"), $this->AMOUNT,
																 $this->DATE_APPROVED, 					 $this->CARD_TOKEN,
																 $this->STRIPE_TOKEN);

		//third, insert the profile to mySQL
		$this->transaction->insert($this->mysqli);

		// fourth, update the transaction and post the changes to mySQL
		$newAmount = 200.00;
		$this->transaction->setAmount($newAmount);
		$this->transaction->update($this->mysqli);

		// finally, compare the fields
		$this->assertNotNull($this->transaction->getTransactionId());
		$this->assertTrue($this->transaction->getTransactionId() > 0);
		$this->assertIdentical($this->transaction->getProfileId(), 	  $this->PROFILE->__get("profileId"));
		$this->assertIdentical($this->transaction->getAmount(),  	  $newAmount);
		$this->assertIdentical($this->transaction->getDateApproved(), $this->DATE_APPROVED);
		$this->assertIdentical($this->transaction->getCardToken(), 	  $this->CARD_TOKEN);
		$this->assertIdentical($this->transaction->getStripeToken(),  $this->STRIPE_TOKEN);
	}

	// test deleting a Transaction
	public function testDeleteTransaction() {
		// first, verify my SQL connected OK
		$this->assertNotNull($this->mysqli);

		// second, create a transaction to post to mySQL
		$this->transaction = new Transaction(null, $this->PROFILE->__get("profileId"), $this->AMOUNT,
																 $this->DATE_APPROVED, 					 $this->CARD_TOKEN,
																 $this->STRIPE_TOKEN);

		//third, insert the profile to mySQL
		$this->transaction->insert($this->mysqli);

		// fourth, verify the Transaction was inserted
		$this->assertNotNull($this->transaction->getTransactionId());
		$this->assertTrue($this->transaction->getTransactionId() > 0);
		$transactionId = $this->transaction->getTransactionId();
		// fifth, delete the ticket
		$this->transaction->delete($this->mysqli);
		$this->transaction = null;

		// finally, try to get the transaction and assert we didn't get a thing
		$hopefulTransaction = Transaction::getTransactionByTransactionId($this->mysqli, $transactionId);
		$this->assertNull($hopefulTransaction);


	}

	// test grabbing a Transaction from mySQL
	public function testGetTransactionByTransactionId() {
		// first verify mySQL connected Ok
		$this->assertNotNull($this->mysqli);

		// second create a new transaction to post to mySQL
		$this->transaction = new Transaction(null, $this->PROFILE->__get("profileId"), $this->AMOUNT, $this->DATE_APPROVED, $this->CARD_TOKEN, $this->STRIPE_TOKEN);

		// third, insert the transaction to mySQL
		$this->transaction->insert($this->mysqli);

		// fourth, get the transaction using the static method
		$staticTransaction = Transaction::getTransactionByTransactionId($this->mysqli, $this->transaction->getTransactionId()					);

		// finally, compare the fields
		$this->assertNotNull($staticTransaction->getTransactionId());
		$this->assertTrue($staticTransaction->getTransactionId() > 0);
		$this->assertIdentical($staticTransaction->getTransactionId(), $this->transaction->getTransactionId());
		$this->assertIdentical($staticTransaction->getProfileId(), 		$this->PROFILE->__get("profileId"));
		$this->assertIdentical($staticTransaction->getAmount(), 			$this->AMOUNT);
		$this->assertIdentical($staticTransaction->getDateApproved(), 	$this->DATE_APPROVED);
		$this->assertIdentical($staticTransaction->getCardToken(), 		$this->CARD_TOKEN);
		$this->assertIdentical($staticTransaction->getStripeToken(), 	$this->STRIPE_TOKEN);
	}

}