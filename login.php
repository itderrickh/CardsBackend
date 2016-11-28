<?php
require_once './vendorlib/passwordLib.php';
require_once './config.php';
require_once './dbclasses/user.php';
$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$userDao = new UserDAO($config);

//Get the posted variables
$email = $AJAX_FORM["email"];
$password = $AJAX_FORM["password"];
$isRegister = $AJAX_FORM["isRegister"];
$result = array();

//If the user doesn't exist, we should register them

if($isRegister) {
    if(is_null($userDao->getUser($email)["email"])) {
        $userDao->createUser($email, $password);

        $secret_key = $config['key'];
        $payload = '{"email": "' . $email . '"}'; 

        $encoded_header = base64_encode('{"alg": "HS256","typ": "JWT"}');
        $encoded_payload = base64_encode($payload);

        $header_and_payload_combined = $encoded_header . '.' . $encoded_payload;
        $signature = base64_encode(hash_hmac('sha256', $header_and_payload_combined, $secret_key, true));

        $jwt_token = $header_and_payload_combined . '.' . $signature;
        
        $result['success'] = true;
        $result['message'] = $jwt_token;
    } else {
        $result['success'] = false;
        $result['message'] = "User already exists";
    }
} else {
    if(is_null($userDao->getUser($email)["email"])) {
        $result['success'] = false;
        $result['message'] = "User does not exist";
    } else {
        //Ensure they have the correct password and send the token if so
        if($userDao->authenticate($email, $password)) {
            $secret_key = $config['key'];
            $payload = '{"email": "' . $email . '"}'; 

            $encoded_header = base64_encode('{"alg": "HS256","typ": "JWT"}');
            $encoded_payload = base64_encode($payload);

            $header_and_payload_combined = $encoded_header . '.' . $encoded_payload;
            $signature = base64_encode(hash_hmac('sha256', $header_and_payload_combined, $secret_key, true));

            $jwt_token = $header_and_payload_combined . '.' . $signature;
            
            $result['success'] = true;
            $result['message'] = $jwt_token;
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid credentials, please try again";
        }
    }
}

echo json_encode($result);
?>