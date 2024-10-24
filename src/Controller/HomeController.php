<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GameRepository $gameRepository, ReviewRepository $reviewRepository): Response
    {
        $latestGames = $gameRepository->findLatest();
        $latestReviews = $reviewRepository->findLast5Reviews();
        $user = $this->getUser();
        if ($user) {
            foreach ($latestReviews as $review) {
                $review->likedByUser = $review->isLikedByUser($this->getUser());
                $review->dislikedByUser = $review->isDislikedByUser($this->getUser());
            }
        }
        return $this->render('home/index.html.twig', [
            'games' => $latestGames,
            'reviews' => $latestReviews
        ]);
    }

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('/mentions_legales.html.twig');
    }
}
