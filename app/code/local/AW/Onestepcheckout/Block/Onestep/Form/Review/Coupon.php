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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Coupon extends Mage_Checkout_Block_Onepage_Abstract
{
    public function canShow()
    {
        $isAvailable = Mage::helper('aw_onestepcheckout/config')->isCoupon();
        if (Mage::helper('aw_onestepcheckout/points')->isPointsEnabled()) {
            if (!Mage::helper('points/config')->getCanUseWithCoupon()) {
                $isAvailable = $isAvailable && !$this->getAppliedPointsAmount();
            }
        }
        if (Mage::helper('aw_onestepcheckout/referafriend')->isReferafriendEnabled()) {
            if (!Mage::helper('awraf/config')->isAllowedWithCoupons(Mage::app()->getStore())) {
                $isAvailable = $isAvailable && !$this->getAppliedRafDiscount();
            }
        }
        return $isAvailable;
    }

    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }

    public function getApplyCouponAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/applyCoupon', array('_secure'=>true));
    }

    public function getAppliedPointsAmount()
    {
        return Mage::helper('aw_onestepcheckout/points')->getAppliedPointsAmount();
    }

    public function getAppliedRafDiscount()
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getAppliedAmount();
    }

    public function getConfig()
    {
        return Mage::helper('aw_onestepcheckout/config');
    }
}