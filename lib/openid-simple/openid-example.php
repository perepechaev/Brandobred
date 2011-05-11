<?
require('class.openid.php');


// EXAMPLE
if ($_POST['openid_action'] == "login"){ // Get identity from user and redirect browser to OpenID Server
	$openid = new SimpleOpenID;
	$openid->SetIdentity($_POST['openid_url']);
	$openid->SetTrustRoot('http://' . $_SERVER["HTTP_HOST"]);
	$openid->SetRequiredFields(array('email','fullname'));
	$openid->SetOptionalFields(array('dob','gender','postcode','country','language','timezone'));
	if ($openid->GetOpenIDServer()){
		$openid->SetApprovedURL('http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PATH_INFO"]);  	// Send Response from OpenID server to this script
		$openid->Redirect(); 	// This will redirect user to OpenID Server
	}else{
		$error = $openid->GetError();
		echo "ERROR CODE: " . $error['code'] . "<br>";
		echo "ERROR DESCRIPTION: " . $error['description'] . "<br>";
	}
	exit;
}
else if($_GET['openid_mode'] == 'id_res'){ 	// Perform HTTP Request to OpenID server to validate key
	$openid = new SimpleOpenID;
	$openid->SetIdentity($_GET['openid_identity']);
	$openid_validation_result = $openid->ValidateWithServer();
	if ($openid_validation_result == true){ 		// OK HERE KEY IS VALID
		echo "VALID";
	}else if($openid->IsError() == true){			// ON THE WAY, WE GOT SOME ERROR
		$error = $openid->GetError();
		echo "ERROR CODE: " . $error['code'] . "<br>";
		echo "ERROR DESCRIPTION: " . $error['description'] . "<br>";
	}else{											// Signature Verification Failed
		echo "INVALID AUTHORIZATION";
	}
}else if ($_GET['openid_mode'] == 'cancel'){ // User Canceled your Request
	echo "USER CANCELED REQUEST";
}
?>

<form action="<?echo 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PATH_INFO"]; ?>" method="post" onsubmit="this.login.disabled=true;">
<input type="hidden" name="openid_action" value="login">
<div>
  <input type="text" name="openid_url">
  <input type="submit" name="login" value="login &gt;&gt;">
</div>
</form>