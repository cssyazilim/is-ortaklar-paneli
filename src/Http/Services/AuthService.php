<?php
// src/Services/AuthService.php
require_once __DIR__ . '/../Http/HttpClient.php';

class AuthService {
  private HttpClient $http;

  public function __construct() {
    $this->http = new HttpClient();
  }

  /**
   * API login -> access/refresh/session + user döner
   * @return array ['accessToken','refreshToken','sessionId','refreshExpiresAt','user'=>[]]
   */
  public function login(string $email, string $password): array {
    $url = rtrim(API_BASE, '/') . '/api/auth/login';
    $payload = [
      'email' => $email,
      'password' => $password,
    ];
    return $this->http->postJson($url, $payload);
  }
}
