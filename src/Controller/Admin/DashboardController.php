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
        ->setTitle('<img src="/img/logo_login.svg" style="height:80px;">')
        ->setFaviconPath('build/images/favicon.png') // Tu imagen SVG
        ->disableDarkMode(); // Esta línea deshabilita la opción de tema oscuro
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
           // Cabecera para Memos
        yield MenuItem::section('Memos');
        yield MenuItem::linkToCrud('Listado de memos', '', Memo::class);
        yield MenuItem::linkToCrud('Ítems', '', Item::class);
        
        // Cabecera para Archivos
        yield MenuItem::section('Archivos');
        yield MenuItem::linkToCrud('Lista de archivos', '', Archivo::class);
        yield MenuItem::linkToCrud('Categorías', '', Categoria::class);
        
        // Cabecera para Usuarios
        yield MenuItem::section('Usuarios');
        yield MenuItem::linkToCrud('Listado de clientes', '', User::class)
            ->setController(ClienteCrudController::class);
        yield MenuItem::linkToCrud('Listado de administradores', '', User::class)
            ->setController(AdministradorCrudController::class);

        } elseif ($this->isGranted('ROLE_USER')) {
            yield MenuItem::linkToCrud('Archivos', 'fa-solid fa-folder-open', Archivo::class);
        }
    }

    
}
