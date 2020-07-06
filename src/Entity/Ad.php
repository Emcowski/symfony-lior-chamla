<?php

namespace App\Entity;

use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *      fields={"title"},
 *      message="Une autre annonce possède déjà ce titre, merci de le modifier."
 * )
 */
class Ad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=10, max=255, minMessage="Le titre doit faire au moins 10 caractères.", maxMessage="Le titre ne peut pas faire plus de 255 caractères.")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=3, max=150, minMessage="Le slug web doit faire au moins 3 caractères.", maxMessage="Le slug web ne peut pas faire plus 150 caractères.")
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=20, minMessage="Votre introduction doit faire plus de 20 caractères.")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=50, minMessage="Votre description doit faire plus de 50 caractères.")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url()
     */
    private $coverImage;

    /**
     * @ORM\Column(type="integer")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="ad", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="ad")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="ad", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Permet d'initialiser le slug (=termes du titre ans l'URL mais sans les caractères spéciaux et avec des - à la place des espaces), il faut l'appeler AVANT (Pre) la création/sauvegarde (Persist) ou de la mise à jour (Update)
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeSlug() {
        //au moment de ma sauvegarde est ce que mon slug est vide
        if(empty($this->slug)) {
            //si oui on instancie la classe Slugify 
            $slugify = new Slugify();
            // mon title sera donc un argument de la fonction Slugify de l'instance de la classe Slugify
            $this->slug = $slugify->slugify($this->title);
        }
    }

    /**
     * Trouver un commentaire à partir d'un auteur
     *
     * @param User $author
     * 
     * @return Comment|null
     */
    public function getCommentFromAuthor(User $author) {
        // Parmi tous les commentaires de l'annonce, vérifier si l'auteur du commentaire est le même que l'auteur passé dans la fonction, alors on retourne le commentaire pour pouvoir l'afficher
        foreach($this->comments as $comment) {
            if($comment->getAuthor() === $author) return $comment;
        }

        return null;
    }

    /**
     * Faire la moyenne des notes
     *
     * @return float
     */
    public function getAvgRatings() {
        // Calculer la somme des notations
        $sum = array_reduce($this->comments->toArray(), function($total, $comment) {
            // retourner le total + la note du commentaire, pour chaque commentaire, afin d'obtenir une somme
            return $total + $comment->getRating();
        }, 0);
        // Faire la division pour avoir la moyenne, s'il y a au moins 1 commentaire
        if(count($this->comments) > 0) return $sum / count($this->comments);

        return 0;
    }

    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponibles pour une annonce
     *
     * @return array Un tableau d'objets DateTime représentant les jours d'occupation
     */
    public function getNotAvailableDays() {

        //Calculer les jours qui se trouvent entre date arrivée et date départ
        $notAvailableDays = [];

        
        foreach($this->bookings as $booking) {
            // Date d'arrivée du booking, sous forme de timestamp
            $startDate = $booking->getStartDate()->getTimestamp();
            // Date de départ du booking, sous forme de timestamp
            $endDate = $booking->getEndDate()->getTimestamp();
            // Nombre de journées entre les deux timestamp : Calculer 24h * 60min * 60sec
            $step = 24 * 60 * 60;
            // La fonction range() de PHP créé un tableau qui contient chaque étape existant entre deux nombre
            $resultat = range($startDate, $endDate, $step);

            // Transformer le timestamp calculé en vraie date objet DateTime
            $days = array_map(function($dayTimestamp){
                return new \DateTime(date('Y-m-d', $dayTimestamp));
            }, $resultat);

            // Enrichir le tableau des journées qui ne sont pas disponibles
            $notAvailableDays = array_merge($notAvailableDays, $days);

        }

        return $notAvailableDays;

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }
}
