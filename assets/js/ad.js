/* 
** Ajouter une ligne d'ajout d'image 
*/
jQuery('#add-image').click(function() {
    console.log('OK');
    // Récupérer le numéro des futurs champs à créer
    const index = +jQuery('#widgets-counter').val();

    // Récupérer le prototype des entrées
    const tpl = jQuery('#annonce_images').data('prototype').replace(/__name__/g, index);

    //Injecter le template récupéré au clic du bouton
    jQuery('#annonce_images').append(tpl);

    // Incrémenter le compteur
    jQuery('#widgets-counter').val(index + 1);

    // Gérer le bouton supprimer
    handleDeleteButtons();
});

/* 
** Supprimer une ligne d'ajout d'image 
*/
function handleDeleteButtons() {
    jQuery('button[data-action="delete"]').click(function() {
        const target = this.dataset.target; // this représente le bouton sur lequel on a cliqué (dans un event JS le this représente l'élément HTML qui a déclenché l'event), dataset représente tous les attributs de ce bouton, target car on souhaite accéder au data-target.
        jQuery(target).remove();
    });
}

/*
** Mettre à jour le compteur d'images
*/
function updateCounter() {
    const count = +jQuery('#annonce_images div.form-group').length;
    jQuery('#widgets-counter').val(count);
}

updateCounter();
handleDeleteButtons();
