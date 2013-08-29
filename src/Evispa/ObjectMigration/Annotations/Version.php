<?php

namespace Evispa\ObjectMigration\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Version
{
    public $version;

    public function __construct(array $data)
    {
        if (!isset($data['value']) && !isset($data['version'])) {
            throw new \Symfony\Component\Form\Exception\LogicException('Annotation "Resource" requires a "version" parameter.');
        }
        $this->version = isset($data['value']) ? $data['value'] : $data['version'];
    }
}