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
require './WeChat.Class.php';
// require './wechatcallback.php';
// require('./jssdk.php');
define("TOKEN", "wechat");


$servername = "8.141.166.239";
$username = "we_reply";
$password = "XJe5GEC67NbW5PJx";
$dbname = "we_reply";
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
//自动关注事件

switch ($result['type']) {
    case 'image':
        $sqlopenid = "SELECT * FROM invite_main WHERE user_wx='{$result['obj']->FromUserName}'";
        $searchuser = $conn->query($sqlopenid);
        $is_exist = $searchuser->fetch_assoc();
        if ($is_exist != null) {
            $showtext = $is_exist['invite_code'];
            transmitText($result['obj'], $showtext);
            $conn->close();
            exit();
        } else {
            //如果是第一次获取抽奖码，则分配一个未使用的抽奖码
            $sql = "SELECT * FROM invite_main WHERE is_get!='1' ORDER BY account ASC";
            $backinfo = $conn->query($sql);
            $row = $backinfo->fetch_assoc();
            $showtext;
            if ($row != null) {
                $showtext = "您的抽奖序号为：" . $row['invite_code'];
                $sqlchange1 = "UPDATE invite_main SET is_get='1' WHERE account='{$row['account']}'";
                $sqlchange2 = "UPDATE invite_main SET user_wx='{$result['obj']->FromUserName}' WHERE account='{$row['account']}'";
                mysqli_query($conn, $sqlchange1);
                mysqli_query($conn, $sqlchange2);
                if (mysqli_query($conn, $sqlchange1)) {
                    echo "修改成功";
                } else {
                    echo "修改失败";
                };
                transmitText($result['obj'], $showtext);
                $conn->close();
                exit();
            } else {
                $showtext = "未找到相关抽奖序号";
                transmitText($result['obj'], $showtext);
                $conn->close();
                exit();
            }
        }
        break;
    case 'event':
        if ($result['obj']->Event == 'subscribe') {
            $showtext = "欢迎来到南开人都爱的\"喃开树洞\"\n在这里, 你可以随时随地\"分享校园生活, 找到心灵知己\"。说心事，找同伴，寻帮助; 更有\"不定时的校园热点\", 汇聚全校所有的同学一同讨论交流。有了树说, 校园生活变得丰富多彩, 不再为社交圈子烦恼";
            transmitText($result['obj'], $showtext);
            exit();
        }
        break;
    default:
        $showtext = "发送集赞及注册截图即可获得专属抽奖码";
        transmitText($result['obj'], $showtext);
        exit();
        break;
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
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = file_get_contents("php://input");

        //extract post data
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $type = $postObj->MsgType;
            $customerevent = $postObj->Event;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
       <ToUserName><![CDATA[%s]]></ToUserName>
       <FromUserName><![CDATA[%s]]></FromUserName>
       <CreateTime>%s</CreateTime>
       <MsgType><![CDATA[%s]]></MsgType>
       <Content><![CDATA[%s]]></Content>
       <FuncFlag>0</FuncFlag>
       </xml>";
            if ($type == "event" and $customerevent == "subscribe") {
                $contentStr = "欢迎来到南开人都爱的\"喃开树洞\"\n在这里, 你可以随时随地\"分享校园生活, 找到心灵知己\"。
                说心事，找同伴，寻帮助; 更有\"不定时的校园热点\", 汇聚全校所有的同学一同讨论交流。有了树说, 校园生活变得丰富多彩, 不再为社交圈子烦恼";
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
            if (!empty($keyword)) {
                $msgType = "text";
                if ($keyword == "1") {
                    $contentStr = "qiphon";
                }
                if ($keyword == "2") {
                    $contentStr = "test 。";
                }
                if ($keyword == "3") {
                    $contentStr = "test333";
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else {
                echo "Input something...";
            }
        } else {
            echo "";
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}
