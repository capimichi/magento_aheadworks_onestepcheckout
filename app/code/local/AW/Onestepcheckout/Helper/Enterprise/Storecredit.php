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


class AW_Onestepcheckout_Helper_Enterprise_Storecredit extends Mage_Core_Helper_Data
{
    protected $_storeCreditBlock;
    /**
     * Check is Points & Rewards enabled
     */
    public function isStoreCreditEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_CustomerBalance')) {
            if (Mage::helper('enterprise_customerbalance')->isEnabled()) {
                return true;
            }
        }
        return false;
    }

    public function isStoreCreditSectionAvailable()
    {
        return $this->_getStoreCreditBlock()->isAllowed();
    }

    public function isCustomerBalanceUsed()
    {
        return $this->_getStoreCreditBlock()->isCustomerBalanceUsed();
    }

    public function getBalance()
    {
        return $this->_getStoreCreditBlock()->getBalance();
    }

    protected function _getStoreCreditBlock()
    {
        if (!$this->_storeCreditBlock) {
            $this->_storeCreditBlock = Mage::app()->getLayout()->createBlock(
                'enterprise_customerbalance/checkout_onepage_payment_additional'
            );
        }
        return $this->_storeCreditBlock;
    }
}