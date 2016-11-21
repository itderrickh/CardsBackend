<?php
//Save as example how to get auth token
//$token = $_SERVER['HTTP_AUTHORIZE'];
function verifyToken($token, $config) {
    $secret_key = $config['key'];

    $jwt_values = explode('.', $token);

    $recieved_signature = $jwt_values[2];
    $recieved_header_and_payload = $jwt_values[0] . '.' . $jwt_values[1]; 

    $what_signature_should_be = base64_encode(hash_hmac('sha256', $recieved_header_and_payload, $secret_key, true));

    return ($what_signature_should_be == $recieved_signature);
}

function getTokenInfo($token) {
    $jwt_values = explode('.', $token);
    return json_decode(base64_decode($jwt_values[1]), true);
}
?>