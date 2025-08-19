<?php
// src/Http/HttpClient.php
class HttpClient {
  private array $defaultHeaders = [
    'Content-Type: application/json',
    'Accept: application/json',
  ];

  public function postJson(string $url, array $payload, array $headers = [], int $timeout = 20): array {
    $ch = curl_init($url);
    $allHeaders = array_merge($this->defaultHeaders, $headers);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => $allHeaders,
      CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
      CURLOPT_TIMEOUT => $timeout,
    ]);

    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
      throw new RuntimeException('Ağ hatası: ' . $err);
    }

    $json = json_decode($raw, true);
    if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
      throw new RuntimeException('Geçersiz JSON yanıtı (HTTP '.$status.'): ' . $raw);
    }

    if ($status < 200 || $status >= 300) {
      $message = $json['message'] ?? $json['error'] ?? ('HTTP '.$status.' hata');
      $details = $json['errors'] ?? null;
      $ex = new RuntimeException($message);
      $ex->code = $status;
      $ex->details = $details;
      throw $ex;
    }

    return $json;
  }
}
