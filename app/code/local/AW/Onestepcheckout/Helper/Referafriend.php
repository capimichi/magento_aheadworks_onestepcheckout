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

class AW_Onestepcheckout_Helper_Referafriend extends Mage_Core_Helper_Data
{
    protected $_rafBlock;

    /**
     * Check is Refer a Friend enabled
     */
    public function isReferafriendEnabled()
    {
        if ($this->isModuleEnabled('AW_Raf')) {
            return true;
        }
        return false;
    }

    public function getReservedAmount()
    {
        return Mage::helper('awraf')->getReservedAmount();
    }

    public function isDiscountSectionAvailable()
    {
        return $this->_getRafBlock()->discountAllowed();
    }

    public function getAppliedDiscountAmount()
    {
        // hack for AW_Raf
        if ($this->isReferafriendEnabled()) {
            $appliedDiscountAmount = Mage::helper('awraf')->getAppliedAmount();
            if (!$appliedDiscountAmount) {
                Mage::helper('awraf')->clearAppliedAmount();
            }
            Mage::getSingleton('checkout/session')->setData('raf_discount_amount', (float)$appliedDiscountAmount);
            return $appliedDiscountAmount + $this->getPercentDiscountAmount();
        }
        return 0;
    }

    public function getAppliedAmount()
    {
        // hack for AW_Raf
        $appliedDiscountAmount = min(
            Mage::helper('awraf')->getAppliedAmount(),
            $this->getMaxDiscount()
        );
        if ($appliedDiscountAmount <= 0) {
            Mage::helper('awraf')->clearAppliedAmount();
        }
        Mage::getSingleton('checkout/session')->setData('raf_discount_amount', (float)$appliedDiscountAmount);
        return $appliedDiscountAmount;
    }

    public function getAvailableAmount($toPrice = false)
    {
        $referAFriendHelper = Mage::helper('awraf');
        $amount = Mage::getModel('awraf/api')->getAvailableAmount(
            $referAFriendHelper->getCustomerId(),
            Mage::app()->getWebsite()->getId()
        );
        if ($toPrice) {
            $store = Mage::app()->getStore();
            return $referAFriendHelper->convertAmount($amount, array(
                'store' => $store, 'format' => true, 'direction' => AW_Raf_Helper_Data::CONVERT_TO_CURRENT
            ));
        }
        return $amount;
    }

    public function getNumericAmount()
    {
        return $this->_getRafBlock()->getNumericAmount();
    }

    public function getMaxDiscountPercent()
    {
        return Mage::getStoreConfig('awraf/general/max_limit');
    }

    public function getMaxDiscount($toPrice = false)
    {
        $referAFriendHelper = Mage::helper('awraf');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $pointsDiscount = Mage::helper('aw_onestepcheckout/points')->getAppliedPointsDiscountAmount();
        $subtotalWithDiscount = $quote->getBaseSubtotalWithDiscount() - $pointsDiscount;

        if (!$subtotalWithDiscount) {
            return 0;
        }
        $maxDiscount = $this->getMaxDiscountPercent();
        $maxDiscountAmountForOrder = $subtotalWithDiscount;
        if ($maxDiscount !== '' && !is_null($maxDiscount)) {
            $maxDiscountAmountForOrder = $subtotalWithDiscount * intval($maxDiscount) / 100;
        }
        $maxDiscountAmountForOrder -= $this->getPercentDiscountAmount();
        $maxDiscountAmountForOrder = max($maxDiscountAmountForOrder, 0);
        if ($toPrice) {
            return $referAFriendHelper->convertAmount($maxDiscountAmountForOrder, array(
                'store'     => Mage::app()->getStore(),
                'format'    => true,
                'direction' => AW_Raf_Helper_Data::CONVERT_TO_CURRENT
            ));
        }
        return $maxDiscountAmountForOrder;
    }

    public function getPercentDiscount()
    {
        $referAFriendHelper = Mage::helper('awraf');
        $store = Mage::app()->getStore();
        $discountObject = Mage::getModel('awraf/api')->getAvailableDiscount(
            $referAFriendHelper->getCustomerId(),
            $store->getWebsite()->getId()
        );

        if (!$discountObject || !$discountObject->getId()
            || ($discountObject->getType() != AW_Raf_Model_Source_ActionType::PERCENT_DISCOUNT_VALUE)) {
            return null;
        }
        return $discountObject->getDiscount();
    }

    public function getPercentDiscountAmount()
    {
        if ($percentDiscount = $this->getPercentDiscount()) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            return $quote->getBaseSubtotalWithDiscount() * $percentDiscount / 100;
        }
        return;
    }

    protected function _getRafBlock()
    {
        if (!$this->_rafBlock) {
            $this->_rafBlock = Mage::app()->getLayout()->createBlock('awraf/checkout_cart_discount');
        }
        return $this->_rafBlock;
    }


}