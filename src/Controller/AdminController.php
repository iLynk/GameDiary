<?php

// src/Controller/AdminController.php
namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository, ReviewRepository $reviewRepository): Response
    {
        $users = $userRepository->findAll();
        $reviews = $reviewRepository->findAll();
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'reviews' => $reviews
        ]);
    }
}
