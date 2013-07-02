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

use VinceT\BaseBundle\Twig\VinceTBaseExtension;

/**
 * Generator base class
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
abstract class Generator implements GeneratorInterface
{
    protected $skeletonDir = null;
    private $_bundleName = null;

    /**
     * __construct
     *
     * @param string $bundleName Name of the bundle to make generation
     */
    public function __construct($bundleName = '')
    {
        $this->setSkeletonDir(__DIR__.'/../Resources/skeletons/');
        $this->setBundleName($bundleName);
    }

    /**
     * render a twig template
     *
     * @param string $template   Template filename
     * @param array  $parameters Twig parameters
     *
     * @return string
     */
    protected function render($template, $parameters)
    {
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($this->getSkeletonDir()),
            array(
                'debug'            => true,
                'cache'            => false,
                'strict_variables' => true,
                'autoescape'       => false,
            )
        );
        $twig->addExtension(new VinceTBaseExtension());

        return $twig->render($template, $parameters);
    }

    /**
     * render a file
     *
     * @param string $template   Template filename
     * @param string $target     Output filename
     * @param array  $parameters Twig parameters
     *
     * @return [type]
     */
    protected function renderFile($template, $target, $parameters)
    {
        if ( is_file($target) ) {
            return sprintf('<comment>%s already exists</comment>', $target);
        }
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        if ( file_put_contents($target, $this->render($template, $parameters)) !== false ) {
            return sprintf('<info>Create %s</info>', $target);
        } else {

        }
    }

    /**
     * Get entity name
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata [description]
     *
     * @return string
     */
    protected function getEntityNameFromMetadata($metadata)
    {
        $name_explode = explode('\\', $metadata->name);

        return $name_explode[count($name_explode)-1];
    }

    /**
     * Camelize a string
     *
     * @param string $string [description]
     *
     * @return string
     */
    protected function camelize($string)
    {
        $string = preg_replace('#([A-Z])#', '_\\1', $string);
        $string = strtolower($string);
        $string = trim($string, '_');

        return $string;
    }

    /**
     * getBundleName
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->_bundleName;
    }

    /**
     * setBundleName
     *
     * @param string $bundleName Name of the bundle to make generation
     *
     * @return Generator
     */
    public function setBundleName($bundleName)
    {
        $this->_bundleName = $bundleName;

        return $this;
    }

    /**
     * getSkeletonDir
     *
     * @return string
     */
    public function getSkeletonDir()
    {
        return $this->skeletonDir;
    }

    /**
     * setSkeletonDir
     *
     * @param string $skeletonDir Path to skeletons files
     *
     * @return Generator
     */
    public function setSkeletonDir($skeletonDir)
    {
        $this->skeletonDir = $skeletonDir;

        return $this;
    }

}
