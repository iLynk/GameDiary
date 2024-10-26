<?php
// Controller crée automatiquement avec le make:auth, la route register a été cependant modifiée

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserList;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Security\AppAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{

    #[Route(path: '/register', name: 'app_register')]
    // FONCTION POUR S'INSCRIRE
    public function register(
        Request $request,
        EntityManagerInterface $em,
        User $user,
        UserPasswordHasherInterface $hasher,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
    ): Response {
        // si l'utilisateur est connecté, on le renvoit vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        // on crée le formulaire via notre form UserType 
        $form = $this->createForm(UserType::class, $user);
        // on récupère la reponse envoyé depuis le navigateur
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // on hache le password et on ajoute le role user
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()))
                ->setRoles(['ROLE_USER']);
            $userList = new UserList();
            $userList->setUser($user);
            // on enregistre l'utilisateur
            $em->persist($userList);
            $em->persist($user);
            $em->flush();
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
        // on envoie le formulaire d'inscription à la vue
        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    // FONCTION POUR SE CONNECTER
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // si l'utilisateur est connecté, on le renvoit vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // on récupère les erreurs s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();
        // on récupère le dernier 
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    // FONCTION POUR SE DECONNECTER
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
