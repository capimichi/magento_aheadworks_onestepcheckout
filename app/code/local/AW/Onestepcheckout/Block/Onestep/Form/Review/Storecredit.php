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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Storecredit extends Mage_Checkout_Block_Onepage_Abstract
{
    public function canShow()
    {
        if (Mage::helper('aw_onestepcheckout/storecredit')->isStoreCreditEnabled()) {
            return true;
        }
        return false;
    }

    public function isStoreCreditSectionAvailable()
    {
        return Mage::helper('aw_onestepcheckout/storecredit')->isStoreCreditSectionAvailable();
    }

    public function getBalance()
    {
        return Mage::helper('aw_onestepcheckout/storecredit')->getBalance();
    }

    public function isStorecreditUsed()
    {
        return Mage::helper('aw_onestepcheckout/storecredit')->isStorecreditUsed();
    }

    public function currency($value)
    {
        return Mage::helper('core')->currency($value, true, false);
    }

    public function getApplyStorecreditAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/applyStorecredit', array('_secure' => true));
    }

    public function getRemoveStorecreditAjaxUrl()
    {
        return Mage::getUrl(
            'onestepcheckout/ajax/removeStorecredit',
            array(
                '_secure' => true,
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            )
        );
    }
}