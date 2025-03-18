<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // Importer la bonne classe Route
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupérer l'erreur de connexion si elle existe
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut être vide - elle sera interceptée par le système de sécurité
        throw new \LogicException('This method can be blank - it will be intercepted by the logout keys on your firewall.');
    }

    #[Route("/public-keys", name: "public_key", methods:["GET"])]
    public function getPublicKey(): Response
    {
        // Charger la clé publique (assurez-vous que la clé est stockée en sécurité)
        $publicKey = file_get_contents('/path/to/public_key.pem'); // Chemin vers la clé publique RSA

        return new Response($publicKey);
    }
}

