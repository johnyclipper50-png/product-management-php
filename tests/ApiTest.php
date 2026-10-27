<?php

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private $baseUrl = 'http://localhost/product';

    // Helper fungsi untuk melakukan HTTP Request (cURL)
    private function makeRequest($url, $method = 'GET', $data = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    // 1. Test Endpoint index dengan Kredensial Benar
    public function testindexSuccess()
    {
        $payload = [
            'email' => 'admin@inventory.co.id',
            'password' => 'admin'
        ];

        $response = $this->makeRequest($this->baseUrl . '/index.php', 'POST', $payload);

        $this->assertEquals(200, $response['code']);
        $this->assertEquals('success', $response['body']['status']);
        $this->assertArrayHasKey('token', $response['body']);
    }

    // 2. Test Endpoint index dengan Password Salah
    public function testindexFailedInvalidPassword()
    {
        $payload = [
            'email' => 'admin@inventory.co.id',
            'password' => 'password_salah'
        ];

        $response = $this->makeRequest($this->baseUrl . '/index.php', 'POST', $payload);

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('error', $response['body']['status']);
    }

    // 3. Test Endpoint Statistik Produk (GET)
    public function testGetProductStatistics()
    {
        $response = $this->makeRequest($this->baseUrl . '/api_stats.php', 'GET');

        $this->assertEquals(200, $response['code']);
        $this->assertEquals('success', $response['body']['status']);
        $this->assertArrayHasKey('total_produk', $response['body']['data']);
        $this->assertArrayHasKey('produk_tersedia', $response['body']['data']);
        $this->assertArrayHasKey('produk_habis', $response['body']['data']);
    }

    // 4. Test Method Not Allowed pada Endpoint Statistik
    public function testStatsMethodNotAllowed()
    {
        $response = $this->makeRequest($this->baseUrl . '/api_stats.php', 'POST');

        $this->assertEquals(405, $response['code']);
        $this->assertEquals('error', $response['body']['status']);
    }
}