<?php

namespace App\Controller\Admin;

use App\Entity\Archivo;
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
        ->setTitle('<img src="/img/logoBackend.svg" style="height:30px;">')
        ->setFaviconPath('build/images/logoBackend.svg'); // Tu imagen SVG
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addWebpackEncoreEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        foreach ($this->getMenuItemsForRole() as $item) {
            yield $item;
        }
    }

    private function getMenuItemsForRole(): iterable
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::subMenu('Memos', 'fa-solid fa-book')->setSubItems([
                MenuItem::linkToCrud('Listado de memos', '', Memo::class),
                MenuItem::linkToCrud('Ítems', '', Item::class),
            ])
            ;

            yield MenuItem::subMenu('Archivos', 'fa-solid fa-folder-open')->setSubItems([
                MenuItem::linkToCrud('Lista de archivos', '', Archivo::class),
                MenuItem::linkToCrud('Categorías', '', Categoria::class),
            ]);
            yield MenuItem::linkToCrud('Clientes', 'fa-solid fa-users', User::class);
        } elseif ($this->isGranted('ROLE_USER')) {
            yield MenuItem::linkToCrud('Archivos', 'fa-solid fa-folder-open', Archivo::class);
        }
    }

    
}
