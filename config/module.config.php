<?php

return array(
    'form_elements' => array(
        'factories' => array(
            'Zf2DoctrineAutocomplete\Form\Element\ObjectAutocomplete' => 'Zf2DoctrineAutocomplete\Form\Element\ObjectAutocomplete',
        ),
    ),
    'router' => array(
        'routes' => array(
            'zf2-doctrine-autocomplete' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/zf2-doctrine-autocomplete[/:element]',
                    'defaults' => array(
                        'action' => 'search',
                        'controller' => 'Zf2DoctrineAutocomplete\Controller\Search'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Zf2DoctrineAutocomplete\Controller\Search' =>
            'Zf2DoctrineAutocomplete\Controller\SearchController'
        )
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
);
