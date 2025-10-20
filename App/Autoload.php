<?php
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/Controllers/',
        __DIR__ . '/Models/',
        __DIR__ . '/../Config/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
