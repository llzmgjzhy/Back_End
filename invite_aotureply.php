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
    //如果用户发送的是"邀请码"
    if (($result['obj']->Content) == "邀请码") {
        //如果该用户之前已经获取过邀请码
        $sqlopenid = "SELECT * FROM invite_main WHERE user_wx='{$result['obj']->FromUserName}'";
        $searchuser = $conn->query($sqlopenid);
        $is_exist = $searchuser->fetch_assoc();
        if ($is_exist != null) {
            $showtext = $is_exist['invite_code'];
            transmitText($result['obj'], $showtext);
            $conn->close();
            exit();
        } else {
            //如果是第一次获取邀请码，则分配一个未使用的邀请码
            $sql = "SELECT * FROM invite_main WHERE is_get!='1' ORDER BY account ASC";
            $backinfo = $conn->query($sql);
            $row = $backinfo->fetch_assoc();
            $showtext;
            if ($row != null) {
                $showtext = "您的邀请码为：" . $row['invite_code'];
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
                $showtext = "未找到相关邀请码";
                transmitText($result['obj'], $showtext);
                $conn->close();
                exit();
            }
        }
    }
    //如果用户发送的邀请码，先判断是否是8位长度的字符串 
    elseif (strlen($result['obj']->Content) == 8) {
        //验证该用户是否已经有了邀请码
        $check_new_user = mysqli_query($conn, "SELECT * FROM invite_main WHERE user_wx='{$result['obj']->FromUserName}'");
        $check_user = mysqli_fetch_assoc($check_new_user);
        //如果有邀请码，则返回该用户自己的邀请码，不做发送邀请码对应用户的邀请人数处理
        if ($check_user != NULL) {
            $showtext = $check_user['invite_code'];
            transmitText($result['obj'], $showtext);
            exit();
        }
        //如果总表里显示没有该用户，则说明这是使用别人邀请码来第一次登入的用户
        //验证发送的内容是否是有效邀请码
        else {
            $check_code_exist = mysqli_query($conn, "SELECT * FROM invite_main WHERE invite_code='{$result['obj']->Content}'");
            $check_code = mysqli_fetch_assoc($check_code_exist);
            //如果是有效邀请码,则给该用户分配一个新邀请码，并插入细节表，并在总表修改invite_count
            if ($check_code != NULL) {
                $sql_new_code = "SELECT * FROM invite_main WHERE is_get!='1' ORDER BY account ASC ";
                $new_code = $conn->query($sql_new_code);
                $row = $new_code->fetch_assoc();
                $showtext;
                //如果剩余未分配的邀请码
                if ($row != null) {
                    //分配一个新邀请码并将invite_count更新
                    $showtext = "您的邀请码为：" . $row['invite_code'];
                    $sqlchange = "UPDATE invite_main SET is_get='1' AND user_wx='{$result['obj']->FromUserName}' WHERE account='{$row['account']}'";
                    mysqli_query($conn, "UPDATE invite_main SET invite_count=invite_count+1 WHERE invite_code='{$result['obj']->Content}'");
                    if (mysqli_query($conn, $sqlchange)) {
                        echo "修改成功";
                    } else {
                        echo "修改失败";
                    };
                    //插入到细节表
                    $detailed_insert = "INSERT INTO invite_detailed (inviter_code,invitee_code,invitee_wx) VALUES ('{$result['obj']->Content}','{$row['invite_code']}','{$result['obj']->FromUserName}')";
                    if (mysqli_query($conn, $detailed_insert)) {
                        $outtext = '成功插入数据';
                        $outarray = array("code" => 0, "data" => $outtext);
                        $json = json_encode($outarray);
                        echo $json;
                    } else {
                        $outtext = '插入数据失败';
                        $outarray = array("code" => 0, "data" => $outtext);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                    transmitText($result['obj'], $showtext);
                    $conn->close();
                    exit();
                }
                //没有剩余的邀请码 
                else {
                    $showtext = "未找到相关邀请码";
                    transmitText($result['obj'], $showtext);
                    $conn->close();
                    exit();
                }
            }
            //如果是无效邀请码
            else {
                $showtext = "请输入有效邀请码";
                transmitText($result['obj'], $showtext);
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
