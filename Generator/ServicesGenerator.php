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

use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Yaml;

/**
 * Generate services.yml file.
 *
 * @category VinceT
 *
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 *
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class ServicesGenerator extends Generator
{
    /**
     * generate function.
     *
     * @param string        $namespace Namespace of the bundle
     * @param string        $basePath  Path to the bundle root dir
     * @param ClassMetadata $metadata  Entity metadata
     *
     * @return [type]
     */
    public function generate($namespace, $basePath, ClassMetadata $metadata)
    {
        $out = array();
        $out[] = $this->generateManagers($namespace, $basePath, $metadata);
        $out[] = $this->generateAdmins($namespace, $basePath, $metadata);

        $out = array_merge(
            $out,
            $this->updateExtension($namespace, $basePath)
        );

        return $out;
    }

    protected function generateManagers($namespace, $basePath, ClassMetadata $metadata)
    {
        $yamlFile = $basePath.'/Resources/config/managers.yml';
        $bundleName = $this->getBundleName();
        $bundleNameCamelized = str_replace('_bundle', '', $this->camelize($bundleName));
        $entityName = $this->getEntityNameFromMetadata($metadata);
        if (is_file($yamlFile)) {
            $config = Yaml::parse(file_get_contents($yamlFile));
        } else {
            $config = array();
        }

        $config['parameters'][$bundleNameCamelized.'.'.strtolower($entityName).'_manager.class'] = $namespace.'\\Manager\\'.$entityName.'Manager';
        $managerService = array(
            'class' => '%'.$bundleNameCamelized.'.'.strtolower($entityName).'_manager.class'.'%',
            'arguments' => array('@service_container'),
        );
        $config['services'][$bundleNameCamelized.'.'.strtolower($entityName).'_manager'] = $managerService;

        $out = Yaml::dump($config, 4);
        if (file_put_contents($yamlFile, $out) !== false) {
            return sprintf('<info>Update %s</info>', $yamlFile);
        } else {
            return sprintf('<error>Unable to update %s</error>', $yamlFile);
        }
    }

    protected function generateAdmins($namespace, $basePath, ClassMetadata $metadata)
    {
        $yamlFile = $basePath.'/Resources/config/admins.yml';
        $bundleName = $this->getBundleName();
        $bundleNameCamelized = str_replace('_bundle', '', $this->camelize($bundleName));
        $entityName = $this->getEntityNameFromMetadata($metadata);
        if (is_file($yamlFile)) {
            $config = Yaml::parse(file_get_contents($yamlFile));
        } else {
            $config = array();
        }

        $adminServices = array(
            'class' => $namespace.'\\Admin\\'.$entityName.'Admin',
            'tags' => array(
                array(
                    'name' => 'sonata.admin',
                    'manager_type' => 'orm',
                    'group' => str_replace('Bundle', '', $bundleName),
                    'label' => $entityName,
                    'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                ),
            ),
            'arguments' => array(
                null,
                $metadata->rootEntityName,
                $bundleName.':Admin/'.$entityName.'Admin',
            ),
            'calls' => array(
                array(
                    'setTranslationDomain',
                    array($bundleName.$entityName),
                ),
                array(
                    'setModelManager',
                    array('@'.$bundleNameCamelized.'.'.strtolower($entityName).'_manager'),
                ),
            ),
        );
        if (isset($config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)])) {
            $current = $config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)];
            $keys = array('class', 'tags', 'arguments', 'calls');
            foreach ($keys as $key) {
                if (isset($current[$key])) {
                    $adminServices[$key] = $current[$key];
                }
            }
        }
        $config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)] = $adminServices;

        $out = Yaml::dump($config, 4);
        if (file_put_contents($yamlFile, $out) !== false) {
            return sprintf('<info>Update %s</info>', $yamlFile);
        } else {
            return sprintf('<error>Unable to update %s</error>', $yamlFile);
        }
    }

    protected function updateExtension($namespace, $basePath)
    {
        $out = array();
        $bundleName = $this->getBundleName();
        $file = $basePath.'/DependencyInjection/'.str_replace('Bundle', 'Extension', $bundleName).'.php';
        $content = file_get_contents($file);
        $loadAdmins = !preg_match('/load\([\'\"]admins.yml[\'\"]\)/', $content);
        $loadManagers = !preg_match('/load\([\'\"]managers.yml[\'\"]\)/', $content);

        if ($loadAdmins || $loadManagers) {
            $out[] = sprintf('<error>You must update your bundle\'s extension in %s.</error>', $file);
        }
        if ($loadAdmins) {
            $out[] = sprintf('    <error>-> You must load the admins.yml file.</error>');
        }
        if ($loadManagers) {
            $out[] = sprintf('    <error>-> You must load the managers.yml file.</error>');
        }

        return $out;
    }
}
