<?php

require_once(dirname(__FILE__) . "/../../.inc/globals.inc.php");
require_once(dirname(__FILE__) . "/../../.inc/shared.inc.php");
require_once(dirname(__FILE__) . "/../../.inc/functions.inc.php");

class SubscriptionTest extends PHPUnit_Framework_Testcase {
	private $mysqli;

	protected function setUp() {
		global $webvars;
		$this->assertTrue(mysqlDump());
		$this->assertTrue(dropAndReloadDatabase());
		$this->assertTrue(insertCommonData());

		global $db;
		$this->mysqli = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
	}

	protected function tearDown() {
		$this->mysqli->close();
	}

	public function testAddSubscription() {
		$uniqueCode = generateRandomString();
		$password = password_hash("pass", PASSWORD_BCRYPT);

		$sql1 = sprintf("insert into company(companyName, address, province, city, telNo, website, tin, uniqueCode, createDate)
			values('%s', '%s', %d, %d, '%s', '%s', '%s', '%s', now());",
			"ABC Company", "Brgy. Talamban", 25, 48, "3251234", "www.yahoo.com", "01234567891235", $uniqueCode);
		$sql2 = "SET @companyId = LAST_INSERT_ID();";
		$sql3 = sprintf("insert into users(companyId,userId,username, passwd, fName, lName, email, gender, createDate, role)
			values(@companyId, 1, '%s', '%s', '%s', '%s', '%s', '%s', now(), 0);",
			"abc123@yahoo.com", $password, "Justin", "Cruz", "abc123@yahoo.com", "M");
		$sql4 = "insert into `documents`(companyId, documentCode, documentName, lastNo)
                    values(@companyId, 'BP', 'BusinessPartners', 0),
                    (@companyId, 'CU', 'Customers', 0),
                    (@companyId, 'EM', 'Employees', 0),
                    (@companyId, 'SVS', 'Services', 0),
                    (@companyId, 'TRAN', 'Transactions', 0),
                    (@companyId, 'USR', 'Users', 1);";
        $sql5 = "insert into customer(companyId, customerId, custType, fName, midName, lName, active, createdBy, createDate)
                values(@companyId, 1, 0, 'Guest', 'Guest', 'Guest', 'Y', 1, now());";

		try {
			$this->mysqli->autocommit(false);
			if (!$this->mysqli->query($sql1))
				throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql1.");
			if (!$this->mysqli->query($sql2))
				throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql2.");
			if (!$this->mysqli->query($sql3))
				throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql3.");
			if (!$this->mysqli->query($sql4))
				throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql4.");
			if (!$this->mysqli->query($sql5))
				throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql5.");
			
			$this->mysqli->commit();
		} catch (Exception $e) {
			error_log($e->getMessage());
			$this->mysqli->rollback();
		} finally {
			$this->mysqli->autocommit(TRUE);
		}

		$data = array("username" => "abc123@yahoo.com", "password" => "pass");

		global $webvars;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookieFileName");
		curl_setopt($ch, CURLOPT_URL, $webvars["SERVER_ROOT"] . "/api/signIn");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);

		$this->assertEquals(302, $status_code);	// 302 is a redirection code

		$result = $this->mysqli->query("SELECT * FROM users");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$this->assertNotNull($row["lastLogIn"]);

		$result = $this->mysqli->query("SELECT * FROM ci_sessions");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$this->assertNotEmpty($row["user_data"]);

		$result = $this->mysqli->query("SELECT * FROM customer");
		$this->assertEquals(1, $result->num_rows);

		// let's add employee aka Masseur
		$sql1 = "SET @employeeId=(SELECT CAST(lastNo+1 AS char(11)) FROM documents WHERE documentCode='EM' and companyId=1);";
        $sql2 = "insert into employee(employeeId, companyId, nickname, fName, midName, lName, createDate, createdBy)
                    values(@employeeId, 1, 'Mike', 'Michael', NULL, 'De la cruz', now(), 1);";
        $sql3 = "Update documents set lastNo=@employeeId where documentCode='EM' and companyId=1;";

        try {
            $this->mysqli->autocommit(false);
            if (!$this->mysqli->query($sql1))
                throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql1.");
            if (!$this->mysqli->query($sql2))
                throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql2.");
            if (!$this->mysqli->query($sql3))
                throw new Exception("Error: " . $this->mysqli->error . ". SQL: $sql3.");
            
            $this->mysqli->commit();
            $returnValue = true;
        } catch (Exception $e) {
            $this->mysqli->rollback();
            error_log($e->getMessage());
            $returnValue = false;
        } finally {
            $this->mysqli->autocommit(true);
        }

        // let's add new service
        $sql1 = "SET @serviceId=(SELECT CAST(lastNo+1 AS char(11)) FROM documents WHERE documentCode='SVS' and companyId=1);";
		$sql2 = "insert into services(companyId, serviceId, serviceName, description, createdBy, createDate)
				values(1, @serviceId, 'SHIATSU', 'SHIATSU description', 1, NOW());";
		$sql3 = "SET @id = LAST_INSERT_ID();";
		$sql4 = "insert into pricelist(serviceId, pricelistCode, `price`, createDate, createdBy) values(@id, 0, 100, now(), 1);";
		$sql5 = "insert into pricelist(serviceId, pricelistCode, `price`, createDate, createdBy) values(@serviceId, 1, 90, now(), 1);";
		$sql6 = "Update documents set lastNo=@serviceId where documentCode='SVS' and companyId=1;";

		try {
			$this->mysqli->autocommit(false);
			if (!$this->mysqli->query($sql1))
				throw new exception ("Error: " . $this->mysqli->error . ". SQL: $sql1.");
			if (!$this->mysqli->query($sql2))
				throw new exception ("Error: " . $this->mysqli->error . ". SQL: $sql2.");
			if (!$this->mysqli->query($sql3))
				throw new exception ("Error: " . $this->mysqli->error . ". SQL: $sql3.");
			if (!$this->mysqli->query($sql4))
				throw new exception ("Error: " . $this->mysqli->error . ". SQL: $sql4.");
			if (!$this->mysqli->query($sql5))
				throw new exception ("Error: " . $this->mysqli->error . ". SQL: $sql5.");
			if (!$this->mysqli->query($sql6))
				throw new exception ("Error: " . $this->mysqli->error . ". SQL: $sql6.");

			$this->mysqli->commit();
		} catch (exception $e) {
			error_log($e->getMessage());
			$this->mysqli->rollback();
		} finally {
			$this->mysqli->autocommit(true);
		}

		// add payment types
		$sql = "INSERT INTO payment(paymentName, intervalInMonths, amt) VALUES('1 Month', 1, 500),
					('2 Months', 2, 1000), ('4 Months', 4, 2000);";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->execute();

		$sql = "SELECT * FROM payment";
		$result = $this->mysqli->query($sql);
		$this->assertEquals(3, $result->num_rows);


		// after the inputs
		global $webvars;

		$url = $webvars["SERVER_ROOT"] . "/admin/addSubscription?";
		$data = array("companyId" => 1,
					"paymentId" => 1,
					"stripeToken" => "sk_test_BQokikJOvBiI2HlWgH4olfQ2",
					"createdBy" => 1
				);

		$getData = "";
		foreach ($data as $key => $value)
			$getData .= $key . "=" . urlencode($value) . "&";

		$getData = rtrim($getData, "&");
		$url .= $getData;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookieFileName");
		curl_setopt($ch, CURLOPT_URL, $url);
		$output = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);

		$query = $this->mysqli->query("SELECT * FROM company_payment");
		$this->assertEquals(1, $query->num_rows);
		$row = $query->fetch_array(MYSQLI_ASSOC);
		$this->assertEquals($data["companyId"], $row["companyId"]);
		$this->assertEquals($data["paymentId"], $row["paymentId"]);
		$this->assertEquals($data["stripeToken"], $row["stripeToken"]);
		$this->assertEquals($data["createdBy"], $row["createdBy"]);
	}
}