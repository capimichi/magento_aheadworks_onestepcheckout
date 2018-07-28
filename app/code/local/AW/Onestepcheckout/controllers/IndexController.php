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

class AW_Onestepcheckout_IndexController extends Mage_Checkout_Controller_Action
{
    /**
     * @return AW_Onestepcheckout_IndexController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();

        $checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($checkoutSessionQuote->getIsMultiShipping()) {
            $checkoutSessionQuote->setIsMultiShipping(false);
            $checkoutSessionQuote->removeAllAddresses();
        }

        if(!$this->_canShowForUnregisteredUsers()){
            $this->norouteAction();
            $this->setFlag('',self::FLAG_NO_DISPATCH,true);
            return;
        }

        return $this;
    }

    public function indexAction()
    {
        if (!Mage::helper('aw_onestepcheckout/config')->isEnabled()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onestep checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();

        //need set billing and shipping data from session
        $currentData = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        if ($currentData && array_key_exists('billing', $currentData)) {
            if (isset($currentData['billing_address_id'])) {
                Mage::helper('aw_onestepcheckout/address')->saveBilling($currentData['billing'], $currentData['billing_address_id']);
            }

            if (isset($currentData['billing']['use_for_shipping'])
                && $currentData['billing']['use_for_shipping'] == 0
                && isset($currentData['shipping_address_id'])
            ) {
                Mage::helper('aw_onestepcheckout/address')->saveShipping($currentData['shipping'], $currentData['shipping_address_id']);
            }
        }

        Mage::helper('aw_onestepcheckout/address')->initAddress();
        Mage::helper('aw_onestepcheckout/shipping')->initShippingMethod();
        Mage::helper('aw_onestepcheckout/payment')->initPaymentMethod();

        // Reset Enterprise Giftwrap options.
        $wrappingInfo = array();
        $wrappingInfo['gw_id'] = null;
        $wrappingInfo['gw_allow_gift_receipt'] = false;
        $wrappingInfo['gw_add_card'] = false;
        if ($this->getOnepage()->getQuote()->getShippingAddress()) {
            $this->getOnepage()->getQuote()->getShippingAddress()->addData($wrappingInfo);
        }
        $this->getOnepage()->getQuote()->addData($wrappingInfo);
        foreach ($this->getOnepage()->getQuote()->getItemsCollection() as $item) {
            if ($item->getGwId()){
                $item->setGwId(null)->save();
            }
        }
        // Reset Enterprise Giftwrap options.

        $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
        $this->getOnepage()->getQuote()->collectTotals()->save();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _canShowForUnregisteredUsers()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || $this->getRequest()->getActionName() == 'index'
            || Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
            || !Mage::helper('aw_onestepcheckout')->isCustomerMustBeLogged();
    }
}