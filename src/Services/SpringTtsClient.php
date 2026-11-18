<?php

namespace App\Services;

use GuzzleHttp\Client;
use RuntimeException;

class SpringTtsClient
{
    private Client $http;
    private string $defaultVoice;
    private string $tempDir;

    public function __construct(string $baseUrl, string $defaultVoice)
    {
        $this->defaultVoice = $defaultVoice;
        $this->tempDir = sys_get_temp_dir();

        $this->http = new Client([
            'base_uri'    => rtrim($baseUrl, '/'),
            'timeout'     => 60.0, // Timeout alto per dare tempo a Piper di generare l'audio
            'http_errors' => false
        ]);
    }

    /**
     * Chiama POST /api/tts
     */
    public function synthesizeText(string $text, ?string $voice = null): string
    {
        $response = $this->http->post('/api/tts', [
            'headers' => ['Accept' => 'audio/wav, application/json'],
            'json' => [
                'text'  => $text,
                'voice' => $voice ?? $this->defaultVoice,
                'filename' => 'Message'
            ]
        ]);

        return $this->handleAudioResponse($response);
    }

    /**
     * Chiama POST /api/tts/file
     */
    public function synthesizeFile(string $localFilePath, string $originalName, ?string $voice = null): string
    {
        $multipart = [
            [
                'name'     => 'file',
                'contents' => fopen($localFilePath, 'r'),
                'filename' => $originalName
            ]
        ];

        // TODO
        if ($voice !== null) {
            $multipart[] = [
                'name'     => 'voice',
                'contents' => $voice
            ];
        }

        $response = $this->http->post('/api/tts/file', [
            'headers'   => ['Accept' => 'audio/wav, application/json'],
            'multipart' => $multipart
        ]);

        return $this->handleAudioResponse($response);
    }

    /**
     * Controlla lo status code di Spring Boot:
     * 200 -> salva i byte in un .wav temporaneo e ritorna il percorso
     * Errore -> legge il tuo ErrorResponse JSON e lancia un'eccezione leggibile
     */
    private function handleAudioResponse($response): string
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode === 200) {
            $outputPath = $this->tempDir . DIRECTORY_SEPARATOR . 'tts_' . uniqid() . '.wav';
            file_put_contents($outputPath, $body);
            return $outputPath;
        }

        $errorData = json_decode($body, true);
        $errorMessage = $errorData['message'] ?? $errorData['error'] ?? "Errore HTTP $statusCode dal server TTS.";

        throw new RuntimeException($errorMessage);
    }
}
