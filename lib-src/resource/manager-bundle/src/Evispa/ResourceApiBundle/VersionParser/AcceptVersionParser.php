<?php

namespace Evispa\ResourceApiBundle\VersionParser;

/**
 * @author nerijus
 */
class AcceptVersionParser
{
    private $knownFormats = array(
        'html' => true,
        'xml' => true,
        'json' => true,
    );
    private $requestedFormat = 'html';
    private $formatDefaults = array();
    private $allowedVersions = array();

    /**
     * Add a format to known format list.
     *
     * @param string $format Format name, for example "json".
     *
     * @return self
     */
    public function addKnownFormat($format)
    {
        $this->knownFormats[$format] = true;
        return $this;
    }

    /**
     * Change requested format to specified one.
     *
     * @param string $format Format name, for example "json".
     *
     * @return self
     */
    public function setRequestedFormat($format)
    {
        $this->requestedFormat = $format;
        return $this;
    }

    /**
     * Set default format object version.
     *
     * @param string $format Format name, for example "json".
     * @param string $version Version name, for example "vnd.product.v1".
     *
     * @return self
     */
    public function setDefault($format, $version)
    {
        $this->formatDefaults[$format] = $version;
        return $this;
    }

    /**
     * Set allowed version list.
     *
     * @param string[] $versions List of allowed versions and coresponding class names (version) => (className).
     *
     * @return self
     */
    public function setAllowedVersions($versions)
    {
        $this->allowedVersions = $versions;
        return $this;
    }

    /**
     * Parse required version and format from accepted version types.
     *
     * @param string[] $acceptedContentTypes
     *
     * @return VersionAndFormat
     */
    public function parseVersionAndFormat($acceptedContentTypes)
    {
        $parsedVersion = null;
        $parsedFormat = null;

        foreach ($acceptedContentTypes as $acceptedContentType) {
            $plusPos = strpos($acceptedContentType, '+');
            if (false !== $plusPos) {
                $versionString = substr($acceptedContentType, 0, $plusPos);
                if (isset($this->allowedVersions[$versionString])) {
                    $parsedVersion = $versionString;
                    $parsedFormat = substr($acceptedContentType, $plusPos + 1);
                }
            } else {
                if (isset($this->allowedVersions[$acceptedContentType])) {
                    $parsedVersion = $acceptedContentType;
                }
            }
        }

        if (null === $parsedFormat) {
            $parsedFormat = $this->requestedFormat;
        }

        if (null === $parsedVersion) {
            if (isset($this->formatDefaults[$parsedFormat])) {
                $parsedVersion = $this->formatDefaults[$parsedFormat];
            }
        }

        if (null === $parsedFormat || null === $parsedVersion) {
            return null;
        }

        return new VersionAndFormat($parsedVersion, $parsedFormat);
    }
}