<?php

// Set up symfony autoloader
$loader = FALSE;
if (file_exists($autoloadFile = __DIR__ . '/vendor/autoload.php')
  || file_exists($autoloadFile = __DIR__ . '/../autoload.php')
  || file_exists($autoloadFile = __DIR__ . '/../../autoload.php')
) {
    $loader = include_once($autoloadFile);
    $loader->setUseIncludePath(TRUE);
}
else {
    throw new \Exception("Could not locate autoload.php. cwd is $cwd; __DIR__ is " . __DIR__);
}

// Add custom autoloader.
$rootReps = ['Runner', 'Actions'];
const SOURCE_REP = 'src';
spl_autoload_register(function ($className) {
    global $rootReps;
    $rootDirElements = explode('\\', $className);
    if (in_array($rootDirElements[0], $rootReps)) {
        // Define file path.
        $path = implode('/', [
          __DIR__,
          SOURCE_REP,
          str_replace('\\', '/', $className) . '.php'
        ]);

        if (file_exists($path)) {
            include_once $path;
        }
    }
});

return $loader;

