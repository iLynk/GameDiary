<?php

namespace App\Controller;

use App\Service\IGDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    private IGDBService $IGDBService;

    public function __construct(IGDBService $IGDBService)
    {
        $this->IGDBService = $IGDBService;
    }

    #[Route('/games', name: 'app_games')]
    public function index(): Response
    {
        // Récupérer les jeux via FetchGames
        $games = $this->FetchGames();

        // Envoyer les jeux à la vue
        return $this->render('game/index.html.twig', [
            'games' => $games,
        ]);
    }

    // Méthode pour récupérer les jeux depuis l'API
    public function FetchGames(): array
    {
        // Définir la requête pour l'API IGDB (ajuste selon tes besoins)
        $query = "fields *; limit 500;";

        // Appeler le service IGDB pour récupérer les jeux
        $games = $this->IGDBService->fetchGamesData($query);

        return $games;
    }
}
