<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\PaginationService;
use App\Form\AdminCommentType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * Afficher les commentaires
     * 
     * @Route("/admin/comments/{page<\d+>?1}", name="admin_comments")
     * Le </d+?> signifie un requirment d'une expression regulière sur l'option page, un chiffre decimal est attendu, le ? signifie qu'il est optionnel, le 1 après ? signifie la valeur par défaut
     * 
     * @param AdRepository $repo
     */
    public function indexComments(CommentRepository $repo, $page, PaginationService $pagination)
    {
        $pagination->setEntityClass(Comment::class)
                    ->setLimit(7)
                    ->setPage($page);

        return $this->render('admin/comment/admin_comments_show.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Editer un commentaire
     * 
     * @Route("/admin/comments/{id}/edit", name="admin_comment_edit")
     * 
     * @param Comment $comment
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function editComment(Comment $comment, Request $request, ObjectManager $manager) {

        $form = $this->createForm(AdminCommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($comment);
            $manager->flush();

            // Ajouter un flash message si tout est ok
            $this->addFlash('success', "Le commentaire <strong>{$comment->getId()}</strong> écrit par <strong>{$comment->getAuthor()->getFullName()}</strong> a bien été modifié.");

            return $this->redirectToRoute('admin_comments');
        }

        return $this->render('admin/comment/admin_comment_edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprimer un commentaire
     *
     * @Route("/admin/comments/{id}/delete", name="admin_comment_delete")
     * 
     * @param Comment $comment
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function deleteComment(Comment $comment, Request $request, ObjectManager $manager) {

        // Supprimer le commentaire passé en param
        $manager->remove($comment);
        // Confirmer la suppression à la bdd
        $manager->flush();
        // Ajouter un flash message si tout est ok
        $this->addFlash('success', "Le commentaire de <strong>{$comment->getAuthor()->getFullName()}</strong> a bien été supprimé.");

        return $this->redirectToRoute('admin_comments');
    }

}
