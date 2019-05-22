<?php
/**
 * Created by priyashantha@silverstripers.com
 * Date: 3/27/19
 * Time: 3:30 PM
 */

namespace Silverstripe\Shortcodable\Forms;

use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormFactory;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBClassName;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\HiddenClass;
use Silverstripe\Shortcodable\Shortcodable;
use SilverStripe\View\SSViewer;
use function singleton;


class ShortCodeFormFactory implements FormFactory
{
    use Extensible;
    use Injectable;
    use Configurable;

    /**
     * @param RequestHandler $controller
     * @param string $name
     * @param array $context
     * @return Form
     */
    public function getForm(RequestHandler $controller = null, $name = FormFactory::DEFAULT_NAME, $context = [])
    {
        // Validate context
        foreach ($this->getRequiredContext() as $required) {
            if (!isset($context[$required])) {
                throw new InvalidArgumentException("Missing required context $required");
            }
        }

        $fields = $this->getFormFields($controller, $name, $context);
        $actions = $this->getFormActions($controller, $name, $context);
        $validator = $this->getValidator($controller, $name, $context);
        /** @var Form $form */
        $form = Form::create($controller, $name, $fields, $actions, $validator);
        $form->addExtraClass('form--no-dividers');

        return $form;
    }

    protected function getFormFields($controller, $name, $context)
    {
        SSViewer::config()->update('theme_enabled', false);
        $classes = Shortcodable::get_shortcodable_classes_fordropdown();
        $classname = isset($context['type']) ? $context['type'] : '';
        $id = isset($context['id']) ? $context['id'] : '';

        $fields = FieldList::create([
            $classesField = DropdownField::create('ShortcodeClass', _t('Shortcodable.SHORTCODECLASS', 'Shortcode class'), $classes, $classname)
                ->setHasEmptyDefault(true)
                ->addExtraClass('shortcode-type'),
        ]);
        // attribute and object id fields
        if ($classname && class_exists($classname)) {
            $class = singleton($classname);
            if (is_subclass_of($class, DataObject::class)) {
                if (singleton($classname)->hasMethod('getShortcodableRecords')) {
                    $dataObjectSource = singleton($classname)->getShortcodableRecords();
                } else {
                    $dataObjectSource = $classname::get()->map()->toArray();
                }
                $fields->push(
                    DropdownField::create('id', $class->singular_name(), $dataObjectSource)
                        ->setValue($id)
                        ->setHasEmptyDefault(true)
                        ->addExtraClass('shortcode-value')
                );
            }
            if (singleton($classname)->hasMethod('getShortcodeFields')) {
                if ($attrFields = singleton($classname)->getShortcodeFields()) {
                    $fields->push(
                        CompositeField::create($attrFields)
                            ->addExtraClass('attributes-composite')
                            ->setName('AttributesCompositeField')
                    );
                }
            }
            $typeName = DBField::create_field('DBClassName', $classname)->getShortName();
            $fields->push(HiddenField::create('ShortcodeType', '', $typeName));

            $this->extend('updateFormFields', $classname, $fields, $context);
        }
        return $fields;
    }

    protected function getFormActions($controller, $name, $context)
    {
        $actions = FieldList::create([
            FormAction::create('addshortcode', 'Add')
                ->setSchemaData(['data' => ['buttonStyle' => 'primary']]),
            FormAction::create('cancel', 'Cancel'),
        ]);

        return $actions;
    }

    protected function getValidator($controller, $name, $context)
    {
        return null;
    }

    public function getRequiredContext()
    {
        return [];
    }
}