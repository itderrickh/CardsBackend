<?php
require_once './config.php';
require_once './dbclasses/bid.php';
require_once './dbclasses/user.php';
require_once './dbclasses/game.php';
require_once './verify.php';

$bidDao = new BidDAO($config);
$userDao = new UserDAO($config);
$gameDao = new GameDAO($config);

$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $tokenInfo = getTokenInfo($token);
    $user = $userDao->getUser($tokenInfo['email']);

    $game = $gameDao->getOrCreateGame();
    $bidDao->createBid($user['id'], $AJAX_FORM['bid'], $game['id']);

    $result = array();
    $result['status'] = true;
    echo json_encode($result);
}
?>