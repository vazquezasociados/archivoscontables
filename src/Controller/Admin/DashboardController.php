<?php

namespace App\Controller\Admin;

use App\Entity\Items;
use App\Entity\Memo;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Application');
    }

    public function configureMenuItems(): iterable
    {

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::subMenu(label: 'Memos')->setSubItems([
        //  MenuItem::linkToCrud('Memos', 'fa-solid fa-book', Memo::class),
        //  MenuItem::linkToCrud('Items', 'fas fa-list', Items::class),
        ]);
        
        yield MenuItem::linkToCrud('Usuarios','fa-solid fa-users', User::class);
    }
    
}
