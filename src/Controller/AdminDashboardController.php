<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function indexDashboard(ObjectManager $manager, StatsService $statsService)
    {
        // Aller chercher le tableau des données créées dans le service
        $stats = $statsService->getStats();
        // Générer les meilleures et moins bonnes annonces en fonction des notes (req dans le service)
        $bestAds = $statsService->getAdsStats('DESC');
        $worstAds = $statsService->getAdsStats('ASC');

        return $this->render('admin/dashboard/admin_dashboard.html.twig', [
            'stats' => $stats,
            'bestAds' => $bestAds,
            'worstAds' => $worstAds,
            
        ]);
    }

}
