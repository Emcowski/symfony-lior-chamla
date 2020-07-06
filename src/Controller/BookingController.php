<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function booking(Ad $ad, Request $request, ObjectManager $manager)
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        // Le formulaire regarde la requête passée
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Relier à l'user qui a réservé
            $user = $this->getUser();
            // Lier le booking (l'action de réservation) au booker (l'user qui réserve et qui est connecté) et l'annonce qui lui est reliée donc $ad
            $booking->setBooker($user)
                    ->setAd($ad);
            // Date de réservation et montant total sont calculés dans l'entité grâce à des fonctions rajoutées et de l'annotation HasLifecycleCallbacks sur la classe qui stipule la gestion de son cycle de vie (donc d'executer les fonctions rajoutées)

            // Si les dates ne sont pas disponibles, message d'erreur
            if(!$booking->isBookableDates()) {
                $this->addFlash(
                    'warning',
                    "Les dates sélectionnées ne sont pas disponibles."
                );
            } 
            // Sinon ok redirection
            else {
                // dans le booking nous avons ce qui est inséré dans le form càd date d'arrivée, date de départ, commentaire
                $manager->persist($booking);
                $manager->flush();
    
                return $this->redirectToRoute('booking_show', [
                    'id' => $booking->getId(),
                    'withAlert' => true,
                ]);
            }

        }

        return $this->render('booking/booking_book.html.twig', [
            'ad' => $ad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Afficher la page d'une réservation
     *
     * @Route("/booking/{id}", name="booking_show")
     * 
     * @param Booking $booking
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function show(Booking $booking, Request $request, ObjectManager $manager) {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Relier le commentaire à l'annonce et à l'user connecté
            $comment->setAd($booking->getAd())
                    ->setAuthor($this->getUser());

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Merci, votre commentaire a bien été pris en compte"
            );
        }

        return $this->render('booking/booking_show_booked.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

}
