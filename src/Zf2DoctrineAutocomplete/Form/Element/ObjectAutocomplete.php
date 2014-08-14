<?php

/**
 * @author fabio <paiva.fabiofelipe@gmail.com>
 */

namespace Zf2DoctrineAutocomplete\Form\Element;
use RuntimeException;
use Zend\Form\Element\Text;
use DoctrineModule\Form\Element\Proxy;

class ObjectAutocomplete extends Text {

    /**
     * @var Proxy
     */
    protected $proxy;
    private $initialized = false;

    /**
     * 
     * @param mixed $value
     * @return void
     * @throws \RuntimeException
     */
    public function setValue($value) {
        if(!is_object($value)){
            if(is_array($value) && array_key_exists('id', $value)){
                $this->setAttribute('data-zf2doctrineacid', $value['id']);
                return parent::setValue($value[$this->getProxy()->getProperty()]);
            }
            else{
            $this->setAttribute('data-zf2doctrineacid', $value);
            return parent::setValue($value);
            }
        }
        $id = $this->getProxy()->getValue($value);
        $this->setAttribute('data-zf2doctrineacid', $id);
        $metadata   = $this->getProxy()->getObjectManager()
                ->getClassMetadata($this->getProxy()->getTargetClass());
        $identifier = $metadata->getIdentifierFieldNames();
        $object = $this->getProxy()->getObjectManager()
                        ->getRepository($this->getProxy()->getTargetClass())->find($id);
        if (
                is_callable($this->getOption('label_generator')) 
                && null !== ($generatedLabel = call_user_func($this->getOption('label_generator'), $object))
                ) {
            $label = $generatedLabel;
        } elseif ($property = $this->getProxy()->getProperty()) {
            if ($this->getProxy()->getIsMethod() == false && !$metadata->hasField($property)) {
                        throw new RuntimeException(
                            sprintf(
                                'Property "%s" could not be found in object "%s"',
                                $property,
                                $targetClass
                            )
                        );
                    }
            $getter = 'get' . ucfirst($property);
            //var_dump(get_class($object));
            //var_dump(get_class_methods($object));
            if (!is_callable(array($object, $getter))) {
                        throw new RuntimeException(
                            sprintf('Method "%s::%s" is not callable', $this->getProxy()->getTargetClass(), $getter)
                        );
                    }
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
            $this->setAttribute('data-zf2doctrineacproperty', $options['property']);
            $this->setAttribute('data-zf2doctrineacselectwarningmessage', $options['select_warning_message']);
            $this->setAttribute('data-zf2doctrineacinit', 'zf2-doctrine-autocomplete');
            if(isset($options['allow_persist_new']) && $options['allow_persist_new']){
                $this->setAttribute('data-zf2doctrineacallowpersist', 'true');
            }
            $this->initialized = true;
        }
        $this->getProxy()->setOptions($options);
        return parent::setOptions($options);
    }

}
