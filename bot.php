<?php
date_default_timezone_set("Asia/Kolkata");

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
$API_CC_PR = getenv('API_KEY'); // Obtener la clave de API de la variable de entorno

if ($message == "/start") {
    send_message($chat_id, $message_id, "🍪 Hola $firstname !
Usa /bin xxxxx para verificar tu BIN.

🌐 Bot creado por @soportecookies
🛒 Visita nuestra tienda @cookiesautoshopBot");
}

// Bin Lookup
if (strpos($message, "/bin") === 0) {
    $bin = substr($message, 5);
    $api_url = "https://api.chk.cards/v1/bins?key=$API_CC_PR&bin=$bin";

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

        $output_message = "🍪Bin: $bin
***💳Brand***: $brand
```💰Type```: $type
🏆Level: $level
🏦Bank: $bank
🌐Country: $country";

        send_message($chat_id, $message_id, $output_message);
    } else {
        send_message($chat_id, $message_id, "***Enter Valid BIN***");
    }
}

function send_message($chat_id, $message_id, $message)
{
    $text = urlencode($message);
    $apiToken = $_ENV['API_TOKEN'];

    // Agrega el formato Markdown
    file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?chat_id=$chat_id&reply_to_message_id=$message_id&text=$text&parse_mode=Markdown");
}
?>
