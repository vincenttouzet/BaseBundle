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

namespace VinceT\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;

use VinceT\BaseBundle\Exception\GeneratorException;

/**
 * GenerateCommand
 *
 * @category VinceT
 * @package  VinceTBaseBundle
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class GenerateCommand extends ContainerAwareCommand
{
    private $_base_path = null;
    private $_namespace = null;

    /**
     * configure command
     *
     * @return null
     */
    protected function configure()
    {
        $this
            ->setName('vincet:generate')
            ->setDescription('Generates Admin, Manager and AdminController classes')
            ->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name')
            ->setHelp(<<<EOF
The <info>vincet:generate</info> command generates Admin, Manager and AdminController classes.

You have to limit generation:

* To a bundle:

  <info>php app/console doctrine:generate:entities MyCustomBundle</info>

* To a single entity:

  <info>php app/console doctrine:generate:entities MyCustomBundle:User</info>
  <info>php app/console doctrine:generate:entities MyCustomBundle/Entity/User</info>

* To a namespace

  <info>php app/console doctrine:generate:entities MyCustomBundle/Entity</info>
EOF
);
    }

    /**
     * execute command
     *
     * @param InputInterface  $input  InputInterface instance
     * @param OutputInterface $output OutputInterface instance
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        try {
            $bundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('name'));

            $output->writeln(sprintf('Generating classes for bundle "<info>%s</info>"', $bundle->getName()));
            $metadata = $manager->getBundleMetadata($bundle);
        } catch (\InvalidArgumentException $e) {
            $name = strtr($input->getArgument('name'), '/', '\\');

            if (false !== $pos = strpos($name, ':')) {
                $name = $this->getContainer()->get('doctrine')->getEntityNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
            }

            if (class_exists($name)) {
                $output->writeln(sprintf('Generating classes for entity "<info>%s</info>"', $name));
                $metadata = $manager->getClassMetadata($name);
            } else {
                $output->writeln(sprintf('Generating classes for namespace "<info>%s</info>"', $name));
                $metadata = $manager->getNamespaceMetadata($name);
            }
        }

        $this->_namespace = str_replace('\\Entity', '', $metadata->getNamespace());
        $path = $metadata->getPath();

        $this->_base_path = sprintf(
            '%s/%s',
            $path,
            str_replace('\\', '/', $this->_namespace)
        );

        $dialog = $this->getHelperSet()->get('dialog');

        foreach ($metadata->getMetadata() as $m) {
            $entityName = $this->_getEntityNameFromMetadata($m);
            $output->writeln('');
            $output->writeln(sprintf('Entity %s', $entityName));
            //var_dump($m);
            
            $generateManager = $dialog->askConfirmation($output, 'Generate Manager ? [yes] ', true);
            if ( $generateManager ) {
                $output->writeln($this->_generateManager($entityName));
            }
            $generateAdmin = $dialog->askConfirmation($output, 'Generate Admin ?[yes] ', true);
            $generateAdminController = false;
            if ( $generateAdmin ) {
                $output->writeln($this->_generateAdmin($entityName, $m));
                $generateAdminController = $dialog->askConfirmation($output, 'Generate AdminController ? [yes] ', true);
                if ( $generateAdminController ) {
                    $output->writeln($this->_generateAdminController($entityName));
                }
            }

            $output->writeln($this->_generateConfig($m, $generateManager, $generateAdmin, $generateAdminController));
        }
    }

    /**
     * generateManager
     *
     * @param string $entity EntityName
     *
     * @throws \VinceT\BaseBundle\Exception\GeneratorException
     * @return string
     */
    private function _generateManager($entity)
    {
        $fileName = sprintf(
            '%s/Manager/%sManager.php',
            $this->_base_path,
            $entity
        );
        
        // generate
        $code = <<<EOF
<?php

namespace {$this->_namespace}\\Manager;

use VinceT\\BaseBundle\\Manager\BaseManager;

class {$entity}Manager extends BaseManager
{
}
EOF;
        return $this->_generateFile($fileName, $code);
    }

    /**
     * _generateAdmin
     *
     * @param string $entity   [description]
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata [description]
     *
     * @return string
     */
    private function _generateAdmin($entity, \Doctrine\ORM\Mapping\ClassMetadata $metadata)
    {
        $fileName = sprintf(
            '%s/Admin/%sAdmin.php',
            $this->_base_path,
            $entity
        );
        
        // generate
        $code = <<<EOF
<?php

namespace {$this->_namespace}\\Admin;

use Sonata\\AdminBundle\\Admin\Admin as BaseAdmin;
use Sonata\\AdminBundle\\Form\\FormMapper;
use Sonata\\AdminBundle\\Datagrid\\DatagridMapper;
use Sonata\\AdminBundle\\Datagrid\\ListMapper;
use Sonata\\AdminBundle\\Show\\ShowMapper;

class {$entity}Admin extends BaseAdmin
{
    protected function configureFormFields(FormMapper \$formMapper)
    {
        \$formMapper

EOF;
        foreach ($metadata->fieldMappings as $field) {
            $options = array();
            if ( $field['type'] == 'boolean' ) {
                $code .= sprintf(
                    '            ->add(\'%s\', null, array(\'required\'=>false))'.PHP_EOL,
                    $field['fieldName']
                );
            } else {
                $code .= sprintf(
                    '            ->add(\'%s\')'.PHP_EOL,
                    $field['fieldName']
                );
            }
        }
        $code .= <<<EOF
        ;
    }

    protected function configureDatagridFilters(DatagridMapper \$datagridMapper)
    {
        \$datagridMapper

EOF;
        foreach ($metadata->fieldMappings as $field) {
            $code .= sprintf(
                '            ->add(\'%s\')'.PHP_EOL,
                $field['fieldName']
            );
        }
        $code .= <<<EOF
        ;
    }

    protected function configureListFields(ListMapper \$listMapper)
    {
        \$listMapper

EOF;
        foreach ($metadata->fieldMappings as $field) {
            $code .= sprintf(
                '            ->add(\'%s\')'.PHP_EOL,
                $field['fieldName']
            );
        }
        $code .= <<<EOF
        ;
    }

    protected function configureShowFields(ShowMapper \$showMapper)
    {
        \$showMapper

EOF;
        foreach ($metadata->fieldMappings as $field) {
            $code .= sprintf(
                '            ->add(\'%s\')'.PHP_EOL,
                $field['fieldName']
            );
        }
        $code .= <<<EOF
        ;
    }
}
EOF;
        return $this->_generateFile($fileName, $code);
    }

    /**
     * _generateAdminController
     *
     * @param string $entity [description]
     *
     * @return string
     */
    private function _generateAdminController($entity)
    {
        $fileName = sprintf(
            '%s/Controller/Admin/%sAdminController.php',
            $this->_base_path,
            $entity
        );
        
        // generate
        $code = <<<EOF
<?php

namespace {$this->_namespace}\\Controller\Admin;

use VinceT\\BaseBundle\\Controller\BaseAdminController;

class {$entity}AdminController extends BaseAdminController
{
}
EOF;
        return $this->_generateFile($fileName, $code);    
    }

    /**
     * _generateFile
     *
     * @param string $fileName [description]
     * @param string $content  [description]
     *
     * @return string
     */
    private function _generateFile($fileName, $content)
    {
        if ( file_exists($fileName) ) {
            return sprintf('File %s already exists.', $fileName);
        }
        $dir = dirname($fileName);
        if ( !file_exists($dir) ) {
            if ( !mkdir($dir) ) {
                throw new GeneratorException(sprintf('Unable to create directory %s', $dir));
            }
        }
        if ( file_put_contents($fileName, $content) === false ) {
            throw new GeneratorException(sprintf('Unable to create file %s', $fileName));
        } else {
            return sprintf('Generate %s', str_replace($this->_base_path, '', $fileName));
        }
    }

    /**
     * _generateConfig
     *
     * @param boolean $generateManager         [description]
     * @param boolean $generateAdmin           [description]
     * @param boolean $generateAdminController [description]
     *
     * @return [type]
     */
    private function _generateConfig($metadata, $generateManager, $generateAdmin, $generateAdminController)
    {
        $yamlFile = $this->_base_path.'/Resources/config/services.yml';
        $bundleName = $this->_getBundleNameFromEntity($metadata->rootEntityName);
        $bundleNameCamelized = str_replace('_bundle', '', $this->_camelize($bundleName));
        $entityName = $this->_getEntityNameFromMetadata($metadata);
        $config = Yaml::parse($yamlFile);

        if ( $generateManager ) {
            $config['parameters'][$bundleNameCamelized.'.'.strtolower($entityName).'_manager.class'] = $this->_namespace.'\\Manager\\'.$entityName.'Manager';
            $config['services'][$bundleNameCamelized.'.'.strtolower($entityName).'_manager']['class'] = '%'.$bundleNameCamelized.'.'.strtolower($entityName).'_manager.class'.'%';
        }

        if ( $generateAdmin ) {
            $config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)]['class'] = $this->_namespace.'\\Admin\\'.$entityName.'Admin';
            $config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)]['tags'] = array(
                array(
                    'name' => 'sonata.admin',
                    'manager_type' => 'orm',
                    'group' => str_replace('Bundle', '', $bundleName),
                    'label' => $entityName,
                    'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                ),
            );
            $arguments = array(null, $metadata->rootEntityName);
            if ( $generateAdminController ) {
                $arguments[] = $bundleName.':Admin/'.$entityName.'\\Admin';
            } else {
                $arguments[] = 'SonataAdminBundle::CRUD';
            }
            $config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)]['arguments'] = $arguments;
            $calls = array(
                array(
                    'setTranslationDomain',
                    array($bundleName)
                ),
            );
            if ( $generateManager ) {
                $calls[]= array(
                    'setModelManager',
                    array('@'.$bundleNameCamelized.'.'.strtolower($entityName).'_manager')
                );
            }
            $config['services'][$bundleNameCamelized.'.admin.'.strtolower($entityName)]['calls'] = $calls;

        }


        $out = Yaml::dump($config, 4);
        if ( file_put_contents($yamlFile, $out) === false ) {
            throw new GeneratorException(sprintf('Unable to create file %s', $yamlFile));
        } else {
            return sprintf('Update %s', str_replace($this->_base_path, '', $yamlFile));
        }
    }

    /**
     * Get a bundle name from a root entity name (e.g: VinceT\\DemoBundle\\Entity\\Post)
     *
     * @param string $rootEntityName Root entity name
     *
     * @return string
     */
    private function _getBundleNameFromEntity($rootEntityName)
    {
        $bundles = $this->getContainer()->get('kernel')->getBundles();
        $bundleName = '';

        foreach($bundles as $type=>$bundle){
            $className = get_class($bundle);

            $entityClass = substr($rootEntityName,0,strpos($rootEntityName,'\\Entity\\'));

            if(strpos($className,$entityClass) !== false){
                $bundleName = $type;
            }
        }
        return $bundleName;
    }

    /**
     * Get entity name
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata [description]
     *
     * @return string
     */
    private function _getEntityNameFromMetadata($metadata)
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
    private function _camelize($string)
    {
        $string = preg_replace('#([A-Z])#', '_\\1', $string);
        $string = strtolower($string);
        $string = trim($string, '_');
        return $string;
    }
}