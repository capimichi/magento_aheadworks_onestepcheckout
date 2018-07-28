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

class AW_Onestepcheckout_Block_Onestep_Form_Review_Points extends Mage_Checkout_Block_Onepage_Abstract
{
    public function canShow()
    {
        if (Mage::helper('aw_onestepcheckout/points')->isPointsEnabled()) {
            return true;
        }
        return false;
    }

    public function isPointsSectionAvailable()
    {
        return Mage::helper('aw_onestepcheckout/points')->isPointsSectionAvailable();
    }

    public function getPointsUnitName()
    {
        return Mage::helper('aw_onestepcheckout/points')->getPointsUnitName();
    }

    public function getAppliedPointsAmount()
    {
        return Mage::helper('aw_onestepcheckout/points')->getAppliedPointsAmount();
    }

    public function getSummaryForCustomer()
    {
        return Mage::helper('aw_onestepcheckout/points')->getSummaryForCustomer();
    }

    public function getMoneyForPoints()
    {
        return Mage::helper('aw_onestepcheckout/points')->getMoneyForPoints();
    }

    public function getNeededPoints()
    {
        return Mage::helper('aw_onestepcheckout/points')->getNeededPoints();
    }

    public function getLimitedPoints()
    {
        return Mage::helper('aw_onestepcheckout/points')->getLimitedPoints();
    }

    public function getMaxAvailablePointsAmount()
    {
        return min($this->getSummaryForCustomer()->getPoints(), $this->getNeededPoints(), $this->getLimitedPoints());
    }

    public function getApplyPointsAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/applyPoints', array('_secure'=>true));
    }
}