<?php

namespace App\Actions\DrupalDocker;

use App\Tools\DrupalTools;
use BimRunner\Actions\Base\AbstractAction;
use BimRunner\Tools\IO\IOHelper;
use BimRunner\Tools\Tools\ProjectTools;
use BimRunner\Tools\Traits\GitTrait;
use BimRunner\Tools\Traits\ReplaceTrait;
use Symfony\Component\Console\Command\Command;
use BimRunner\Actions\Manager\Annotation\Action;

/**
 * @Action(
 *     name = "Initialisation de Docker pour Drupal ann",
 *     weight = 0
 * )
 */
class DrupalDockerAction extends AbstractAction {

    use GitTrait, ReplaceTrait;

    /**
     * Propriété version du docker
     *
     * @const string
     */
    const PROP_DOCKER_REPO_VERSION = 'docker_rep_version';

    /**
     * Propréité gid
     *
     * @const string
     */
    const PROP_WEB_GID = 'web_gid';

    /**
     * Propriété uid
     *
     * @const string
     */
    const PROP_WEB_UID = 'web_uid';

    /**
     * {@inheritdoc}
     */
    public function __construct() {
        // Intialisation de la branche à utiliser par défaut.
        // On peut la surcharger via les options ;).
        $this->properties[static::PROP_DOCKER_REPO_VERSION] = $this->getLocalConfig()['config']['tag'];

        // Récuépration des web uid et gid.
        $userIdsData = $this->getUserIdsData();
        $this->properties[static::PROP_WEB_GID] = isset($userIdsData['gid']) ? $userIdsData['gid'] : NULL;
        $this->properties[static::PROP_WEB_UID] = isset($userIdsData['uid']) ? $userIdsData['uid'] : NULL;
    }

    public function initOptions(Command $command) {
        ProjectTools::me()->addProjectOption($command);
    }

    /**
     * {@inheritdoc}
     */
    public function initQuestions() {
        // Nom du projet.
        ProjectTools::me()->askName($this);

        // Qeuestion sur les users ids.
        $this->ask(static::PROP_WEB_UID, 'Quel est l\'utiisateur uid (généralement 1000 sous linux');
        $this->ask(static::PROP_WEB_GID, 'Quel est l\'utiisateur gid (généralement 1000 sous linux');
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksQueue() {
        return [
          [$this, 'cloneRepo'],
          [$this, 'initEnvs'],
          [$this, 'initGitignore'],
          [$this, 'composerInstall'],
          [$this, 'makeBuild'],
        ];
    }

    /**
     * Clone the repo.
     */
    protected function cloneRepo() {
        IOHelper::me()->info('Git clone');
        $this->cloneGitRepo(
          $this->getLocalConfig()['config']['repo'],
          $this->properties[ProjectTools::PROP_PROJECT_NAME],
          $this->properties[static::PROP_DOCKER_REPO_VERSION]
        );

        // Suppression du repo git.
        $this->command('rm -rf .git', DrupalTools::me()->getDrupalRoot());

        // Modification du rep de départ de drupal.
        DrupalTools::me()->setDrupalRoot(ProjectTools::me()
            ->getProjectDir() . '/www/');
    }

    /**
     * Init env file.
     */
    protected function initEnvs() {
        IOHelper::me()->info('Initialisation du .env');

        // Copie les .env.example et remplace les placeholder avec lesd onnées dispo
        $dirsWithEnv = [
          ProjectTools::me()->getProjectDir(),
          DrupalTools::me()->getDrupalRoot(),
        ];
        foreach ($dirsWithEnv as $dirname) {
            $this->copyAndReplace($dirname . '/.env.example',
              $dirname . '/.env',
              $this->properties,
              ['{{' . $this->str_content_id . '}}']
            );
        }

    }

    /**
     * Rename the gitignore file.
     */
    protected function initGitignore() {
        $dirsWithGitignore = [
          ProjectTools::me()->getProjectDir(),
          ProjectTools::me()->getProjectDir() . '/www/',
        ];

        foreach ($dirsWithGitignore as $dir) {
            $this->rename('.gitignore.example', '.gitignore', $dir, TRUE);
        }
    }

    /**
     * Composer install.
     */
    protected function composerInstall() {
        $this->composer('install', DrupalTools::me()->getDrupalRoot());
    }

    /**
     * Make build.
     */
    protected function makeBuild() {
        $this->command('make build', ProjectTools::me()->getProjectDir());
    }

    /**
     * Récupération des identifiants du user
     *
     * @return array
     */
    protected function getUserIdsData(): array {
        $ids = $this->command('id')[0];

        $data = [];
        foreach (explode(' ', $ids) as $item) {
            list($key, $value) = explode('=', preg_replace("/\([^)]+\)/", "", $item));
            if (strpos($value, ',') !== FALSE) {
                $data[$key] = explode(',', $value);
            }
            else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

}
