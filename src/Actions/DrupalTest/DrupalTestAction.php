<?php

namespace App\Actions\DrupalTest;

use App\Tools\DrupalTools;
use BimRunner\Actions\Base\AbstractAction;
use BimRunner\Tools\IO\PropertiesHelperInterface;
use BimRunner\Tools\Tools\DockerTools;
use BimRunner\Tools\Tools\ProjectTools;
use BimRunner\Tools\Traits\OSTrait;
use BimRunner\Tools\Traits\ReplaceTrait;
use Symfony\Component\Console\Command\Command;
use BimRunner\Actions\Manager\Annotation\Action;

/**
 * @Action(
 *     name = "Drupal test ann",
 *     weight = 20
 * )
 */
class DrupalTestAction extends AbstractAction {
    use OSTrait, ReplaceTrait;

    /**
     * {@inheritdoc}
     */
    public function initQuestions() {
        // Project name.
        ProjectTools::me()->askName($this);
    }

    /**
     * {@inheritdoc}
     */
    public function initOptions(Command $command) {
        // Nom du projet.
        ProjectTools::me()->addProjectOption($command);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksQueue() {
        return [
          [DockerTools::me(), 'dockerUp'],
          [$this, 'requirePackage'],
          [$this, 'enable'],
        ];
    }

    /**
     * Require des packages composer.
     */
    protected function requirePackage(PropertiesHelperInterface $propertiesHelper) {
        $dirname = DrupalTools::me()->getDrupalRoot();
        $config = $this->getLocalConfig();

        // Required.
        $required = array_column($config['config']['all'], 'package');
        if (!empty($required)) {
            $this->composer('require ' . implode(' ', $required), $dirname);
        }

        // Dev.
        $dev = array_column($config['config']['dev'], 'package');
        if (!empty($dev)) {
            $this->composer('require ' . implode(' ', $dev) . ' --dev', $dirname);
        }
    }

    /**
     * Activation des modules.
     */
    protected function enable(PropertiesHelperInterface $propertiesHelper) {
        $dirname = DrupalTools::me()->getDrupalRoot();
        $config = $this->getLocalConfig();

        // Dev.
        $modules = array_column($config['config']['all'] + $config['config']['dev'], 'id');
        if (!empty($dev)) {
            $this->command('make drush en ' . implode(', ', $modules), $dirname);
        }
    }

}
