<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {

    /**
     * @Route("/hello/{prenom}/{age}", name="hello")
     * @Route("/hello", name="hello_base")
     * @Route("/hello/{prenom}", name="hello_prenom")
     * 
     * Montre la page qui dit Bonjour
     *
     * @return void
     */
    public function hello($prenom = "anonyme", $age = 0) {
        return $this->render(
            'hello.html.twig',
            [
                'prenom' => $prenom,
                'age' => $age
            ]
        );
        //return new Response("Bonjour " . $prenom . " vous avez " . $age . " ans");

    }


    /**
     * @Route("/", name="homepage")
     *
     */
    public function home() {
        $prenoms = ["Lior" => 31, "Joseph" => 12, "Anne" => 55];

        return $this->render( // render prend 2param, c'est une fonction qui interprète un fichier Twig sous la forme de réponse
            // Le 1er est le chemin du fichier Twig sans mettre le chemin template
            'home.html.twig',
            // Le 2ème est un tableau associatif lié à la vue Twig, on lui passe des tableaux, des var etc. qui contiennent les données à afficher
            [ 
                'title' => "Bonjour à tous!!!",
                'age' => 15,
                'tableau' => $prenoms
            ]
        );
    }

}

?>