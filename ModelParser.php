<?php

namespace kapitanluffy\PropelModelParserBundle;

use Symfony\Component\Routing\RouterInterface;
use PropelModelPager;

class ModelParser
{
    public function __construct(RouterInterface $router)
    {
        $this->router =& $router;
    }

    /**
     * parses paginated propel object collections
     *
     * @method parseCollection
     *
     * @param  PropelModelPager $pager the propel pager
     * @param  string $route the resource url
     * @param  array $url_parameters parameters for the resource url
     * @param  array $properties the extra properties to be included in each objects in collection
     *
     * @return array the parsed collection
     */
    public function parseCollection(PropelModelPager $pager, $route, $url_parameters = array(), $properties = array())
    {
        $properties = $properties ?: array('extended' => array());
        $collection = $pager->getResults();
        $data = array(
            'data' => array(),
            'meta' => array(
                'total' => $pager->count(),
                'prev' => null,
                'next' => null
            )
        );

        if(!$pager->isFirstPage() && $pager->count() > 0){
            $parameters = array_merge($url_parameters, array('page' => $pager->getPreviousPage(), 'limit' => $pager->getMaxPerPage()));
            $data['meta']['prev'] = $this->router->generate($route, $parameters, true);
        }

        if(!$pager->isLastPage() && $pager->count() > 0){
            $parameters = array_merge($url_parameters, array('page' => $pager->getNextPage(), 'limit' => $pager->getMaxPerPage()));
            $data['meta']['next'] = $this->router->generate($route, $parameters, true);
        }

        foreach($collection as $item){
            $_item = $this->parseObject($item, $properties['extended']);
            // $_item = $item->parseObject();
            $_item['meta'] = array();

            $_item['meta']['view_url'] = $this->router->generate($item->resource['route'], $item->resource['parameters'], true);
            $_item['meta']['resource_url'] = $this->router->generate($item->resource['route'], $item->resource['parameters'], true);

            $data['data'][] = $_item;
        }
        return $data;
    }

    /**
     * parses propel base objects
     *
     * @method parseObject
     *
     * @param  BaseObject $item the baseobject to be parsed
     * @param  array $extended_properties the properties to be included in the object
     *
     * @return array the parsed object
     */
    public function parseObject($item, $extended_properties = array())
    {
        // if(!method_exists($item, 'parseObject')) throw new \Exception("Object has not parseObject() method", 1);

        $_extended_data = array();
        foreach($extended_properties as $key => $value){
            $method = isset($value['method']) ? $value['method'] : $value;
            $_ext_prop = isset($value['extended']) ? $value['extended']: array();
            $_criteria = isset($value['criteria']) ? $value['criteria']: null;
            $_extended_data[$key] = null;

            $property = $item->$method($_criteria);
            if($property instanceof \BaseObject){
                $_extended_data[$key] = $this->_parseObject($property, $_ext_prop);
            }
            else if($property instanceof \PropelCollection){
                $_extended_data[$key] = array();
                foreach($property as $p){
                    $_extended_data[$key][] = $this->_parseObject($p, $_ext_prop);
                }
            }
            else if($property instanceof \DateTime){
                $_extended_data[$key] = $property->format('Y-m-d H:i:s O');
            }
            else {
                $_extended_data[$key] = $property;
            }
        }

        $_default_data = $item->toArray(\BasePeer::TYPE_FIELDNAME);

        if(@$_default_data['created_at'] instanceof \DateTime) $_default_data['created_at'] = $_default_data['created_at']->format('Y-m-d H:i:s O');
        if(@$_default_data['updated_at'] instanceof \DateTime) $_default_data['updated_at'] = $_default_data['updated_at']->format('Y-m-d H:i:s O');

        $item->data = array_merge($_default_data, $_extended_data);

        if(method_exists($item, 'parseObject')) {
            $data = $item->parseObject();
            // if(!is_array($data)) throw new \Exception("Object::parseObject() does not return an array", 1);
        }


        if(!empty($item->resource)){
            $item->data['meta'] = array();
            // $item->data['meta']['view_url'] = $this->router->generate($item->resource['route'], $item->resource['parameters'], true);
            $item->data['meta']['resource_url'] = $this->router->generate($item->resource['route'], $item->resource['parameters'], true);
        }
        return $item->data;
    }

    protected function _parseObject($o, $extended_properties = array())
    {
        return method_exists($o, 'parseObject') ? $this->parseObject($o, $extended_properties) : $o->toArray(\BasePeer::TYPE_FIELDNAME);
    }
}