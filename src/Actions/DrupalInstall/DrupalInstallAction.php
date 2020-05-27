<?php

namespace App\Actions\DrupalInstall;

use App\Tools\DrupalTools;
use BimRunner\Actions\Base\AbstractAction;
use BimRunner\Tools\Tools\DockerTools;
use BimRunner\Tools\Tools\ProjectTools;
use BimRunner\Tools\Traits\OSTrait;
use BimRunner\Tools\Traits\ReplaceTrait;
use Symfony\Component\Console\Command\Command;
use BimRunner\Actions\Manager\Annotation\Action;

/**
 * @Action(
 *     name = "Installation de Drupal ann",
 *     weight = 10
 * )
 */
class DrupalInstallAction extends AbstractAction {
    use OSTrait, ReplaceTrait;

    /**
     * {@inheritdoc}
     */
    public function initQuestions() {
        // Nom du projet.
        ProjectTools::me()->askName($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksQueue() {
        return [
          [DockerTools::me(), 'dockerUp'],
          [$this, 'install'],
          [$this, 'initSettingsCommons'],
          [$this, 'initSettingsLocal'],
          [$this, 'finalize'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initOptions(Command $command) {
        ProjectTools::me()->addProjectOption($command);
    }

    /**
     * make install
     */
    protected function install() {
        $this->command('make site-install', ProjectTools::me()
          ->getProjectDir());
    }

    /**
     *  Init settings.
     */
    protected function initSettingsCommons() {
        $drupalRoot = DrupalTools::me()->getDrupalRoot();
        $this->command('chmod 775 ' . $drupalRoot . '/web/sites/default');
        $this->command('chmod 775 ' . $drupalRoot . '/web/sites/default/settings.php');
        $this->append(
          $drupalRoot . '/web/sites/default/default_common_settings.php.add',
          $drupalRoot . '/web/sites/default/settings.php',
          '# if (file_exists($app_root . \'/\' . $site_path . \'/settings.local.php\')) {',
          FALSE);

        unlink($drupalRoot . '/web/sites/default/default_common_settings.php.add');
    }

    /**
     * Init settings.local.
     */
    protected function initSettingsLocal() {
        $drupalRoot = DrupalTools::me()->getDrupalRoot();
        $this->rename('settings.local.php.example', 'settings.local.php', $drupalRoot . '/web/sites/default');
        $this->command('chmod 775 ' . $drupalRoot . '/web/sites/default/settings.local.php');
    }

    /**
     * Clear cache.
     */
    protected function finalize() {
        $dir = ProjectTools::me()->getProjectDir();
        $this->command('make drush cr', $dir);
        $this->command('make drush uli', $dir);
    }

}
