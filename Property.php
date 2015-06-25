<?php

namespace kapitanluffy\PropelModelParserBundle;

class Property extends PropertyCollection
{
    protected $parent;
    protected $name;
    protected $value;
    protected $criteria;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;

    }

    public function getValue()
    {
        return $this->value;
    }

    public function setCriteria(\Criteria $criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setParent($parent)
    {
        $this->parent =& $parent;
    }

    public function endUse()
    {
        return $this->parent;
    }
}