<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface {
    
    // Prend la donnée originelle et la transforme pour qu'elle s'affiche comme on le souhaite (par exemple ici format français)
    public function transform($date) {
        if($date === null) {
            return '';
        }
        return $date->format('d/m/Y');
    }

    // Prend la donnée qui arrive du formulaire (donc ici date formatée en français) et la remet dans le sens attendu en datetime
    public function reverseTransform($frenchDate) {

        // Exception si la transformation de la date n'a pas réussi
        if($frenchDate === null) {
            throw new TransformationFailedException("Vous devez fournir une date.");
        }
        $date = \DateTime::createFromFormat('d/m/Y', $frenchDate);

        //Exception si la date est mal formatée, par exemple contient de - au lieu des /
        if($date === false) {
            throw new TransformationFailedException("Le format de la date n'est pas le bon.");
        }

        return $date;
    }
}