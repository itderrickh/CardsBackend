<?php
require_once './config.php';
require_once './dbclasses/game.php';
require_once './dbclasses/user.php';
require_once './dbclasses/hand.php';
require_once './verify.php';

$gameDao = new GameDAO($config);
$handDao = new HandDAO($config);
$userDao = new UserDAO($config);

$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $tokenInfo = getTokenInfo($token);
    $user = $userDAO->getUser($tokenInfo['email']);

    $game = $gameDao->getOrCreateGame();
    $handDao->playCard($user['id'], $AJAX_FORM['id'], $game['id'], $gameDao);

    $result = array();
    $result['status'] = true;
    echo json_encode($result);
}
?>