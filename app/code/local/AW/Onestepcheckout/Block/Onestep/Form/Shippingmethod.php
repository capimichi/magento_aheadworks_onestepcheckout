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


class AW_Onestepcheckout_Block_Onestep_Form_Shippingmethod extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $_rates;
    protected $_address;

    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            return $this->_rates = $groups;
        }
        return $this->_rates;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Check is Quote items can ship to
     *
     * @return boolean
     */
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }

    public function getSaveShipmentUrl()
    {
        return Mage::getUrl('aw_onestepcheckout/ajax/saveShippingMethod');
    }

    public function getBlockNumber($isIncrementNeeded = true)
    {
        return Mage::helper('aw_onestepcheckout')->getBlockNumber($isIncrementNeeded);
    }

    public function getEnterpriseGiftWrappingHtml()
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_GiftWrapping')) {
            $giftWrapHtml = Mage::app()->getLayout()
                ->createBlock('enterprise_giftwrapping/checkout_options')
                ->setTemplate('giftwrapping/checkout/options.phtml')
                ->toHtml()
            ;
            $giftWrapHtml .= Mage::app()->getLayout()
                ->createBlock('aw_onestepcheckout/onestep_form_shipping_enterprise_giftwrap')
                ->setTemplate('aw_onestepcheckout/onestep/form/shipping/enterprise/giftwrap.phtml')
                ->toHtml()
            ;
            return $giftWrapHtml;
        }
        return '';
    }
}