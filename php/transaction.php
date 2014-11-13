<?php
/**
 * mySQL Enabled Transaction
 *
 * This is a mySQL enabled container for Transaction processing at an airline site selling tickets. It can easily be extended to include more fields as necessary.
 *
 * @author Paul Morbitzer <pmorbitz@gmail.com>
 **/

class Transaction {
	/**
	 * transaction id for the Transaction; this is the primary key
	 **/
	private $transactionId;
	/**
	 * profile id; this is a foreign key
	 **/
	private $profileId;
	/**
	 * amount of the transaction
	 **/
	private $amount;
	/**
	 * date the transaction was approved
	 **/
	private $dateApproved;
	/*
	 * card token for the transaction from API stripe.com
	 */
	private $cardToken;
	/*
	 * stripe token for the transaction from stripe.com
	 */
	private $stripeToken;

	/**
	 * constructor for Transaction
	 *
	 * @param mixed $newTransactionId transaction id (or null if new object)
	 * @param mixed $newProfileId profile id
	 * @param float $newAmount amount
	 * @param string $newDateApproved date approved
	 * @param string $newCardToken card token
	 * @param string $newStripeToken stripe token
	 * @throws UnexpectedValueException when a parameter is of the wrong type
	 * @throws RangeException when a parameter is invalid
	 **/
	public function __construct($newTransactionId, $newProfileId, $newAmount, $newDateApproved, $newCardToken, $newStripeToken) {
		try {
			$this->setTransactionId($newTransactionId);
			$this->setProfileId($newProfileId);
			$this->setAmount($newAmount);
			$this->setDateApproved($newDateApproved);
			$this->setCardToken($newCardToken);
			$this->setStripeToken($newStripeToken);
		} catch(UnexpectedValueException $unexpectedValue) {
			// rethrow to the caller
			throw(new UnexpectedValueException("Unable to construct Transaction", 0, $unexpectedValue));
		} catch(RangeException $range) {
			// rethrow to the caller
			throw(new RangeException("Unable to construct Transaction", 0, $range));
		}
	}

	/**
	 * gets the value of transaction id
	 *
	 * @return mixed transaction id (or null if new object)
	 **/
	public function getTransactionId() {
		return($this->transactionId);
	}

	/**
	 * sets the value of trasaction id
	 *
	 * @param mixed $newTransactionId tranaction id (or null if new object)
	 * @throws UnexpectedValueException if not an integer or null
	 * @throws RangeException if transaction id isn't positive
	 **/
	public function setTransactionId($newTransactionId) {
		// zeroth, set allow the transaction id to be null if a new object
		if($newTransactionId === null) {
			$this->transactionId = null;
			return;
		}

		// first, ensure the transactoin id is an integer
		if(filter_var($newTransactionId, FILTER_VALIDATE_INT) === false) {
			throw(new UnexpectedValueException("transaction id $newTransactionId is not numeric"));
		}

		// second, convert the transaction id to an integer and enforce it's positive
		$newTransactionId = intval($newTransactionId);
		if($newTransactionId <= 0) {
			throw(new RangeException("transaction id $newTransactionId is not positive"));
		}

		// finally, take the transaction id out of quarantine and assign it
		$this->transactionId = $newTransactionId;
	}

	/**
	 * gets the value of profile id
	 *
	 * @return mixed profile id (or null if new object)
	 **/
	public function getProfileId() {
		return($this->profileId);
	}

	/**
	 * sets the value of profile id
	 *
	 * @param mixed $newProfileId profile id (or null if new object)
	 * @throws UnexpectedValueException if not an integer or null
	 * @throws RangeException if profile id isn't positive
	 **/
	public function setProfileId($newProfileId) {
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
	 * gets the value of amount
	 *
	 * @return float amount
	 **/
	public function getAmouont() {
		return($this->amount);
	}

	/**
	 * sets the value of amount
	 *
	 * @param float $newAmount amount
	 * @throws UnexpectedValueException if not a double
	 * @throws RangeException if amount isn't positive
	 **/
	public function setAmount($newAmount) {
		// first, ensure the amount is a double
		if(filter_var($newAmount, FILTER_VALIDATE_FLOAT) === false) {
			throw(new UnexpectedValueException("amount $newAmount is not numeric"));
		}

		// second, convert the amount to a double and enforce it's positive
		$newAmount = floatval($newAmount);
		if($newAmount <= 0) {
			throw(new RangeException("amount $newAmount is not positive"));
		}

		// finally, take the amount out of quarantine and assign it
		$this->amount = $newAmount;
	}

	/**
	 * gets the value of date approved
	 *
	 * @return string date approved
	 **/
	public function getDateApproved() {
		return($this->dateApproved);
	}

	/**
	 * sets the value of date approved
	 *
	 * @param mixed $newDateApproved object or string with the date approved
	 * @throws RangeException if date is not a valid date
	 **/
	public function setDateApproved($newDateApproved)
	{
		// zeroth, allow the date to be null if a new object
		if($newDateApproved ===  null) {
			$this->dateApproved = null;
			return;
		}

		// zeroth, allow a DateTime object to be directly assigned
		if(gettype($newDateApproved) === "object" && get_class($newDateApproved) === "DateTime") {
			$this->dateApproved = $newDateApproved;
			return;
		}

		// treat the date as a mySQL date string
		$newDateApproved = trim($newDateApproved);
		if((preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $newDateApproved, $matches)) !== 1) {
			throw(new RangeException("$newDateApproved is not a valid date"));
		}

		// verify the date is really a valid calendar date
		$year  = intval($matches[1]);
		$month = intval($matches[2]);
		$day   = intval($matches[3]);
		if(checkdate($month, $day, $year) === false) {
			throw(new RangeException("$newDateApproved is not a Gregorian date"));
		}

		// finally, take the date out of quarantine
		$newDateApproved = DateTime::createFromFormat("Y-m-d H:i:s", $newDateApproved);
		$this->dateApproved = $newDateApproved;
	}

	/**
	 * gets the value of card token
	 *
	 * @return string card token
	 **/
	public function getCardToken() {
		return($this->cardToken);
	}

	/**
	 * sets the value of card token
	 *
	 * @param string $newCardToken card token
	 **/
	public function setCardToken($newCardToken) {
		// filter the card token as a generic string
		$newCardToken = trim($newCardToken);
		$newCardToken = filter_var($newCardToken, FILTER_SANITIZE_STRING);

		// then just take the card token out of quarantine
		$this->cardToken = $newCardToken;
	}

	/**
	 * gets the value of stripe token
	 *
	 * @return string stripe token
	 **/
	public function getStripeToken() {
		return($this->stripeToken);
	}

	/**
	 * sets the value of stripe token
	 *
	 * @param string $newStripToken stripe token
	 **/
	public function setStripeToken($newStripeToken) {
		// filter the stripe token as a generic string
		$newStripeToken = trim($newStripeToken);
		$newStripeToken = filter_var($newStripeToken, FILTER_SANITIZE_STRING);

		// then just take the stripe token out of quarantine
		$this->stripeToken = $newStripeToken;
	}




}