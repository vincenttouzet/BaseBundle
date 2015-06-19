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

namespace VinceT\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use VinceT\BaseBundle\Generator\AdminGenerator;
use VinceT\BaseBundle\Generator\AdminControllerGenerator;
use VinceT\BaseBundle\Generator\ManagerGenerator;
use VinceT\BaseBundle\Generator\ServicesGenerator;
use VinceT\BaseBundle\Generator\TranslationsGenerator;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;

/**
 * Generate Admin class.
 *
 * @category VinceT
 *
 * @author   Vincent Touzet <vincent.touzet@gmail.com>
 * @license  MIT License view the LICENSE file that was distributed with this source code.
 *
 * @link     https://github.com/vincenttouzet/BaseBundle
 */
class GenerateCommand extends ContainerAwareCommand
{
    private $_namespace = null;
    private $_basePath = null;
    private $_metadatas = null;

    /**
     * configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('vincet:generate:entity')
            ->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name')
            ->setDescription('Generates classes')
            ->setHelp(
<<<EOF
The <info>vincet:generate:entity</info> command generates following classes.
    * Admin\EntityAdmin
    * Controller\Admin\EntityAdminController
    * Manager\EntityManager
and update:
    * Resources/config/services.yml
    * Resources/translations/MyCustomBundle.en.yml
    * Resources/translations/MyCustomBundle.fr.yml

You have to limit generation:

* To a bundle:

  <info>php app/console vincet:generate:entity MyCustomBundle</info>

* To a single entity:

  <info>php app/console vincet:generate:entity MyCustomBundle:User</info>
  <info>php app/console vincet:generate:entity MyCustomBundle/Entity/User</info>

* To a namespace

  <info>php app/console vincet:generate:entity MyCustomBundle/Entity</info>
EOF
            );
    }

    /**
     * execute command.
     *
     * @param InputInterface  $input  InputInterface instance
     * @param OutputInterface $output OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        try {
            $bundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('name'));

            //$output->writeln(sprintf('Generating classes for bundle "<info>%s</info>"', $bundle->getName()));
            $metadata = $manager->getBundleMetadata($bundle);
        } catch (\InvalidArgumentException $e) {
            $name = strtr($input->getArgument('name'), '/', '\\');

            if (false !== $pos = strpos($name, ':')) {
                $name = $this->getContainer()->get('doctrine')
                        ->getAliasNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
            }

            if (class_exists($name)) {
                //$output->writeln(sprintf('Generating classes for entity "<info>%s</info>"', $name));
                $metadata = $manager->getClassMetadata($name);
            } else {
                //$output->writeln(sprintf('Generating classes for namespace "<info>%s</info>"', $name));
                $metadata = $manager->getNamespaceMetadata($name);
            }
        }

        $this->_metadatas = $metadata->getMetadata();

        $this->_namespace = str_replace('\\Entity', '', $metadata->getNamespace());
        $path = $metadata->getPath();

        $this->_basePath = sprintf(
            '%s/%s',
            $path,
            str_replace('\\', '/', $this->_namespace)
        );

        $adminGenerator = new AdminGenerator();
        $managerGenerator = new ManagerGenerator();
        $adminControllerGenerator = new AdminControllerGenerator();
        $servicesGenerator = new ServicesGenerator();
        $translationsGenerator = new TranslationsGenerator();
        foreach ($this->getMetadatas() as $metadata) {
            $entityName = $this->getEntityNameFromMetadata($metadata);
            $output->writeln('');
            $output->writeln(sprintf('Generate files for entity %s', $entityName));
            // generate Admin class
            $output->writeln($adminGenerator->generate($this->getNamespace(), $this->getBasePath(), $metadata));
            // generate Manager class
            $output->writeln($managerGenerator->generate($this->getNamespace(), $this->getBasePath(), $metadata));
            // generate AdminController class
            $output->writeln($adminControllerGenerator->generate($this->getNamespace(), $this->getBasePath(), $metadata));
            // update services.yml
            $servicesGenerator->setBundleName($this->getBundleNameFromEntity($metadata->rootEntityName));
            $output->writeln($servicesGenerator->generate($this->getNamespace(), $this->getBasePath(), $metadata));
            // update translations
            $translationsGenerator->setBundleName($this->getBundleNameFromEntity($metadata->rootEntityName));
            $output->writeln($translationsGenerator->generate($this->getNamespace(), $this->getBasePath(), $metadata));
        }
    }

    /**
     * command interaction.
     *
     * @param InputInterface  $input  InputInterface instance
     * @param OutputInterface $output OutputInterface instance
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the VinceT Admin generator');
    }

    /**
     * getDialogHelper.
     *
     * @return \Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper
     */
    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('question');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper') {
            $this->getHelperSet()->set($dialog = new QuestionHelper());
        }

        return $dialog;
    }

    /**
     * Get entity name.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata [description]
     *
     * @return string
     */
    public function getEntityNameFromMetadata($metadata)
    {
        $name_explode = explode('\\', $metadata->name);

        return $name_explode[count($name_explode) - 1];
    }

    /**
     * Get a bundle name from a root entity name (e.g: VinceT\\DemoBundle\\Entity\\Post).
     *
     * @param string $rootEntityName Root entity name
     *
     * @return string
     */
    protected function getBundleNameFromEntity($rootEntityName)
    {
        $bundles = $this->getContainer()->get('kernel')->getBundles();
        $bundleName = '';

        foreach ($bundles as $type => $bundle) {
            $className = get_class($bundle);

            $entityClass = substr($rootEntityName, 0, strpos($rootEntityName, '\\Entity\\'));

            if (strpos($className, $entityClass) !== false) {
                $bundleName = $type;
            }
        }

        return $bundleName;
    }

    /**
     * getNamespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * getMetadata.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Mapping\ClassMetadataCollection
     */
    public function getMetadatas()
    {
        return $this->_metadatas;
    }

    /**
     * getBasePath.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }
}
