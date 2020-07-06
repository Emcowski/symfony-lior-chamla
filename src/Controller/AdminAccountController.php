<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminAccountController extends AbstractController
{
    /**
     * Connexion de l'admin
     * 
     * @Route("/admin/login", name="admin_account_login")
     * 
     */
    public function login(AuthenticationUtils $utils)
    {
        // Récupérer erreur et son message si authentification n'a pas fonctionné, ainsi que le dernier nom d'user utilisé dans le champ
        $error = $utils->getLastAuthenticationError();
        $hasError = $error !== null;
        $username = $utils->getLastUserName();

        return $this->render('admin/account/admin_login.html.twig', [
            'hasError' => $hasError,
            'username' => $username,
        ]);
    }

    /**
     * Déconnexion de l'admin
     * 
     * @Route("/admin/logout", name="admin_account_logout")
     * 
     * @return void
     */
    public function logout() { }
}
