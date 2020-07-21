<?php

namespace Silverstripe\Shortcodable;

use SilverStripe\Core\Injector\Injector;
use Silverstripe\Shortcodable\Extensions\ShortcodableParser;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\Core\Config\Config;

use SilverStripe\Dev\Debug;

/**
 * Shortcodable
 * Manages shortcodable configuration and register shortcodable objects
 *
 * @author shea@livesource.co.nz
 **/
class Shortcodable
{
    use Injectable;
    use Configurable;
    use Extensible;

    private static $shortcodable_classes = array();

    public static function register_classes($classes)
    {
        if (is_array($classes) && count($classes)) {
            foreach ($classes as $class) {
                self::register_class($class);
            }
        }
    }

    public static function register_class($class)
    {

        if (class_exists($class)) {
            if (!singleton($class)->hasMethod('parse_shortcode')) {
                user_error("Failed to register \"$class\" with shortcodable. $class must have the method parse_shortcode(). See /shortcodable/README.md", E_USER_ERROR);
            }
            /*  SHOWPRO UPDATE  */
            $safeClassName = str_replace('\\', '_', $class);
            ShortcodeParser::get('default')->register($safeClassName, array($class, 'parse_shortcode'));
            Injector::inst()->get(ShortcodableParser::class)->register($class);

            /*ShortcodeParser::get('default')->register($class, array($class, 'parse_shortcode'));
            singleton(ShortcodableParser::class)->register($class);*/
        }
    }

    public static function get_shortcodable_classes()
    {
        return Config::inst()->get(self::class, 'shortcodable_classes');
    }

    public static function get_shortcodable_classes_fordropdown()
    {
        $classList = self::get_shortcodable_classes();
        $classes = array();
        if (is_array($classList)) {
            foreach ($classList as $class) {
                if (singleton($class)->hasMethod('singular_name')) {
                    $classes[$class] = singleton($class)->singular_name();
                } else {
                    $classes[$class] = $class;
                }
            }
        }
        return $classes;
    }

    public static function get_shortcodable_classes_with_placeholders()
    {
        $classes = array();
        foreach (self::get_shortcodable_classes() as $class) {
            if (singleton($class)->hasMethod('getShortcodePlaceHolder')) {
                $classes[] = $class;
            }
        }
        return $classes;
    }

    public static function generateId()
    {
        $len = 16; //32 bytes = 256 bits
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($len);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($len);
        } else {
            //Use a hash to force the length to the same as the other methods
            $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
        }

        //We don't care about messing up base64 format here, just want a random string
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }
}
