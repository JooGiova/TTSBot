# 🤖 Telegram Piper TTS Bot (PHP)

Bot Telegram sviluppato in **PHP 8+** che funge da interfaccia conversazionale per il servizio backend [TTS Service API (Spring Boot + Piper TTS)](https://github.com/joogiova/ttsservice).

Permette agli utenti di inviare messaggi di testo o documenti (`.txt`, `.docx`) direttamente in chat e di ricevere in risposta la sintesi vocale generata in locale sotto forma di file audio `.wav`.

## ✨ Funzionalità principali

* **Sintesi vocale da chat:** Converte qualsiasi messaggio di testo inviato al bot in un file audio tramite l'endpoint `POST /api/tts`.
* **Lettura documenti (`.txt` e `.docx`):** Scarica i file inviati in chat, li inoltra all'endpoint `POST /api/tts/file` del backend Spring Boot e restituisce l'audio generato.
* **Modalità Long Polling:** Progettato per essere eseguito facilmente in locale o su server senza richiedere configurazioni di rete complesse, domini pubblici o certificati SSL.
* **Gestione pulita delle risorse:** Salvataggio temporaneo dei file in transito con pulizia automatica garantita al termine di ogni operazione.
* **Architettura modulare PSR-4:** Codice suddiviso in `Services` e `Handlers` con gestione delle variabili d'ambiente tramite `.env`.

## 🛠 Stack Tecnologico

* **Linguaggio:** PHP 8.2+ (CLI con estensioni `curl`, `mbstring`, `json`)
* **Bot Framework:** [Nutgram](https://nutgram.dev) (`^4.51`)
* **HTTP Client:** Guzzle HTTP (incluso in Nutgram)
* **Configurazione:** `vlucas/phpdotenv` (`^5.8`)
* **Dependency Manager:** Composer (autoloading PSR-4)

## 📂 Struttura del Progetto

```text
TTSBot/
├── src/
│   ├── Handlers/
│   │   └── TelegramTtsHandler.php   # Gestione eventi Telegram (testo, documenti, risposte)
│   └── Services/
│       └── SpringTtsClient.php      # Client HTTP Guzzle verso le API REST Spring Boot
├── .env.example                     # Modello per le variabili d'ambiente
├── .gitignore
├── bot.php                          # Entry-point e configurazione Polling
├── composer.json
└── composer.lock
```

## 🚀 Installazione e Avvio

### 1. Prerequisiti

* **PHP 8.2** o superiore installato nel sistema (con estensione `php-curl` abilitata).
* **Composer** installato globalmente.
* Il backend **[ttsservice](https://github.com/joogiova/ttsservice)** avviato e raggiungibile (es. `http://localhost:8080`).
* Un **Token API** ottenuto tramite [@BotFather](https://t.me) su Telegram.

### 2. Configurazione

Clona il repository e installa le dipendenze PHP tramite Composer:

```bash
git clone https://github.com/joogiova/TTSBot
cd TTSBot
composer install
```

Crea il file `.env` partendo dal modello di esempio:

```bash
cp .env.example .env
```

Apri il file `.env` e compila i parametri richiesti:

```env
TELEGRAM_TOKEN=inserisci_qui_il_tuo_token
SPRING_BASE_URL=http://localhost:8080
DEFAULT_VOICE=it_IT-paola-medium.onnx
```

### 3. Avvio del Bot

Assicurati che il servizio Spring Boot sia in esecuzione, quindi avvia il bot in modalità Polling dal terminale:

```bash
php bot.php
```

Il terminale resterà in ascolto dei nuovi messaggi. Per interrompere l'esecuzione, premi `Ctrl + C`.

## 💬 Utilizzo su Telegram

1. Avvia una chat con il tuo bot e digita `/start`.
2. **Per convertire un testo:** scrivi un qualsiasi messaggio in chat e attendi la generazione dell'audio.
3. **Per convertire un documento:** allega un file con estensione `.txt` o `.docx` (dimensione massima consentita da Telegram: 20 MB).

## 🔮 Sviluppi Futuri

* [ ] Comando `/voice` con tastiera inline per permettere all'utente di scegliere dinamicamente il modello vocale.
* [ ] Supporto allo spezzamento automatico di documenti molto lunghi in più tracce audio numerate.
* [ ] Conversione opzionale in formato `.ogg` (Opus) per l'invio come messaggio vocale nativo (`sendVoice`).
