<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Archivo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Archivo>
 */
class ArchivoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Archivo::class);
    }

    // public function findArchivosVisiblesParaUser(User $user, ?int $clienteId = null): QueryBuilder
    // {
    //     $qb = $this->createQueryBuilder('a');

    //     if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
    //         // Usuarios comunes: solo archivos asignados a ellos + no expirados
    //         $qb->andWhere('a.usuario_cliente_asignado = :user')
    //         ->setParameter('user', $user)
    //         ->andWhere('a.expira = false OR (a.expira = true AND a.fecha_expira >= :hoy)')
    //         ->setParameter('hoy', new \DateTimeImmutable('today'));
    //     } else {
    //         // Admin: puede ver todo, o filtrar por cliente
    //         if ($clienteId) {
    //             $qb->andWhere('a.usuario_cliente_asignado = :clienteId')
    //             ->setParameter('clienteId', $clienteId);
    //         }
    //     }

    //     return $qb;
    // }

    //    /**
    //     * @return Archivo[] Returns an array of Archivo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Archivo
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
