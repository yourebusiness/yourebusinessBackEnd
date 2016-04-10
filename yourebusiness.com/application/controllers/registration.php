<?php

include_once ".inc/globals.inc.php";

class Registration extends My_Controller {	
    public function __construct() {
        parent::__construct();
    }

	public function view($page = 'register_view') {

		if (!file_exists('application/views/' . $page . '.php'))
			show_404();

        session_start();
        include_once "includes/captcha/simple-php-captcha.php";
        $_SESSION['captcha'] = simple_php_captcha();

		$this->load->model("Api_model");
		$data["province"] = $this->Api_model->getProvinces();
		$headerData['title'] = "Register";
		$this->load->view("templates/header", $headerData);
		$this->load->view($page, $data);
		$this->load->view('templates/footer');
	}

    private function _checkPWAndConfirmPW(array $data) {
        if ($data["password"] !== $data["confirmPassword"])
            return array("statusCode" => parent::ERRORNO_INVALID_VALUE, "statusMessage" => parent::ERRORSTR_INVALID_VALUE, "statusDesc" => "Password and confirm password do not match.");

        return true;
    }

	public function register() {
        $this->method = $_SERVER["REQUEST_METHOD"];

        if ($this->method == "POST" && array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER)) {
            if ($_SERVER["HTTP_X_HTTP_METHOD"] == "DELETE")
                $this->method = "DELETE";
            elseif ($_SERVER["HTTP_X_HTTP_METHOD"] == "PUT")
                $this->method = "PUT";
            else
                throw new Exception("Unexpected Header");
        }

        switch ($this->method) {
            case "DELETE":
                break;
            case "POST":
                $this->_registration_add();
                break;
            case "GET":
                break;
            case 'PUT':
                break;
            default:
                $this->_response(array("Invalid method."), 405);
                break;
        }
	}

    private function _checkCaptcha() {
        $captcha = $this->input->post("g-recaptcha-response");
        if (!$captcha)
            return array("statusCode" => parent::ERRORNO_INVALID_VALUE, "statusMessage" => parent::ERRORSTR_INVALID_VALUE, "statusDesc" => "Captcha is missing. Reload the page.");
        if ($captcha == "")
            return array("statusCode" => parent::ERRORNO_INVALID_VALUE, "statusMessage" => parent::ERRORSTR_INVALID_VALUE, "statusDesc" => "No value for captcha.");

        return true;
    }

    private function _verifyCaptcha() {
        define("secretKey", "6LfIjBgTAAAAADFjUP1JficN0G4QBDo6ohCDAc-D");    //from simply.amazing.wizard@g...
        $data["secret"] = constant("secretKey");
        $data["response"] = $this->input->post("g-recaptcha-response");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        /*$fileLocation = "/tmp/registration.txt";
        $file = fopen($fileLocation, "w");
        fwrite($file, print_r($output, true));
        fclose($file);*/

        $assocArray = json_decode($output, true);
        if (!$assocArray["success"])
            return array("statusCode" => parent::ERRORNO_INVALID_CAPTCHA, "statusMessage" => parent::ERRORSTR_INVALID_CAPTCHA, "statusDesc" => "Invalid captcha.");

        return true;
    }

    private function _registration_add() {
        $status = $this->_checkCaptcha();
        if ($status["statusCode"] != 0) {
            $this->_response($status);
            return;
        }

        // request for verification
        $status = $this->_verifyCaptcha();
        if ($status["statusCode"] != 0) {
            $this->_response($status);
            return;
        }

        $data = array();
        $data["password"] = $this->input->post("password");
        $data["confirmPassword"] = $this->input->post("confirmPassword");

        $status = $this->_checkPWAndConfirmPW($data);
        if ($status["statusCode"] != 0) {
            $this->_response($status);
            return;
        }

        $this->load->helper("utility");

        $data["company"] = $this->input->post("company");
        $data["province"] = $this->input->post("province");
        $data["city"] = $this->input->post("city");
        $data["address"] = $this->input->post("address");
        $data["phoneNo"] = $this->input->post("phoneNo");
        $data["companyWebsite"] = $this->input->post("companyWebsite");
        $data["tin"] = $this->input->post("tin");
        $data["fName"] = $this->input->post("fName");
        $data["lName"] = $this->input->post("lName");
        $data["gender"] = $this->input->post("gender");
        $data["userEmail"] = $this->input->post("userEmail");
        $data["hash"] = generateRandomString(40);
        $data["captcha"] = null;  // we do not need captcha to record anymore, we now use Google captcha.

        $this->load->model("registration_model");
        $status = $this->registration_model->add($data);

        /*if ($status["statusCode"] == 0) {
            global $settings;
            if ($settings["sendEmail"])
                $this->sendEmail($data["userEmail"], $data["hash"]);
        }*/

        $this->_response($status);
    }

    public function registration_success() {
        $data["title"] = "Registration";
        $this->load->view("templates/header", $data);
        $this->load->view("register_success");
    }

    private function sendEmail($email, $hash) {
        $this->load->library("MY_PHPMailer.php");
        $mail = new PHPMailer;

        global $smtp;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = "smtp.mail.yahoo.com";  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $smtp["username"];                 // SMTP username
        $mail->Password = $smtp["password"];                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->From = $smtp["from"];
        $mail->FromName = 'yourspa Mailer';
        $mail->addAddress($email);             // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'Registration to www.yourspa.com';

        $message = "Thank you for registering at our site. Click on the below link to activate your registration.\n
                    <a href='http://yourspa.com/registration/activateRegistration/$hash'>Activate Registration.</a>";
        $mail->Body    = $message;
        $mail->AltBody = "Thank you for registering at our site. Click on the below link to activate your registration.\n
                    http://yourspa.com/registration/activateregistration/$hash";

        if(!$mail->send())
            return false;
        else
            return true;
    }

    public function activateRegistration($hash = "") {
        $this->load->model("register_model");
        if ($this->register_model->activateRegistration($hash))
            $data["message"] = "Congratulations!!! You have successfully activated your account.";
        else
            $data["message"] = "Activation has NOT been successful.";

        $headerData["title"] = "Activation";
        $this->load->view("templates/header", $headerData);
        $this->load->view("activated", $data);
        $this->load->view("templates/footer2");
    }
}