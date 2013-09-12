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
            $choices[] = $resourceId . '.manager: ' . $managerName;
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
    public function makeUnicorn(ResourceApiConfig $apiConfig, ResourceBackendConfigRegistry $backendConfigs, array $appApiBackendMap) {

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
                        'Backend manager "'.$requiredManager.'" assigned to "'.$resourceId.'" was not found, but '.
                        $configSuggestion.' are available. Check configuration for "evispa_resource_api.backend.'.$resourceId.'.manager".'
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
                'Resource "'.$resourceId.'" can use only a single backend manager at a time, '.
                'but multiple are available. '.
                'Please specify one of '.$configSuggestion.' in "evispa_resource_api.backend" configuration.'
            );
        }

        $backendManagerIds = array_keys($availableBackends);
        $backendId = $backendManagerIds[0];
        $backendConfig = $backendManagerConfigs[$backendId];

        $primaryParts = $backendConfig->getParts();

        $secondaryConfigs = array();
        $secondaryParts = array();
        foreach ($backendConfigs->getBackendConfigs() as $config) {
            if ($resourceId === $config->getResourceId()) {
                if (null !== $config->getSecondaryBackend()) {
                    $secondaryConfigs[$config->getBackendId()] = $config;
                    foreach ($config->getParts() as $partId => $_) {
                        $secondaryParts[$config->getBackendId()][$partId] = true;
                    }
                }
            }
        }

        $resourceParts = $apiConfig->getParts();

        // Resolve used parts.
        $prefefinedPartMap = $productBackendMap['parts'];

        // Validate predefined map for existing ids.
        foreach ($prefefinedPartMap as $id => $_) {
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
                    'Use either '.$this->buildChoiceSuggestion(array_keys($resourceParts)).'.'
                );
            }
        }


        //var_dump($prefefinedPartMap); die;

        $primaryBackendConfigInfo = new Config\PrimaryBackendConfigInfo($backendId, $primaryParts);

        $unicornInfo = new Config\UnicornConfigInfo($primaryBackendConfigInfo);

        return $unicornInfo;
    }
}