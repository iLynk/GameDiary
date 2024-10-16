<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ReviewController extends AbstractController
{
    #[Route('/games/{slug}/review', name: 'app_game_review', methods: ['POST'])]
// FONCTION POUR AJOUTER UN AVIS
    public function addReview(
        #[MapEntity(mapping: ['slug' => 'slug'])] Game $game, Request $request, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): JsonResponse
    {
        $user = $this->getUser();
// On vérifie que la personne est bien connectée
        if (!$user) {
// Sinon on retourne une réponse négative
            return new JsonResponse([
                'result' => false,
                'message' => 'Vous devez être connecté espèce de petit malin'
            ]);
// Pareil si l'utilisateur a déjà noté le jeu
        } elseif ($reviewRepository->findOneBy(['user' => $user, 'game' => $game]) !== null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous avez déjà ajouté un avis pour ce jeu !'
            ]);
        }
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

// on récupère la data envoyée
        $form->handleRequest($request);

// Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
// On ajoute l'user
            $review->setUser($this->getUser());
// On ajoute le jeu
            $review->setGame($game)
// On récupère les différentes informations envoyées
                ->setRate($form['rate']->getData())
                ->setComment($form['comment']->getData())
                ->setCompleted($form['completed']->getData());


// On sauvegarde l'avis en bdd
            $entityManager->persist($review);
            $entityManager->flush();

// On envoie une réponse positive
            return new JsonResponse([
                'success' => true,
                'message' => 'Votre avis a été enregistré avec succès.'
            ]);
        }

// Si jamais le form n'était pas valide, on renvoie une erreur
        return new JsonResponse([
            'success' => false,
            'errors' => (string)$form->getErrors(true),
        ], Response::HTTP_BAD_REQUEST);
    }
}

