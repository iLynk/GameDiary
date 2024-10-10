<?php

// src/Controller/GameController.php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameCategory;
use App\Entity\GameCover;
use App\Entity\GamePlatform;
use App\Repository\GameCategoryRepository;
use App\Repository\GameCoverRepository;
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

/* Bienvenue dans l'api controller, le plus gros controller de l'application, vous allez retrouver ici les fonctions faisant appel à l'API IGDB
   Donc, la route pour récupérer les jeux, les catégories, les plateformes, les images ... ...
   Bref, je vous laisse faire un tour ! J'ai essayé de tout commenter pour que ce soit le plus lisible possible, bonne balade :) !
*/

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
     * @param GameRepository $gameRepository
     * @param GameCategoryRepository $gameCategoryRepository
     * @param GamePlatformRepository $gamePlatformRepository
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/getGames', methods: ['GET'])]
// FONCTION QUI RECUPERE LES JEUX QUI ONT ETE EVALUES PAR AU MOINS 500 PERSONNES AFIN D'AVOIR DES JEUX "CONNUS"
    public function getGames(EntityManagerInterface $entityManager, GameRepository $gameRepository, GameCategoryRepository $gameCategoryRepository, GamePlatformRepository $gamePlatformRepository): JsonResponse
    {
        // on englobe la requête dans un try/catch pour gérer les erreurs
        try {
            // Requête pour récupérer les jeux
            $gamesData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/games', [
                'headers' => [
                    'Client-ID' => $this->getParameter('igdb_client_id'),
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                // On adapte les fields pour récupérer seulement ce que l'on veut
                'body' => 'fields id, cover.image_id, genres, name, slug, first_release_date, storyline, platforms; where total_rating_count > 500; limit 500 ;'
            ]);
            // on transforme en tableau pour que ce soit plus simple à traiter
            $gamesData = $gamesData->toArray();

            // Traitement des données de chaque jeu
            foreach ($gamesData as $gameData) {
                $game = new Game();
                $game->setApiId($gameData['id'])
                    ->setName($gameData['name'])
                    ->setSlug($gameData['slug']);

                // Gestion de la date de sortie et conversion en date format "d-m-Y"
                if (isset($gameData['first_release_date'])) {
                    $dateToConvert = $gameData['first_release_date'];
                    $dateConverted = (new \DateTimeImmutable())->setTimestamp($dateToConvert)->format('d-m-Y');
                    $game->setReleaseDate($dateConverted);
                } else {
                    // Dans notre cas, les jeux sont tous sortis mais ça reste intéressant de traiter le cas ou la date n'existe pas encore
                    $game->setReleaseDate('TBA');
                }

                // Gestion du "résumé"
                if (isset($gameData['storyline'])) {
                    $game->setStoryline($gameData['storyline']);
                }

                // Gestion de l'artwork du jeu, pour l'instant ça ne marche pas
                if (isset($gameData['cover'])) {
                    $game->setCover('ça marche pas de fou');
                } else {
                    $game->setCover('');
                }
                // Ajout des catégories si disponibles
                if (isset($gameData['genres'])) {
                    foreach ($gameData['genres'] as $genreApiId) {
                        // On utilise le repo des categories pour trouver l(es)'entité(s) category correspondante(s)
                        $game->addGameCategory($gameCategoryRepository->findOneBy(['apiId' => $genreApiId]));
                    }
                }

                // Ajout des plateformes si disponibles
                if (isset($gameData['platforms'])) {
                    foreach ($gameData['platforms'] as $platformApiId) {
                        // même principe qu'avec les catégories, mais pour les plateformes
                        $game->addGamePlatform($gamePlatformRepository->findOneBy(['apiId' => $platformApiId]));
                    }
                }

                // on persiste le nouveau jeu à chaque itération de la boucle
                $entityManager->persist($game);
            }

            // Et on tire la chasse !
            $entityManager->flush();

            // On envoie une réponse au navigateur pour indiquer que tout s'est bien passé
            return $this->json(['message' => 'Tous les jeux ont été récupérés et enregistrés.',
                'type' => 'success'], Response::HTTP_OK);

        } catch (\Exception $e) {
            // On envoie une réponse pour dire que ça ne s'est pas bien passé malheureusement...
            return $this->json(['message' => 'Une erreur est survenue lors de la récupération des jeux...', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getCovers', methods: ['GET'])]
// FONCTION POUR RECUPERER LES COVER DES JEUX PAR LOTS ET LES MODIFIER POUR 1080p
    public function getCovers(EntityManagerInterface $entityManager, GameRepository $gameRepository): JsonResponse
    {
        // Récupérer tous les jeux avec leurs API ID
        $games = $gameRepository->findAll(); // Récupérer tous les jeux
        $batchSize = 10; // Taille du lot pour les requêtes API
        $gameBatches = array_chunk($games, $batchSize); // Diviser les jeux en lots

        foreach ($gameBatches as $gameBatch) {
            // Extraire les game IDs des jeux dans ce lot
            $gameIds = array_map(fn($game) => $game->getApiId(), $gameBatch);

            // Requête pour récupérer les couvertures via l'API IGDB en une seule requête pour plusieurs jeux
            $gameCoversData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/covers', [
                'headers' => [
                    'Client-ID' => $this->getParameter('igdb_client_id'),
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                'body' => 'fields game, url; where game = (' . implode(',', $gameIds) . '); limit ' . $batchSize . ';'
            ]);

            $gameCoversData = $gameCoversData->toArray();

            // Mettre à jour les entités Game pour chaque couverture récupérée
            foreach ($gameCoversData as $coverData) {
                $game = $gameRepository->findOneBy(['apiId' => $coverData['game']]);
                if ($game && isset($coverData['url'])) {
                    // Remplacer "t_thumb" par "t_1080p" dans l'URL de la couverture
                    $coverUrl = str_replace('t_thumb', 't_1080p', $coverData['url']);

                    // Mettre à jour l'entité Game avec la nouvelle URL de couverture
                    $game->setCover($coverUrl);
                    $entityManager->persist($game); // Persister la mise à jour de la couverture
                }
            }

            // Sauvegarder les entités modifiées après chaque lot
            $entityManager->flush();
            $entityManager->clear(); // Libérer la mémoire après chaque lot
        }

        // Réponse JSON pour indiquer que toutes les couvertures ont été récupérées
        return $this->json(['message' => 'Toutes les couvertures ont été récupérées et mises à jour.']);
    }



    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getPlatforms', methods: ['GET'])]
    // FONCTION POUR RECUPERER TOUTES LES PLATEFORMES DE JEU
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

