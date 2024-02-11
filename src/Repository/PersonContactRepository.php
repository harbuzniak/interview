<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\PersonContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonContact>
 *
 * @method PersonContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonContact[]    findAll()
 * @method PersonContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonContact::class);
    }
}
