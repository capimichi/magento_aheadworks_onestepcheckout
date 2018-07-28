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


class AW_Onestepcheckout_Helper_Enterprise_Points extends Mage_Core_Helper_Data
{
    protected $_pointsBlock;
    /**
     * Check is Points & Rewards enabled
     */
    public function isPointsEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_Reward')) {
            if (Mage::helper('enterprise_reward')->isEnabled()) {
                return true;
            }
        }
        return false;
    }

    public function isPointsSectionAvailable()
    {
        return $this->_getPointsBlock()->getCanUseRewardPoints();
    }

    public function getPointsUnitName()
    {
        return $this->__('Reward points');
    }

    public function getSummaryForCustomer()
    {
        return $this->_getPointsBlock()->getPointsBalance();
    }

    public function getMoneyForPoints()
    {
        return $this->_getPointsBlock()->getCurrencyAmount();
    }

    public function useRewardPoints()
    {
        return $this->_getPointsBlock()->useRewardPoints();
    }

    protected function _getPointsBlock()
    {
        if (!$this->_pointsBlock) {
            $this->_pointsBlock = Mage::app()->getLayout()->createBlock('enterprise_reward/checkout_payment_additional');
        }
        return $this->_pointsBlock;
    }
}