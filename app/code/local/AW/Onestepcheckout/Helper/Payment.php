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

class AW_Onestepcheckout_Helper_Payment extends Mage_Core_Helper_Data
{
    /**
     * set shipping method for first load checkout page
     */
    public function initPaymentMethod()
    {
        // check if payment saved to quote
        if (!$this->getQuote()->getPayment()->getMethod()) {
            $data = array();
            $paymentMethods = $this->getPaymentMethods();
            if ((count($paymentMethods) == 1)) {
                $currentPaymentMethod = current($paymentMethods);
                $data['method'] = $currentPaymentMethod->getCode();
            } elseif ($lastPaymentMethod = $this->_getLastPaymentMethod()) {
                $data['method'] = $lastPaymentMethod;
            } elseif ($defaultPaymentMethod = Mage::helper('aw_onestepcheckout/config')->getDefaultPaymentMethod()) {
                $data['method'] = $defaultPaymentMethod;
            }
            if (!empty($data)) {
                try {
                    $this->getOnepage()->savePayment($data);
                } catch (Exception $e) {
                    // catch this exception
                }
            }
        }
    }

    public function getPaymentMethods()
    {
        $paymentBlock = Mage::app()->getLayout()->createBlock('aw_onestepcheckout/onestep_form_paymentmethod');
        return $paymentBlock->getMethods();
    }

    protected function _getLastPaymentMethod()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer->getId()) {
            return false;
        }
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addFilter('customer_id', $customer->getId())
            ->addAttributeToSort('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->setPageSize(1);

        $lastOrder = $orderCollection->getFirstItem();
        if (!$lastOrder->getId()) {
            return false;
        }
        return $lastOrder->getPayment()->getMethod();
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

    public function isEbizmartsSagePaySuiteEnabled()
    {
        return $this->isModuleOutputEnabled('Ebizmarts_SagePaySuite');
    }

    public function isEbizmartsSagePaySuiteMethod($paymentMethod)
    {
        return $this->isEbizmartsSagePaySuiteEnabled()
            && Mage::helper('sagepaysuite')->isSagePayMethod($paymentMethod)
        ;
    }
}