<?php

/**
 * @author fabio <paiva.fabiofelipe@gmail.com> 
 */

namespace Zf2DoctrineAutocomplete\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Form\Factory;

class SearchController extends AbstractActionController {

    private $proxy;
    private $objects;
    private $om;
    private $options;

    public function searchAction() {
        $elementName = $this->params()->fromRoute('element');
        $elementName = str_replace('-', '\\', $elementName);

        $term = $this->params()->fromQuery('term', '');

        $factory = new Factory();
        $element = $factory->createElement(array(
            'type' => $elementName,
            'options' => array(
                'sm' => $this->getServiceLocator()
            )
        ));
        $options = $element->getOptions();
        $this->setOm($options['object_manager']);
        $proxy = $element->getProxy();
        $this->setProxy($proxy);
        $this->setOptions($options);

        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $proxy->getObjectManager()->getRepository($proxy->getTargetClass())
                ->createQueryBuilder('q');

        foreach ($options['searchFields'] as $field) {
            $qb->orWhere($qb->expr()->like('q.' . $field, $qb->expr()->literal("%{$term}%")));
        }
        $this->setObjects($qb->getQuery()->getResult());
        $valueOptions = $this->getValueOptions();

        $view = new JsonModel($valueOptions);
        return $view;
    }

    private function getValueOptions() {
        $proxy = $this->getProxy();
        $targetClass = $proxy->getTargetClass();
        $metadata = $this->getOm()->getClassMetadata($targetClass);
        $identifier = $metadata->getIdentifierFieldNames();
        $objects = $this->getObjects();
        $options = array();

        if ($proxy->getDisplayEmptyItem() || empty($objects)) {
            $options[] = array('value' => null, 'label' => $proxy->getEmptyItemLabel());
        }

        if (!empty($objects)) {
            $entityOptions = $this->getOptions();
            foreach ($objects as $key => $object) {
                if (is_callable($entityOptions['label_generator']) && null !== ($generatedLabel = call_user_func($entityOptions['label_generator'], $object))) {
                    $label = $generatedLabel;
                } elseif ($property = $proxy->getProperty()) {
                    if ($proxy->getIsMethod() == false && !$metadata->hasField($property)) {
                        throw new RuntimeException(
                        sprintf(
                                'Property "%s" could not be found in object "%s"', $property, $targetClass
                        )
                        );
                    }

                    $getter = 'get' . ucfirst($property);
                    if (!is_callable(array($object, $getter))) {
                        throw new RuntimeException(
                        sprintf('Method "%s::%s" is not callable', $proxy->getTargetClass(), $getter)
                        );
                    }

                    $label = $object->{$getter}();
                } else {
                    if (!is_callable(array($object, '__toString'))) {
                        throw new RuntimeException(
                        sprintf(
                                '%s must have a "__toString()" method defined if you have not set a property'
                                . ' or method to use.', $targetClass
                        )
                        );
                    }

                    $label = (string) $object;
                }

                if (count($identifier) > 1) {
                    $value = $key;
                } else {
                    $value = current($metadata->getIdentifierValues($object));
                }

                $options[] = array('label' => $label, 'value' => $value);
            }
        }

        return $options;
    }

    public function getProxy() {
        return $this->proxy;
    }

    public function getObjects() {
        return $this->objects;
    }

    public function setProxy($proxy) {
        $this->proxy = $proxy;
        return $this;
    }

    public function setObjects($objects) {
        $this->objects = $objects;
        return $this;
    }

    public function getOm() {
        return $this->om;
    }

    public function setOm($om) {
        $this->om = $om;
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    public function setOptions($options) {
        $this->options = $options;
        return $this;
    }

}
