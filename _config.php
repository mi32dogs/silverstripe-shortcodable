<?php

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use Silverstripe\Shortcodable\Shortcodable;

if (!defined('SHORTCODABLE_DIR')) {
    define('SHORTCODABLE_DIR', rtrim(basename(dirname(__FILE__))));
}
if (SHORTCODABLE_DIR != 'silverstripe-shortcodable') {
    throw new \Exception('The edit shortcodable module is not installed in correct directory. The directory should be named "shortcodable"');
}

// enable shortcodable buttons and add to HtmlEditorConfig
$htmlEditorNames = Shortcodable::config()->htmleditor_names;
if (is_array($htmlEditorNames)) {
    $module = ModuleLoader::inst()->getManifest()->getModule('showpro/silverstripe-shortcodable');
    foreach ($htmlEditorNames as $htmlEditorName) {
        $config = TinyMCEConfig::get($htmlEditorName);
//        echo $module->getResource('client/javascript/editor_plugin.js');die();
        $config->enablePlugins(array(
            'shortcodable' => $module->getResource('client/dist/TinyMCE_shortcodable.js'),
        ))->insertButtonsAfter( 'code', ' | shortcodable' );
    }
}

// register classes added via yml config
$classes = Shortcodable::config()->shortcodable_classes;
Shortcodable::register_classes($classes);
