<?php
/* Bienvenue dans l'api controller, le plus gros controller de l'application, vous allez retrouver ici les fonctions faisant appel à l'API IGDB
   Donc, la route pour récupérer les jeux, les catégories, les plateformes, les images ... ...
   Bref, je vous laisse faire un tour ! J'ai essayé de tout commenter pour que ce soit le plus lisible possible, bonne balade :) !
*/
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\{ClientExceptionInterface,
    DecodingExceptionInterface,
    RedirectionExceptionInterface,
    ServerExceptionInterface,
    TransportExceptionInterface
};


class ApiController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private ApiTokenService $apiTokenService;
    private string $apiToken;
    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(HttpClientInterface $httpClient, ApiTokenService $apiTokenService, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->httpClient = $httpClient;
        $this->apiTokenService = $apiTokenService;
        $this->apiToken = $apiTokenService->getToken();
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param GameRepository $gameRepository
     * @param GameCategoryRepository $gameCategoryRepository
     * @param GamePlatformRepository $gamePlatformRepository
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/getGames', name: "api_get_games", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
// FONCTION QUI RECUPERE LES JEUX QUI ONT ETE EVALUES PAR AU MOINS 500 PERSONNES AFIN D'AVOIR DES JEUX "CONNUS"
    public function getGames(
        EntityManagerInterface $entityManager,
        GameRepository         $gameRepository,
        GameCategoryRepository $gameCategoryRepository,
        GamePlatformRepository $gamePlatformRepository,
        Request                $request,
    ): JsonResponse
    {
        // On vérifie le token CSRF
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('api_actions', $request->headers->get('X-CSRF-Token')))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], 403);
        }

        try {
            // Requête pour récupérer les jeux
            $gamesData = $this->httpClient->request('POST', $this->getParameter('igdb_api_url') . '/games', [
                'headers' => [
                    'Client-ID' => $this->getParameter('igdb_client_id'),
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                // On adapte les fields pour récupérer seulement ce que l'on veut
                'body' => 'fields id, cover.image_id, genres, name, slug, first_release_date, storyline, platforms; where total_rating_count > 500; limit 500 ;'
            ])->toArray();

            $allGames = $gameRepository->findAllApiId();
            $allGamesIds = array_column($allGames, 'apiId');

            $newGamesCount = 0;

            // Traitement des données de chaque jeu
            foreach ($gamesData as $gameData) {
                if (!in_array($gameData['id'], $allGamesIds)) {
                    $game = new Game();
                    $game->setApiId($gameData['id'])
                        ->setName($gameData['name'])
                        ->setSlug($gameData['slug']);
                    // Gestion de la date de sortie et conversion en date format "d-m-Y"
                    if (isset($gameData['first_release_date'])) {
                        $dateToConvert = $gameData['first_release_date'];
                        $dateConverted = (new \DateTimeImmutable())->setTimestamp($dateToConvert);
                        $game->setReleaseDate($dateConverted);
                    } else {
                        $game->setReleaseDate(null);
                    }
                    // Gestion du "résumé"
                    if (isset($gameData['storyline'])) {
                        $game->setStoryline($gameData['storyline']);
                    }
                    // Ajout des catégories si disponibles
                    if (isset($gameData['genres'])) {
                        foreach ($gameData['genres'] as $genreApiId) {
                            $game->addGameCategory($gameCategoryRepository->findOneBy(['apiId' => $genreApiId]));
                        }
                    }
                    // Ajout des plateformes si disponibles
                    if (isset($gameData['platforms'])) {
                        foreach ($gameData['platforms'] as $platformApiId) {
                            $game->addGamePlatform($gamePlatformRepository->findOneBy(['apiId' => $platformApiId]));
                        }
                    }
                    // on persiste le nouveau jeu à chaque itération de la boucle
                    $entityManager->persist($game);
                    $newGamesCount++;
                }
            }

            // On tire la chasse !
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => "$newGamesCount nouveaux jeux ont été récupérés et enregistrés.",
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des jeux.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface|DecodingExceptionInterface|TransportExceptionInterface
     */
    #[Route('/getCategories', name: 'api_get_categories', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    // FONCTION POUR POPULATE L'ENTITE GAMECATEGORY
    public function getCategories(
        EntityManagerInterface    $entityManager,
        GameCategoryRepository    $gameCategoryRepository,
        Request                   $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse
    {
        // Vérifier le token CSRF
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('api_actions', $request->headers->get('X-CSRF-Token')))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], 403);
        }

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

            return new JsonResponse(['success' => 'Les catégories ont correctement été récupérées.'], 200);
        } catch (\Exception $e) {
            // Gestion de l'erreur et renvoi d'une réponse JSON
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la récupération des catégories.'], 500);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getCovers', name: 'api_get_covers', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    // FONCTION POUR RECUPERER LES COVER DES JEUX PAR LOTS ET LES MODIFIER POUR 1080p
    public function getCovers(
        EntityManagerInterface $entityManager,
        GameRepository         $gameRepository,
        Request                $request
    ): JsonResponse
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('api_actions', $request->headers->get('X-CSRF-Token')))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], 403);
        }

        try {
            // Récupérer tous les jeux avec leurs API ID
            $games = $gameRepository->findAll(); // Récupérer tous les jeux
            $batchSize = 10; // Taille du lot pour les requêtes API
            $gameBatches = array_chunk($games, $batchSize); // Diviser les jeux en lots

            $updatedCoversCount = 0;

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
                        $updatedCoversCount++;
                    }
                }

                // Sauvegarder les entités modifiées après chaque lot
                $entityManager->flush();
                $entityManager->clear(); // Libérer la mémoire après chaque lot
            }

            return new JsonResponse([
                'success' => true,
                'message' => "$updatedCoversCount couvertures de jeux ont été mises à jour.",
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des couvertures.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/getPlatforms', name: 'api_get_platforms', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
// FONCTION POUR RECUPERER TOUTES LES PLATEFORMES DE JEU
    public function getPlatforms(
        EntityManagerInterface $entityManager,
        GamePlatformRepository $gamePlatformRepository,
        Request                $request
    ): JsonResponse
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('api_actions', $request->headers->get('X-CSRF-Token')))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], 403);
        }

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

            $newPlatformsCount = 0;

            // Boucle pour ajouter les nouvelles plateformes récupérées
            foreach ($gamePlatformsData as $gamePlatformData) {
                if (!in_array($gamePlatformData['id'], $allPlatformIds)) {
                    $gamePlatform = new GamePlatform();
                    $gamePlatform->setApiId($gamePlatformData['id'])
                        ->setName($gamePlatformData['name']);
                    $entityManager->persist($gamePlatform);
                    $newPlatformsCount++;
                }
            }

            // Sauvegarder les nouvelles plateformes en base de données
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => "$newPlatformsCount nouvelles plateformes ont été ajoutées.",
            ], 200);
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|DecodingExceptionInterface $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des plateformes.',
                'details' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur inattendue est survenue lors de la récupération des plateformes.',
                'details' => $e->getMessage()
            ], 500);
        }
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
