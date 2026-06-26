<?php

namespace App\Libraries;

/**
 * GeminiService
 *
 * Centraliza todas as chamadas à API Gemini.
 * Configuração via .env:
 *   gemini.apiKey   = AQ.xxx
 *   gemini.model    = gemini-3.1-flash-lite
 *   gemini.endpoint = https://generativelanguage.googleapis.com/v1beta/models
 */
class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey   = env('gemini.apiKey', '');
        $this->model    = env('gemini.model', 'gemini-3.1-flash-lite');
        $this->endpoint = env('gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');
    }

    // ─────────────────────────────────────────────────────────────────
    // Método principal: gera conteúdo a partir de um prompt simples
    // ─────────────────────────────────────────────────────────────────

    /**
     * @param string $prompt      Texto do prompt
     * @param array  $config      Configurações opcionais (temperature, maxOutputTokens, responseMimeType)
     * @param int    $maxRetries  Número de tentativas em caso de rate limit (429)
     * @return string|null        Texto gerado ou null em caso de erro
     */
    public function gerar(string $prompt, array $config = [], int $maxRetries = 3): ?string
    {
        $url = "{$this->endpoint}/{$this->model}:generateContent?key={$this->apiKey}";

        $generationConfig = array_merge([
            'temperature'     => 0.3,
            'maxOutputTokens' => 1500,
        ], $config);

        $payload = json_encode([
            'contents'         => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => $generationConfig,
        ], JSON_UNESCAPED_UNICODE);

        for ($tentativa = 1; $tentativa <= $maxRetries; $tentativa++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $body     = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                log_message('error', "[GeminiService] CURL: {$curlErr}");
                return null;
            }

            if ($httpCode === 429) {
                // Rate limit — espera e tenta novamente
                sleep(15 * $tentativa);
                continue;
            }

            if ($httpCode !== 200) {
                log_message('error', "[GeminiService] HTTP {$httpCode}: " . substr($body, 0, 300));
                return null;
            }

            $data  = json_decode($body, true);
            $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            return $texto;
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────
    // Gera JSON estruturado (remove markdown code fences se presentes)
    // ─────────────────────────────────────────────────────────────────

    public function gerarJson(string $prompt, array $config = []): ?array
    {
        $config['responseMimeType'] = 'application/json';
        $texto = $this->gerar($prompt, $config);

        if ($texto === null) return null;

        // Remove ```json ... ``` se o modelo incluir
        $texto = preg_replace('/^```(?:json)?\s*/m', '', $texto);
        $texto = preg_replace('/```\s*$/m', '', $texto);

        $result = json_decode(trim($texto), true);
        return is_array($result) ? $result : null;
    }

    // ─────────────────────────────────────────────────────────────────
    // Verifica se a chave está configurada
    // ─────────────────────────────────────────────────────────────────

    public function configurado(): bool
    {
        return ! empty($this->apiKey);
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
