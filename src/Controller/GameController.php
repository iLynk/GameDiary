<?php

// src/Controller/GameController.php
namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{       #[Route('/games', name: 'app_game', methods: ['GET'])]
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
}
