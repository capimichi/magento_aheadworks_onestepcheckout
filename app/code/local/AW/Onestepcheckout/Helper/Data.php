<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onestepcheckout
 * @version    1.3.11
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Onestepcheckout_Helper_Data extends Mage_Core_Helper_Data
{
    const BLOCK_NUMBER_STORAGE_KEY = 'aw-osc-block-number';

    public function isCustomerMustBeLogged()
    {
        $helper = Mage::helper('checkout');
        if (method_exists($helper, 'isCustomerMustBeLogged')) {
            return $helper->isCustomerMustBeLogged();
        }
        return false;
    }

    /**
     * @param bool $isIncrementNeeded
     *
     * @return int|null
     */
    public function getBlockNumber($isIncrementNeeded = true)
    {
        $configHelper = Mage::helper('aw_onestepcheckout/config');
        if (!$configHelper->isBlockNumbering()) {
            return null;
        }
        $currentNumber = Mage::registry(self::BLOCK_NUMBER_STORAGE_KEY);
        if (is_null($currentNumber)) {
            $currentNumber = 0;
        }
        $currentNumber++;
        if ($isIncrementNeeded) {
            Mage::unregister(self::BLOCK_NUMBER_STORAGE_KEY);
            Mage::register(self::BLOCK_NUMBER_STORAGE_KEY, $currentNumber);
        }
        return $currentNumber;
    }

    public function getGrandTotal($quote)
    {
        $grandTotal = $quote->getGrandTotal();
        return Mage::app()->getStore()->getCurrentCurrency()->format($grandTotal, array(), false);
    }
}