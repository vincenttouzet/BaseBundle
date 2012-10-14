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

namespace VinceT\BaseBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Generate Admin class
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class AdminControllerGenerator extends Generator
{
    /**
     * generate function
     *
     * @param string        $namespace Namespace of the bundle
     * @param string        $basePath  Path to the bundle root dir
     * @param ClassMetadata $metadata  Entity metadata
     *
     * @return [type]
     */
    public function generate($namespace, $basePath, ClassMetadata $metadata)
    {
        $entityName = $this->getEntityNameFromMetadata($metadata);
        $fileName = sprintf(
            '%s/Controller/Admin/%sAdminController.php',
            $basePath,
            $entityName
        );

        $parameters = array(
            'namespace' => $namespace,
            'entityName' => $entityName,
            'metadata' => $metadata,
        );

        return $this->renderFile('admincontroller.php.twig', $fileName, $parameters);
    }

}