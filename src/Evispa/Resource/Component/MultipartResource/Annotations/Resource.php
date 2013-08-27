<?php

namespace Evispa\Resource\Component\MultipartResource\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Resource
{
    private $name;
    private $action;

    public function __construct(array $data)
    {
        if (!isset($data['value']) && !isset($data['name'])) {
            throw new \Symfony\Component\Form\Exception\LogicException('Annotation "Resource" requires a "name" parameter.');
        }
        if (!isset($data['action'])) {
            throw new \Symfony\Component\Form\Exception\LogicException('Annotation "Resource" requires an "action" parameter.');
        }
        $this->name = isset($data['value']) ? $data['value'] : $data['name'];
        $this->action = $data['action'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

}