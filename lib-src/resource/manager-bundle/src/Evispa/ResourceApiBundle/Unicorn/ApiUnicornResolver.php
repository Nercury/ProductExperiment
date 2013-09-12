<?php

namespace Evispa\ResourceApiBundle\Unicorn;

use Evispa\ResourceApiBundle\Config\ResourceApiConfig;
use Evispa\ResourceApiBundle\Exception\BackendConfigurationException;
use Evispa\ResourceApiBundle\Registry\ResourceBackendConfigRegistry;

/**
 * Creates a new unicorn based on api config, available backend configs, and project config.
 */
class ApiUnicornResolver
{
    private function buildChoiceSuggestion($choices) {
        $choicesKeys = array_keys($choices);
        $configSuggestion = '"' . $choices[$choicesKeys[0]] . '"';

        for ($i = 1; $i < count($choicesKeys); $i++) {
            if (count($choicesKeys) > $i + 1) {
                $configSuggestion .= ', "' . $choices[$choicesKeys[$i]] . '"';
            } else {
                $configSuggestion .= ' or "' . $choices[$choicesKeys[$i]] . '"';
            }
        }

        return $configSuggestion;
    }

    private function buildManagerBackendSuggestion($resourceId, $availableBackendManagers) {
        $choices = array();

        foreach ($availableBackendManagers as $managerName => $_) {
            $choices[] = $resourceId . '.primary: ' . $managerName;
        }

        return $this->buildChoiceSuggestion($choices);
    }

    /**
     * Get a backend for api.
     *
     * @param ResourceApiConfig $apiConfig
     * @param ResourceBackendConfigRegistry $backendConfigs
     * @param array $appApiBackendMap
     *
     * @return Config\UnicornConfigInfo
     */
    public function getUnicornConfigurationInfo(ResourceApiConfig $apiConfig, ResourceBackendConfigRegistry $backendConfigs, array $appApiBackendMap) {

        $resourceId = $apiConfig->getResourceId();
        $productBackendMap = isset($appApiBackendMap[$resourceId]) ? $appApiBackendMap[$resourceId] : null;

        $availableBackends = array();
        $backendManagerConfigs = array();
        foreach ($backendConfigs->getBackendConfigs() as $config) {
            if ($resourceId === $config->getResourceId()) {
                if (null !== $config->getPrimaryBackend()) {
                    $availableBackends[$config->getBackendId()] = $config->getPrimaryBackend();
                    $backendManagerConfigs[$config->getBackendId()] = $config;
                }
            }
        }
        
        $allPrimaryBackends = $availableBackends;

        // Check for backend managers.

        if (0 === count($availableBackends)) {
            throw new BackendConfigurationException(
                'There is no backend with a manager for "'.$resourceId.'" resource.'
            );
        }

        // Check for strict backend.

        if (null !== $productBackendMap) {
            if (isset($productBackendMap['primary']) && !empty($productBackendMap['primary'])) {
                $requiredManager = $productBackendMap['primary'];
                if (!isset($availableBackends[$requiredManager])) {
                    $configSuggestion = $this->buildManagerBackendSuggestion($resourceId, $availableBackends);
                    throw new BackendConfigurationException(
                        'Backend "'.$requiredManager.'" assigned to "'.$resourceId.'" was not found, but '.
                        $configSuggestion.' are available. Check configuration for "evispa_resource_api.backend.'.$resourceId.'.primary".'
                    );
                }

                $availableBackends = array(
                    $requiredManager => $availableBackends[$requiredManager],
                );
            }
        }

        // Make sure no more than 1 backend is available before continuing.

        if (1 < count($availableBackends)) {
            $configSuggestion = $this->buildManagerBackendSuggestion($resourceId, $availableBackends);

            throw new BackendConfigurationException(
                'Resource "'.$resourceId.'" can only use a single primary backend at a time, '.
                'but multiple are available. '.
                'Please specify '.$configSuggestion.' in "evispa_resource_api.backend" configuration.'
            );
        }

        $backendManagerIds = array_keys($availableBackends);
        $backendId = $backendManagerIds[0];
        $backendConfig = $backendManagerConfigs[$backendId];

        $primaryParts = $backendConfig->getParts();
        
        $secondaryConfigs = array();
        
        // Build available backend part array for both primary and secondary backends.
        
        $backendParts = array();
        
        foreach ($primaryParts as $partId => $_) {
            $backendParts[$backendId][$partId] = true; 
        }
        
        foreach ($backendConfigs->getBackendConfigs() as $config) {
            if ($resourceId === $config->getResourceId()) {
                if (null !== $config->getSecondaryBackend()) {
                    $secondaryConfigs[$config->getBackendId()] = $config;
                    foreach ($config->getParts() as $partId => $_) {
                        $backendParts[$config->getBackendId()][$partId] = true;
                    }
                }
            }
        }

        $resourceParts = $apiConfig->getParts();

        // Resolve used parts.
        $prefefinedPartMap = $productBackendMap['parts'];

        // Validate predefined map for existing ids.
        foreach ($prefefinedPartMap as $id => $mappedBackendId) {
            if (!isset($resourceParts[$id])) {
                foreach ($resourceParts as $existingId => $_) {
                    if (false !== strpos($existingId, $id)) {
                        throw new BackendConfigurationException(
                            'Configured resource part "'.$id.'" was not found on "'.$resourceId.'" resource. '.
                            'Did you mean "'.$existingId.'"?'
                        );
                    }
                }
                throw new BackendConfigurationException(
                    'Configured resource part "'.$id.'" was not found on "'.$resourceId.'" resource. '.
                    'Use '.$this->buildChoiceSuggestion(array_keys($resourceParts)).'.'
                );
            }
            
            $allowedBackendsForPart = array();
            foreach ($backendParts as $possibleBackendId => $partNames) {
                if (isset($partNames[$id])) {
                    $allowedBackendsForPart[$possibleBackendId] = true;
                }
            }

            if (!isset($backendParts[$mappedBackendId])) {
                if (isset($allPrimaryBackends[$mappedBackendId])) {
                    if (0 === count($allowedBackendsForPart)) {
                        throw new BackendConfigurationException(
                            'Backend "'.$mappedBackendId.'" was assigned to manage "'.$resourceId.'" resource\'s part "'.$id.'", '.
                            'but this backend is primary, and only a single primary can be configured for a resource. You can '.
                            'either change primary from "'.$backendId.'" to "'.$mappedBackendId.'" or remove this configuration '.
                            'from "evispa_resource_api.backend.'.$resourceId.'.parts".'
                        );
                    }
                    
                    throw new BackendConfigurationException(
                        'Backend "'.$mappedBackendId.'" was assigned to manage "'.$resourceId.'" resource\'s part "'.$id.'", '.
                        'but this backend is primary, and only a single primary can be configured for a resource. '.
                        'However, you can either change primary backend from "'.$backendId.'" to "'.$mappedBackendId.'" or '.
                        'change backend for this part to '.$this->buildChoiceSuggestion(array_keys($allowedBackendsForPart)).'.'
                    );
                } else {
                    if (0 === count($allowedBackendsForPart)) {
                        throw new BackendConfigurationException(
                            'Backend "'.$mappedBackendId.'" was assigned to manage "'.$resourceId.'" resource\'s part "'.$id.'", '.
                            'but this backend was not found.'
                        );
                    }

                    throw new BackendConfigurationException(
                        'Backend "'.$mappedBackendId.'" was assigned to manage "'.$resourceId.'" resource\'s part "'.$id.'", '.
                        'but this backend was not found. '.
                        'However you change it to '.$this->buildChoiceSuggestion(array_keys($allowedBackendsForPart)).'.'
                    );
                }
            } else {
                if (!isset($allowedBackendsForPart[$mappedBackendId])) {
                    throw new BackendConfigurationException(
                        'Backend "'.$mappedBackendId.'" was assigned to manage "'.$resourceId.'" resource\'s part "'.$id.'", '.
                        'but such functionality is not yet implemented. Implement it and get some cookies! '.
                        'However, if you are extra lazy, change it to '.$this->buildChoiceSuggestion(array_keys($allowedBackendsForPart)).' and hope for the best.'
                    );
                }
            }
        }
        
        // Figure out actual part backends.
        
        $partBackends = $prefefinedPartMap; // Initially it is equal to predefined map.
        
        // First, use predefined map, then assign everything else somehow.
        
        foreach ($resourceParts as $partName => $_) {
            if (!isset($prefefinedPartMap[$partName])) {
                $backendsThatCanManageThePart = array();

                foreach ($backendParts as $candidateBackendId => $candidateParts) {
                    if (isset($candidateParts[$partName])) {
                        $backendsThatCanManageThePart[$candidateBackendId] = true;
                    }
                }
                
                if (0 === count($backendsThatCanManageThePart)) {
                    continue;
                }
                
                if (1 < count($backendsThatCanManageThePart)) {
                    $configSuggestion = $this->buildChoiceSuggestion(array_keys($backendsThatCanManageThePart));
                    
                    throw new BackendConfigurationException(
                        'Resource part "'.$partName.'" of "'.$resourceId.'" can only use a single backend at a time, '.
                        'but multiple are available. '.
                        'Please specify '.$configSuggestion.' in "evispa_resource_api.backend.'.$resourceId.'.parts" configuration.'
                    );
                }
                
                $backendItems = array_keys($backendsThatCanManageThePart);
                $partBackends[$partName] = $backendItems[0];
            }
        }
        
        $primaryParts = array();
        $otherResolvedParts = array();
        
        foreach ($partBackends as $partId => $resolvedBackendId) {
            if ($resolvedBackendId === $backendId) {
                $primaryParts[] = $partId;
            } else {
                $otherResolvedParts[$resolvedBackendId][] = $partId;
            }
        }

        $primaryBackendConfigInfo = new Config\PrimaryBackendConfigInfo($backendId, $primaryParts);

        $unicornInfo = new Config\UnicornConfigInfo($primaryBackendConfigInfo);

        foreach ($otherResolvedParts as $resolvedBackendId => $managedParts) {
            $secondaryBackendConfigInfo = new Config\SecondaryBackendConfigInfo($resolvedBackendId, $managedParts);
            $unicornInfo->addSecondaryBackendConfigInfo($secondaryBackendConfigInfo);
        }
        
        return $unicornInfo;
    }
}