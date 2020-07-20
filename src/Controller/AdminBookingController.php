<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Service\PaginationService;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * Afficher les réservations
     * 
     * @Route("/admin/bookings/{page<\d+>?1}", name="admin_bookings")
     * Le </d+?> signifie un requirment d'une expression regulière sur l'option page, un chiffre decimal est attendu, le ? signifie qu'il est optionnel, le 1 après ? signifie la valeur par défaut
     * 
     * @param BookingRepository $repo
     */
    public function indexBookings(BookingRepository $repo, $page, PaginationService $pagination)
    {
        $pagination->setEntityClass(Booking::class)
                    ->setPage($page);

        return $this->render('admin/booking/admin_bookings_show.html.twig', [
            'pagination' => $pagination,
        ]);
    }


    /**
     * Modifier une réservation
     * 
     * @Route("/admin/bookings/{id}/edit", name="admin_booking_edit")
     * 
     * @param Booking           $booking
     * @param Request           $request
     * @param ObjectManager     $manager
     * 
     * @return Response
     */
    public function editBooking(Booking $booking, Request $request, ObjectManager $manager) {

        $form = $this->createForm(AdminBookingType::class, $booking); //option validation_groups peut être passée dans un tableau en 3eme param et permet de préciser les groupes de validation qu'on veut exécuter
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $booking->setAmount(0); // fonction PrePersist est appelée à chaque fois que l'on veut créer une nouvelle réservation, elle est aussi appelée en preUpdate! Donc mettre la valeur à 0 permet de recalculer le montant lors de la màj des dates.
            $manager->persist($booking); //persist non obligatoire puisque le booking n'est pas un nouveau booking, il existe déjà, le manager l'a déjà pris en compte : dans ce cas il fautdrait seulement faire le flush.
            $manager->flush();

            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "La réservation <strong>{$booking->getId()}</strong> de <strong>{$booking->getbooker()->getFullName()}</strong> a bien été modifiée.");

            return $this->redirectToRoute('admin_bookings');
        }

        return $this->render('admin/booking/admin_booking_edit.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprimer une réservation en mode admin
     *
     * @Route("/admin/bookings/{id}/delete", name="admin_booking_delete")
     * 
     * @param Booking           $booking
     * @param Request           $request
     * @param ObjectManager     $manager
     * 
     * @return Response
     */
    public function deleteBooking(Booking $booking, Request $request, ObjectManager $manager) {
        // if(count($booking->getBookings()) > 0) {
        //     $this->addFlash('warning', "L'annonce <strong>{$ad->getTitle()}</strong> a déjà des réservations, vous ne pouvez pas la supprimer.");
        // }
        // else {
            // Supprimer l'annonce passée en param
            $manager->remove($booking);
            // Confirmer la suppression à la bdd
            $manager->flush();
            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "La réservation de <strong>{$booking->getBooker()->getFullName()}</strong> pour <strong>{$booking->getAd()->getTitle()}</strong> a bien été supprimée.");
        //}
        return $this->redirectToRoute('admin_bookings');
    }    

}
