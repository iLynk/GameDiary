<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function index(User $user): Response
    {
        $user = $this->getUser();
        $reviews = $user->getReviews();
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'reviews' => $reviews
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function edit(EntityManagerInterface $entityManager, Request $request,): Response
    {
        // Récupérer l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        // Créer le formulaire pour l'utilisateur connecté
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);  // Il manquait cette ligne pour gérer la requête

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour les informations de l'utilisateur
            if(!empty($form->get('name')->getData())){
                $user->setName($form->get('name')->getData());
            }
            if(!empty($form->get('email')->getData())){
                $plainPassword = $form->get('password')->getData();

            }
            if(!empty($form->get('password')->getData())){
                $plainPassword = $form->get('password')->getData();
                $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
                $user->setPassword($hashedPassword);
            }
            // Sauvegarder les modifications
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger après la mise à jour
            return $this->redirectToRoute('app_profile');
        }else{
            foreach($form->getErrors(true) as $error){
                dump($error->getMessage());
            }
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}
