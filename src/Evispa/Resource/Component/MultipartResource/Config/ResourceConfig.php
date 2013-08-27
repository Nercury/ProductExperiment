<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Config;

/**
 * Resource configuration.
 */
class ResourceConfig
{
    /**
     * Resource identifier.
     *
     * @var string
     */
    public $id;

    /**
     * Resource name translation string.
     *
     * @var string
     */
    public $nameTranslation;

    /**
     * Translation domain.
     *
     * @var string
     */
    public $translationDomain;

    /**
     * Resource part configurations.
     *
     * @var array<ResourcePartConfig>
     */
    public $resourcePartConfigs;

    /**
     * Create a new resource configuration.
     *
     * @param string $id Identifier.
     * @param string $nameTranslation Name translation string.
     * @param string $translationDomain Translation domain.
     */
    public function __construct($id, $nameTranslation, $translationDomain)
    {
        $this->id = $id;
        $this->nameTranslation = $nameTranslation;
        $this->translationDomain = $translationDomain;
    }

    /**
     * Add resource part.
     *
     * @param ResourcePartConfig $resourcePartConfig Resource part configuration.
     *
     * @return ResourceConfig
     */
    public function addPart(ResourcePartConfig $resourcePartConfig)
    {
        $this->resourcePartConfigs[$resourcePartConfig->getId()] = $resourcePartConfig;

        return $this;
    }
}
