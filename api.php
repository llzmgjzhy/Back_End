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

//读取具体的请求活动
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$jarry = array(); //定义输出数组

//检测是否是有效请求，是否一分钟以内的时间请求
$check_now_time = time();

switch ($action) {
    case 'get_comment':
        $pid = $_REQUEST['pid'];
        $user_token = $_REQUEST['user_token'];
        $sql = "SELECT cid,pid,text,timestamp,islz,name FROM comment WHERE pid='{$pid}'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            array_push($jarry, $row);
        };
        //检测当前用户是否关注了该树洞，如果关注了，则在返回数据中包含attention，避免了用户刷新后重复关注的bug
        $check_attention_info = mysqli_query($conn, "SELECT * FROM user_attention WHERE user_token='{$user_token}' AND pid='{$pid}' ");
        $check_attention = mysqli_fetch_assoc($check_attention_info);

        //检测当前树洞是否为自己发表的树洞，如果是，则也返回attention，便于显示最新消息图标
        $check_mine_info = mysqli_query($conn, "SELECT pid,user_token FROM info_feed WHERE pid='{$pid}' ");
        $check_mine = mysqli_fetch_assoc($check_mine_info);

        //计数当前树洞的总关注数，做到显示后则立马更新关注数的功能，提供用户体验
        $count_attention_info = mysqli_query($conn, "SELECT pid,likenum FROM info_feed WHERE pid='{$pid}' ");
        $count_attention = mysqli_fetch_assoc($count_attention_info);
        if ($count_attention == NULL) {
            $count_attention = 0;
        }
        if (($check_attention != NULL) || ($user_token == $check_mine['user_token'])) {
            $outarray = array("code" => 0, "data" => $jarry, "attention" => '1', "likenum" => $count_attention['likenum']);
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            exit();
            break;
        }
        $outarray = array("code" => 0, "data" => $jarry, "likenum" => $count_attention['likenum']);
        $json = json_encode($outarray);
        echo $json;
        $conn->close();
        exit();
        break;
    case 'get_list':
        $page = $_REQUEST['p'];
        $tag = $_REQUEST['tag'];
        if ($_REQUEST['user_token']) {
            $token = $_REQUEST['user_token'];
        }
        $pagesize = 30; //每一页显示30条信息
        $start;
        $end;
        //置顶消息
        $sql_top_info = mysqli_query($conn, "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE pid='1'");
        $sql_top = mysqli_fetch_assoc($sql_top_info);
        array_push($jarry, $sql_top);
        if ($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $pagesize;
        }
        switch ($tag) {
            case '0':
                $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE hidden='0'  ORDER BY timestamp DESC LIMIT $start,$pagesize";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $check_like_is = mysqli_query($conn, "SELECT * FROM user_like WHERE pid='{$row['pid']}' AND user_token='{$token}'");
                    $check_like = mysqli_fetch_assoc($check_like_is);
                    if ($check_like) {
                        $row["like"] = "1";
                        array_push($jarry, $row);
                    } else {
                        $row["like"] = "0";
                        array_push($jarry, $row);
                    }
                };
                $outarray = array("code" => 0, "data" => $jarry);
                $json = json_encode($outarray);
                echo $json;
                break;
            case '1':
                $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE hidden='0' AND tag='1'  ORDER BY timestamp DESC LIMIT $start,$pagesize";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $check_like_is = mysqli_query($conn, "SELECT * FROM user_like WHERE pid='{$row['pid']}' AND user_token='{$token}'");
                    $check_like = mysqli_fetch_assoc($check_like_is);
                    if ($check_like) {
                        $row["like"] = "1";
                        array_push($jarry, $row);
                    } else {
                        $row["like"] = "0";
                        array_push($jarry, $row);
                    }
                };
                $outarray = array("code" => 0, "data" => $jarry);
                $json = json_encode($outarray);
                echo $json;
                break;
            case '2':
                $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE hidden='0' AND tag='2'  ORDER BY timestamp DESC LIMIT $start,$pagesize";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $check_like_is = mysqli_query($conn, "SELECT * FROM user_like WHERE pid='{$row['pid']}' AND user_token='{$token}'");
                    $check_like = mysqli_fetch_assoc($check_like_is);
                    if ($check_like) {
                        $row["like"] = "1";
                        array_push($jarry, $row);
                    } else {
                        $row["like"] = "0";
                        array_push($jarry, $row);
                    }
                };
                $outarray = array("code" => 0, "data" => $jarry);
                $json = json_encode($outarray);
                echo $json;
                break;
        }
        $conn->close();
        exit();
        break;
    case 'do_post':

        //获取网址传来的信息
        $text = $_REQUEST['text'];
        $type = $_REQUEST['type'];
        $tag = $_REQUEST['tag'];
        $timestamp = $_REQUEST['jsapiver'];
        $user_token = $_REQUEST['user_token'];
        //对user_token进行判断，如果存在且没有被forbid才可以发表，否则发布失败
        $sql_is_authority = mysqli_query($conn, "SELECT user_token,is_forbid FROM user_info WHERE user_token='{$user_token}'");
        $authority_query = mysqli_fetch_assoc($sql_is_authority);

        //如果当前用户有权限发表树洞
        if ($authority_query != NULL && $authority_query['is_forbid'] == 0 && ($check_now_time - $timestamp <= 10)) {

            //先获取pid号码
            $nowpid = 1000;
            $pid = mysqli_query($conn, "SELECT pid FROM info_feed ORDER BY pid DESC LIMIT 1");
            while ($row = mysqli_fetch_assoc($pid)) {
                $nowpid = $row['pid'];
                break;
            }
            $insertpid = $nowpid + 1;;
            if (!empty($_REQUEST['data'])) {
                $url = $_REQUEST['data'];
            }
            if (!empty($_REQUEST['imgtype'])) {
                $imgtype = $_REQUEST['imgtype'];
                $temp = explode('.', $imgtype);
                $imgtype = $temp[1];
            }
            //设置随机生成的文件名
            if (!empty($imgtype)) {
                $namelength = 15;
                $key = '';
                $keys = array_merge(range(0, 9), range('a', 'z'));
                for ($i = 0; $i < $namelength; $i++) {
                    $key .= $keys[array_rand($keys)];
                }
                $img = base64_decode($url);
                $path = './uploadimage/' . $key . '.' . $imgtype;
                if (@$fp = fopen($path, 'w+')) {
                    fwrite($fp, $img);
                    fclose($fp);
                }
                $url = $key . '.' . $imgtype;
            }
            if (!empty($imgtype)) {
                $sql = "INSERT INTO info_feed (pid,text,type,timestamp,reply,url,imgtype,likenum,user_token,hidden,tag)  VALUES ('{$insertpid}','{$text}','{$type}','{$timestamp}','0','{$url}','{$imgtype}','0','{$user_token}','0','{$tag}')";
            } else {
                $sql = "INSERT INTO info_feed (pid,text,type,timestamp,reply,likenum,user_token,hidden,tag) VALUES ('{$insertpid}','{$text}','{$type}','{$timestamp}','0','0','{$user_token}','0','{$tag}')";
            }
            if (mysqli_query($conn, $sql)) {
                $outtext = '发表树洞成功';
                array_push($jarry, $outtext);
                $outarray = array("code" => 0, "data" => $jarry);
                $json = json_encode($outarray);
                echo $json;
            } else {
                $outtext = '发表树洞失败';
                array_push($jarry, $outtext);
                $outarray = array("code" => 0, "data" => $jarry);
                $json = json_encode($outarray);
                echo $json;
            }
        } else {
            $outtext = '您已被禁言';
            array_push($jarry, $outtext);
            $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
            $json = json_encode($outarray);
            echo $json;
        }
        $conn->close();
        break;
    case 'do_comment':

        //拿到传输的数据
        $user_token = $_REQUEST['user_token'];
        $text = $_REQUEST['text'];
        $timestamp = $_REQUEST['jsapiver'];
        $pid = $_REQUEST['pid'];

        //先判断用户是否有权限评论
        $sql_is_authority = mysqli_query($conn, "SELECT user_token,is_forbid FROM user_info WHERE user_token='{$user_token}'");
        $authority_query = mysqli_fetch_assoc($sql_is_authority);

        //得到该树洞的相应信息
        $get_lz_token = mysqli_query($conn, "SELECT user_token,pid,can_comment FROM info_feed WHERE pid='{$pid}'");
        $get_lz = mysqli_fetch_assoc($get_lz_token);

        if ($authority_query != NULL && $authority_query['is_forbid'] == 0 && $get_lz['can_comment'] == 0 && ($check_now_time - $timestamp <= 10)) {

            //定义插入的评论号数
            $nowcid = 1000;
            $cid = mysqli_query($conn, "SELECT cid FROM comment ORDER BY cid DESC LIMIT 1");
            while ($row = mysqli_fetch_assoc($cid)) {
                $nowcid = $row['cid'];
                break;
            }
            $insertcid = $nowcid + 1;

            //判断是否是洞主，如果是则不增加评论数量
            if ($user_token == $get_lz['user_token']) {
                $insertname = '洞主';
                $addname = '[' . '洞主' . ']';
                $text = $addname . ' ' . $text;
                $sql = "INSERT INTO comment (cid,pid,text,timestamp,name,islz,user_token) VALUES ('{$insertcid}','{$pid}','{$text}','{$timestamp}','{$insertname}','1','{$user_token}')";
                if (mysqli_query($conn, $sql)) {
                    $outtext = '评论树洞成功';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 0, "data" => $jarry);
                    $json = json_encode($outarray);
                    echo $json;
                } else {
                    $outtext = '评论树洞失败';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 0, "data" => $jarry);
                    $json = json_encode($outarray);
                    echo $json;
                }
                mysqli_query($conn, "UPDATE info_feed SET reply=reply+1 WHERE pid='{$pid}'");
            } else {
                //不是洞主，判断当前用户在该树洞是否已经有对应的昵称
                $CommentName = mysqli_query($conn, "SELECT user_token,pid,name FROM comment WHERE pid='{$pid}' AND user_token='{$user_token}'");
                $is_have_name = mysqli_fetch_assoc($CommentName);

                //当前树洞没有该user_token用户的评论
                if ($is_have_name == NULL) {
                    //待选名字
                    $OptionalName = array("Ann", "Barry", "Cecil", "Diana", "Eric", "Felix", "Gail", "Helen", "Ian", "Jay", "Karl", "Lois", "Mabel", "Neer", "Orley", "Pavi", "Quron", "Rayla", "Sean", "Tony", "Uri", "Victor", "Walter", "Xayn", "Yazen", "Zana");
                    $OptionalEmoji = array("🤗", "😊", "😃", "😄", "😆", "😂", "🤣", "😭", "🤔", "😌", "🥰", "😍", "😘", "😚", "😜", "😝", "😔", "🙃", "🤓", "😏", "😉", "🙂", "😁", "🥺", "🤕", "🥱");

                    //获取当前pid共有几条评论,以便给用户分配当前树洞的昵称
                    $Count = 0;
                    $CountEmoji = '';
                    $CommentCount = mysqli_query($conn, "SELECT DISTINCT name FROM comment WHERE pid='{$pid}' AND islz='0' ");

                    $Count = mysqli_num_rows($CommentCount);
                    if ($Count > 25) {
                        $CountEmoji = intval($Count / 26) - 1;
                        $CountName = $Count % 26;
                    }

                    if ($CountEmoji >= 0) {
                        $name1 = $OptionalName[$CountName];
                        $name2 = $OptionalEmoji[$CountEmoji];
                        $insertname = $name2  . $name1;
                        $addname = '[' . $name2  . $name1 . ']';
                        $text = $addname . ' ' . $text;
                    } else {
                        $name = $OptionalName[$Count];
                        $insertname = $name;
                        $addname = '[' . $name . ']';
                        $text = $addname . ' ' . $text;
                    }
                    $sql = "INSERT INTO comment (cid,pid,text,timestamp,name,islz,user_token) VALUES ('{$insertcid}','{$pid}','{$text}','{$timestamp}','{$insertname}','0','{$user_token}')";
                    if (mysqli_query($conn, $sql)) {
                        $outtext = '评论树洞成功';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    } else {
                        $outtext = '评论树洞失败';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                    mysqli_query($conn, "UPDATE info_feed SET reply=reply+1 WHERE pid='{$pid}'");
                } else {
                    $addname = $is_have_name['name'];
                    $text = '[' . $addname . ']' . ' ' . $text;
                    $sql = "INSERT INTO comment (cid,pid,text,timestamp,name,islz,user_token) VALUES ('{$insertcid}','{$pid}','{$text}','{$timestamp}','{$addname}','0','{$user_token}')";
                    if (mysqli_query($conn, $sql)) {
                        $outtext = '评论树洞成功';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    } else {
                        $outtext = '评论树洞失败';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                    mysqli_query($conn, "UPDATE info_feed SET reply=reply+1 WHERE pid='{$pid}'");
                }
            }
        } else {
            $outtext = '您没有权限';
            array_push($jarry, $outtext);
            $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
            $json = json_encode($outarray);
            echo $json;
        }

        $conn->close();
        break;
    case 'search':
        //对keywords是Pid还是文字作不同的判断
        $keywords = $_REQUEST['keywords'];
        $page = $_REQUEST['page'];
        $pagesize = $_REQUEST['pagesize'];

        //当搜索pid时
        if (empty($keywords)) {
            $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE pid=0";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                array_push($jarry, $row);
            };
            $outarray = array("code" => 0, "data" => $jarry);
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            exit();
        } elseif (substr($keywords, 0, 1) == '#') {
            $pid = substr($keywords, 1);
            $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE pid='{$pid}' ORDER BY timestamp DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                array_push($jarry, $row);
            };
            $outarray = array("code" => 0, "data" => $jarry);
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            exit();
        } else {
            $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE text LIKE '%$keywords%' LIMIT $pagesize ORDER BY timestamp DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                array_push($jarry, $row);
            };
            $outarray = array("code" => 0, "data" => $jarry);
            $json = json_encode($outarray);
            echo $json;
            $conn->close();
            exit();
        }
        break;
    case 'getone':
        $pid = $_REQUEST['pid'];
        $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE pid='{$pid}'";
        $result = $conn->query($sql);
        $outarray;
        while ($row = $result->fetch_assoc()) {
            $outarray = array("code" => 0, "data" => $row);
        };
        $json = json_encode($outarray);
        echo $json;
        $conn->close();
        exit();
        break;
    case 'attention':
        $user_token = $_REQUEST['user_token'];
        $pid = $_REQUEST['pid'];
        $switch = $_REQUEST['switch'];
        $timestamp = $_REQUEST['jsapiver'];

        //对user_token进行判断，如果存在且没有被forbid才可以关注，否则发布失败
        $sql_is_authority = mysqli_query($conn, "SELECT user_token,is_forbid FROM user_info WHERE user_token='{$user_token}'");
        $authority_query = mysqli_fetch_assoc($sql_is_authority);

        //如果当前用户有权限关注
        if ($authority_query != NULL && $authority_query['is_forbid'] == 0 && ($check_now_time - $timestamp <= 10)) {
            if ($switch) {
                $sql = "INSERT INTO user_attention (user_token,pid) VALUES ('{$user_token}','{$pid}')";
                if (mysqli_query($conn, $sql)) {
                    $sqd = "UPDATE info_feed SET likenum=likenum+1 WHERE pid='{$pid}'";
                    if (mysqli_query($conn, $sqd)) {
                        $outtext = '关注成功';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                } else {
                    $outtext = '关注失败';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 1, "data" => $jarry);
                    $json = json_encode($outarray);
                    echo $json;
                }
                $conn->close();
            } else {
                $check_is_mine = mysqli_query($conn, "SELECT pid,user_token FROM info_feed WHERE pid='{$pid}'");
                $check_mine = mysqli_fetch_assoc($check_is_mine);
                if ($user_token != $check_mine['user_token']) {
                    $sql = "DELETE FROM user_attention WHERE user_token='{$user_token}' AND pid='{$pid}'";
                    if (mysqli_query($conn, $sql)) {
                        $sqd = "UPDATE info_feed SET likenum=likenum-1 WHERE pid='{$pid}'";
                        if (mysqli_query($conn, $sqd)) {
                            $outtext = '取消关注成功';
                            array_push($jarry, $outtext);
                            $outarray = array("code" => 0, "data" => $jarry);
                            $json = json_encode($outarray);
                            echo $json;
                        }
                    } else {
                        $outtext = '取消关注失败';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                } else {
                    $outtext = '自动关注本人发表的树洞';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
                    $json = json_encode($outarray);
                    echo $json;
                }
                $conn->close();
            }
        } else {
            $outtext = '您没有权限';
            array_push($jarry, $outtext);
            $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
            $json = json_encode($outarray);
            echo $json;
        }
        break;
    case 'get_attention':
        $user_token = $_REQUEST['user_token'];
        $sql = "SELECT att.*, info.* FROM user_attention att INNER JOIN info_feed info ON info.pid=att.pid WHERE att.user_token='{$user_token}' ORDER BY timestamp DESC ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            unset($row['user_token']);
            array_push($jarry, $row);
        };
        $outarray = array("code" => 0, "data" => $jarry);
        $json = json_encode($outarray);
        echo $json;
        $conn->close();
        break;
    case 'get_mine':
        $user_token = $_REQUEST['user_token'];
        $sql = "SELECT pid,timestamp,text,type,likenum,reply,url,imgtype,hidden,extra,tag FROM info_feed WHERE user_token='{$user_token}' ORDER BY timestamp DESC ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            array_push($jarry, $row);
        };
        $outarray = array("code" => 0, "data" => $jarry);
        $json = json_encode($outarray);
        echo $json;
        $conn->close();
        break;
    case 'report':
        $user_token = $_REQUEST['user_token'];
        $pid = $_REQUEST['pid'];
        $reason = $_REQUEST['reason'];
        $timestamp = $_REQUEST['jsapiver'];

        //对user_token进行判断，如果存在且没有被forbid才可以举报，否则发布失败
        $sql_is_authority = mysqli_query($conn, "SELECT user_token,is_forbid FROM user_info WHERE user_token='{$user_token}'");
        $authority_query = mysqli_fetch_assoc($sql_is_authority);

        //如果当前用户有权限举报
        if ($authority_query != NULL && $authority_query['is_forbid'] == 0 && ($check_now_time - $timestamp <= 10)) {

            //判断当天是否有举报额度
            //获取当前时间，用于判断当天是否还剩举报额度
            $now_time = time();
            $get_report_info = mysqli_query($conn, "SELECT * FROM report WHERE from_user_token='{$user_token}' ORDER BY timestamp DESC ");
            $get_report = mysqli_fetch_assoc($get_report_info);

            //剩余举报额度
            if (empty($get_report) || ($now_time - $get_report['timestamp'] >= 86400)) {

                //判断当前树洞该用户是否已经举报过
                $judge_report_info = mysqli_query($conn, "SELECT * FROM report WHERE pid='{$pid}' AND from_user_token = '{$user_token}'");
                $judge_report = mysqli_fetch_assoc($judge_report_info);

                //如果没有举报过
                if (empty($judge_report)) {

                    //获取被举报树洞的洞主的token
                    $get_dz_token = mysqli_query($conn, "SELECT pid,user_token FROM info_feed WHERE pid='{$pid}'");
                    $get_dz = mysqli_fetch_assoc($get_dz_token);
                    $reported_token = $get_dz['user_token'];

                    //在report表中插入本次的举报信息
                    $sql = "INSERT INTO report (pid,reason,timestamp,from_user_token,user_token)  VALUES ('{$pid}','{$reason}','{$timestamp}','{$user_token}','{$reported_token}')";
                    if (mysqli_query($conn, $sql)) {
                        $outtext = '举报成功';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry, "msg" => $outtext);
                        $json = json_encode($outarray);
                        echo $json;
                    } else {
                        $outtext = '举报失败';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 1, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    }

                    //对应更新user_info中被举报者的被举报数
                    mysqli_query($conn, "UPDATE user_info SET report_nums=report_nums+1 WHERE user_token='{$reported_token}'");

                    //与当前用户无关，搜集被举报用户的信息，判断是否要禁言或者屏蔽ta的所有树洞
                    $get_reported_user_info = mysqli_query($conn, "SELECT user_token,report_nums FROM user_info WHERE user_token='{$reported_token}'");
                    $get_reported_user = mysqli_fetch_assoc($get_reported_user_info);
                    if ($get_reported_user['report_nums'] >= 2) {
                        mysqli_query($conn, "UPDATE user_info SET is_forbid=1 WHERE user_token='{$reported_token}'");
                    }
                    if ($get_reported_user['report_nums'] >= 3) {
                        mysqli_query($conn, "UPDATE info_feed SET hidden=1 WHERE pid='{$pid}'");
                    }
                    if ($get_reported_user['report_nums'] >= 6) {
                        mysqli_query($conn, "UPDATE info_feed SET hidden=1 WHERE user_token='{$reported_token}'");
                    }
                }
                //当前树洞已经举报过 
                else {
                    $outtext = '一条树洞只能举报一次';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
                    $json = json_encode($outarray);
                    echo $json;
                }
            }
            //当天不剩余举报额度 
            else {
                $outtext = '一天只能举报一次';
                array_push($jarry, $outtext);
                $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
                $json = json_encode($outarray);
                echo $json;
            }
        } else {
            $outtext = '您没有权限';
            array_push($jarry, $outtext);
            $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
            $json = json_encode($outarray);
            echo $json;
        }
        $conn->close();
        break;
    case 'set_like':
        $user_token = $_REQUEST['user_token'];
        $pid = $_REQUEST['pid'];
        $switch = $_REQUEST['switch'];
        $timestamp = $_REQUEST['jsapiver'];

        //对user_token进行判断，如果存在且没有被forbid才可以关注，否则操作失败
        $sql_is_authority = mysqli_query($conn, "SELECT user_token,is_forbid FROM user_info WHERE user_token='{$user_token}'");
        $authority_query = mysqli_fetch_assoc($sql_is_authority);

        //如果当前用户有权限点赞
        if ($authority_query != NULL && $authority_query['is_forbid'] == 0 && ($check_now_time - $timestamp <= 10)) {
            if ($switch=='1') {
                $sql = "INSERT INTO user_like (user_token,pid) VALUES ('{$user_token}','{$pid}')";
                if (mysqli_query($conn, $sql)) {
                    $sqd = "UPDATE info_feed SET likes=likes+1 WHERE pid='{$pid}'";
                    if (mysqli_query($conn, $sqd)) {
                        $outtext = '点赞成功';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                } else {
                    $outtext = '点赞失败';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
                    $json = json_encode($outarray);
                    echo $json;
                }
                $conn->close();
            } else {
                $sql = "DELETE FROM user_like WHERE user_token='{$user_token}' AND pid='{$pid}'";
                if (mysqli_query($conn, $sql)) {
                    $sqd = "UPDATE info_feed SET likes=likes-1 WHERE pid='{$pid}'";
                    if (mysqli_query($conn, $sqd)) {
                        $outtext = '取消点赞成功';
                        array_push($jarry, $outtext);
                        $outarray = array("code" => 0, "data" => $jarry);
                        $json = json_encode($outarray);
                        echo $json;
                    }
                } else {
                    $outtext = '取消点赞失败';
                    array_push($jarry, $outtext);
                    $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
                    $json = json_encode($outarray);
                    echo $json;
                }
                $conn->close();
            }
        } else {
            $outtext = '您没有权限';
            array_push($jarry, $outtext);
            $outarray = array("code" => 1, "data" => $jarry, "msg" => $outtext);
            $json = json_encode($outarray);
            echo $json;
        }
        break;
}
