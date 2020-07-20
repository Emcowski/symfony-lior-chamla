<?php

namespace App\Service;

use Twig\Environment;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService {
    // Définir l'entité sur laquelle le service va travailler
    private $entityClass;
    // Puis les var auxquelles on veut accéder
    private $limit = 10;
    private $currentPage = 1;
    private $manager;
    private $twig;
    private $route;
    private $templatePath;


    public function __construct(ObjectManager $manager, Environment $twig, RequestStack $request, $templatePath) {
        $this->manager      = $manager;
        $this->twig         = $twig;
        // Récupérer la route de la page grâce à RequestStack qui contient un objet request récupéré via getCurrentRequest qui lui même a des attributes dont des parameters dans lesquels se trouvent la _route
        $this->route        = $request->getCurrentRequest()->attributes->get('_route');

        $this->templatePath = $templatePath;

    }

    /**
     * Afficher les données triées avec une limite par page
     *
     * @return void
     */
    public function getData() {
        // Préciser les erreurs potentielles pour les prochains dev
        if(empty($this->entityClass)) {
            throw new \Exception("Vous n'avez pas spcifié l'entité sur laquelle nous devons paginer. Utiliser la methode setEntityClass() de l'objet du Service Pagination.");
        }
        // Nombre limite des annonces à afficher
        $limit = 10;
        // Calculer l'offset, d'où on part par rapport aux données qui remontent : la page où l'on se trouve multiplié par la limite et moins la limite...
        $offset = $this->currentPage * $this->limit - $this->limit;

        // Demander au repo de trouver les éléments
        $repo = $this->manager->getRepository($this->entityClass);
        $data = $repo->findBy([], [], $this->limit, $offset); //1er param: tableau de critère de recherche; 2eme param: tableau de critere pour ordonner les informations; 3eme param: nombre d'élément à remonter; 4eme paramt: d'où on commence.

        return $data;
    }
 
    /**
     * Nombre de page par rapport aux nombres d'éléments dans l'entité
     *
     * @return void
     */
    public function getPages() {
        // Préciser les erreurs potentielles pour les prochains dev
        if(empty($this->entityClass)) {
            throw new \Exception("Vous n'avez pas spcifié l'entité sur laquelle nous devons paginer. Utiliser la methode setEntityClass() de l'objet du Service Pagination.");
        }

        // Nombre d'enregistrements de la table
        $repo = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findAll());
        // Diviser et arrondir
        $pages = ceil($total / $this->limit);

        return $pages;
    }

    /**
     * Afficher twig
     *
     * @return void
     */
    public function displayTwig() {

        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route,
        ]);
    }

    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;
        return $this;
    }
    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }
    public function setPage($page) {
        $this->currentPage = $page;
        return $this;
    }
    public function setRoute($route) {
        $this->route = $route;
        return $this;
    }
    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
        return $this;
    }

    public function getEntityClass() {
        return $this->entityClass;
    }
    public function getLimit() {
        return $this->limit;
    }
    public function getPage() {
        return $this->currentPage;
    }
    public function getRoute() {
        return $this->route;
    }
    public function getTemplatePath() {
        return $this->templatePath;
    }

}