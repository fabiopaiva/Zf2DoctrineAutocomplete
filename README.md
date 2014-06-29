Zf2DoctrineAutocomplete
=======================

A form element autocomplete for Doctrine 2 and ZF2

## Requirements

[DoctrineModule](https://github.com/doctrine/doctrinemodule).
[jQuery](http://jquery.com).
[jQueryUi](http://jqueryui.com).

## Instalation

### Using github

    cd vendor
    git clone https://github.com/fabiopaiva/Zf2DoctrineAutocomplete

### Copy javascript initializer to your public folder

    cp data/zf2-doctrine-autocomplete.min.js public/js/

## Enable the module
Enable this module in your application.config.php

    return array(
        'modules' => array(
        'DoctrineModule',
        'Zf2DoctrineAutocomplete',
        'Application',
        )   
    );

## Add javascript file to your layout (copy this file from data folder)

    echo $this
        ->headScript()
        ->prependFile($this->basePath() . '/js/zf2-doctrine-autocomplete.min.js');

## Create a custom form element and configure the options

    <?php

    namespace Application\Form\Element;
    use Zf2DoctrineAutocomplete\Form\Element\ObjectAutocomplete;

    class MyAutocompleteElement extends ObjectAutocomplete {

        private $initialized = false;

        public function setOptions($options) {
        if (!$this->initialized) {
            $options = array_merge($options, array(
                'class' => get_class($this),
                'object_manager' => $options['sm']->get('Doctrine\ORM\EntityManager'),
                'target_class' => 'Application\Entity\MyEntity',
                'searchFields' => array('code', 'description'),
                'empty_item_label' => 'Nothing found',
                'property' => 'description'
            ));
            $this->initialized = true;
            }

        parent::setOptions($options);
        }

    }

## Add the custom element to your form

    $form->add(array(
            'name' => 'myAutocompleteElement',
            'type' => 'Application\Form\Element\MyAutocompleteElement',
            'options' => array(
                'label' => 'My label here',
                'sm' => $serviceManager // don't forget to send Service Manager
            ),
            'attributes' => array(
                'required' => true,
                'class' => 'form-control input-sm'
            )
        ));

## Add elements dinamically

    zf2DoctrineAutocomplete.init('#jQuerySelector');