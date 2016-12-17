<?php
require_once './config.php';
require_once './dbclasses/message.php';
require_once './dbclasses/user.php';
require_once './dbclasses/game.php';
require_once './verify.php';

$messageDao = new MessageDAO($config);
$userDao = new UserDAO($config);
$gameDao = new GameDAO($config);

$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $tokenInfo = getTokenInfo($token);
    $user = $userDao->getUser($tokenInfo['email']);

    $game = $gameDao->getOrCreateGame();
    $messageDao->addMessage($user['id'], $game['id'], $AJAX_FORM['message']);

    $result = array();
    $result['status'] = true;
    echo json_encode($result);
}
?>