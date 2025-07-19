<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use App\Entity\Memo;
use App\Entity\User;
use App\Entity\Categoria;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

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
    
    

    public function configureAssets(): Assets
    {
        return Assets::new()->addWebpackEncoreEntry('admin');
    }

    public function configureMenuItems(): iterable
    {

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::subMenu(label: 'Memos')->setSubItems([
         MenuItem::linkToCrud('Memos', 'fa-solid fa-book', Memo::class),
         MenuItem::linkToCrud('Items', 'fas fa-list', Item::class),
        ]);
        yield MenuItem::linkToCrud('Categorias', 'fas fa-list', Categoria::class);
        yield MenuItem::section('Usuario');
        yield MenuItem::linkToCrud('Usuarios','fa-solid fa-users', User::class);
       
    }
    
}
