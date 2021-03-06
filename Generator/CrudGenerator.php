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
namespace VinceT\BaseBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Crud generator.
 *
 * @category VinceT
 *
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 *
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class CrudGenerator extends Generator
{
    protected $filesystem;
    protected $routePrefix;
    protected $routeNamePrefix;
    protected $bundle;
    protected $entity;
    protected $metadata;
    protected $format;
    protected $actions;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem  A Filesystem instance
     * @param string     $skeletonDir Path to the skeleton directory
     */
    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->setSkeletonDir($skeletonDir);
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface   $bundle           A bundle object
     * @param string            $entity           The entity relative class name
     * @param ClassMetadataInfo $metadata         The entity class metadata
     * @param string            $format           The configuration format (xml, yaml, annotation)
     * @param string            $routePrefix      The route name prefix
     * @param array             $needWriteActions Wether or not to generate write actions
     *
     * @throws \RuntimeException
     */
    public function generate($bundle, $entity, ClassMetadataInfo $metadata, $format, $routePrefix, $needWriteActions)
    {
        $this->routePrefix = $routePrefix;
        $this->routeNamePrefix = str_replace('/', '_', $routePrefix);
        $this->actions = $needWriteActions ? array('index', 'show', 'new', 'edit', 'delete') : array('index', 'show');

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The CRUD generator does not support entity classes with multiple primary keys.');
        }

        if (!in_array('id', $metadata->identifier)) {
            throw new \RuntimeException('The CRUD generator expects the entity object has a primary key field named "id" with a getId() method.');
        }

        $this->entity = $entity;
        $this->bundle = $bundle;
        $this->metadata = $metadata;
        $this->setFormat($format);

        $this->generateControllerClass();

        $dir = sprintf('%s/Resources/views/%s', $this->bundle->getPath(), str_replace('\\', '/', $this->entity));

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }

        $this->generateIndexView($dir);

        if (in_array('show', $this->actions)) {
            $this->generateShowView($dir);
        }

        if (in_array('edit', $this->actions)) {
            $this->generateEditView($dir);
        }

        $this->generateTestClass();
        $this->generateConfiguration();
    }

    /**
     * Sets the configuration format.
     *
     * @param string $format The configuration format
     */
    protected function setFormat($format)
    {
        switch ($format) {
        case 'yml':
        case 'xml':
        case 'php':
        case 'annotation':
            $this->format = $format;
            break;
        default:
            $this->format = 'yml';
            break;
        }
    }

    /**
     * Generates the routing configuration.
     */
    protected function generateConfiguration()
    {
        if (!in_array($this->format, array('yml', 'xml', 'php'))) {
            return;
        }

        $target = sprintf(
            '%s/Resources/config/routing/%s.%s',
            $this->bundle->getPath(),
            strtolower(str_replace('\\', '_', $this->entity)),
            $this->format
        );

        $this->renderFile(
            'config/routing.'.$this->format.'.twig',
            $target,
            array(
                'actions' => $this->actions,
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'bundle' => $this->bundle->getName(),
                'entity' => $this->entity,
            )
        );
    }

    /**
     * Generates the controller class only.
     */
    protected function generateControllerClass()
    {
        $dir = $this->bundle->getPath();

        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Controller/%s/%sController.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        if (file_exists($target)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists.');
        }

        $this->renderFile(
            'controller.php.twig',
            $target,
            array(
                'actions' => $this->actions,
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'dir' => $this->skeletonDir,
                'bundle' => $this->bundle->getName(),
                'entity' => $this->entity,
                'entity_class' => $entityClass,
                'namespace' => $this->bundle->getNamespace(),
                'entity_namespace' => $entityNamespace,
                'format' => $this->format,
            )
        );
    }

    /**
     * Generates the functional test class only.
     */
    protected function generateTestClass()
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $dir = $this->bundle->getPath().'/Tests/Controller';
        $target = $dir.'/'.str_replace('\\', '/', $entityNamespace).'/'.$entityClass.'ControllerTest.php';

        $this->renderFile(
            'tests/test.php.twig',
            $target,
            array(
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'entity' => $this->entity,
                'entity_class' => $entityClass,
                'namespace' => $this->bundle->getNamespace(),
                'entity_namespace' => $entityNamespace,
                'actions' => $this->actions,
                'form_type_name' => strtolower(str_replace('\\', '_', $this->bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.$entityClass.'Type'),
                'dir' => $this->skeletonDir,
            )
        );
    }

    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateIndexView($dir)
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);

        $this->renderFile(
            'views/index.html.twig',
            $dir.'/index.html.twig',
            array(
                'dir' => $this->skeletonDir,
                'entity' => $this->entity,
                'fields' => $this->metadata->fieldMappings,
                'actions' => $this->actions,
                'record_actions' => $this->getRecordActions(),
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'bundle' => $this->bundle->getName(),
                'entity_class' => $entityClass,
            )
        );
    }

    /**
     * Generates the show.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateShowView($dir)
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);

        $this->renderFile(
            'views/show.html.twig',
            $dir.'/show.html.twig',
            array(
                'dir' => $this->skeletonDir,
                'entity' => $this->entity,
                'fields' => $this->metadata->fieldMappings,
                'actions' => $this->actions,
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'bundle' => $this->bundle->getName(),
                'entity_class' => $entityClass,
            )
        );
    }

    /**
     * Generates the edit.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateEditView($dir)
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);

        $this->renderFile(
            'views/edit.html.twig',
            $dir.'/edit.html.twig',
            array(
                'dir' => $this->skeletonDir,
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'entity' => $this->entity,
                'actions' => $this->actions,
                'bundle' => $this->bundle->getName(),
                'entity_class' => $entityClass,
            )
        );
    }

    /**
     * Returns an array of record actions to generate (edit, show).
     *
     * @return array
     */
    protected function getRecordActions()
    {
        return array_filter(
            $this->actions,
            function ($item) {
                return in_array($item, array('show', 'edit'));
            }
        );
    }
}
