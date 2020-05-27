<?php

namespace App\Tools;


use BimRunner\Tools\IO\FileHelper;
use BimRunner\Tools\Tools\ProjectTools;

class DrupalTools {

    /**
     * Singleton
     *
     * @var
     */
    protected static $me;

    /**
     * Drupal root.
     *
     * @var string
     */
    protected $drupalRoot;

    /**
     * Retourne le singleton.
     *
     * @return static
     *   Le singleton.
     */
    public static function me() {
        if (!isset(static::$me)) {
            static::$me = new static();
        }

        return static::$me;
    }

    /**
     * Create singleton.
     */
    public static function create() {
        static::$me = new static();

        return static::$me;
    }

    /**
     * DrupalTools constructor.
     */
    protected function __construct() {
    }

    /**
     * @param string $drupalRoot
     */
    public function setDrupalRoot($drupalRoot): void {
        $this->drupalRoot = $drupalRoot;
    }

    /**
     * Retourne le drupal root.
     */
    public function getDrupalRoot() {
        if (is_null($this->drupalRoot)) {
            $dir = FileHelper::me()->getExecutionDir();

            if ($projectDir = ProjectTools::me()->getProjectDir()) {
                $dir = $projectDir.'/';
            }

            if (strpos($dir, '/web/')) {
                $this->setDrupalRoot(explode('/web/', $dir)[0]);
            }
            elseif (is_dir($dir . '/web')) {
                $this->setDrupalRoot($dir);
            }
            elseif (is_dir($dir . '/www/web')) {
                $this->setDrupalRoot($dir . '/www/');
            }
            else {
                throw new \Exception('Pas de projet Drupal trouvé');
            }
        }

        return $this->drupalRoot;
    }

    /**
     * Retourne le nom du projet drupal (nom du rep).
     */
    public function getDrupalProjectName() {
        if ($drupalRoot = $this->getDrupalRoot()) {
            return basename($drupalRoot);
        }

        return NULL;
    }

}
