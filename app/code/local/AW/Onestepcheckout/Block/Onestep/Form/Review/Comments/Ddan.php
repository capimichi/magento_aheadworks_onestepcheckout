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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Comments_Ddan extends Mage_Core_Block_Template
{
    public function getCalendarHtml()
    {
        if (Mage::helper('aw_onestepcheckout/ddan')->isDDANEnabled()) {
            $block = $this->getLayout()->createBlock('deliverydate/frontend_checkout_onepage_deliverydate');
            Mage::getSingleton('customer/session')->setAwDeliverydateDate($this->getDeliveryDate());
            return $block->getCalendarHtml();
        }
        return '';
    }

    public function getDeliveryDate()
    {
        $data = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        if (isset($data['aw_deliverydate_date'])) {
            return $data['aw_deliverydate_date'];
        }
        return '';
    }

    public function getComments()
    {
        $data = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        if (isset($data['comments'])) {
            return $data['comments'];
        }
        return null;
    }

    public function isGeneralNoticeEnabled()
    {
        return Mage::getStoreConfig(AW_Deliverydate_Helper_Config::XML_PATH_GENERAL_NOTICE_ENABLED);
    }

    public function isTimeNoticeEnabled()
    {
        return Mage::getStoreConfig(AW_Deliverydate_Helper_Config::XML_PATH_GENERAL_TIME_NOTICE_ENABLED);
    }

    /**
     * copy-paste from ddan
     *
     * @return string
     */
    public function getFormattedTime()
    {
        $Date = Mage::app()->getLocale()->date();
        $time = array('hour' => null, 'minute' => null, 'second' => null);
        $maxSameDay = Mage::getStoreConfig(AW_Deliverydate_Helper_Config::XML_PATH_GENERAL_MAX_SAMEDAY_TIME);
        list($time['hour'], $time['minute'], $time['second']) = explode(",", $maxSameDay);
        $Date->setTime($time);
        return $this->formatTime($Date);
    }
}