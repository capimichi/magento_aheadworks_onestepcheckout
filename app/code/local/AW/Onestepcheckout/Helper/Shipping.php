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

class AW_Onestepcheckout_Helper_Shipping extends Mage_Core_Helper_Data
{
    /**
     * set shipping method for first load checkout page
     */
    public function initShippingMethod()
    {
        if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
            $shippingRates = $this->getShippingRates();
            if ((count($shippingRates) == 1)) {
                $currentShippingRate = current($shippingRates);
                if (count($currentShippingRate) == 1) {
                    $shippingRate = current($currentShippingRate);
                    $shippingMethod = $shippingRate->getCode();
                }
            } elseif ($lastShippingMethod = $this->_getLastShippingMethod()) {
                $shippingMethod = $lastShippingMethod;
            } elseif ($defaultShippingMethod = Mage::helper('aw_onestepcheckout/config')->getDefaultShippingMethod()) {
                $shippingMethod = $defaultShippingMethod;
            }
            if (isset($shippingMethod)) {
                $this->getOnepage()->saveShippingMethod($shippingMethod);
            }
        }
    }

    public function getShippingRates()
    {
        $address = Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getShippingAddress()
            ->collectShippingRates()
            ->save()
        ;
        return $address->getGroupedAllShippingRates();;
    }

    protected function _getLastShippingMethod()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer->getId()) {
            return false;
        }
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addFilter('customer_id', $customer->getId())
            ->addFieldToFilter('shipping_method', array('neq' => ''))
            ->addAttributeToSort('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->setPageSize(1);

        $lastOrder = $orderCollection->getFirstItem();
        if (!$lastOrder->getId()) {
            return false;
        }
        return $lastOrder->getShippingMethod();
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}