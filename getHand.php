<?php
require_once './config.php';
require_once './dbclasses/user.php';
require_once './dbclasses/game.php';
require_once './dbclasses/hand.php';
require_once './verify.php';
$AJAX_FORM = json_decode(file_get_contents('php://input'), true);

$userDao = new UserDAO($config);
$gameDao = new GameDAO($config);
$handDao = new HandDAO($config);

$result = array();
$result['hand'] = array();
$token = $_SERVER['HTTP_AUTHORIZE'];

if(verifyToken($token, $config)) {
    $tokenInfo = getTokenInfo($token);
    $user = $userDAO->getUser($tokenInfo['email']);
    $gameId = $gameDao->getCurrentGame();

    $result['hand'] = $handDao->getHand($user['id'], $gameId);
    echo json_encode($result);
} else {
    header('HTTP/1.1 401 Unauthorized');
}
?>