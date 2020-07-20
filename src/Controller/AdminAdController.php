<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Service\PaginationService;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     * Le </d+?> signifie un requirment d'une expression regulière sur l'option page, un chiffre decimal est attendu, le ? signifie qu'il est optionnel, le 1 après ? signifie la valeur par défaut
     * 
     * @param AdRepository $repo
     */
    public function indexAds(AdRepository $repo, $page, PaginationService $pagination)
    {
        $pagination->setEntityClass(Ad::class)
                    ->setPage($page);

        return $this->render('admin/ad/admin_ads_index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Editer d'une annonce en mode admin
     * 
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     *
     * @param Ad $ad
     * @return Response
     */
    public function editAd(Ad $ad, Request $request, ObjectManager $manager) {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ad);
            $manager->flush();

            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée.");
        }

        return $this->render('admin/ad/admin_ad_edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprimer une annonce en mode admin
     *
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     * 
     * @param Ad $ad
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function deleteAd(Ad $ad, Request $request, ObjectManager $manager) {
        if(count($ad->getBookings()) > 0) {
            $this->addFlash('warning', "L'annonce <strong>{$ad->getTitle()}</strong> a déjà des réservations, vous ne pouvez pas la supprimer.");
        }
        else {
            // Supprimer l'annonce passée en param
            $manager->remove($ad);
            // Confirmer la suppression à la bdd
            $manager->flush();
            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée.");
        }
        return $this->redirectToRoute('admin_ads_index');
    }

}
