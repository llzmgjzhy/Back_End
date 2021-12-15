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
require './WeChat.class.php';
// require('./jssdk.php');

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

$wx = new WeChat($appid, $appsecret, $token);
$result = $wx->responseMsg();

if ($result['type'] == 'text') {
    if (($result['obj']->Content) == "邀请码" || ($result['obj']->Content) == "获取账号") {
        $sqlopenid = "SELECT user_token,open_id FROM user_info WHERE open_id='{$result['obj']->FromUserName}'";
        $searchuser = $conn->query($sqlopenid);
        $is_exist = $searchuser->fetch_assoc();
        if ($is_exist != null) {
            $showtext = $is_exist['user_token'];
            $conn->close();
        } else {
            $randnum = mt_rand(1, 150);
            $account = 123456 + $randnum;
            $sql = "SELECT * FROM user_info ORDER BY ASC WHERE is_get!='1'";
            $backinfo = $conn->query($sql);
            $row = $backinfo->fetch_assoc();
            $showtext;
            if ($row != null) {
                $showtext = $row['user_token'];
                $sqlchange = "UPDATE user_info SET is_get='1' AND open_id='{$result['obj']->FromUserName}' WHERE account='{$row['account']}'";
                if (mysqli_query($conn, $sqlchange)) {
                    echo "修改成功";
                } else {
                    echo "修改失败";
                };
                $conn->close();
            } else {
                $showtext = "未找到相关账号";
                // transmitText($result['obj'], $showtext);
                $conn->close();
            }
        }
        transmitText($result['obj'], $showtext);
        exit();
    }
    $sql = "SELECT * FROM user_info WHERE account='{$result['obj']->Content}'";
    $backinfo = $conn->query($sql);
    $row = $backinfo->fetch_assoc();
    $showtext;
    if ($row != null) {
        $showtext = '您的密码为：' . $row['login_password'];
        $conn->close();
    } else {
        $showtext = "未找到相关账号";
        $conn->close();
    }
    //文本消息
    // transmitText($result['obj'], '你发送的是文本，内容为：'.$result['obj']->Content);
    transmitText($result['obj'], $showtext);
}


function transmitText($object, $content)
{
    $textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        </xml>";
    $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
    file_put_contents('./test.txt', $result);
    echo $result;
    // die;
    // return $result;
}
