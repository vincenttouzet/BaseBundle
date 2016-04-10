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

use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    /** @var ClassMetadata */
    private $metadata = null;

    /**
     * configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('vincet:generate:entity')
            ->setAliases(array('vincet:generate:admin'))
            ->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name')
            ->setDescription('Generates Admin classes (Admin, Controller, Manager) and translations')
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
     * @param InputInterface $input InputInterface instance
     * @param OutputInterface $output OutputInterface instance
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->metadata) {
            try {
                $this->metadata = $this->retrieveMetadatas($input->getArgument('name'));
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                return;
            }
        }
        $metadatas = $this->metadata->getMetadata();

        $namespace = str_replace('\\Entity', '', $this->metadata->getNamespace());
        $path = $this->metadata->getPath();

        $basePath = sprintf(
            '%s/%s',
            $path,
            str_replace('\\', '/', $namespace)
        );

        $appDir = $this->getContainer()->getParameter('kernel.root_dir');

        $adminGenerator = new AdminGenerator($appDir);
        $managerGenerator = new ManagerGenerator($appDir);
        $adminCtlGenerator = new AdminControllerGenerator($appDir);
        $servicesGenerator = new ServicesGenerator($appDir);
        $transGenerator = new TranslationsGenerator($appDir);
        foreach ($metadatas as $metadata) {
            $entityName = $this->getEntityNameFromMetadata($metadata);
            $output->writeln('');
            $output->writeln(sprintf('Generate files for entity %s', $entityName));
            // generate Admin class
            $output->writeln($adminGenerator->generate($namespace, $basePath, $metadata));
            // generate Manager class
            $output->writeln($managerGenerator->generate($namespace, $basePath, $metadata));
            // generate AdminController class
            $output->writeln($adminCtlGenerator->generate($namespace, $basePath, $metadata));
            // update translations
            $transGenerator->setBundleName($this->getBundleNameFromEntity($metadata->rootEntityName));
            $output->writeln($transGenerator->generate($namespace, $basePath, $metadata));
            // update services.yml
            $servicesGenerator->setBundleName($this->getBundleNameFromEntity($metadata->rootEntityName));
            $output->writeln($servicesGenerator->generate($namespace, $basePath, $metadata));
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
        $io = new SymfonyStyle($input, $output);
        $io->title('Welcome to the VinceT Admin generator');

        if (!$input->getArgument('name')) {
            while (!$this->metadata) {
                $question = new Question('Enter the model for admin generation');
                $name = $io->askQuestion($question);
                $input->setArgument('name', $name);
                try {
                    $this->metadata = $this->retrieveMetadatas($name);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                }
            }
        }
    }

    protected function retrieveMetadatas($name)
    {
        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        try {
            $bundle = $this->getApplication()->getKernel()->getBundle($name);

            //$output->writeln(sprintf('Generating classes for bundle "<info>%s</info>"', $bundle->getName()));
            $metadata = $manager->getBundleMetadata($bundle);
        } catch (\InvalidArgumentException $e) {
            $name = strtr($name, '/', '\\');

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

        return $metadata;
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
}
