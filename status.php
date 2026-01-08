<?php
header('Content-Type: application/json');

require_once 'rcon.php';

$host = '49.12.82.39';
$port = 25597;
$password = 'password';

$rcon = new Rcon();

if ($rcon->connect($host, $port, $password)) {
    // Получаем список игроков
    $list = $rcon->command('list');
    
    // Парсим количество игроков
    preg_match('/There are (\d+) of a max of (\d+) players online/', $list, $matches);
    $online = $matches[1] ?? 0;
    $max = $matches[2] ?? 0;

    // Получаем имена игроков (если есть)
    $playerList = [];
    if ($online > 0 && strpos($list, ':') !== false) {
        $parts = explode(':', $list);
        if (isset($parts[1])) {
            $players = trim($parts[1]);
            $playerList = array_map('trim', explode(',', $players));
        }
    }

    echo json_encode([
        'online' => true,
        'players' => [
            'online' => (int)$online,
            'max' => (int)$max,
            'list' => $playerList
        ]
    ]);

    $rcon->disconnect();
} else {
    echo json_encode([
        'online' => false
    ]);
}
?>
