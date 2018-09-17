<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Adapter\Configuration\Service;

/**
 * @internal
 */
interface ShopSettingEncoderInterface
{
    /**
     * @param mixed $value
     * @return string
     */
    public function encode($value): string;

    /**
     * @param string $encodingType
     * @param string $value
     * @return mixed
     */
    public function decode(string $encodingType, string $value);

    /**
     * @param mixed $value
     * @return string
     */
    public function getEncodingType($value): string;
}