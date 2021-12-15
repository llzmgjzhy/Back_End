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
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET NAMES utf8mb4");

$account = $_POST['account'];
$psw = $_POST['password'];

$sql = "SELECT * FROM admin_login WHERE account='{$account}' AND BINARY password='{$psw}'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$outarray;
if ($row != null) {
    $outarray = array("code" => 0, "username" => $row['username']);
    $json = json_encode($outarray);
    echo $json;
    $conn->close();
    exit();
} else {
    $outarray = array("code" => 1, "msg" => "账号或密码错误");
    $json = json_encode($outarray);
    echo $json;
    $conn->close();
    exit();
}
?>
