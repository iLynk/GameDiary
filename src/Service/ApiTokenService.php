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
    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient, string $clientId, string $clientSecret)
    {
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    // FONCTION POUR RECUPERER LE TOKEN VALIDE
    public function getToken(): string
    {
        // On regarde s'il existe un token valide en BDD
        $tokenRepo = $this->entityManager->getRepository(ApiToken::class);
        $token = $tokenRepo->find(1);

        if (!$token || $token->getExpiresAt() <= new \DateTime()) {
            // Si le token n'existe pas et/ou est expiré, on le régen
            return $this->refreshToken();
        }

        return $token->getAccessToken();
    }

    // FONCTION POUR REGENERER LE TOKEN 
    private function refreshToken(): string
    {
        // On envoie la requête à l'API de twitch pour obtenir un nouveau token
        $response = $this->httpClient->request('POST', 'https://id.twitch.tv/oauth2/token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]
        ]);

        $data = $response->toArray();
        // on récupère le token et le delai d'expiration
        $accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'];

        // On met à jour le token en BDD ou on crée si il n'y en a pas ()
        $tokenRepo = $this->entityManager->getRepository(ApiToken::class);
        $token = $tokenRepo->find(1) ?? new ApiToken(); // opérateur sql coalesce
        $token->setAccessToken($accessToken);
        $token->setExpiresAt((new \DateTime())->add(new \DateInterval('PT' . $expiresIn . 'S')));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $accessToken;
    }
}
