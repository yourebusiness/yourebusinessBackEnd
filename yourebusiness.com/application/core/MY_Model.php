<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* My_model will now be the extended model not the CI_model to make error centralized. */

class My_model extends CI_Model {
	const ERRORNO_OK = 0;
	const ERRORSTR_OK = "OK";

	const ERRORNO_NOT_AUTHORIZED = 1;
	const ERRORSTR_NOT_AUTHORIZED = "User not authorized.";

	const ERRORNO_UNEXPECTED_VALUE = 2;
	const ERRORSTR_UNEXPECTED_VALUE = "Unexpected returned value.";
	
	const ERRORNO_EMPTY_VALUE = 4;
	const ERRORSTR_EMPTY_VALUE = "Data should not be empty.";

	const ERRORNO_MAX_REACHED = 5;
	const ERRORSTR_MAX_REACHED = "Maximum allowed records has been reached.";

	const ERRORNO_INVALID_VALUE = 7;
	const ERRORSTR_INVALID_VALUE = "Invalid passed value.";

	const ERRORNO_INVALID_PARAMETER = 8;
	const ERRORSTR_INVALID_PARAMETER = "Invalid passed parameter.";

	const ERRORNO_DB_VALUE_EXISTS = 9;
	const ERRORSTR_DB_VALUE_EXISTS = "Database value already exists.";

	const ERRORNO_RECORD_DOES_NOT_EXISTS = 11;
	const ERRORSTR_RECORD_DOES_NOT_EXISTS = "Record does not exists.";

	// a generic error
	const ERRORNO_DB_ERROR = 10;
	const ERRORSTR_DB_ERROR = "Database error.";

	const ERRORNO_NO_SUBSCRIPTION = 20;
	const ERRORSTR_NO_SUBSCRIPTION = "Permission denied with no subscription.";

	public function __construct() {
		parent::__construct();
	}

	/**
	* $needles: values to check
	* $haystack: an array with keys to check
	* returns array whether one key doesn't exists in $haystack
	*/

	protected function checkArrayKeyExists(array $needles, array $haystack) {
    	foreach ($needles as $needle) {
            if (!array_key_exists($needle, $haystack)) {
                error_log(self::ERRORNO_INVALID_PARAMETER . ": " . self::ERRORSTR_INVALID_PARAMETER . " '$needle' ");
                return array("statusCode" => self::ERRORNO_INVALID_PARAMETER, "statusMessage" => self::ERRORSTR_INVALID_PARAMETER, "statusDesc" => "Missing key: " . $needle);
            }
        }

    	return array("statusCode" => self::ERRORNO_OK, "statusMessage" => self::ERRORSTR_OK);
    }

    protected function checkCompanySubscription($companyId) {
		if (empty($companyId))
			return array("statusCode" => self::ERRORNO_INVALID_VALUE, "statusMessage" => self::ERRORSTR_INVALID_VALUE, "statusDesc" => "Invalid company id.");

		$query = "SELECT id FROM company_payment WHERE companyId = ? AND expiry > CURDATE() ORDER BY id DESC;";
		$query = $this->db->query($query, array($companyId));
		if (!$query) {
			$msg = $this->db->_error_number();
            $num = $this->db->_error_message();
            log_message("error", "Error running sql query in " . __METHOD__ . "(). ($num) $msg");
            return array("statusCode" => self::ERRORNO_DB_ERROR, "statusMessage" => self::ERRORSTR_DB_ERROR, "statusDesc" => "");
		}

		if ($query->num_rows())
			return TRUE;
		else
			return FALSE;
	}
}