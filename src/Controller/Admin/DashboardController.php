<?php

namespace App\Controller\Admin;

use App\Entity\Outing;
use App\Entity\User;
use App\Repository\OutingRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @property UserRepository userRepository
 * @property OutingRepository outingRepository
 */
class DashboardController extends AbstractDashboardController
{


    public function __construct(UserRepository $userRepository, OutingRepository $outingRepository)
    {
        $this->userRepository = $userRepository;
        $this->outingRepository= $outingRepository;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('bundles/EasyAdminBundle/welcome.html.twig', [
                'countAllUser'=> $this->userRepository->countAllUsers(),
                'countAllOutings'=>$this->outingRepository->countAllOutings()
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ENI Sortir');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Users', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('Outings', 'fas fa-list', Outing::class);

    }
}
