<?php

namespace App\Handlers;

use App\Services\SpringTtsClient;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use Throwable;

class TelegramTtsHandler
{
    public function __construct(private SpringTtsClient $ttsClient)
    {
    }

    public function handleText(Nutgram $bot): void
    {
        $text = $bot->message()?->text;
        if (empty($text)) {
            return;
        }

        $bot->sendChatAction(ChatAction::RECORD_VOICE);
        $wavPath = null;

        try {
            $wavPath = $this->ttsClient->synthesizeText($text);

            $bot->sendAudio(
                audio: InputFile::make($wavPath),
                title: "Message"
            );
        } catch (Throwable $e) {
            $bot->sendMessage("❌ Errore durante la generazione audio:\n" . $e->getMessage());
        } finally {
            if ($wavPath && file_exists($wavPath)) {
                unlink($wavPath);
            }
        }
    }

    public function handleDocument(Nutgram $bot): void
    {
        $document = $bot->message()?->document;
        if (!$document) {
            return;
        }

        $fileName = $document->file_name ?? 'documento.txt';
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Controllo preventivo prima di scaricare il file
        if (!in_array($extension, ['txt', 'docx'], true)) {
            $bot->sendMessage("⚠️ Formato non supportato. Inviami solo file **.txt** o **.docx**.", parse_mode: 'Markdown');
            return;
        }

        $bot->sendChatAction(ChatAction::RECORD_VOICE);

        $tempInputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('in_') . '_' . $fileName;
        $wavPath = null;

        try {
            // Scarica il file dai server di Telegram in locale
            $telegramFile = $bot->getFile($document->file_id);
            $telegramFile->save($tempInputPath);

            // Invialo a Spring Boot (POST /api/tts/file)
            $wavPath = $this->ttsClient->synthesizeFile($tempInputPath, $fileName);

            // Manda il .wav risultante in chat
            $cleanTitle = pathinfo($fileName, PATHINFO_FILENAME);
            $bot->sendAudio(
                audio: InputFile::make($wavPath),
                title: $cleanTitle
            );
        } catch (Throwable $e) {
            $bot->sendMessage("❌ Errore nell'elaborazione del file:\n" . $e->getMessage());
        } finally {
            // Pulizia di entrambi i file
            if (file_exists($tempInputPath)) {
                unlink($tempInputPath);
            }
            if ($wavPath && file_exists($wavPath)) {
                unlink($wavPath);
            }
        }
    }
}
