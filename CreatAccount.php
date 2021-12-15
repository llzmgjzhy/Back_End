<?php
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header("Content-type:text/html;charset=utf-8");
$servername = "39.105.113.112";
$username = "Tree_Hole";
$password = "Y8xCmmWL4Eyir55W";
$dbname = "tree_hole";
// $round=$_POST["round"];
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET NAMES utf-8");

//设置随机生成的token
$namelength = 8;
$passlength = 10;
$account = 13956;
while ($account < 15000) {
    $tokentinsert = '';
    $passwordinsert = '';
    $keys = array_merge(range('A', 'Z'),range(0, 9));
    for ($i = 0; $i < $namelength; $i++) {
        $tokentinsert .= $keys[array_rand($keys)];
    }
    $sql = "INSERT INTO invite_main (account,invite_code)
   VALUES ('{$account}','{$tokentinsert}')";
    if (mysqli_query($conn, $sql)) {
        $outtext = '成功';
        echo $outtext;
    } else {
        $outtext = '失败';
        echo $outtext;
    }
    $account++;
}

