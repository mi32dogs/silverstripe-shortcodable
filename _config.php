<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use Silverstripe\Shortcodable\Shortcodable;
use SilverStripe\View\Requirements;

//Requirements::css( 'mi32dogs/silverstripe-shortcodable: css/shortcodable.css' );
//Requirements::javascript( 'mi32dogs/silverstripe-shortcodable: javascript/editor_plugin.js' );
//Requirements::javascript( 'mi32dogs/silverstripe-shortcodable: javascript/shortcodable.js' );

// enable shortcodable buttons and add to HtmlEditorConfig
$htmlEditorNames = Config::inst()->get(Shortcodable::class, 'htmleditor_names');

if (is_array($htmlEditorNames)) {
    $plugin = ModuleResourceLoader::singleton()
        ->resolveURL('mi32dogs/silverstripe-shortcodable: javascript/editor_plugin.js');

    foreach ($htmlEditorNames as $htmlEditorName) {
        TinyMCEConfig::get($htmlEditorName)->enablePlugins(['shortcodable' => $plugin]);
        //TinyMCEConfig::get($htmlEditorName)->addButtonsToLine(1, '| shortcodable');
        TinyMCEConfig::get($htmlEditorName)->insertButtonsAfter( 'code', ' | shortcodable' );
    }
}

// register classes added via yml config
$classes = Config::inst()->get(Shortcodable::class, 'shortcodable_classes');
Shortcodable::register_classes($classes);
