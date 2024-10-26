<?php
// Dans ce controller, on trouve les routes liées aux jeux, celle classique et la route d'un jeu en particulier

// src/Controller/GameController.php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewType;
use App\Repository\GameCategoryRepository;
use App\Repository\GameRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            'categories' => $categories,
        ]);
    }

    #[Route('/games/{slug}', name: 'app_game_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Game $game, ReviewRepository $reviewRepository, UserListRepository $userListRepository): Response
    {
        $user = $this->getUser();
        $hasReviewed = false;
        $userList = null;
        $reviews = $game->getReviews();
        $user = $this->getUser();
        if ($user) {
            foreach ($reviews as $review) {
                $review->likedByUser = $review->isLikedByUser($this->getUser());
                $review->dislikedByUser = $review->isDislikedByUser($this->getUser());
            }
        }
        if ($user) {
            $hasReviewed = $reviewRepository->findOneBy([
                    'user' => $user,
                    'game' => $game
                ]) !== null;
            $userList = $userListRepository->findOneBy(['user' => $user]);
        }
        $form = $this->createForm(reviewType::class);
        return $this->render('game/show.html.twig', [
            'game' => $game,
            'hasReviewed' => $hasReviewed,
            'form' => $form,
            'reviews' => $game->getReviews(),
            'userList' => $userList,
        ]);
    }
}


