<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request,
                            EntityManagerInterface $em, 
                            User $user,
                            UserPasswordHasherInterface $hasher, 
                            ): Response
    {
        // on crée le formulaire via la class UserType 
        $form = $this->createForm(UserType::class, $user);
        // on récupère la reponse envoyé depuis le navigateur
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // on hache le password et on ajoute le role user
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()))
                ->setRoles(['user']);
            // on enregistre l'utilisateur
            $em->persist($user);
            $em->flush();
 
            // connecter le user?

            // on retourne vers l'accueil avec un petit message de succès 
            $this->addFlash('success', 'Merci de vous être enregistré');
            return $this->redirectToRoute('app_login');
        }
        // on envoie le formulaire d'inscription à la vue
        return $this->render('security/register.html.twig', ['form' => $form]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
