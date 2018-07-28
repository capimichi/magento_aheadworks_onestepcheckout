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

class AW_Onestepcheckout_Helper_Points extends Mage_Core_Helper_Data
{
    protected $_pointsBlock;
    /**
     * Check is Points & Rewards enabled
     */
    public function isPointsEnabled()
    {
        if ($this->isModuleEnabled('AW_Points')) {
            if (Mage::helper('points/config')->isPointsEnabled()) {
                return true;
            }
        }
        return false;
    }

    public function isPointsSectionAvailable()
    {
        return $this->_getPointsBlock()->pointsSectionAvailable();
    }

    public function getPointsUnitName()
    {
        return Mage::helper('points/config')->getPointUnitName();
    }

    public function getSummaryForCustomer()
    {
        return $this->_getPointsBlock()->getSummaryForCustomer();
    }

    public function getAppliedPointsAmount()
    {
        // hack for AW_Points
        $appliedPointsAmount = min(
            (int)Mage::getSingleton('checkout/session')->getData('points_amount'),
            $this->getNeededPoints(),
            $this->getLimitedPoints()
        );
        Mage::getSingleton('checkout/session')->setData('points_amount', $appliedPointsAmount);
        return $appliedPointsAmount;
    }

    public function getAppliedPointsDiscountAmount()
    {
        $result = 0;
        if ($this->isPointsEnabled()) {
            $spentRateModel = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
            if ($spentRateModel->getId()) {
                $result = $spentRateModel->exchange($this->getAppliedPointsAmount());
            }
        }
        return $result;
    }

    public function getMoneyForPoints()
    {
        return $this->_getPointsBlock()->getMoneyForPoints();
    }

    //TODO: remove when POINTS will be refactored
    public function getNeededPoints()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $summary = $quote->getData('base_subtotal_with_discount');
        $isApplyBefore = Mage::helper('points/config')->getPointsSpendingCalculation() == AW_Points_Helper_Config::BEFORE_TAX;
        if (!$isApplyBefore) {
            if ($quote->isVirtual()) {
                $summary += $quote->getBillingAddress()->getData('base_tax_amount');
            } else {
                $summary += $quote->getShippingAddress()->getData('base_tax_amount');
            }
        }
        $rafDiscount = Mage::helper('aw_onestepcheckout/referafriend')->getAppliedDiscountAmount();
        return Mage::helper('points')->getNeededPoints($summary - $rafDiscount);
    }

    public function getLimitedPoints()
    {
        return $this->_getPointsBlock()->getLimitedPoints();
    }

    protected function _getPointsBlock()
    {
        if (!$this->_pointsBlock) {
            $this->_pointsBlock = Mage::app()->getLayout()->createBlock('points/checkout_onepage_payment_methods');
        }
        return $this->_pointsBlock;
    }
}
