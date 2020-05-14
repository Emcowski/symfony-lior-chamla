<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditAccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * Afficher et gérer le formulaire de connexion
     * 
     * @Route("/login", name="account_login")
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $hasError = $error !== null;
        $username = $utils->getLastUserName();

        return $this->render('account/login.html.twig', [
            'hasError' => $hasError,
            'username' => $username,
        ]);
    }

    /**
     * Déconnexion
     * 
     * @Route("/logout", name="account_logout")
     */
    public function logout() { }

    /**
     * Afficher le formulaire d'inscription
     * 
     * @Route("/register", name="account_register")
     * 
     * @return Response
     */
    public function register(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getHash());
            $user->setHash($hash);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre compte a bien été créé. Vous pouvez désormais vous connecter.'
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Afficher et éditer le formulaire de modification du profil
     *
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER") 
     * 
     * @return Response
     */
    public function editProfile(Request $request, ObjectManager $manager) {
        // On peut récupérer l'user actuellement connecté grâce à getUser, peut être utilisé n'importe où dans un controller
        $user = $this->getUser();
        // Je veux créer un formulaire de type EditAccount qui concerne un user
        $form = $this->createForm(EditAccountType::class, $user);

        // Je veux que mon formulaire gère la request, récupérée via les param
        $form->handleRequest($request);

        // Persister et enregistrer les données en base grâce au manager
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les données ont bien été enregistrées."
            );
        }

        return $this->render('account/edit-profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifier le mot de passe
     * 
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function updatePassword(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
        // Créer nouvel objet de l'entité
        $passwordUpdate = new PasswordUpdate();
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();
        // Créer formulaire de type PasswordUpdate
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        // On veut que notre formulaire gère la request reçue dans les param
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Vérifier que le oldPassword tapé correspond au mot de passe actuel en bdd, càd le mdp actuel de l'user connecté
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) { // fonction PHP password_verify qui permet de vérifier le mot tapé avec le has/sel utilisé
                // Gérer l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé ne correspond pas au mot de passe actuel.")); // accès au champ oldPassword puis lui ajouter une fonction Symfo addError qui prend en param la classe FormError...
            }
            else {
                // Si l'ancien mot de passe a bien été tapé, il faut maintenant récupérer le nouveau mot de passe tapé 
                $newPassword = $passwordUpdate->getNewPassword();
                // L'encoder avec le hash
                $hash = $encoder->encodePassword($user, $newPassword);
                // Modifier le hash de l'utilisateur en lui mettant le nouveau hash
                $user->setHash($hash);

                // L'ajouter à l'utilisateur
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Le mot de passe a bien été changé."
                );
                
                return $this->redirectToRoute('homepage');
            }

        }

        return $this->render('account/password-update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Afficher le profil de user connecté
     *
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function myAccount() {
        $user = $this->getUser();

        return $this->render('user/user-page.html.twig', [
            'user' => $user,
        ]);
    }

}
