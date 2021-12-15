<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header("Content-type:text/html;charset=utf-8");

use Workerman\Worker;
use PHPSocketIO\SocketIO;

require_once __DIR__ . '/vendor/autoload.php';

// Listen port 2021 for socket.io client
$io = new SocketIO(3120);
$io->on('connection', function ($socket) use ($io) {
  //登陆监听
  $socket->on('Login', function ($login) use ($socket) {
    $socket->join($login);
    $socket->emit('Login_success', "登陆成功");
  });
  //评论监听
  $socket->on('comment', function ($comment) use ($io) {
    //定义数据库的信息
    $servername = "39.105.113.112";
    $username = "Tree_Hole";
    $password = "Y8xCmmWL4Eyir55W";
    $dbname = "tree_hole";
    // $round=$_POST["round"];
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->query("SET NAMES utf8mb4");

    //发送给洞主
    $pid = $comment;
    $get_dz_token = mysqli_query($conn, "SELECT pid,user_token FROM info_feed WHERE pid='{$pid}'");
    $get_dz = mysqli_fetch_assoc($get_dz_token);
    $dz_token = $get_dz['user_token'];
    $io->to($dz_token)->emit('receive_comment', '1');
    $conn->close();
  });
});

Worker::runAll();
