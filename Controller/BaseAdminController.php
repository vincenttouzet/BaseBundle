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

namespace VinceT\BaseBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
class BaseAdminController extends CRUDController
{
    /**
     * createAction override from CRUDController.
     *
     * @see Sonata\AdminBundle\Controller\CRUDController::create()
     *
     * @return Response|RedirectResponse
     */
    public function createAction()
    {
        try {
            return parent::createAction();
        } catch (\Exception $e) {
            return $this->createActionException($e);
        }
    }

    /**
     * createActionException.
     *
     * @param \Exception $e Throwed exception
     *
     * @return Response|RedirectResponse
     */
    protected function createActionException(\Exception $e)
    {
        $this->handleException($e);

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * editAction override from CRUDController.
     *
     * @param int|null $id Object id
     *
     * @see Sonata\AdminBundle\Controller\CRUDController::edit()
     *
     * @return Response|RedirectResponse
     */
    public function editAction($id = null)
    {
        try {
            return parent::editAction($id);
        } catch (\Exception $e) {
            return $this->editActionException($e);
        }
    }

    /**
     * editActionException.
     *
     * @param \Exception $e Throwed exception
     *
     * @return Response|RedirectResponse
     */
    protected function editActionException(\Exception $e)
    {
        $this->handleException($e);
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        return $this->redirectTo($object);
    }

    /**
     * deleteAction override from CRUDController.
     *
     * @param int|null $id Object id
     *
     * @see Sonata\AdminBundle\Controller\CRUDController::delete()
     *
     * @return Response|RedirectResponse
     */
    public function deleteAction($id)
    {
        try {
            return parent::deleteAction($id);
        } catch (\Exception $e) {
            return $this->deleteActionException($e);
        }
    }

    /**
     * deleteActionException.
     *
     * @param \Exception $e Throwed exception
     *
     * @return Response|RedirectResponse
     */
    protected function deleteActionException(\Exception $e)
    {
        $this->handleException($e);

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * execute a batch delete.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchActionDelete(ProxyQueryInterface $query)
    {
        try {
            return parent::batchActionDelete($query);
        } catch (\Exception $e) {
            return $this->batchActionDeleteException($e);
        }
    }

    /**
     * batchActionDeleteException.
     *
     * @param \Exception $e Throwed exception
     *
     * @return Response|RedirectResponse
     */
    protected function batchActionDeleteException(\Exception $e)
    {
        $this->handleException($e);

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * @param \Exception $exception
     */
    protected function handleException(\Exception $exception)
    {
        if ($this->get('kernel')->isDebug()
            || $exception instanceof AccessDeniedException
        ) {
            throw $exception;
        }
        $this->get('session')->getFlashBag()->add('sonata_flash_error', $exception->getMessage());
    }
}
