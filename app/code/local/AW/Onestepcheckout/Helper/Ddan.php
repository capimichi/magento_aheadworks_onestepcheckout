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

class AW_Onestepcheckout_Helper_Ddan extends Mage_Core_Helper_Data
{
    public function applyDeliveryData($date = null, $notice = null)
    {
        if ($this->isDDANEnabled()) {
            if (!is_null($date)) {
                try {
                    $date = $this->getDateFromPost($date);
                    // Check if date is available
                    $minAllowed = Mage::getBlockSingleton('deliverydate/html_date')->getFirstAvailableDate();
                    if (($date->compare($minAllowed, Zend_Date::DATE_SHORT) < 0)
                        || !Mage::getBlockSingleton('deliverydate/html_date')->isDateAvail($date)) {
                        throw new Exception('Date is not available');
                    }
                } catch(Exception $e) {
                    return array(
                        'error'   => 1,
                        'message' => Mage::helper('deliverydate')->__('Specified delivery date is invalid.')
                    );
                }
            } else {
                $date = Mage::getBlockSingleton('deliverydate/html_date')->getFirstAvailableDate();
            }
            Mage::getSingleton('customer/session')
                ->setAwDeliverydateDate($date)
                ->setAwDeliverydateNotice($notice)
            ;
        }
        return array();
    }

    public function getDateFromPost($date)
    {
        $date = new Zend_Date(
            $date,
            Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            Mage::app()->getLocale()->getLocaleCode()
        );
        return $date;
    }

    public function isDDANEnabled()
    {
        return $this->isModuleEnabled('AW_Deliverydate');
    }

    public function isDDANCanBeShown()
    {
        return $this->isDDANEnabled()
            && $this->isDDANOutputEnabled()
            && (!$this->isCurrQuoteVirtual())
            && (!$this->isCurrQuoteHasSubscriptionProduct());
    }

    public function isDDANOutputEnabled()
    {
        return $this->isModuleOutputEnabled('AW_Deliverydate');
    }

    public function isCurrQuoteVirtual()
    {
        $currQuote = $this->getCurrQuote();
        return $currQuote->isVirtual();
    }

    public function isCurrQuoteHasSubscriptionProduct()
    {
        $currQuote = $this->getCurrQuote();

        if ($currQuote->hasRecurringItems()) {
            return true;
        }

        if (Mage::helper('core')->isModuleEnabled('AW_Sarp2')) {
            if (Mage::helper('aw_sarp2/quote')->isQuoteHasSubscriptionProduct($currQuote)) {
                return true;
            }
        }

        return false;
    }

    public function getCurrQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}