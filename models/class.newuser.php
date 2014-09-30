<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/


class User 
{
	public $user_active = 0;
	private $clean_email;
	public $status = false;
	private $clean_password;
	public $first_name;
	public $last_name;
	public $company;
	public $address_1;
	public $address_2;
	public $city;
	public $state;
	public $zip;
	public $sql_failure = false;
	public $mail_failure = false;
	public $email_taken = false;
	public $username_taken = false;
	public $displayname_taken = false;
	public $activation_token = 0;
	public $success = NULL;
	
	function __construct($password, $email, $token, $activationRequest, $passwordRequest, $active, $title, $signUp, $signIn, $company, $address_1, $address_2, $city, $state, $zip, $paid, $first_name, $last_name)
	{
		//Used for display only
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->company = $company;
		$this->address_1 = $address_1;
		$this->address_2 = $address_2;
		$this->city = $city;
		$this->state = $state;
		$this->zip = $zip;
		
		//Sanitize
		$this->clean_email = sanitize($email);
		$this->clean_password = trim($pass);
		
		if(emailExists($this->clean_email))
		{
			$this->email_taken = true;
		}
		else
		{
			//No problems have been found.
			$this->status = true;
		}
	}
	
	public function userCakeAddUser()
	{
		global $mysqli,$emailActivation,$websiteUrl,$db_table_prefix;
		
		//Prevent this function being called if there were construction errors
		if($this->status)
		{
			//Construct a secure hash for the plain text password
			$secure_pass = generateHash($this->clean_password);
			
			//Construct a unique activation token
			$this->activation_token = generateActivationToken();
			
			//Do we need to send out an activation email?
			if($emailActivation == "true")
			{
				//User must activate their account first
				$this->user_active = 0;
				
				$mail = new userCakeMail();
				
				//Build the activation message
				$activation_message = lang("ACCOUNT_ACTIVATION_MESSAGE",array($websiteUrl,$this->activation_token));
				
				//Define more if you want to build larger structures
				$hooks = array(
					"searchStrs" => array("#ACTIVATION-MESSAGE","#ACTIVATION-KEY","#USERNAME#"),
					"subjectStrs" => array($activation_message,$this->activation_token,$this->first_name)
					);
				
				/* Build the template - Optional, you can just use the sendMail function 
				Instead to pass a message. */
				
				if(!$mail->newTemplateMsg("new-registration.txt",$hooks))
				{
					$this->mail_failure = true;
				}
				else
				{
					//Send the mail. Specify users email here and subject. 
					//SendMail can have a third parementer for message if you do not wish to build a template.
					
					if(!$mail->sendMail($this->clean_email,"New User"))
					{
						$this->mail_failure = true;
					}
				}
				$this->success = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
			}
			else
			{
				//Instant account activation
				$this->user_active = 1;
				$this->success = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
			}	
			
			
			if(!$this->mail_failure)
			{
				//Insert the user into the database providing no errors have been found.
				$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."users (
					password,
					email,
					activation_token,
					last_activation_request,
					lost_password_request,
					active,
					title,
					sign_up_stamp,
					last_sign_in_stamp,
					company,
					address_1,
					address_2,
					city,
					state,
					zip,
					paid,
					first_name,
					last_name
					)
					VALUES (
					?,
					?,
					?,
					'".time()."',
					'0',
					?,
					'New Member',
					'".time()."',
					'0',
					?,
					?,
					?,
					?,
					?,
					?,
					'0',
					?,
					?
					)");
				
				$stmt->bind_param("sssisssssiss", $secure_pass, $this->clean_email, $this->activation_token, $this->user_active, $this->company, $this->address_1, $this->address_2, $this->city, $this->state, $this->zip, $this->first_name, $this->last_name);
				$stmt->execute();
				print_r($stmt);
				$inserted_id = $mysqli->insert_id;
				$stmt->close();
				
				//Insert default permission into matches table
				$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."user_permission_matches  (
					user_id,
					permission_id
					)
					VALUES (
					?,
					'1'
					)");
				$stmt->bind_param("s", $inserted_id);
				$stmt->execute();
				$stmt->close();
			}
		}
	}
}

?>