<?php
date_default_timezone_set("Asia/kolkata");
// Data From Webhook
$content = file_get_contents("php://input");
$update = json_decode($content, true);
$chat_id = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];
$message_id = $update["message"]["message_id"];
$id = $update["message"]["from"]["id"];
$username = $update["message"]["from"]["username"];
$firstname = $update["message"]["from"]["first_name"];
$start_msg = $_ENV['START_MSG'];

if ($message == "/start") {
    send_message($chat_id, $message_id, "***Hey $firstname \nUse !bin xxxxxx to Check BIN \n$start_msg***");
}

// Bin Lookup
if (strpos($message, "/bin") === 0) {
    $bin = substr($message, 5);
    $api_key = "4964390fd2240937725f366f17fdbf7f5a08f8b5";
    $api_url = "https://api.chk.cards/v1/bins?key=$api_key&bin=$bin";

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);

    $data = json_decode($result, true);

    if ($httpCode == 200) {
        $bank = $data['issuer'];
        $country = strtoupper($data['country']);
        $brand = strtoupper($data['brand']);
        $level = strtoupper($data['level']);
        $type = strtoupper($data['type']);

        send_message($chat_id, $message_id, "***✅ Valid BIN
        Bin: $bin
        Brand: $brand
        Level: $level
        Bank: $bank
        Country: $country
        Type: $type
        Checked By @$username ***");
    } else {
        send_message($chat_id, $message_id, "***Enter Valid BIN***");
    }
}

function send_message($chat_id, $message_id, $message)
{
    $text = urlencode($message);
    $apiToken = $_ENV['API_TOKEN'];
    file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?chat_id=$chat_id&reply_to_message_id=$message_id&text=$text&parse_mode=Markdown");
}
?>
