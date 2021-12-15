<?php

/**
 * wechat php test
 */
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header("Content-type:text/html;charset=utf-8");
header('Content-type:text/html;charset=utf8');

$servername = "39.105.113.112";
$username = "Tree_Hole";
$password = "Y8xCmmWL4Eyir55W";
$dbname = "tree_hole";
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET NAMES utf-8");
$appid = "wxf375f4a57e5a61ff";
$appsecret = "5b15c6385847f34489488b0176c38bfd";
$token = "wechat";

//做js-sdk验证
// $post_url=$_REQUEST['url'];
// $jssdk = new JSSDK("wxf375f4a57e5a61ff","5b15c6385847f34489488b0176c38bfd");
// $signPackage = $jssdk->GetSignPackage($post_url);
// // $data['signPackage'] = $signPackage;
// $result=array('status' => 0, 'msg' => 'success！', 'data' => $signPackage);
// $json =json_encode($result);
// echo $json;

//如果是第一次获取邀请码，则分配一个未使用的邀请码
// $sql = "SELECT invite_code FROM invite_main ORDER BY ASC WHERE is_get!='1'";
// $backinfo = $conn->query($sql);
// $row = $backinfo->fetch_assoc();
// echo $row;
$sql = mysqli_query($conn,"SELECT * FROM invite_main WHERE is_get!=1 ORDER BY account ASC");
$backinfo = mysqli_fetch_assoc($sql);
$json = json_encode($backinfo);
echo $json;
// $sql_top_info = mysqli_query($conn, "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE pid='1'");
// $sql_top = mysqli_fetch_assoc($sql_top_info);
// $json = json_encode($sql_top);
// echo $json;
// if ($row != null) {
//     $showtext = "您的邀请码为：" . $row['invite_code'];
//     $sqlchange = "UPDATE invite_main SET is_get='1' AND user_wx='{$result['obj']->FromUserName}' WHERE account='{$row['account']}'";
//     if (mysqli_query($conn, $sqlchange)) {
//         echo "修改成功";
//     } else {
//         echo "修改失败";
//     };
//     echo  $showtext;
//     $conn->close();
//     exit();
// } else {
//     $showtext = "未找到相关邀请码";
//     echo  $showtext;
//     $conn->close();
//     exit();
// }