<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameCategory;
use App\Entity\GamePlatform;
use App\Repository\GameCategoryRepository;
use App\Repository\GamePlatformRepository;
use App\Repository\GameRepository;
use App\Service\ApiTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\{ClientExceptionInterface,
    DecodingExceptionInterface,
    RedirectionExceptionInterface,
    ServerExceptionInterface,
    TransportExceptionInterface};

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
     * @return Response
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/getGames', name: "api_get_games", methods: ['POST'])]
    // FONCTION QUI RECUPERE LES JEUX QUI ONT ETE EVALUES PAR AU MOINS 500 PERSONNES AFIN D'AVOIR DES JEUX "CONNUS"
    public function getGames(
        EntityManagerInterface $entityManager,
        GameRepository         $gameRepository,
        GameCategoryRepository $gameCategoryRepository,
        GamePlatformRepository $gamePlatformRepository
    ): Response
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
                'body' => 'fields id, cover.image_id, genres, name, slug, first_release_date, storyline, platforms; where total_rating_count > 500; limit 500;',
            ])->toArray();

            // Traitement des données de chaque jeu
            foreach ($gamesData as $gameData) {
                // Vérifier si le jeu existe déjà dans la base de données par son apiId
                $existingGame = $gameRepository->findOneBy(['apiId' => $gameData['id']]);
                if (!$existingGame) {
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
                        // Dans la requête que j'effectue, les jeux sont tous sortis mais ça reste intéressant de traiter le cas ou la date n'existe pas encore
                        $game->setReleaseDate('TBA');
                    }

                    // Gestion du "résumé"
                    if (isset($gameData['storyline'])) {
                        $game->setStoryline($gameData['storyline']);
                    }

                    // Ajout des catégories si disponibles
                    if (isset($gameData['genres'])) {
                        foreach ($gameData['genres'] as $genreApiId) {
                            // On utilise le repo des categories pour trouver l(es)'entité(s) category correspondante(s)
                            $category = $gameCategoryRepository->findOneBy(['apiId' => $genreApiId]);
                            if ($category) {
                                $game->addGameCategory($category);
                            }
                        }
                    }

                    // Ajout des plateformes si disponibles
                    if (isset($gameData['platforms'])) {
                        foreach ($gameData['platforms'] as $platformApiId) {
                            // même principe qu'avec les catégories, mais pour les plateformes
                            $platform = $gamePlatformRepository->findOneBy(['apiId' => $platformApiId]);
                            if ($platform) {
                                $game->addGamePlatform($platform);
                            }
                        }
                    }

                    // on persiste le nouveau jeu à chaque itération de la boucle
                    $entityManager->persist($game);
                }
            }

            // Et on tire la chasse !
            $entityManager->flush();

            // On envoie une réponse au navigateur pour indiquer que tout s'est bien passé
            $this->addFlash('success', 'Tous les jeux ont été récupérés et enregistrés.');
        } catch (\Exception $e) {
            // On envoie une réponse pour dire que ça ne s'est pas bien passé malheureusement...
            $this->handleError('Une erreur est survenue lors de la récupération des jeux', $e);
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getCategories', name: 'api_get_categories', methods: ['POST'])]
    // FONCTION POUR POPULATE L'ENTITE GAMECATEGORY
    public function getCategories(EntityManagerInterface $entityManager, GameCategoryRepository $gameCategoryRepository): Response
    {
        try {
            // on récupère les catégories via l'API
            $categories = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/genres', [
                'headers' => [
                    'Client-ID' => $this->getParameter('igdb_client_id'),
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                'body' => 'fields *; limit 500;',
            ])->toArray();

            // on récupère toutes les catégories existantes
            $allCategories = $gameCategoryRepository->findAllApiId();
            $allCategoryIds = array_column($allCategories, 'apiId'); // Optimisation du traitement des ID

            // on populate l'entité avec les résultats
            foreach ($categories as $category) {
                // on vérifie que la catégorie n'existe pas déjà en bdd
                if (!in_array($category['id'], $allCategoryIds)) {
                    $gameCategory = new GameCategory();
                    $gameCategory->setApiId($category['id'])
                        ->setName($category['name'])
                        ->setSlug($category['slug']);
                    $entityManager->persist($gameCategory);
                }
            }

            // On exécute la requête à la fin
            $entityManager->flush();

            $this->addFlash('success', 'Les catégories ont correctement été récupérées.');
        } catch (\Exception $e) {
            $this->handleError('Une erreur est survenue lors de la récupération des catégories', $e);
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getCovers', name: 'api_get_covers', methods: ['POST'])]
    // FONCTION POUR RECUPERER LES COVER DES JEUX PAR LOTS ET LES MODIFIER POUR 1080p
        // ATTENTION, CE CODE A EN PARTIE ETE GENERE AVEC CHAT GPT CAR MON CODE DE BASE FAISAIT PLANTER LA MEMOIRE DE DOCTRINE
    public function getCovers(EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
    {
        try {
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
                    'body' => 'fields game, url; where game = (' . implode(',', $gameIds) . '); limit ' . $batchSize . ';',
                ])->toArray();

                // Mettre à jour les entités Game pour chaque couverture récupérée
                foreach ($gameCoversData as $coverData) {
                    $game = $gameRepository->findOneBy(['apiId' => $coverData['game']]);
                    if ($game && isset($coverData['url']) && $game->getCover() !== $coverData['url']) {
                        // Remplacer "t_thumb" par "t_1080p" dans l'URL de la couverture
                        $coverUrl = str_replace('t_thumb', 't_1080p', $coverData['url']);
                        $game->setCover($coverUrl);
                        $entityManager->persist($game); // Persister la mise à jour de la couverture
                    }
                }

                // Sauvegarder les entités modifiées après chaque lot
                $entityManager->flush();
                $entityManager->clear(); // Libérer la mémoire après chaque lot
            }

            // Ajout d'un flash message de succès si tout s'est bien passé
            $this->addFlash('success', 'Les artworks ont correctement été récupérés.');
        } catch (\Exception $e) {
            // Gestion des erreurs avec un message flash d'erreur
            $this->handleError('Une erreur est survenue lors de la récupération des artworks', $e);
        }

        // Redirection vers le tableau de bord admin après l'exécution
        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getPlatforms', name: 'api_get_platforms', methods: ['POST'])]
    // FONCTION POUR RECUPERER TOUTES LES PLATEFORMES DE JEU
    public function getPlatforms(EntityManagerInterface $entityManager, GamePlatformRepository $gamePlatformRepository, Request $request): Response
    {
        try {
            // Requête pour récupérer les plateformes via l'API IGDB
            $gamePlatformsData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/platforms', [
                'headers' => [
                    'Client-ID' => $this->getParameter('igdb_client_id'),
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                'body' => 'fields id,name; limit 500;',
            ])->toArray();

            // Récupérer les ID des plateformes déjà en base de données
            $allPlatforms = $gamePlatformRepository->findAllApiId();
            $allPlatformIds = array_column($allPlatforms, 'apiId'); // Optimisation du traitement des ID

            // Boucle pour ajouter les nouvelles plateformes récupérées
            foreach ($gamePlatformsData as $gamePlatformData) {
                if (!in_array($gamePlatformData['id'], $allPlatformIds)) {
                    $gamePlatform = new GamePlatform();
                    $gamePlatform->setApiId($gamePlatformData['id'])
                        ->setName($gamePlatformData['name']);
                    $entityManager->persist($gamePlatform);
                }else{
                    $this->addFlash('success', 'pas de nouvelle plateforme à ajouter');
                }
            }

            // Sauvegarder les nouvelles plateformes en base de données
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Les plateformes ont correctement été récupérées.');
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | DecodingExceptionInterface $e) {
            $this->handleError('Une erreur est survenue lors de la récupération des plateformes', $e);
        } catch (\Exception $e) {
            $this->handleError('Une erreur inattendue est survenue lors de la récupération des plateformes', $e);
        }
        //$this->get('session')->getFlashBag()->add('success', 'Message ajouté');
        // Redirection vers le tableau de bord admin
         return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * Gestion des erreurs en fonction de l'environnement
     */
    private function handleError(string $message, \Exception $e): void
    {
        // Vérifier si l'environnement est en production ou non
        if ($this->getParameter('kernel.environment') === 'prod') {
            // En production, on ne montre pas les détails de l'erreur
            $this->addFlash('error', $message);
        } else {
            // En développement, on affiche le message détaillé de l'exception
            $this->addFlash('error', $message . ' : ' . $e->getMessage());
        }
    }
}
