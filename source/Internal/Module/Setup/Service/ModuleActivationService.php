<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Setup\Service;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Provider\ModuleConfigurationProviderInterface;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\ModuleSettingHandlerNotFoundException;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Handler\ModuleSettingHandlerInterface;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Validator\ModuleSettingValidatorInterface;

/**
 * @internal
 */
class ModuleActivationService implements ModuleActivationServiceInterface
{
    /**
     * @var ModuleConfigurationProviderInterface
     */
    private $moduleConfigurationProvider;

    /**
     * @var array
     */
    private $moduleSettingHandlers = [];

    /**
     * @var array
     */
    private $moduleSettingValidators = [];

    /**
     * ModuleActivationService constructor.
     * @param ModuleConfigurationProviderInterface $moduleConfigurationProvider
     */
    public function __construct(ModuleConfigurationProviderInterface $moduleConfigurationProvider)
    {
        $this->moduleConfigurationProvider = $moduleConfigurationProvider;
    }

    /**
     * @param string $moduleId
     * @param int    $shopId
     */
    public function activate(string $moduleId, int $shopId)
    {
        $moduleConfiguration = $this->moduleConfigurationProvider->getModuleConfiguration(
            $moduleId,
            'dev',
            $shopId
        );

        $this->validateModuleSettings($moduleConfiguration);

        /**
         * @todo [II] wrap it in transaction.
         */
        foreach ($moduleConfiguration->getSettings() as $setting) {
            $handler = $this->getHandler($setting);
            $handler->handle($setting, $moduleId, $shopId);
        }
    }

    /**
     * @param ModuleSettingHandlerInterface $moduleSettingHandler
     */
    public function addHandler(ModuleSettingHandlerInterface $moduleSettingHandler)
    {
        $this->moduleSettingHandlers[] = $moduleSettingHandler;
    }

    /**
     * @param ModuleSettingValidatorInterface $moduleSettingValidator
     */
    public function addValidator(ModuleSettingValidatorInterface $moduleSettingValidator)
    {
        $this->moduleSettingValidators[] = $moduleSettingValidator;
    }

    /**
     * @param ModuleSetting $setting
     * @return ModuleSettingHandlerInterface
     */
    private function getHandler(ModuleSetting $setting): ModuleSettingHandlerInterface
    {
        foreach ($this->moduleSettingHandlers as $moduleSettingHandler) {
            if ($moduleSettingHandler->canHandle($setting)) {
                return $moduleSettingHandler;
            }
        }

        throw new ModuleSettingHandlerNotFoundException(
            'Handler for the setting ' . $setting->getName() . ' wasn\'t found.'
        );
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     */
    private function validateModuleSettings(ModuleConfiguration $moduleConfiguration)
    {
        foreach ($moduleConfiguration->getSettings() as $setting) {
            foreach ($this->moduleSettingValidators as $validator) {
                if ($validator->canValidate($setting)) {
                    $validator->validate($setting);
                }
            }
        }
    }
}