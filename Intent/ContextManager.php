<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 16.05.2018
 * Time: 14:40
 */

namespace EmmabotBundle\Intent;


use CRMBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use EmmabotBundle\Entity\Context;
use EmmabotBundle\Repository\ContextRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class ContextManager
 *
 * @package EmmabotBundle\Intent
 */
class ContextManager
{

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var ContextRepository
     */
    protected $contextRepository;

    /**
     * @var EntityManager
     */
    protected $manager;


    /**
     * ContextManager Constructor
     *
     * @param TokenStorage $tokenStorage
     * @param ContextRepository $repository
     * @param EntityManager $manager
     */
    public function __construct(TokenStorage $tokenStorage, ContextRepository $repository, EntityManager $manager){

        $this->tokenStorage = $tokenStorage;
        $this->contextRepository = $repository;
        $this->manager = $manager;
    }


    /**
     * @param $type
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getContext($type){

        $user = $this->getUser();

        $context = $this->contextRepository->findContext($type, $user);

        if($context == null) {
            return null;
        }

        return unserialize($context->getData());
    }

    /**
     * @param $type
     * @param $data
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveContext($type, $data){

        $context = new Context($type, $data);

        $this->manager->persist($context);
        $this->manager->flush();

    }

    /**
     * @param $type
     */
    public function removeContext($type){

        $this->contextRepository->removeContext($type, $this->getUser());
    }


    /**
     * @return User
     */
    private function getUser(){

        return $this->tokenStorage->getToken()->getUser();
    }

}