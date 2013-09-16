<?php

namespace Evispa\ResourceApiBundle\VersionParser;

/**
 * Information about version and format.
 */
class VersionAndFormat
{

    private $version;
    private $format;

    public function __construct($version, $format)
    {
        $this->version = $version;
        $this->format = $format;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getFormat()
    {
        return $this->format;
    }
}
