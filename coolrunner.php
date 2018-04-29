<?php
/*
* Plugin Name: CoolRunner Levering
* Plugin URI: http://www.ulvemosenshandelsselskab.dk
* Description: Udvider WooCommerce ved at integrere www.coolrunner.dk.
* Version: 2.1.0
* Author: Rasmus Bundsgaard
* Author URI: http://www.ulvemosenshandelsselskab.dk
* License: MIT License
*/

//Autoload classes
spl_autoload_register(function($c){
    $cd = getcwd();
    chdir(__DIR__.'/classes');
    $files_in_dirs = glob("*/*.php");
    $files_in_root = glob("*.php");

    $files = array_merge($files_in_root, $files_in_dirs);

    foreach ($files as $file) {
        if (file_exists($file)){
            require_once($file);
        }
    }

    chdir($cd);
});


//Init file
$coolrunner = new Coolrunner(plugin_basename(__FILE__));

$_GLOBALS['coolrunner'] = $coolrunner;


function pred($array){
	echo '<pre>';
	var_dump($array);
	echo '</pre>';
}
