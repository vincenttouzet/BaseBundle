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
        if ( !file_exists($basePath.'/Resources/translations') ) {
            mkdir($basePath.'/Resources/translations', 0777, true);
        }
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
        $entityNameCamelized = $this->camelize($entityName);
        $yamlFile = $basePath.'/Resources/translations/'.$this->getBundleName().$entityName.'.en.yml';
        $trans = $this->getTrans($yamlFile);

        if ( !array_key_exists($entityName, $trans) ) {
            $trans[$entityName] = $entityName;
        }

        if ( !array_key_exists('link_'.$entityNameCamelized.'_list', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_list'] = $entityName.' List';
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_show', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_show'] = 'Show '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_create', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_create'] = 'Create '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_edit', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_edit'] = 'Edit '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_delete', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_delete'] = 'Delete '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_history', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_history'] = $entityName.' History';
        }

        foreach ($metadata->fieldMappings as $field) {
            $fieldName = $field['fieldName'];
            $key = $this->camelize($fieldName);
            if ( !array_key_exists('label_'.$key, $trans['list']) ) {
                $trans['list']['label_'.$key] = $this->toText($fieldName);
            }
            if ( !array_key_exists('label_'.$key, $trans['filter']) ) {
                $trans['filter']['label_'.$key] = $this->toText($fieldName);
            }
            if ( !array_key_exists('label_'.$key, $trans['show']) ) {
                $trans['show']['label_'.$key] = $this->toText($fieldName);
            }
            if ( !array_key_exists('label_'.$key, $trans['form']) ) {
                $trans['form']['label_'.$key] = $this->toText($fieldName);
            }
        }
        $trans['list']['label__action'] = 'Actions';

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
        $entityNameCamelized = $this->camelize($entityName);
        $yamlFile = $basePath.'/Resources/translations/'.$this->getBundleName().$entityName.'.fr.yml';
        $trans = $this->getTrans($yamlFile);

        if ( !array_key_exists('link_'.$entityNameCamelized.'_list', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_list'] = $entityName.'s';
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_show', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_show'] = 'Fiche '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_create', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_create'] = 'Création de '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_edit', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_edit'] = 'Édition de '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_delete', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_delete'] = 'Suppression de '.$entityName;
        }
        if ( !array_key_exists('link_'.$entityNameCamelized.'_history', $trans['breadcrumb']) ) {
            $trans['breadcrumb']['link_'.$entityNameCamelized.'_history'] = 'Historique de '.$entityName;
        }

        foreach ($metadata->fieldMappings as $field) {
            $fieldName = $field['fieldName'];
            $key = $this->camelize($fieldName);
            if ( !array_key_exists('label_'.$key, $trans['list']) ) {
                $trans['list']['label_'.$key] = $this->toText($fieldName);
            }
            if ( !array_key_exists('label_'.$key, $trans['filter']) ) {
                $trans['filter']['label_'.$key] = $this->toText($fieldName);
            }
            if ( !array_key_exists('label_'.$key, $trans['show']) ) {
                $trans['show']['label_'.$key] = $this->toText($fieldName);
            }
            if ( !array_key_exists('label_'.$key, $trans['form']) ) {
                $trans['form']['label_'.$key] = $this->toText($fieldName);
            }
        }
        $trans['list']['label__action'] = 'Actions';

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

    /**
     * Load translation file
     *
     * @param string $filename Filename
     *
     * @return array
     */
    protected function getTrans($filename)
    {
        if ( is_file($filename) ) {
            $trans = Yaml::parse($filename);
        } else {
            $trans = array();
        }

        $keys = array('breadcrumb', 'list', 'filter', 'show', 'form');
        foreach ($keys as $key) {
            if ( !array_key_exists($key, $trans) ) {
                $trans[$key] = array();

            }
        }

        return $trans;
    }
}
