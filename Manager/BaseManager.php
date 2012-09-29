<?php
/**
 * This file is part of VinceTBaseBundle for Symfony2
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */

namespace VinceT\BaseBundle\Manager;

use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This file is part of VinceTBaseBundle for Symfony2
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class BaseManager extends ModelManager implements ContainerAwareInterface
{
    protected $container = null;

    /**
     * __construct
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
     *
     * @return null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * create new entity in database
     *
     * @param Object $object Object
     *
     * @see Sonata\DoctrineORMAdminBundle\Model\ModelManager::create()
     * @return null
     */
    public function create($object)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $this->preCreate($object);
            $entityManager->persist($object);
            $entityManager->flush();
            $this->postCreate($object);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * process before create new entity in database
     *
     * @param Object $object Object
     *
     * @return null
     */
    public function preCreate($object) 
    {

    }

    /**
     * process after create new entity in database
     *
     * @param Object $object Object
     *
     * @return null
     */
    public function postCreate($object)
    {

    }

    /**
     * update an object into database
     *
     * @param Object $object Object
     *
     * @see Sonata\DoctrineORMAdminBundle\Model\ModelManager::update()
     * @return null
     */
    public function update($object)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $this->preUpdate($object);
            $entityManager->persist($object);
            $entityManager->flush();
            $this->postUpdate($object);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * process before update an object into database
     *
     * @param Object $object Object
     *
     * @return null
     */
    public function preUpdate($object)
    {

    }

    /**
     * process after update an object into database
     *
     * @param Object $object Object
     *
     * @return null
     */
    public function postUpdate($object)
    {

    }

    /**
     * delete an object from database
     *
     * @param Object $object Object
     *
     * @see Sonata\DoctrineORMAdminBundle\Model\ModelManager::delete()
     * @return null
     */
    public function delete($object)
    {
        try {
            $entityManager = $this->getEntityManager($object);
            $this->preDelete($object);
            $entityManager->remove($object);
            $entityManager->flush();
            $this->postDelete($object);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * process before delete an object from database
     *
     * @param Object $object Object
     *
     * @return null
     */
    public function preDelete($object)
    {

    }

    /**
     * process after delete an object from database
     *
     * @param Object $object Object
     *
     * @return null
     */
    public function postDelete($object)
    {

    }

}