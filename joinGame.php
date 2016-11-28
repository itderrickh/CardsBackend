<?php
require_once './config.php';
require_once './dbclasses/user.php';
require_once './dbclasses/game.php';
require_once './verify.php';
$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$userDao = new UserDAO($config);
$gameDao = new GameDAO($config);

$result = array();
$result['success'] = false;
$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $tokenInfo = getTokenInfo($token);
    $user = $userDAO->getUser($tokenInfo['email']);
    $gameId = $gameDao->getCurrentGame();

    if(!$gameDao->isGameReady($gameId)) {
        $gameDao->addUserToGame($user['id'], $gameId);
        $result['success'] = true;
    }
}

echo json_encode($result);
?>