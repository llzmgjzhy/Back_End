<?php
define("TOKEN", "wechat");
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
       <MsgType><![CDATA[event]]></MsgType>
       <Content><![CDATA[subscribe]]></Content>
       <FuncFlag>0</FuncFlag>
       </xml>";
            if ($type == "event" and $customerevent == "subscribe") {
                $contentStr = "感谢你的关注\n回复1查看联系方式\n回复2查看最新资讯\n回复3查看法律文书";
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
