<?php
// Your bot token
$botToken = "8106825482:AAFo65SYkGn7OtftJWlL9NlNzNPvHsW4Cn4";
$apiURL = "https://api.telegram.org/bot$botToken/";

// Read Telegram incoming update
$update = json_decode(file_get_contents("php://input"), TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;
$text = $update["message"]["text"] ?? null;
$user = $update["message"]["from"]["first_name"] ?? "User";

// Basic response
if ($chatId && $text) {
    
    // Command-based responses
    switch (strtolower($text)) {
        case "/start":
            $reply = "Hi $user! Welcome to SohilGPT Music & Entertainment Bot!";
            break;
        case "/help":
            $reply = "Use commands:\n- /image to generate image\n- /music to listen music\n- /about";
            break;
        case "/about":
            $reply = "I'm an advanced entertainment bot with ChatGPT, images, music & more! Made By @SohilCodes";
            break;
        case "/image":
            $reply = "Image generation feature coming soon!";
            break;
        case "/music":
            $reply = "Music feature coming soon! Stay tuned.";
            break;
        default:
            $reply = "You said: $text";
    }

    // Send reply
    file_get_contents($apiURL . "sendMessage?chat_id=$chatId&text=" . urlencode($reply));
}
?>
