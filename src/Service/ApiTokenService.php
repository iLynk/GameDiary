<?php
// src/Service/ApiTokenService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ApiToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiTokenService
{
    private $entityManager;
    private $httpClient;
    private $clientId;
    private $clientSecret;
    private $apiUrl;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient, string $clientId, string $clientSecret, string $apiUrl)
    {
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->apiUrl = $apiUrl;
    }

    public function getToken(): string
    {
        // Check if token exists and is valid
        $tokenRepo = $this->entityManager->getRepository(ApiToken::class);
        $token = $tokenRepo->find(1); // Assuming only one token stored

        if (!$token || $token->getExpiresAt() <= new \DateTime()) {
            // Token is missing or expired, regenerate it
            return $this->refreshToken();
        }

        return $token->getAccessToken();
    }

    private function refreshToken(): string
    {
        // Request a new token from IGDB API
        $response = $this->httpClient->request('POST', 'https://id.twitch.tv/oauth2/token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]
        ]);

        $data = $response->toArray();
        $accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'];

        // Update token in database
        $tokenRepo = $this->entityManager->getRepository(ApiToken::class);
        $token = $tokenRepo->find(1) ?? new ApiToken();
        $token->setAccessToken($accessToken);
        $token->setExpiresAt((new \DateTime())->add(new \DateInterval('PT' . $expiresIn . 'S')));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $accessToken;
    }
}
