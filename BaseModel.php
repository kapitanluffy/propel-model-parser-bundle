<?php

namespace kapitanluffy\PropelModelParserBundle;

use \BaseObject;

abstract class BaseModel extends \BaseObject
{
    public function parseObject($collection = array(), $object = null)
    {
        $object = $object ?: $this;
        $data = array();

        foreach($collection as $key => $property){

            $value = $property->getValue();
            $criteria = $property->getCriteria();

            $item = $value;
            if(method_exists($object, $value)){
                $item = $object->$value($criteria);
            }

            if($item instanceof BaseModel){
                $data[$key] = $this->parseObject($property, $item);
            }
            else if($item instanceof \PropelCollection){
                $data[$key] = array();

                foreach($item as $o){
                    $data[$key][] = $this->parseObject($property, $o);
                }
            }
            else {
                $data[$key] = $item;
            }

        }

        $_default_data = $object->toArray(\BasePeer::TYPE_FIELDNAME);

        return array_merge($_default_data, $data);
    }
}