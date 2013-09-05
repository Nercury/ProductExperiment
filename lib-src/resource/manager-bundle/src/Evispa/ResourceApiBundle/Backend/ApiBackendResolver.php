<?php

namespace Evispa\ResourceApiBundle\Backend;

use Evispa\ResourceApiBundle\Config\ResourceApiConfig;
use Evispa\ResourceApiBundle\Exception\BackendConfigurationException;
use Evispa\ResourceApiBundle\Registry\ResourceBackendConfigRegistry;

/**
 * Creates a new backend based on api config, available backend configs, and project config.
 */
class ApiBackendResolver
{
    private function buildManagerBackendSuggestion($resourceId, $availableBackendManagers) {
        $managersKeys = array_keys($availableBackendManagers);
        $configSuggestion = '"' . $resourceId . '.manager: ' . $managersKeys[0] . '"';

        for ($i = 1; $i < count($managersKeys); $i++) {
            if (count($managersKeys) > $i + 1) {
                $configSuggestion .= ', "' . $resourceId . '.manager: ' . $managersKeys[$i] . '"';
            } else {
                $configSuggestion .= ' or "' . $resourceId . '.manager: ' . $managersKeys[$i] . '"';
            }
        }

        return $configSuggestion;
    }

    /**
     * Get a backend for api.
     *
     * @param ResourceApiConfig $apiConfig
     * @param ResourceBackendConfigRegistry $backendConfigs
     * @param array $appApiBackendMap
     *
     * @return Backend
     */
    public function createBackend(ResourceApiConfig $apiConfig, ResourceBackendConfigRegistry $backendConfigs, array $appApiBackendMap) {

        $resourceId = $apiConfig->getResourceId();
        $productBackendMap = isset($appApiBackendMap[$resourceId]) ? $appApiBackendMap[$resourceId] : null;

        $availableBackendManagers = array();
        foreach ($backendConfigs->getBackendConfigs() as $config) {
            if ($resourceId === $config->getResourceId()) {
                if (null !== $config->getBackendManager()) {
                    $availableBackendManagers[$config->getBackendId()] = $config->getBackendManager();
                }
            }
        }

        // Check for backend managers.

        if (0 === count($availableBackendManagers)) {
            throw new BackendConfigurationException(
                'There is no backend with a manager for "'.$resourceId.'" resource.'
            );
        }

        // Check for strict backend.

        if (null !== $productBackendMap) {
            if (isset($productBackendMap['manager']) && !empty($productBackendMap['manager'])) {
                $requiredManager = $productBackendMap['manager'];
                if (!isset($availableBackendManagers[$requiredManager])) {
                    $configSuggestion = $this->buildManagerBackendSuggestion($resourceId, $availableBackendManagers);
                    throw new BackendConfigurationException(
                        'Backend manager "'.$requiredManager.'" assigned to "'.$resourceId.'" was not found, but '.
                        $configSuggestion.' are available. Check configuration for "evispa_resource_api.backend.'.$resourceId.'.manager".'
                    );
                }

                $availableBackendManagers = array(
                    $requiredManager => $availableBackendManagers[$requiredManager],
                );
            }
        }

        // Make sure no more than 1 backend is available before continuing.

        if (1 < count($availableBackendManagers)) {
            $configSuggestion = $this->buildManagerBackendSuggestion($resourceId, $availableBackendManagers);

            throw new BackendConfigurationException(
                'Resource "'.$resourceId.'" can use only a single backend manager at a time, '.
                'but multiple are available. '.
                'Please specify one of '.$configSuggestion.' in "evispa_resource_api.backend" configuration.'
            );
        }

        $backendManagerIds = array_keys($availableBackendManagers);
        $backendManager = $availableBackendManagers[$backendManagerIds[0]];

        $backend = new Backend();

        $backend->setBackendManager($backendManager);

        return $backend;
    }
}