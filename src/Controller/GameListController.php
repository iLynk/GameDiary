<?php

// src/Controller/GameListController.php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\UserList;
use App\Repository\GameRepository;
use App\Repository\UserListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameListController extends AbstractController
{
    #[Route('/games/{id}/add', name: 'app_list_add', methods: ['POST'])]
    public function addGameToList(#[MapEntity(mapping: ['id' => 'id'])] Game $game, Request $request, EntityManagerInterface $entityManager, UserListRepository $userListRepository): Response
    {
        $user = $this->getUser();

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('add' . $game->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée');
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        // Vérifier s'il existe déjà une liste pour cet utilisateur
        $userList = $userListRepository->findOneBy(['user' => $user]);
        if (!$userList) {
            $userList = new UserList();
            $userList->setUser($user);
            $entityManager->persist($userList);
        }

        // Ajouter le jeu si ce n'est pas déjà fait
        if (!$userList->getGames()->contains($game)) {
            $userList->addGame($game);
            $entityManager->persist($userList);
            $entityManager->flush();
            $this->addFlash('success', 'Jeu ajouté à votre liste !');
        } else {
            $this->addFlash('info', 'Ce jeu est déjà dans votre liste.');
        }

        return $this->redirectToRoute('app_game_show', ['slug' => $game->getSlug()]);
    }

    #[Route('/games/{id}/delete', name: 'app_list_delete', methods: ['POST'])]
    public function DeleteGameToList(#[MapEntity(mapping: ['id' => 'id'])] Game $game, Request $request, EntityManagerInterface $entityManager, UserListRepository $userListRepository): Response
    {
        $user = $this->getUser();

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('add' . $game->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée');
            return $this->redirectToRoute('game_show', ['id' => $game->getId()]);
        }

        // Vérifier s'il existe déjà une liste pour cet utilisateur
        $userList = $userListRepository->findOneBy(['user' => $user]);
                    $userList->removeGame($game);
            $entityManager->persist($userList);
            $entityManager->flush();
            $this->addFlash('success', 'Jeu supprimé de votre liste !');

        return $this->redirectToRoute('app_game_show', ['slug' => $game->getSlug()]);
    }
}


