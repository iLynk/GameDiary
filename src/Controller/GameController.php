<?php

// src/Controller/GameController.php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameCategory;
use App\Repository\GameCategoryRepository;
use App\Repository\GameRepository;
use App\Service\ApiTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private ApiTokenService $apiTokenService;
    private string $apiToken;

    public function __construct(HttpClientInterface $httpClient, ApiTokenService $apiTokenService)
    {
        $this->httpClient = $httpClient;
        $this->apiTokenService = $apiTokenService;
        $this->apiToken = $apiTokenService->getToken();
    }

    #[Route('/games', name: 'app_game', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        // Appel à fetchGames pour récupérer les jeux
        $games = $gameRepository->findAll();
        // dd($games);
        // Retourne la vue avec les jeux
        return $this->render('game/index.html.twig', [
            'games' => $games,
        ]);
    }

    // #[Route('/getGames/{id}', requirements:['id' => Requirement::DIGITS], methods:['GET'])] POUR LES REQUIREMENTS AFIN DE CLOISONER LE PARAM URL

    #[Route('/getGames', methods: ['GET'])]
    // FONCTION POUR POPULATE L'ENTITE GAME
    public function getGames(EntityManagerInterface $entityManager, Game $game, GameRepository $gameRepository): JsonResponse
    {
        // on récupère les jeux via l'API 
        $gamesData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/games', [
            'headers' => [
                'Client-ID' => $this->getParameter('igdb_client_id'),
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
            // on adapte les fields pour récuperer seulement ce que l'on veut
            'body' => 'fields id, cover, genres, name, slug, first_release_date, storyline, platforms; limit 500;'
        ]);
        $gamesData = $gamesData->toArray();

        // $allGames = [];
        
        // foreach ($gamesData as $gameData) {
        //     $game = new Game();
        //     $game->setApiId($gameData['id'])
        //     ->setName($gameData['name'])
        //     ->setSlug($gameData['slug'])
        //     ->setReleaseDate($gameData['first_release_date'])
        //     ->setCover($gameData['cover'])
        //     ->setPlatform(['2']);
        // }
        dd($gamesData);
        return $this->json(['tout a marche?' => 'ouiiiiii']);
        
    }
    #[Route('/getCategories', methods: ['GET'])]
    // FONCTION POUR POPULATE L'ENTITE GAMECATEGORY
    public function getCategory(EntityManagerInterface $entityManager, GameCategory $gameCategory, GameCategoryRepository $gameCategoryRepository): JsonResponse
    {
        // on récupère les categories via l'API
        $categories = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/genres', [
            'headers' => [
                'Client-ID' => $this->getParameter('igdb_client_id'),
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
            'body' => 'fields *; limit 500;'
        ]);
        $categories = $categories->toArray();

        // on récupère toutes les catégories existantes
        $allCategories = $gameCategoryRepository->findAllApiId();
        $AllIds = [];
        // foreach afin de faciliter la comparaison par la suite (tableau simple au lieu d'une collection)
        foreach ($allCategories as $allCategory) {
            $AllIds[] = $allCategory['apiId'];
        }
        // on populate l'entite avec les résultats
        foreach ($categories as $category) {
            // on vérifie que la catégorie n'existe pas déjà en bdd
            if (!in_array($category['id'], $AllIds)) {
                $gameCategory = new GameCategory();
                $gameCategory->setApiId($category['id'])
                    ->setName($category['name'])
                    ->setSlug($category['slug']);

                // on prépare et execute la requête
                $entityManager->persist($gameCategory);
                $entityManager->flush();
            }
        }

        return $this->json(['result' => 'success']);
    }

}
