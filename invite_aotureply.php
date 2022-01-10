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
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();

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


if ($result['type'] == 'text') {
    //如果用户发送的是"抽奖%"
    if (($result['obj']->Content) == "抽奖序号" || ($result['obj']->Content) == "抽奖" || ($result['obj']->Content) == "抽奖码") {
        //如果该用户之前已经获取过抽奖码
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
    }
    //不满足要求的输入 
    else {
        $showtext = "请正确输入";
        transmitText($result['obj'], $showtext);
        exit();
    }
    //如果用户发送的邀请码，先判断是否是8位长度的字符串 
    // elseif (strlen($result['obj']->Content) == 8) {
    //     //验证该用户是否已经被邀请过了
    //     $check_new_user = mysqli_query($conn, "SELECT * FROM invite_detailed WHERE invitee_wx='{$result['obj']->FromUserName}'");
    //     $check_user = mysqli_fetch_assoc($check_new_user);
    //     // 如果已经被邀请过了，则提示感谢参与，您已经完成了邀请
    //     if ($check_user != NULL) {
    //         $showtext = "您已完成邀请";
    //         transmitText($result['obj'], $showtext);
    //         exit();
    //     }
    //     //如果详细表里显示没有该用户，则说明这是使用别人邀请码来第一次登入的用户
    //     //验证发送的内容是否是有效邀请码
    //     else {
    //         $check_code_exist = mysqli_query($conn, "SELECT * FROM invite_main WHERE invite_code='{$result['obj']->Content}'");
    //         $check_code = mysqli_fetch_assoc($check_code_exist);
    //         //如果是有效邀请码,则给该用户分配一个新邀请码，并插入细节表，并在总表修改invite_count
    //         // 如果是有效邀请码，则给邀请人的invite_count+1，并在detailed表中记录，
    //         if ($check_code != NULL) {
    //             // $sql_new_code = "SELECT * FROM invite_main WHERE is_get!='1' ORDER BY account ASC ";
    //             // $new_code = $conn->query($sql_new_code);
    //             // $row = $new_code->fetch_assoc();
    //             $showtext;
    //             //如果剩余未分配的邀请码

    //             //分配一个新邀请码并将invite_count更新
    //             $showtext = "感谢参与，您已完成邀请";
    //             // $sqlchange = "UPDATE invite_main SET is_get='1' AND user_wx='{$result['obj']->FromUserName}' WHERE account='{$row['account']}'";
    //             mysqli_query($conn, "UPDATE invite_main SET invite_count=invite_count+1 WHERE invite_code='{$result['obj']->Content}'");
    //             // if (mysqli_query($conn, $sqlchange)) {
    //             //     echo "修改成功";
    //             // } else {
    //             //     echo "修改失败";
    //             // };
    //             //插入到细节表
    //             $detailed_insert = "INSERT INTO invite_detailed (inviter_code,invitee_wx) VALUES ('{$result['obj']->Content}','{$result['obj']->FromUserName}')";
    //             if (mysqli_query($conn, $detailed_insert)) {
    //                 $outtext = '成功插入数据';
    //                 $outarray = array("code" => 0, "data" => $outtext);
    //                 $json = json_encode($outarray);
    //                 echo $json;
    //             } else {
    //                 $outtext = '插入数据失败';
    //                 $outarray = array("code" => 0, "data" => $outtext);
    //                 $json = json_encode($outarray);
    //                 echo $json;
    //             }
    //             transmitText($result['obj'], $showtext);
    //             $conn->close();
    //             exit();
    //         }
    //         //如果是无效邀请码
    //         else {
    //             $showtext = "请输入有效邀请码";
    //             transmitText($result['obj'], $showtext);
    //             exit();
    //         }
    //     }
    // }

    //文本消息
    // transmitText($result['obj'], '你发送的是文本，内容为：'.$result['obj']->Content);
    // transmitText($result['obj'], $showtext);
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
                $contentStr = $showtext = "欢迎来到南开人都爱的\"喃开树洞\"\n\n在这里, 你可以随时随地“分享校园生活, 找到心灵知己”✨说心事，找同伴，寻帮助; 💡更有“不时的校园热点”, 汇聚全校所有的同学一同讨论交流。🤓\n\n有了树说, 校园生活变得丰富多彩, 不再为社交圈子烦恼！🥰🥰🥰";
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
