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

namespace VinceT\BaseBundle\Twig;

/**
 * Twig extension
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class VinceTBaseExtension extends \Twig_Extension
{
    /**
     * define filters
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'camelizeBundle' => new \Twig_Filter_Method($this, 'camelizeBundle'),
            'camelize' => new \Twig_Filter_Method($this, 'camelize'),
        );
    }

    /**
     * camelize
     *
     * @param string $string String to camelize
     *
     * @return string
     */
    public function camelize($string)
    {
        $string = preg_replace('#([A-Z])#', '_\\1', $string);
        $string = strtolower($string);
        $string = trim($string, '_');
        return $string;
    }

    /**
     * camelize a bundle name
     *
     * @param string $string Bundle name to camelize
     *
     * @return string
     */
    public function camelizeBundle($string)
    {
        $string = $this->camelize($string);
        $string = str_replace('_bundle', '', $string);
        return $string;
    }

    /**
     * [getName description]
     *
     * @return string
     */
    public function getName()
    {
        return 'vince_t_base_extension';
    }
}