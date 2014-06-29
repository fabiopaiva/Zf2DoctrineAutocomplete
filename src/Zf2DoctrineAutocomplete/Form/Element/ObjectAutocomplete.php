<?php

/**
 * @author fabio <paiva.fabiofelipe@gmail.com>
 */

namespace Zf2DoctrineAutocomplete\Form\Element;

use Zend\Form\Element\Text;
use DoctrineModule\Form\Element\Proxy;

class ObjectAutocomplete extends Text {

    /**
     * @var Proxy
     */
    protected $proxy;
    private $initialized = false;

    public function setValue($value) {
        $id = $this->getProxy()->getValue($value);
        $this->setAttribute('data-zf2doctrineacid', $id);
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $object = $this->getProxy()->getObjectManager()
                        ->getRepository($this->getProxy()->getTargetClass())->find($id);
        if (is_callable($this->getOption('label_generator')) && null !== ($generatedLabel = call_user_func($this->getOption('label_generator'), $object))) {
            $label = $generatedLabel;
        } elseif ($property = $this->getProxy()->getProperty()) {
            $getter = 'get' . ucfirst($property);
            $label = $object->{$getter}();
        } else {
            if (!is_callable(array($object, '__toString'))) {
                throw new RuntimeException(
                sprintf(
                        '%s must have a "__toString()" method defined if you have not set a property'
                        . ' or method to use.', $this->getProxy()->getTargetClass()
                )
                );
            }
            $label = (string) $object;
        }
        return parent::setValue($label);
    }

    /**
     * @return Proxy
     */
    public function getProxy() {
        if (null === $this->proxy) {
            $this->proxy = new Proxy();
        }
        return $this->proxy;
    }

    /**
     * @param  array|\Traversable $options
     * @return ObjectSelect
     */
    public function setOptions($options) {
        if (!$this->initialized) {
            $this->setAttribute('data-zf2doctrineacclass', urlencode(str_replace('\\', '-', $options['class'])));
            $this->setAttribute('data-zf2doctrineacinit', 'zf2-doctrine-autocomplete');
            $this->initialized = true;
        }
        $this->getProxy()->setOptions($options);
        return parent::setOptions($options);
    }

}
