<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {  
        $ads = $repo->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
     * Permet de créer une annonces
     * 
     * @Route("/ads/new", name="ads_create")
     * 
     * @return Response
     */
    public function create(Request $request, ObjectManager $manager) {
        $ad = new Ad();

        // Créer le formulaire d'ajout d'une annonce
        $form = $this->createForm(AnnonceType::class, $ad);

        // Analyse de la req pour les ajouter à une Ad
        $form->handleRequest($request);

        // Si soumis et valide on enregistre en bdd
        if($form->isSubmitted() && $form->isValid()) {
            // Sur chaque image
            foreach($ad->getImages() as $image) {
                // Préciser que l'image appartient à l'annonce
                $image->setAd($ad);
                // Manager persiste l'image avant de persister l'annonce
                $manager->persist($image);
            }

            // Relier l'auteur à l'utilisateur connecté lors de la création d'une annonce
            $ad->setAuthor($this->getUser());

            //Prévenir doctrine qu'on veut sauvegarder
            $manager->persist($ad);
            //Envoyer la req SQL
            $manager->flush();

            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "L'annonce <em>{$ad->getTitle()}</em> a bien été enregistrée.");

            // Redirection vers l'annonce créée
            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug(),
            ]);
        }

        return $this->render('ad/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher le form d'édition
     *@route("/ads/{slug}/edit", name="ads_edit")
     * 
     * @return Response
     */
    public function edit(Request $request, ObjectManager $manager, Ad $ad) {
        // Créer le formulaire d'ajout d'une annonce
        $form = $this->createForm(AnnonceType::class, $ad);

        // Analyse de la req pour les ajouter à une Ad
        $form->handleRequest($request);



        // Si soumis et valide on enregistre en bdd
        if($form->isSubmitted() && $form->isValid()) {
            // Sur chaque image
            foreach($ad->getImages() as $image) {
                // Préciser que l'image appartient à l'annonce
                $image->setAd($ad);
                // Manager persiste l'image avant de persister l'annonce
                $manager->persist($image);
            }
            //Prévenir doctrine qu'on veut sauvegarder
            $manager->persist($ad);
            //Envoyer la req SQL
            $manager->flush();

            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "Modification de l'annonce <em>{$ad->getTitle()}</em> enregistrée.");

            // Redirection vers l'annonce créée
            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug(),
            ]);
        }



        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad,
        ]);
    }

    /**
     * Permet d'afficher une seule annonce
     * 
     * @Route("/ads/{slug}", name="ads_show")
     * 
     * @return Response
     */
    public function show(Ad $ad) {
        // Convertir un paramètre reçu dans la route (ici le slug) en un objet déjà construit (ici l'annonce)
        return $this->render('ad/show.html.twig', [
            'ad' => $ad,
        ]);
    }

}
