<?php

namespace App\Model;

class Provider
{
    protected $name;
    protected $entity;
    protected $regex;
    protected $match;
    protected $fields;

    public function __construct($name, $entity, $regex, $match, $fields)
    {
        $this->setName($name);
        $this->setEntity($entity);
        $this->setRegex($regex);
        $this->setMatch($match);
        $this->setFields($fields);
    }

    /**
     * @param mixed $entity
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param mixed $regex
     * @return self
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * @return array
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * @param mixed $match
     * @return self
     */
    public function setMatch(array $match)
    {
        $result = [];
        foreach ($match as $key => $value) {
            $result[$key] = json_decode($value, true);
        }
        $this->match = $result;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     * @return self
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityClassName()
    {
        return $this->entity;
    }
}
