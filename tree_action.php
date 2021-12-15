<?php
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:*');
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

$input = $_REQUEST['input'];
$value = $_REQUEST['value'];
$outarray;
switch ($value) {
    case '1':
        if (mysqli_query($conn, "UPDATE info_feed SET hidden=1 WHERE pid='{$input}'")) {
            $outarray = array("code" => 20000, "data" => '操作成功');
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            break;
        } else {
            $outarray = array("code" => 20001, "message" => '请输入正确的PID');
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            exit();
            break;
        }

    case '2':
        $get_lz_token = mysqli_query($conn, "SELECT user_token,pid FROM info_feed WHERE pid='{$input}'");
        $get_lz = mysqli_fetch_assoc($get_lz_token);
        if ($get_lz['user_token']) {
            $action_token = $get_lz['user_token'];
            if (mysqli_query($conn, "UPDATE user_info SET is_forbid=1 WHERE user_token='{$action_token}'")) {
                $outarray = array("code" => 20000, "data" => '操作成功');
                $json = json_encode($outarray);
                echo $json;
                $conn->close();
                break;
            } else {
                $outarray = array("code" => 20001, "message" => '操作失败');
                $json = json_encode($outarray);
                echo $json;
                $conn->close();
                break;
            }
        } else {
            $outarray = array("code" => 20001, "message" => '请输入正确的PID');
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            break;
        }
    case '3':
        $get_lz_token = mysqli_query($conn, "SELECT user_token,pid FROM info_feed WHERE pid='{$input}'");
        $get_lz = mysqli_fetch_assoc($get_lz_token);
        if ($get_lz['user_token']) {
            $action_token = $get_lz['user_token'];
            if (mysqli_query($conn, "UPDATE user_info SET is_forbid=1 WHERE user_token='{$action_token}'")) {
            } else {
                $outarray = array("code" => 20001, "message" => '操作失败');
                $json = json_encode($outarray);
                echo $json;
                $conn->close();
                break;
            }
            if (mysqli_query($conn, "UPDATE info_feed SET hidden=1 WHERE user_token='{$action_token}'")) {
                $outarray = array("code" => 20000, "data" => '操作成功');
                $json = json_encode($outarray);
                echo $json;
                $conn->close();
                break;
            } else {
                $outarray = array("code" => 20001, "message" => '操作失败');
                $json = json_encode($outarray);
                echo $json;
                $conn->close();
                break;
            }
        } else {
            $outarray = array("code" => 20001, "message" => '请输入正确的PID');
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            break;
        }
}
