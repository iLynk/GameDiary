<?php

// src/Controller/GameController.php
namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameCategoryRepository;
use App\Repository\GameRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/games', name: 'app_game', methods: ['GET'])]
    public function index(GameRepository $gameRepository, GameCategoryRepository $categoryRepository): Response
    {
        // Appel à fetchGames pour récupérer les jeux
        $games = $gameRepository->findAll();
        $categories = $categoryRepository->findAll();
        // dd($games);
        // Retourne la vue avec les jeux
        return $this->render('game/index.html.twig', [
            'games' => $games,
            'categories' => $categories
        ]);
    }

    #[Route('/games/{slug}', name: 'app_game_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Game $game): Response
    {
        $form = ReviewType::class;
        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }
}


