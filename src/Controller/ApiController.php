<?php

// src/Controller/GameController.php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameCategory;
use App\Entity\GamePlatform;
use App\Repository\GameCategoryRepository;
use App\Repository\GamePlatformRepository;
use App\Repository\GameRepository;
use App\Service\ApiTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController
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

    /**
     * @param EntityManagerInterface $entityManager
     * @param Game $game
     * @param GameRepository $gameRepository
     * @param GameCategoryRepository $gameCategoryRepository
     * @return JsonResponse
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    #[Route('/getGames', methods: ['GET'])]
    // FONCTION POUR POPULATE L'ENTITE GAME
    public function getGames(EntityManagerInterface $entityManager, Game $game, GameRepository $gameRepository, GameCategoryRepository $gameCategoryRepository): JsonResponse
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
        foreach ($gamesData as $gameData) {
            $game = new Game();
            $game->setApiId($gameData['id'])
                ->setName($gameData['name'])
                ->setSlug($gameData['slug']);
            if (isset($gameData['first_release_date'])) {
                $dateToConvert = $gameData['first_release_date'];
                $dateConverted = (new \DateTimeImmutable())->setTimestamp($dateToConvert)->format('Y-m-d');
                $game->setReleaseDate($dateConverted);
            } else {
                $game->setReleaseDate('TBA');
            }
            if (isset($gameData['storyline'])) {
                $game->setStoryline($gameData['storyline']);
            }
            if (isset($gameData['cover'])) {
                $game->setCover($gameData['cover']);
            } else {
                $game->setCover('');
            }
            if (isset($gameData['genres'])) foreach ($gameData['genres'] as $genreApiId) {
                $game->addGameCategory($gameCategoryRepository->findOneBy(['apiId' => $genreApiId]));
            }

            $entityManager->persist($game);
            $entityManager->flush();
        }

        return $this->json(['tout a marche?' => 'ouiiiiii']);
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getCategories', methods: ['GET'])]
// FONCTION POUR POPULATE L'ENTITE GAMECATEGORY
    public function getCategory(EntityManagerInterface $entityManager, GameCategoryRepository $gameCategoryRepository): JsonResponse
    {
        try {
            // on récupère les catégories via l'API
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
            $AllCategoriesIds = [];

            // foreach afin de faciliter la comparaison par la suite (tableau simple au lieu d'une collection)
            foreach ($allCategories as $allCategory) {
                $AllCategoriesIds[] = $allCategory['apiId'];
            }

            // on populate l'entité avec les résultats
            foreach ($categories as $category) {
                // on vérifie que la catégorie n'existe pas déjà en bdd
                if (!in_array($category['id'], $AllCategoriesIds)) {
                    $gameCategory = new GameCategory();
                    $gameCategory->setApiId($category['id'])
                        ->setName($category['name'])
                        ->setSlug($category['slug']);

                    // on prépare et exécute la requête
                    $entityManager->persist($gameCategory);
                }
            }

            // On exécute la requête à la fin
            $entityManager->flush();

            return $this->json(['result' => 'catégories bien reçues !']);

        } catch (TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|DecodingExceptionInterface|ClientExceptionInterface $e) {
            // Gestion des erreurs de l'API
            return $this->json([
                'result' => 'Une erreur est survenue au niveau de l\'api',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            // Gestion des autres erreurs générales
            return $this->json([
                'result' => 'une erreur est survenue',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*   #[Route('/getGameCovers', methods: ['GET'])]
  // FONCTION POUR RECUPERER TOUTES LES COVER DES JEUX
     public function getGameCovers(EntityManagerInterface $entityManager, GameCategoryRepository $gameCategoryRepository): JsonResponse
      {
          $gameCoversData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/covers', [
              'headers' => [
                  'Client-ID' => $this->getParameter('igdb_client_id'),
                  'Authorization' => 'Bearer ' . $this->apiToken,
              ],
              // on adapte les fields pour récuperer seulement ce que l'on veut
              'body' => 'fields*; limit 500;'
          ]);
          ])
          return $this->json(['resultat' => 'toutes les images ont bien été récupérées']);
  }*/

    // FONCTION POUR RECUPERER TOUTES LES PLATEFORMES DE JEU
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getPlatforms', methods: ['GET'])]
    public function getPlatforms(EntityManagerInterface $entityManager, GamePlatformRepository $gamePlatformRepository): JsonResponse
    {
        $gamePlatformsData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/platforms', [
            'headers' => [
                'Client-ID' => $this->getParameter('igdb_client_id'),
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
            'body' => 'fields id,name; limit 500;'
        ]);

        $gamePlatformsData = $gamePlatformsData->toArray();

        $allPlatforms = $gamePlatformRepository->findAllApiId();
        $AllPlatformIds = [];
        foreach ($allPlatforms as $allPlatform) {
            $AllPlatformIds[] = $allPlatform['apiId'];
        }
        foreach ($gamePlatformsData as $gamePlatformData) {
            if (!in_array($gamePlatformData['id'], $AllPlatformIds)) {
                $gamePlatform = new GamePlatform();
                $gamePlatform->setApiId($gamePlatformData['id']);
                $gamePlatform->setName($gamePlatformData['name']);
                $entityManager->persist($gamePlatform);
            }
        }
        $entityManager->flush();
        return $this->json(['result' => 'plateformes bien ajoutées en bdd']);
    }
}

/*le petit problème c'est que l'api ne peut retourner que 500 résultats par requête, j'obtiens donc mes 500 premiers jeux (qui sont amenés à changer si l'api est alimentée de nouveautés)
pour les covers de jeu par exemple, je ne vois pas trop comment faire...
j'ai une idée de récupérer mes jeux dans un premier temps en initiant game.cover à ' ', faire par la suite une boucle sur tous mes jeux en effectuant une requête vers l'api /covers
avec dans le body un équivalent de "where game = $apiIdGame et ça me retournerait l'url de l'image du jeu que je set dans mes $game mais ça m'a l'air vachement relou...

deuxieme point, j'ai fait une route qui me permet d'aller chercher toutes mes plateformes, mais je ne sais pas comment faire entre, faire une relation entre game et gamePlatform pour procéder comme avec categories
ou alors, faire une propriété platform dans mon entité game, et faire un truc du style $platform = $gamePlatformRepository->FindOneBy(['apiId' => id récup par l'api])
*/

