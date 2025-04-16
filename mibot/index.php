<?php
$TELEGRAM_TOKEN = '8053676303:AAF2zoZsdsgaDPuAHpfOkWm5EgLclbjhHBI';
$API_TASAS_URL = 'https://pydolarve.org/api/v2/dollar';

// Recibir datos de Telegram
$update = json_decode(file_get_contents('php://input'), true);

if(isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'] ?? '';
    
    // Comandos
    switch($text) {
        case '/start':
            $response = "🪙 *Bot de Tasas de Venezuela* \n\n";
            $response .= "Comandos disponibles:\n";
            $response .= "/tasas - Ver todas las tasas\n";
            $response .= "/bcv - Tasa oficial BCV\n";
            $response .= "/paralelo - Tasa del mercado paralelo";
            sendMessage($chat_id, $response, 'Markdown');
            break;
            
        case '/tasas':
        case '/bcv':
        case '/paralelo':
            $rates = getRates();
            if(!$rates) {
                sendMessage($chat_id, "⚠️ Error al obtener tasas. Intenta más tarde.");
            } else {
                sendRates($chat_id, $rates, $text);
            }
            break;
            
        default:
            sendMessage($chat_id, "❌ Comando no reconocido. Usa /start para ayuda.");
    }
}

// Obtener tasas de la API
function getRates() {
    global $API_TASAS_URL;
    $data = file_get_contents($API_TASAS_URL);
    return json_decode($data, true);
}

// Enviar tasas formateadas
function sendRates($chat_id, $rates, $command) {
    $bcv = $rates['monitors']['bcv'];
    $paralelo = $rates['monitors']['enparalelovzla'];
    
    $formatRate = function($rate) {
        return number_format($rate['price'], 2, ',', '.') . " VES/USD\n" .
               "➤ Cambio: " . $rate['symbol'] . " " . 
               number_format($rate['change'], 2, ',', '.') . 
               " (" . number_format($rate['percent'], 2, ',', '.') . "%)";
    };
    
    switch($command) {
        case '/bcv':
            $message = "🏦 *Tasa BCV (Oficial)*:\n" . $formatRate($bcv);
            break;
            
        case '/paralelo':
            $message = "💱 *Tasa Paralelo*:\n" . $formatRate($paralelo);
            break;
            
        default:
            $message = "📊 *Tasas de Venezuela*:\n\n" .
                       "🏦 *BCV (Oficial)*:\n" . $formatRate($bcv) . "\n\n" .
                       "💱 *Paralelo*:\n" . $formatRate($paralelo);
    }
    
    $message .= "\n\n🕒 Actualizado: " . $rates['datetime']['time'];
    sendMessage($chat_id, $message, 'Markdown');
}

// Función para enviar mensajes
function sendMessage($chat_id, $text, $parse_mode = '') {
    global $TELEGRAM_TOKEN;
    $url = "https://api.telegram.org/bot$TELEGRAM_TOKEN/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $parse_mode
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}
?>