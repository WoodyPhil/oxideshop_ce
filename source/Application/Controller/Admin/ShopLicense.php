<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\ShopConfiguration;
use OxidEsales\Eshop\Core\Curl;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\OnlineCaller;

/**
 * Admin shop license setting manager.
 * Collects shop license settings, updates it on user submit, etc.
 * Admin Menu: Main Menu -> Core Settings -> License.
 */
class ShopLicense extends ShopConfiguration
{
    /**
     * Current class template.
     *
     * @var string
     */
    protected $_sThisTemplate = "shop_license.tpl";

    /** @var string Current shop version links for edition. */
    private $versionCheckLink = 'http://admin.oxid-esales.com/CE/onlinecheck.php';

    /**
     * Executes parent method parent::render(), creates oxshop object, passes it's
     * data to Smarty engine and returns name of template file "shop_license.tpl".
     *
     * @return string
     */
    public function render()
    {
        $myConfig = $this->getConfig();
        if ($myConfig->isDemoShop()) {
            /** @var SystemComponentException $oSystemComponentException */
            $oSystemComponentException = oxNew(SystemComponentException::class, "license");
            throw $oSystemComponentException;
        }

        parent::render();

        $soxId = $this->_aViewData["oxid"] = $this->getEditObjectId();
        if ($soxId != "-1") {
            // load object
            $oShop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
            $oShop->load($soxId);
            $this->_aViewData["edit"] = $oShop;
        }

        $this->_aViewData["version"] = $myConfig->getVersion();

        $this->_aViewData['aCurVersionInfo'] = $this->_fetchCurVersionInfo($this->versionCheckLink);

        if (!$this->_canUpdate()) {
            $this->_aViewData['readonly'] = true;
        }

        return $this->_sThisTemplate;
    }

    /**
     * Checks if the license key update is allowed.
     *
     * @return bool
     */
    protected function _canUpdate()
    {
        $myConfig = $this->getConfig();

        $blIsMallAdmin = Registry::getSession()->getVariable('malladmin');
        if (!$blIsMallAdmin) {
            return false;
        }
        if ($myConfig->isDemoShop()) {
            return false;
        }

        return true;
    }

    /**
     * Fetch current shop version information from url
     * @param string $sUrl current version info fetching url by edition
     * @return string
     */
    protected function _fetchCurVersionInfo($sUrl)
    {
        try {
            $response = $this->requestVersionInfo($sUrl);
        } catch (\Throwable $e) {
            /** Exception is not logged! */
            $this->handleConnectionError();
            return '';
        }
        return $this->insertUpdateLinkIntoResponse($response);
    }

    private function requestVersionInfo(string $url): string
    {
        $curl = oxNew(Curl::class);
        $curl->setMethod("POST");
        $curl->setUrl(sprintf('%s/%s', $url, $this->getLanguageAbbreviation()));
        $curl->setParameters(["myversion" => $this->getConfig()->getVersion()]);
        $curl->setOption(
            Curl::CONNECT_TIMEOUT_OPTION,
            OnlineCaller::CURL_CONNECT_TIMEOUT
        );
        $curl->setOption(
            Curl::EXECUTION_TIMEOUT_OPTION,
            OnlineCaller::CURL_EXECUTION_TIMEOUT
        );
        return $curl->execute();
    }

    private function getLanguageAbbreviation(): string
    {
        $language = Registry::getLang();
        return $language->getLanguageAbbr($language->getTplLanguage());
    }

    private function handleConnectionError()
    {
        $this->displayErrorMessage();
    }

    private function displayErrorMessage()
    {
        Registry::getUtilsView()->addErrorToDisplay(
            sprintf(
                '%s! %s.',
                Registry::getLang()->translateString('ADMIN_SETTINGS_LICENSE_VERSION_FETCH_INFO_ERROR'),
                Registry::getLang()->translateString('CURL_EXECUTE_ERROR')
            )
        );
    }

    private function insertUpdateLinkIntoResponse(string $response): string
    {
        $response = strip_tags($response, "<br>, <b>");
        $result = explode("<br>", $response);
        if (!isset($result[5]) || !strstr($result[5], "update")) {
            return $response;
        }
        /** URL is set through i18n (lang file) */
        $sUpdateLink = Registry::getLang()->translateString("VERSION_UPDATE_LINK");
        $result[5] = "<a id='linkToUpdate' href='$sUpdateLink' target='_blank'>" . $result[5] . "</a>";
        return implode("<br>", $result);
    }
}
