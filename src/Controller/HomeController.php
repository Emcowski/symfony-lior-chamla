<?php

namespace App\Controller;

use App\Repository\AdRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {

    /**
     * @Route("/", name="homepage")
     *
     */
    public function home(AdRepository $adRepo, UserRepository $userRepo) {

        return $this->render(// render prend 2param, c'est une fonction qui interprète un fichier Twig sous la forme de réponse
            // Le 1er est le chemin du fichier Twig sans mettre le chemin template
            'home.html.twig', // Le 2ème est un tableau associatif lié à la vue Twig, on lui passe des tableaux, des var etc. qui contiennent les données à afficher
            [
            'ads' => $adRepo->findBestAds(3),
            'users' => $userRepo->findBestUsers(),
            ]
        );

    }

}

?>