<?php
// 指定允许其他域名访问
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

//获取当前用户的token
$user_token = $_REQUEST['user_token'];
$jarry = array(); //定义输出数组

//查询当前用户发表的树洞，并将pid和reply进行返回，在前端进行比较，只返回reply>=1的树洞
$sql = "SELECT pid,user_token,reply,timestamp FROM info_feed WHERE user_token = '{$user_token}' AND reply>=1 ORDER BY timestamp DESC ";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    unset($row['user_token']);
    unset($row['timestamp']);
    array_push($jarry, $row);
}
$json = json_encode($jarry);
echo $json;
$conn->close();
