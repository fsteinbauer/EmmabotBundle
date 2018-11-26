<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 16.05.2018
 * Time: 13:55
 */

namespace EmmabotBundle\Repository;
use CRMBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * ContextRepository
 *
 */
class ContextRepository extends EntityRepository
{
    /**
     * @param $type
     * @param User $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findContext($type, User $user){

        $qb = $this->createQueryBuilder('c')
            ->where('c.type = :type')
            ->andWhere('c.createdAt > :youngerThan')
            ->join('c.createdBy', 'u', 'WITH', 'u.id = :userid')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('type', $type)
            ->setParameter('userid', $user->getId())
            ->setParameter('youngerThan', new \DateTime('-20 minutes'))
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }


    /**
     * @param $type
     * @param User $user
     * @return mixed
     */
    public function removeContext($type, User $user){

        $qb = $this->createQueryBuilder('c')
            ->delete('EmmabotBundle:Context', 'c')
            ->where('c.type LIKE :type')
            ->join('c.createdBy', 'u', 'WITH', 'u.id = :userid')
            ->setParameter('type', $type)
            ->setParameter('userid', $user->getId())
        ;
        return $qb->getQuery()->getResult();
    }

}
