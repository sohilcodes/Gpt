<?php

$bot_token = "8106825482:AAFo65SYkGn7OtftJWlL9NlNzNPvHsW4Cn4";
$openai_api_key = "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";
$admin_chat_id = "6411315434";

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update) exit;

// Extract info
$message = $update['message'] ?? $update['callback_query']['message'];
$text = $update['message']['text'] ?? '';
$chat_id = $message['chat']['id'];
$user = $message['from'];
$first_name = $user['first_name'];
$username = $user['username'] ?? 'NoUsername';

// Notify admin for new users
if($text == "/start") {
    sendMessage($chat_id, "Hi $first_name! I’m your music + image AI bot.");
    sendMessage($admin_chat_id, "New user joined:\n@$username\nUser ID: $chat_id");

    $keyboard = [
        'inline_keyboard' => [
            [['text' => '🎧 Music', 'callback_data' => 'music']],
            [['text' => '🧠 ChatGPT', 'callback_data' => 'chat']],
            [['text' => '🎨 Image Generator', 'callback_data' => 'image']]
        ]
    ];
    sendMessage($chat_id, "What would you like to do?", $keyboard);
}

// Handle callback buttons
if(isset($update['callback_query'])) {
    $data = $update['callback_query']['data'];

    if($data == "music") {
        sendAudio($chat_id, "https://file-examples.com/wp-content/uploads/2017/11/file_example_MP3_700KB.mp3", "Chill Music");
    } elseif($data == "chat") {
        sendMessage($chat_id, "Send me any message and I’ll reply like ChatGPT.");
    } elseif($data == "image") {
        sendMessage($chat_id, "Send me a prompt to generate an image.");
    }
}

// ChatGPT & Image generator
if($text && !in_array($text, ["/start"])) {
    if(strpos(strtolower($text), "draw") !== false || strpos(strtolower($text), "image") !== false){
        // Generate image
        $image_url = generateImage($text);
        sendPhoto($chat_id, $image_url, "Here’s your image for: \"$text\"");
    } else {
        // ChatGPT
        $response = chatGPT($text);
        sendMessage($chat_id, $response);
    }
}

// Send text
function sendMessage($chat_id, $text, $keyboard = null) {
    global $bot_token;
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $post = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if($keyboard) $post['reply_markup'] = json_encode($keyboard);
    file_get_contents($url . "?" . http_build_query($post));
}

// Send audio
function sendAudio($chat_id, $audio_url, $title) {
    global $bot_token;
    $url = "https://api.telegram.org/bot$bot_token/sendAudio";
    $post = ['chat_id' => $chat_id, 'audio' => $audio_url, 'caption' => $title];
    file_get_contents($url . "?" . http_build_query($post));
}

// Send photo
function sendPhoto($chat_id, $url, $caption) {
    global $bot_token;
    $send = "https://api.telegram.org/bot$bot_token/sendPhoto?chat_id=$chat_id&photo=" . urlencode($url) . "&caption=" . urlencode($caption);
    file_get_contents($send);
}

// OpenAI ChatGPT
function chatGPT($prompt) {
    global $openai_api_key;
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $openai_api_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]));

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? "Sorry, I didn’t understand.";
}

// OpenAI Image Generator
function generateImage($prompt) {
    global $openai_api_key;
    $ch = curl_init("https://api.openai.com/v1/images/generations");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $openai_api_key"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'prompt' => $prompt,
        'n' => 1,
        'size' => '512x512'
    ]));
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    return $data['data'][0]['url'] ?? '';
}

?>
