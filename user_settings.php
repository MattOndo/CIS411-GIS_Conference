<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/
error_reporting(E_ALL); 
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he is not logged in
if (!isUserLoggedIn()) { header("Location: login.php"); die(); }

if (!empty($_POST)) {
	$errors = array();
	$successes = array();
	$password = $_POST["password"];
	$password_new = $_POST["passwordc"];
	$password_confirm = $_POST["passwordcheck"];
	$email = $_POST["email"];
	$first_name = trim($_POST["first_name"]);
	$last_name = trim($_POST["last_name"]);
	$company = trim($_POST["company"]);
	$email = trim($_POST["email"]);
	$address_1 = trim($_POST["address_1"]);
	$address_2 = trim($_POST["address_2"]);
	$city = trim($_POST["city"]);
	$state = trim($_POST["state"]);
	$zip = trim($_POST["zip"]);
	
	//Perform some validation
	//Feel free to edit / change as required
	
	//Confirm the hashes match before updating a users password
	$entered_pass = generateHash($password,$loggedInUser->hash_pw);
	
	if (trim($password) == ""){
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}
	else if ($entered_pass != $loggedInUser->hash_pw) {
		//No match
		$errors[] = lang("ACCOUNT_PASSWORD_INVALID");
	}

	if ($email != $loggedInUser->email) {
		if(trim($email) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
		} else if (!isValidEmail($email)) {
			$errors[] = lang("ACCOUNT_INVALID_EMAIL"); 
		} else if (emailExists($email)) {
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE", array($email));	
		}
		
		//End data validation
		if(count($errors) == 0) {
			$loggedInUser->updateEmail($email);
			$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
		}
	}

	if ($first_name != $loggedInUser->first_name) {
		if(trim($first_name) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_FNAME");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateFirstName($loggedInUser->user_id, $first_name);
			$loggedInUser->first_name = $first_name;
			$successes[] = lang("ACCOUNT_FNAME_UPDATED");
		}
	}

	if ($last_name != $loggedInUser->last_name) {
		if(trim($last_name) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_LNAME");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateLastName($loggedInUser->user_id, $last_name);
			$loggedInUser->last_name = $last_name;
			$successes[] = lang("ACCOUNT_LNAME_UPDATED");
		}
	}

	if ($company != $loggedInUser->company) {
		if(trim($company) == "") {
			$success[] = lang("ACCOUNT_SPECIFY_COMPANY");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateCompany($loggedInUser->user_id, $company);
			$loggedInUser->company = $company;
			$successes[] = lang("ACCOUNT_COMPANY_UPDATED");
		}
	}

	if ($address_1 != $loggedInUser->address_1) {
		if(trim($address_1) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_ADDRESS");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateAddress_1($loggedInUser->user_id, $address_1);
			$loggedInUser->address_1 = $address_1;
			$successes[] = lang("ACCOUNT_ADDRESS_UPDATED");
		}
	}

	if ($address_2 != $loggedInUser->address_2) {
		if(trim($address_2) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_ADDRESS");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateAddress_2($loggedInUser->user_id, $address_1);
			$loggedInUser->address_2 = $address_2;
			$successes[] = lang("ACCOUNT_ADDRESS_UPDATED");
		}
	}

	if ($city != $loggedInUser->city) {
		if(trim($city) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_CITY");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateCity($loggedInUser->user_id, $city);
			$loggedInUser->city = $city;
			$successes[] = lang("ACCOUNT_CITY_UPDATED");
		}
	}

	if ($state != $loggedInUser->state) {
		if(trim($state) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_STATE");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateState($loggedInUser->user_id, $state);
			$loggedInUser->state = $state;
			$successes[] = lang("ACCOUNT_STATE_UPDATED");
		}
	}

	if ($zip != $loggedInUser->zip) {
		if(trim($zip) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_ZIP");
		}
		
		//End data validation
		if(count($errors) == 0) {
			updateZip($loggedInUser->user_id, $zip);
			$loggedInUser->zip = $zip;
			$successes[] = lang("ACCOUNT_ZIP_UPDATED");
		}
	}
	
	if ($password_new != "" OR $password_confirm != "") {
		if (trim($password_new) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
		} else if (trim($password_confirm) == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_CONFIRM_PASSWORD");
		} else if (minMaxRange(8,50,$password_new)) {	
			$errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(8,50));
		} else if ($password_new != $password_confirm) {
			$errors[] = lang("ACCOUNT_PASS_MISMATCH");
		}
		
		//End data validation
		if (count($errors) == 0) {
			//Also prevent updating if someone attempts to update with the same password
			$entered_pass_new = generateHash($password_new,$loggedInUser->hash_pw);
			
			if ($entered_pass_new == $loggedInUser->hash_pw) {
				//Don't update, this fool is trying to update with the same password Â¬Â¬
				$errors[] = lang("ACCOUNT_PASSWORD_NOTHING_TO_UPDATE");
			} else {
				//This function will create the new hash and update the hash_pw property.
				$loggedInUser->updatePassword($password_new);
				$successes[] = lang("ACCOUNT_PASSWORD_UPDATED");
			}
		}
	}
	if (count($errors) == 0 AND count($successes) == 0) {
		$errors[] = lang("NOTHING_TO_UPDATE");
	}
}


require_once("models/header.php");
?>
<body>
	<?php include("nav.php"); ?>
	<section class="container">
		<div class="row">
			<? echo resultBlock($errors,$successes); ?>
			<div class="col-80" style="margin-left: 0;">
				<h1>User Settings</h1>
				<form name='updateAccount' action='<? $_SERVER['PHP_SELF'] ?>' method='post' class="forms">
	
					<fieldset id="general-info" class="col-50">
				        <legend>Account Information</legend>
						<label>First Name
							<input type='text' name='first_name' class="width-100" value="<? echo $loggedInUser->first_name; ?>" />
						</label>
	
						<label>Last Name
							<input type='text' name='last_name' class="width-100" value="<? echo $loggedInUser->last_name; ?>" />
						</label>
	
						<label>Company / Institution
							<input type='text' name='company' class="width-100" value="<? echo $loggedInUser->company; ?>" />
						</label>
						
						<label>Email Address
							<input type='email' name='email' class="width-100" value="<? echo $loggedInUser->email; ?>" />
						</label>
	
						<label>Address Line 1
							<input type='text' name='address_1' class="width-100" value="<? echo $loggedInUser->address_1; ?>" />
						</label>
	
						<label>Address Line 2
							<input type='text' name='address_2' class="width-100" value="<? echo $loggedInUser->address_2; ?>" />
						</label>
	
						<label>City
							<input type='text' name='city' class="width-100" value="<? echo $loggedInUser->city; ?>" />
						</label>
	
						<label>State / Province
							<input type='text' name='state' class="width-100" value="<? echo $loggedInUser->state; ?>" />
						</label>
	
						<label>Zip Code
							<input type='text' name='zip' class="width-100" value="<? echo $loggedInUser->zip; ?>" />
						</label>
					</fieldset>
						
					<div class="col-40">
						<fieldset class="width-100">
							<legend>Account Status</legend>
							<? if ($loggedInUser->paid == '1') { 
								echo '<span class="success">Paid</span>';
							} else if ($loggedInUser->paid == '0') { 
								echo '<span class="error">You have an unpaid balance of '.$loggedInUser->balance.'</span>';
							} ?>
						</fieldset>
						
						<fieldset class="width-100 left" >
						    <legend>Change Password</legend>
							<label>New Password
								<input type='password' name='passwordc' class="width-100" />
							</label>
							
							<label>Confirm Password
								<input type='password' name='passwordcheck' class="width-100" />
							</label>
						</fieldset>
	
						<fieldset class="width-100" >
						    <legend>Enter Password to apply changes</legend>
							<label>Password
								<input type='password' name='password' class="width-100" required />
							</label>
							<input type='submit' value='Update' class='btn' />
						</fieldset>
					</div>
				</form>
			</div>
			<aside class="col-20 nav">
				<? 
				if(isUserLoggedIn()) {
					include('includes/sideNav.php');
				} else {
					include('includes/loginForm.php');
				}
				?>
			</aside>
		</div>
	</section>
	<?php include("models/footer.php"); ?>
</body>
</html>