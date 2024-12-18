<?php
// Ce controller est utilisé pour tout ce qui est relatif au profil de l'utilisateur (affichage, modifs et suppression)

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserListRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ProfileController extends AbstractController
{

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    // seulement l'affichage du profil avec diverses informations comme les avis et la liste
    public function index(User $user, UserListRepository $userListRepository): Response
    {
        $user = $this->getUser();
        $reviews = $user->getReviews();
        $userList = $userListRepository->findOneBy(['user' => $user]);
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'reviews' => $reviews,
            'userList' => $userList
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    // fonction pour modifier les informations hors mot de passe qui se passe dans une autre route
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
            if (!empty($form->get('name')->getData())) {
                $user->setName($form->get('name')->getData());
            }
            if (!empty($form->get('email')->getData())) {
                $user->setEmail($form->get('email')->getData());

                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
                if($existingUser) {
                    // on ajoute un petit message pour dire à l'utilisateur que le compte existe déjà, mais on donne une information pour un potentiel hacker....
                    $this->addFlash('danger', "On ne se connaît pas déjà ?");
                    return $this->redirectToRoute('app_profile_edit');
                }
            }

            $user->setpassword($user->getPassword());
            // Sauvegarder les modifications
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger après la mise à jour
            $this->addFlash('success', 'Vos informations ont bien été modifiées' );
            return $this->redirectToRoute('app_profile');
        } else {
            foreach ($form->getErrors(true) as $error) {
                dump($error->getMessage());
            }
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profile/editPassword', name: 'app_profile_edit_password', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    // fonction pour modifier uniquement le mot de passe, je trouvais ça plus clair de séparer les informations "classiques" du mot de passe
    public function editPassword(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        // Récupérer l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        // Créer le formulaire pour l'utilisateur connecté
        $form = $this->createForm(UserType::class, $user, ['is_password' => true]);
        $form->handleRequest($request);  // Il manquait cette ligne pour gérer la requête

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour les informations de l'utilisateur
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $hasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                // Sauvegarder les modifications
                $entityManager->persist($user);
                $entityManager->flush();
            }

            // Rediriger après la mise à jour
            $this->addFlash('success', 'Votre mot de passe a bien été modifié' );
            return $this->redirectToRoute('app_profile');
        } else {
            foreach ($form->getErrors(true) as $error) {
                dump($error->getMessage());
            }
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profile/delete/{id}', name: 'app_profile_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    // route pour supprimer un compte (c'est la même pour l'admin qui delete un compte et un user qui delete son compte)
    public function delete(#[MapEntity(mapping: ['id' => 'id'])] User $user,UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $review = $userRepository->findOneBy(['id' => $user->getId()]);
        $entityManager->remove($user);
        $entityManager->flush();
        if ($this->getUser() === $user) {
            $tokenStorage->setToken(null); // Supprimer le token de session
            $request->getSession()->invalidate(); // Invalider la session
        }
        // sert à rediriger correctement en fonction d'ou vient la requête
        $referer = $request->headers->get('referer');
        if($referer && str_contains($referer, "/admin")) {
            return $this->redirectToRoute('admin_dashboard');
        }
        $this->addFlash('success', 'Votre compte à bien été supprimé');
        return $this->redirectToRoute('app_home');
    }
}
