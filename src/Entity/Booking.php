<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\HasLifecycleCallBacks()
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ad", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="Attention la date doit être au bon format.")
     * @Assert\GreaterThan("today", message="La date d'arrivée doit être ultérieure à ce jour")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="Attention la date doit être au bon format.")
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de départ doit être supérieure à celle d'arrivée")
     * 
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * A chaque fois qu'on fait une réservation, certaines fonctions sont appelées automatiquement grâce à HasLifecycleCallbacks qui signigie que cette entité doit gérer son cycle de vie (à différent moment de son cycle de vie des fonctions lui sont reliées et font des actions, par exp ici insérer la date du jour à la réservation de l'annonce). 
     *
     * Callback appelé à chaque fois qu'on créé une réservation
     * 
     * @ORM\PrePersist
     * 
     * @return void
     */
    public function prePersist() {
        // Insérer date du jour lors de la réservation
        if(empty($this->createdAd)) {
            $this->createdAt = new \DateTime();
        }
        // Insérer montant calculé pour la réservation
        if(empty($this->amount)) {
            // Calculer prix total = tarif annonce * nombre de jour
            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }
    }

    /**
     * Calculer la durée entre date de début et date fin réservation
     *
     * @return void
     */
    public function getDuration() {
        // utiliser les objets startDate et endDate pour voir le nombre de jour entre les 2. La méthode diff des objets DateTime fait la différence entre 2 dates et renvoie un objet DateInterval.
        $diff = $this->endDate->diff($this->startDate);
        return $diff->days;
    }

    /**
     * Définir si les dates sont possibles
     *
     * @return boolean
     */
    public function isBookableDates() {
        // Connaitre les dates indisponibles pour l'annonces, faire appel à la fonction créée dans l'entité Ad qui calcule ces jours
        $notAvailableDays = $this->ad->getNotAvailableDays();
        // Comparer les dates de la réservation actuelle avec les dates indispo
        $bookingDays = $this->getDays();

        // Sotcker dans une var une fonction de formatage des timestamp en string
        $formatDay = function($day) {
            return $day->format('Y-m-d');
        };
        // Transformer les timestamp en tableau de chaines de caractères
        $days = array_map($formatDay, $bookingDays);
        $notAvailable = array_map($formatDay, $notAvailableDays);

        // Boucler sur les journées de la réservation actuelle
        foreach($days as $day) {
            // Si la journée sélectionnée se trouve dans le tableau des journées non dispo
            if(array_search($day, $notAvailable) !== false) {
                return false;
            }

            return true;
        }

    }

    /**
     * Récupérer un tableau des journée qui correspondent à la réservation
     *
     * @return array Un tableau d'objets DateTime représentants les jours de la réservation
     */
    public function getDays() {
        // Date d'arrivée du booking, sous forme de timestamp
        $startDate = $this->startDate->getTimestamp();
        // Date de départ du booking, sous forme de timestamp
        $endDate = $this->endDate->getTimestamp();
        // Nombre de journées entre les deux timestamp : Calculer 24h * 60min * 60sec
        $step = 24 * 60 * 60;
        // La fonction range() de PHP créé un tableau qui contient chaque étape existant entre deux nombre
        $resultat = range($startDate, $endDate, $step);
        // Transformer le timestamp calculé en vraie date objet DateTime
        $days = array_map(function($dayTimestamp) {
            return new \DateTime(date('Y-m-d', $dayTimestamp));
        }, $resultat);

        return $days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
