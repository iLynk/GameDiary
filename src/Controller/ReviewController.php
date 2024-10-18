<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Review;
use App\Entity\Vote;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class ReviewController extends AbstractController
{
    #[Route('/games/{slug}/review', name: 'app_game_review', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
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

    #[Route('review/edit/{id}', name: 'app_review_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function editReview(#[MapEntity(mapping: ['id' => 'id'])] Review $review, Request $request, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $review = $reviewRepository->findOneBy(['id' => $review->getId()]);
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review->setRate($form['rate']->getData())
                ->setComment($form['comment']->getData())
                ->setCompleted($form['completed']->getData());

            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/review/delete/{id}', name: 'app_review_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function delete(#[MapEntity(mapping: ['id' => 'id'])] Review $review, ReviewRepository $reviewRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $review = $reviewRepository->findOneBy(['id' => $review->getid()]);
        $entityManager->remove($review);
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        $this->addFlash('success', 'Votre avis à bien été supprimé');
        if($referer && str_contains($referer, "/admin")) {
            return $this->redirectToRoute('admin_dashboard');
        }else{
            return $this->redirectToRoute('app_profile');
        }
    }

    #[Route('/vote/{id}/{type}', name: 'vote_review', methods: ['POST'])]
    public function vote(Review $review, int $type, EntityManagerInterface $entityManager, ReviewRepository $voteRepository): JsonResponse
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        if (!$user) {
            return new JsonResponse(['error' => 'Not logged in'], 403);
        }

        // Vérifier si l'utilisateur a déjà voté sur cette review
        $vote = $voteRepository->findOneBy(['user' => $user, 'review' => $review]);

        if ($vote) {
            // Si l'utilisateur a déjà voté sur cette review, on met à jour le vote
            if ($vote->getType() === $type) {
                // Si le même vote est soumis (like sur like ou dislike sur dislike), on annule le vote
                $entityManager->remove($vote);
                $entityManager->flush();
                return new JsonResponse(['success' => true, 'score' => $review->getScore()]);
            }

            // Sinon, on change le type de vote (de like à dislike ou inversement)
            $vote->setType($type);
        } else {
            // Ajouter un nouveau vote si l'utilisateur n'a pas encore voté
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setReview($review);
            $vote->setType($type);
            $entityManager->persist($vote);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true, 'score' => $review->getScore()]);
    }

}

