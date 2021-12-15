<?php
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header("Content-type:text/html;charset=utf8mb4");
$servername = "39.105.113.112";
$username = "Tree_Hole";
$password = "Y8xCmmWL4Eyir55W";
$dbname = "tree_hole";
// $round=$_POST["round"];
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET NAMES utf8mb4");

//获取传输数据
$token = $_REQUEST['user_token'];
// $login_password = $_REQUEST['password'];

if ($token) {
    $sql = "SELECT * FROM user_info WHERE  BINARY user_token='{$token}'";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $outarray;
    if ($row != null) {
        $outarray = array("code" => 0, "user_token" => $row['user_token']);
        $json = json_encode($outarray);
        echo $json;
        $conn->close();
        exit();
    } else {
        $outarray = array("code" => 1, "msg" => "token错误!");
        $json = json_encode($outarray);
        echo $json;
        $conn->close();
        exit();
    }
}
