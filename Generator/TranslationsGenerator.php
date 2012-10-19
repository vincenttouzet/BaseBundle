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
use Symfony\Component\Yaml\Yaml;

/**
 * Generate services.yml file
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class TranslationsGenerator extends Generator
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
        $out = array();
        $out[] = $this->generateLangEn($namespace, $basePath, $metadata);
        $out[] = $this->generateLangfr($namespace, $basePath, $metadata);
        return $out;
    }

    /**
     * generate function
     *
     * @param string        $namespace Namespace of the bundle
     * @param string        $basePath  Path to the bundle root dir
     * @param ClassMetadata $metadata  Entity metadata
     *
     * @return [type]
     */
    protected function generateLangEn($namespace, $basePath, ClassMetadata $metadata)
    {
        $entityName = $this->getEntityNameFromMetadata($metadata);
        $yamlFile = $basePath.'/Resources/translations/'.$this->getBundleName().$entityName.'.en.yml';
        if ( is_file($yamlFile) ) {
            $trans = Yaml::parse($yamlFile);
        } else {
            $trans = array();            
        }

        $trans['breadcrumb']['link_'.strtolower($entityName).'_list'] = $entityName.' List';
        $trans['breadcrumb']['link_'.strtolower($entityName).'_show'] = 'Show '.$entityName;
        $trans['breadcrumb']['link_'.strtolower($entityName).'_create'] = 'Create '.$entityName;
        $trans['breadcrumb']['link_'.strtolower($entityName).'_edit'] = 'Edit '.$entityName;
        $trans['breadcrumb']['link_'.strtolower($entityName).'_history'] = $entityName.' History';

        foreach ( $metadata->fieldMappings as $field ) {
            $trans['list']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
            $trans['filter']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
            $trans['show']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
            $trans['form']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
        }


        $out = Yaml::dump($trans, 4);
        if ( file_put_contents($yamlFile, $out) !== false ) {
            return sprintf('<info>Update %s</info>', $yamlFile);
        } else {
            return sprintf('<error>Unable to update %s</error>', $yamlFile);
        }
    }

    /**
     * generate function
     *
     * @param string        $namespace Namespace of the bundle
     * @param string        $basePath  Path to the bundle root dir
     * @param ClassMetadata $metadata  Entity metadata
     *
     * @return [type]
     */
    protected function generateLangFr($namespace, $basePath, ClassMetadata $metadata)
    {
        $entityName = $this->getEntityNameFromMetadata($metadata);
        $yamlFile = $basePath.'/Resources/translations/'.$this->getBundleName().$entityName.'.fr.yml';
        if ( is_file($yamlFile) ) {
            $trans = Yaml::parse($yamlFile);
        } else {
            $trans = array();            
        }

        $trans['breadcrumb']['link_'.strtolower($entityName).'_list'] = $entityName.'s';
        $trans['breadcrumb']['link_'.strtolower($entityName).'_show'] = 'Vue de '.$entityName;
        $trans['breadcrumb']['link_'.strtolower($entityName).'_create'] = 'Création de '.$entityName;
        $trans['breadcrumb']['link_'.strtolower($entityName).'_edit'] = 'Édition de '.$entityName;
        $trans['breadcrumb']['link_'.strtolower($entityName).'_history'] = 'Historique de '.$entityName;

        foreach ( $metadata->fieldMappings as $field ) {
            $trans['list']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
            $trans['filter']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
            $trans['show']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
            $trans['form']['label_'.$field['fieldName']] = $this->toText($field['fieldName']);
        }


        $out = Yaml::dump($trans, 4);
        if ( file_put_contents($yamlFile, $out) !== false ) {
            return sprintf('<info>Update %s</info>', $yamlFile);
        } else {
            return sprintf('<error>Unable to update %s</error>', $yamlFile);
        }
    }

    /**
     * Transform a fieldName to a text
     *
     * @param string $fieldName [description]
     *
     * @return string
     */
    protected function toText($fieldName)
    {
        $fieldName = $this->camelize($fieldName);
        $fieldName = str_replace('_', ' ', $fieldName);
        return ucfirst($fieldName);
    }


}