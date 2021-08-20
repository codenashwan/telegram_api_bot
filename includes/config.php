<?php
session_start();
$db = mysqli_connect("localhost"  , "root" , "" , "telegram");
$token = "YOUR_SECRET_KEY_FROM_BotFather";
function checkUser($id){
    global $db;
    $query = mysqli_query($db , "SELECT `id` FROM `users` WHERE `id` = '$id'");
    return mysqli_num_rows($query);
}
$update = file_get_contents('php://input');
$update = json_decode($update, true);

if(isset($update["message"]["from"]["id"])){
    $userChatId=$update["message"]["from"]["id"];
}else{
    exit("404 Not Found");
}

if($userChatId){
    if(checkUser($userChatId)){
        $id = mysqli_real_escape_string($db , htmlentities($update["message"]["text"]));
        $msg = $id;
        $query = mysqli_query($db , "SELECT * FROM `item` WHERE `barcode` = '$id'");
        if(mysqli_num_rows($query) == 1){
            $row   = mysqli_fetch_array($query);
            $msg = "ناوی کاڵا : " . $row['name'] . "\n" . "نرخی کاڵا : " . $row['price'] . "\n" . "کۆدی کاڵا : " . $row['barcode'] . "\n" . "ئایدی کاڵا : " .$row['id'] . "\n";
            $img = "https://65f38f645752.ngrok.io/telegram/upload/".$row['image'];
            $parameters = array(
                "chat_id" => $userChatId,
                'photo'     => $img
            );
            send("sendPhoto", $parameters);
    }else{
        $msg= "ئەم کاڵایە بوونی نییە";
    }
    }else{
        $msg = "ببورە, ".$update["message"]["from"]["first_name"]." ".$update["message"]["from"]["last_name"]." تۆ ناتوانیت بچیتە ئەم پەیجە چونکە ڕێگەت پێ نەدراوە";
        $msg .= "\n" . "ئایدییەکەت :" . $update["message"]["from"]["id"];
        }
    $parameters = array(
        "chat_id" => $userChatId,
        "text" => $msg,
        "parseMode" => "html"
    );
    send("sendMessage", $parameters);
}

function send($method, $data){
    global $token;
    $url = "https://api.telegram.org/bot$token/$method";
    if(!$curld = curl_init()){
        exit;
    }
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($curld, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curld, CURLOPT_URL, $url);
    curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curld);
    curl_close($curld);
    return $output;
}
?>