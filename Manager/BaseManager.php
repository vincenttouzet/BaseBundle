<?php

/**
 * This file is part of VinceTBaseBundle for Symfony2.
 *
 * @category VinceT
 *
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 *
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
namespace VinceT\BaseBundle\Manager;

use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This file is part of VinceTBaseBundle for Symfony2.
 *
 * @category VinceT
 *
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 *
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class BaseManager extends ModelManager implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * __construct.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        parent::__construct($this->container->get('doctrine'));
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Persist an object into database.
     *
     * @param Object $object Object
     * @param bool   $flush  Flush entity manager
     */
    public function persist($object, $flush = true)
    {
        if ($object->getId()) {
            $this->update($object, $flush);
        } else {
            $this->create($object, $flush);
        }
    }

    /**
     * create new entity in database.
     *
     * @param Object  $object Object
     * @param Boolean $flush  Flush entity manager
     *
     * @see Sonata\DoctrineORMAdminBundle\Model\ModelManager::create()
     */
    public function create($object, $flush = true)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $this->preCreate($object);
            $entityManager->persist($object);
            if ($flush) {
                $entityManager->flush();
            }
            $this->postCreate($object);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * process before create new entity in database.
     *
     * @param Object $object Object
     */
    public function preCreate($object)
    {
    }

    /**
     * process after create new entity in database.
     *
     * @param Object $object Object
     */
    public function postCreate($object)
    {
    }

    /**
     * update an object into database.
     *
     * @param Object  $object Object
     * @param Boolean $flush  Flush entity manager
     *
     * @see Sonata\DoctrineORMAdminBundle\Model\ModelManager::update()
     */
    public function update($object, $flush = true)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $this->preUpdate($object);
            $entityManager->persist($object);
            if ($flush) {
                $entityManager->flush();
            }
            $this->postUpdate($object);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * process before update an object into database.
     *
     * @param Object $object Object
     */
    public function preUpdate($object)
    {
    }

    /**
     * process after update an object into database.
     *
     * @param Object $object Object
     */
    public function postUpdate($object)
    {
    }

    /**
     * delete an object from database.
     *
     * @param Object  $object Object
     * @param Boolean $flush  Flush entity manager
     *
     * @see Sonata\DoctrineORMAdminBundle\Model\ModelManager::delete()
     */
    public function delete($object, $flush = true)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $this->preDelete($object);
            $entityManager->remove($object);
            if ($flush) {
                $entityManager->flush();
            }
            $this->postDelete($object);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * process before delete an object from database.
     *
     * @param Object $object Object
     */
    public function preDelete($object)
    {
    }

    /**
     * process after delete an object from database.
     *
     * @param Object $object Object
     */
    public function postDelete($object)
    {
    }

    /**
     * Retrieve objects from a batch request.
     *
     * @param string              $class      Class name
     * @param ProxyQueryInterface $queryProxy ProxyQueryInterface instance
     *
     * @throws \Exception
     */
    public function getBatchObjects($class, ProxyQueryInterface $queryProxy)
    {
        $queryProxy->select('DISTINCT '.$queryProxy->getRootAlias());

        return $queryProxy->getQuery()->execute();
    }

    /**
     * Override of Sonata\DoctrineORMAdminBundle\Model\ModelManager::batchDelete.
     *
     * @param string              $class      class name
     * @param ProxyQueryInterface $queryProxy ProxyQueryInterface instance
     *
     * @throws \Exception
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        $objects = $this->getBatchObjects($class, $queryProxy);
        foreach ($objects as $object) {
            $this->delete($object);
        }
    }

    /**
     * Get a repository.
     *
     * @param string $name Entity shortcut name
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($name = null)
    {
        if (is_null($name) || empty($name)) {
            $manager_class = get_class($this);
            $name = str_replace(array('\\Manager\\', 'Manager', '\\'), array(':', '', ''), $manager_class);
            $name = str_replace(
                array('\\Manager\\', '\\Bundle\\', 'Manager', '\\'),
                array(':', '', ''),
                $manager_class
            );
        }

        return $this->container->get('doctrine.orm.entity_manager')->getRepository($name);
    }
}
