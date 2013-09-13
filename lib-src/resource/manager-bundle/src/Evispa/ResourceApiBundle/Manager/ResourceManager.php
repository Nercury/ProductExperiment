<?php
/**
 * @author nerijus
 */

namespace Evispa\ResourceApiBundle\Manager;

use Evispa\Api\Resource\Model\ApiResourceInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Migration\ClassMigrationInfo;
use Evispa\ResourceApiBundle\Unicorn\Unicorn;
use LogicException;

class ResourceManager
{
    /**
     * Primary class of this resource manager.
     *
     * @var \ReflectionClass
     */
    private $class;
    
    /**
     * Resource parts.
     * 
     * @var array 
     */
    private $resourceParts;

    /**
     * Class migration information.
     *
     * @var ClassMigrationInfo
     */
    public $migrationInfo;
    
    /**
     * Required options for resource operations.
     * 
     * @var array
     */
    private $requiredOptions;
    
    /**
     * Backend driver unicorn.
     *
     * @var Unicorn
     */
    private $unicorn;

    /**
     * Used to read/write resource properties.
     *
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccess;
    
    public function __construct(
        $className,
        $resourceParts,
        ClassMigrationInfo $migrationInfo,
        $requiredOptions,
        Unicorn $unicorn
    ) {
        $this->class = new \ReflectionClass($className);
        $this->resourceParts = $resourceParts;
        $this->migrationInfo = $migrationInfo;
        $this->requiredOptions = $requiredOptions;
        $this->unicorn = $unicorn;
        
        $this->propertyAccess = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
    }
    
    public function getClassVersion($className) {
        if (!isset($this->migrationInfo->classVersions[$className])) {
            throw new LogicException('Class name "'.$className.'" is not known for "'.$className.'" resource manager.');
        }
        return $this->migrationInfo->classVersions[$className];
    }
    
    private function checkOptions($options) {
        foreach ($this->requiredOptions as $optionName => $info) {
            if (!isset($options[$optionName])) {
                throw new \Evispa\ResourceApiBundle\Exception\ResourceRequestException('Required option "'.$optionName.'" was not set. It is required to migrate from "'.$info['from'].'" to "'.$info['to'].'".');
            }
        }
    }
    
    /**
     * Find a single resource object.
     *
     * @param string $slug Resource identifier.
     *
     * @throws LogicException
     *
     * @return ApiResourceInterface|null
     */
    public function fetchOne($slug, $options = array())
    {
        $this->checkOptions($options);
    }
    
    /**
     * Find batch of resources
     *
     * @param FetchParameters $params
     *
     * @return FetchResult
     */
    public function fetchAll(FetchParameters $params, $options = array())
    {
        $this->checkOptions($options);
        
        $resultsObject = $this->unicorn->getPrimaryBackend()->fetchAll($params, $options);

        $resources = array();

        foreach ($resultsObject->getObjects() as $resultObject) {

            // Create a new resource.

            /** @var ApiResourceInterface $resource */
            $resource = $this->class->newInstance();
            $resource->setSlug($resultObject->getResourceSlug());

            foreach ($resultObject->getResourceParts() as $partName => $part) {

                if (null === $part) {
                    continue;
                }

                $this->propertyAccess->setValue(
                    $resource,
                    $this->resourceParts[$partName],
                    $part
                );
            }

            $resources[$resultObject->getResourceSlug()] = $resource;
        }

        $slugs = array_keys($resources);

        if (0 < count($slugs)) {
            // set parts form secondary backends
            foreach ($this->unicorn->getSecondaryBackends() as $unicornBackend) {
                $resourcesParts = $unicornBackend->fetchBySlugs($slugs);

                foreach ($resourcesParts as $slug => $otherParts) {

                    foreach ($otherParts as $partName => $part) {
                        if (null === $part) {
                            continue;
                        }

                        $this->propertyAccess->setValue(
                            $resource,
                            $this->resourceParts[$partName],
                            $part
                        );
                    }
                }
            }
        }

        return new FetchResult($params, $resources, $resultsObject->getTotalFound());
    }
}
