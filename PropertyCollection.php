<?php

namespace kapitanluffy\PropelModelParserBundle;

class PropertyCollection implements \Iterator
{
    protected $position = 0;
    protected $coll = array();
    protected $keys = array();

    public function addProperty($name, $value)
    {
        $this->coll[$name] = new Property($name, $value);
        $this->keys[] = $name;
        $this->coll[$name]->setParent($this);

        return $this;
    }

    public function useProperty($property)
    {
        return $this->coll[$property];
    }

    public function current()
    {
        return $this->coll[$this->keys[$this->position] ];
    }

    public function key()
    {
        return $this->keys[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->keys[$this->position]);
    }

}