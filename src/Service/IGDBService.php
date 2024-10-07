<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IGDBService
{
private HttpClientInterface $client;
private string $clientId;
private string $accessToken;
private string $apiUrl;

public function __construct(HttpClientInterface $client, string $clientId, string $accessToken, string $apiUrl)
{
$this->client = $client;
$this->clientId = "r513xzi90fsgjaybw77g3klzbypjqa";
$this->accessToken = "bj34ho3ogfjcg0elo6vppwve0evhfq";
$this->apiUrl = "https://api.igdb.com/v4/";
}

public function fetchGamesData(string $query): array
{
$response = $this->client->request('POST', $this->apiUrl . 'games', [
'headers' => [
'Client-ID' => $this->clientId,
'Authorization' => 'Bearer ' . $this->accessToken,
],
'body' => $query,
]);

return $response->toArray();
}
}
