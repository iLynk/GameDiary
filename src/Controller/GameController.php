<?php

// src/Controller/GameController.php
namespace App\Controller;

use App\Service\ApiTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private ApiTokenService $apiTokenService;
    private string $apiUrl;
    private string $clientId;

    public function __construct(HttpClientInterface $httpClient, ApiTokenService $apiTokenService, string $apiUrl, string $clientId)
    {
        $this->httpClient = $httpClient;
        $this->apiTokenService = $apiTokenService;
        $this->apiUrl = $apiUrl;
        $this->clientId = $clientId;
    }

    #[Route('/games', name: 'app_game')]
    public function index(): Response
    {
        // Appel à fetchGames pour récupérer les jeux
        $games = $this->fetchGames();
        dd($games);
        // Retourne la vue avec les jeux
        return $this->render('game/index.html.twig', [
            'games' => $games,
        ]);
    }


    private function fetchGames(): array
    {
        // Get valid token
        $accessToken = $this->apiTokenService->getToken();

        // Make API request to IGDB
        $response = $this->httpClient->request('POST', $this->apiUrl . '/games', [
            'headers' => [
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => 'fields name,genres,platforms; limit 10;'
        ]);

        // Retourne les jeux sous forme de tableau
        return $response->toArray();
    }
}

