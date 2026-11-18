<?php

require __DIR__ . '/vendor/autoload.php';

use App\Handlers\TelegramTtsHandler;
use App\Services\SpringTtsClient;
use Dotenv\Dotenv;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\Telegram\Properties\MessageType;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$ttsClient = new SpringTtsClient(
    baseUrl: $_ENV['SPRING_API_URL'],
    defaultVoice: $_ENV['DEFAULT_VOICE']
);
$handler = new TelegramTtsHandler($ttsClient);

// Configura Nutgram in modalità Polling
$bot = new Nutgram($_ENV['TELEGRAM_TOKEN']);
$bot->setRunningMode(Polling::class);

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage(
        "🎙️ **Benvenuto nel Bot Piper TTS!**\n\n" .
        "Scrivimi un messaggio di testo oppure inviami un file **.txt** o **.docx** e lo trasformerò in un file audio.",
        parse_mode: 'Markdown'
    );
});

$bot->onMessageType(MessageType::TEXT, function (Nutgram $bot) use ($handler) {
    if (str_starts_with($bot->message()->text, '/')) {
        return;
    }
    $handler->handleText($bot);
});

$bot->onDocument([$handler, 'handleDocument']);

// Svuota tutti i messaggi ricevuti mentre il bot era spento
$bot->deleteWebhook(drop_pending_updates: true);

echo "Bot avviato in modalità Polling. Premi Ctrl+C per fermarlo.\n";
$bot->run();
