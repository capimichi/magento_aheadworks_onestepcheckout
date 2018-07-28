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

class AW_Onestepcheckout_AjaxController extends Mage_Checkout_Controller_Action
{

    /**
     * @return AW_Onestepcheckout_AjaxController|Mage_Core_Controller_Front_Action
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
        return $this;
    }

    /**
     * action for customer login
     */
    public function customerLoginAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $customerSession = Mage::getSingleton('customer/session');
        $result = array(
            'success' => true,
            'messages' => array()
        );
        if (!$customerSession->isLoggedIn()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customerSession->login($login['username'], $login['password']);
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = $this->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $result['success'] = false;
                    $result['messages'][] = $message;
                    $customerSession->setUsername($login['username']);
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $this->__("Oops something's wrong");
                    //TODO: think about redirect to login page
                }
            } else {
                $result['success'] = false;
                $result['messages'][] = $this->__('Login and password are required.');
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * action for customer forgot password
     */
    public function customerForgotPasswordAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $customerSession = Mage::getSingleton('customer/session');
        $result = array(
            'success' => true,
            'messages' => array()
        );
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (Zend_Validate::is($email, 'EmailAddress')) {
                /** @var $customer Mage_Customer_Model_Customer */
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);
                if ($customer->getId()) {
                    try {
                        Mage::helper('aw_onestepcheckout/customer')->sendForgotPasswordForCustomer($customer);
                    } catch (Exception $exception) {
                        $result['success'] = false;
                        $result['messages'][] = $exception->getMessage();
                    }
                }
            } else {
                $customerSession->setForgottenEmail($email);
                $result['success'] = false;
                $result['messages'][] = $this->__('Invalid email address.');
            }
        } else {
            $result['success'] = false;
            $result['messages'][] = $this->__('Please enter your email.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveFormValuesAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'     => true,
            'messages'    => array(),
        );
        if ($this->getRequest()->isPost()) {
            $newData = $this->getRequest()->getPost();
            //store ddan date as Zend_Date object
            if (array_key_exists('aw_deliverydate_date', $newData)) {
                try {
                    $newData['aw_deliverydate_date']
                        = Mage::helper('aw_onestepcheckout/ddan')->getDateFromPost($newData['aw_deliverydate_date']);
                } catch(Exception $e) {}
            }
            $currentData = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
            if (!is_array($currentData)) {
                $currentData = array();
            }
            Mage::getSingleton('checkout/session')->setData(
                'aw_onestepcheckout_form_values', array_merge($currentData, $newData)
            );
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * save checkout billing address
     */
    public function saveAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
                $this->getOnepage()->getQuote()->setCustomerEmail($data['email']);
            }
            if (isset($data['firstname'])) {
                $this->getOnepage()->getQuote()->setCustomerFirstname($data['firstname']);
            }
            if (isset($data['lastname'])) {
                $this->getOnepage()->getQuote()->setCustomerLastname($data['lastname']);
            }

            $saveBillingResult = Mage::helper('aw_onestepcheckout/address')->saveBilling($data, $customerAddressId);
            $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;
            if ($usingCase === 0) {
                $data = $this->getRequest()->getPost('shipping', array());
                $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                $saveShippingResult = Mage::helper('aw_onestepcheckout/address')->saveShipping($data, $customerAddressId);
            }
            if (isset($saveShippingResult)) {
                $saveResult = array_merge($saveBillingResult, $saveShippingResult);
            } else {
                $saveResult = $saveBillingResult;
            }

            if (isset($saveResult['error'])) {
                $result['success'] = false;
                if (is_array($saveResult['message'])) {
                    $result['messages'] = array_merge($result['messages'], $saveResult['message']);
                } else {
                    $result['messages'][] = $saveResult['message'];
                }
            }
            $shippingRates = $this->getOnepage()->getQuote()->getShippingAddress()
                ->collectTotals()
                ->collectShippingRates()
                ->getAllShippingRates()
            ;
            //if single shipping rate available then apply it as shipping method
            if (count($shippingRates) == 1) {
                $shippingMethod = $shippingRates[0]->getCode();
                $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $result['blocks'] = $this->getUpdater()->getBlocks();
            $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
        } else {
            $result['success'] = false;
            $result['messages'][] = $this->__('Please specify billing address information.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Shipping method save
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $saveResult = $this->getOnepage()->saveShippingMethod($data);
            Mage::dispatchEvent(
                'checkout_controller_onepage_save_shipping_method',
                array(
                     'request' => $this->getRequest(),
                     'quote'   => $this->getOnepage()->getQuote()
                )
            );
            if (isset($saveResult['error'])) {
                $result['success'] = false;
                $result['messages'][] = $saveResult['message'];
            }
            $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
            $result['blocks'] = $this->getUpdater()->getBlocks();
            $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
        } else {
            $result['success'] = false;
            $result['messages'][] = $this->__('Please specify shipping method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Add Enterprise printed card (Giftwrap)
     */
    public function saveEnterpriseGiftOptionsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'              => true,
            'messages'             => array(),
            'blocks'               => array(),
            'grand_total'          => "",
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method',
                    array(
                        'request' => $this->getRequest(),
                        'quote'   => $this->getOnepage()->getQuote()
                    )
                );

                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
            } catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $this->__('Cannot save Gift Options.');
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Payment method save
     */
    public function savePaymentMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        try{
            if ($this->getRequest()->isPost()) {
                $this->_savePayment();
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
            } else {
                $result['success'] = false;
                $result['messages'][] = $this->__('Please specify payment method.');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['success'] = false;
            $result['error'][] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyCouponAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'        => true,
            'coupon_applied' => false,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            $couponCode = (string) $this->getRequest()->getParam('coupon_code');
            $oldCouponCode = $this->getOnepage()->getQuote()->getCouponCode();
            if (!strlen($couponCode) && !strlen($oldCouponCode)) {
                $result['success'] = false;
            } else {
                try {
                    if (Mage::helper('aw_onestepcheckout/referafriend')->isReferafriendEnabled()) {
                        //store current raf discount
                        $rafPercentDiscount = Mage::helper('aw_onestepcheckout/referafriend')->getPercentDiscountAmount();
                        $rafDiscount = Mage::helper('awraf')->getAppliedAmount();
                        // reset raf discount
                        Mage::helper('awraf')->clearAppliedAmount();
                        Mage::helper('awraf')->setReservedAmount(0);
                    }

                    $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    $this->getOnepage()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                        ->collectTotals()
                        ->save();
                    if ($couponCode == $this->getOnepage()->getQuote()->getCouponCode()) {
                        // apply points
                        if (Mage::helper('aw_onestepcheckout/points')->isPointsEnabled()) {
                            $this->_applyPointsAfterCoupon();
                        }
                        // apply raf
                        if (Mage::helper('aw_onestepcheckout/referafriend')->isReferafriendEnabled()) {
                            $this->_applyAwRafAfterCoupon($rafPercentDiscount, $rafDiscount);
                        }

                        $shippingRates = $this->getOnepage()->getQuote()->getShippingAddress()
                            ->collectTotals()
                            ->collectShippingRates()
                            ->getAllShippingRates()
                        ;
                        //if single shipping rate available then apply it as shipping method
                        if (count($shippingRates) == 1) {
                            $shippingMethod = $shippingRates[0]->getCode();
                            $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
                        }

                        $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                        $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                        $this->getOnepage()->getQuote()->collectTotals()->save();
                        //fix for raf
                        Mage::getSingleton('checkout/session')->getMessages(true);
                        if (strlen($couponCode)) {
                            $result['coupon_applied'] = true;
                            $result['messages'][] = $this->__('Coupon code was applied.');
                        } else {
                            $result['coupon_applied'] = false;
                            $result['messages'][] = $this->__('Coupon code was canceled.');
                        }
                    } else {
                        $result['success'] = false;
                        $result['messages'][] = $this->__('Coupon code is not valid.');
                    }
                    $result['blocks'] = $this->getUpdater()->getBlocks();
                    $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
                } catch (Mage_Core_Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $e->getMessage();
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $this->__('Cannot apply the coupon code.');
                    Mage::logException($e);
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyEnterpriseStorecreditAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'        => true,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                $quote = $this->getOnepage()->getQuote();

                $store = Mage::app()->getStore($quote->getStoreId());
                if (
                    !$quote
                    || !$quote->getCustomerId()
                    || $quote->getBaseGrandTotal() + $quote->getBaseCustomerBalanceAmountUsed() <= 0
                ) {
                    $result['success'] = false;
                }

                $quote->setUseCustomerBalance($this->getUseCustomerBalanceFlagFromRequest());
                if ($quote->getUseCustomerBalance()) {
                    $balance = Mage::getModel('enterprise_customerbalance/balance')
                        ->setCustomerId($quote->getCustomerId())
                        ->setWebsiteId($store->getWebsiteId())
                        ->loadByCustomer();
                    if ($balance) {
                        $quote->setCustomerBalanceInstance($balance);
                        if (!$quote->getPayment()->getMethod()) {
                            $quote->getPayment()->setMethod('free');
                        }
                        $result['messages'][] = $this->__('Store credit was applied.');
                    }
                    else {
                        $quote->setUseCustomerBalance(false);
                        $result['messages'][] = $this->__(
                            'Store Credit payment is not being used in your shopping cart.'
                        );
                    }
                } else {
                    $quote->setUseCustomerBalance(false);
                    $result['messages'][] = $this->__('The store credit payment has been removed from the order.');
                }

                $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $this->getOnepage()->getQuote()->collectTotals()->save();

                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
            } catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $this->__('Cannot apply the Store Credit.');
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyStorecreditAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'        => true,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                $quote = $this->getOnepage()->getQuote();

                if (
                    !$quote
                    || !$quote->getCustomerId()
                    || $quote->getBaseGrandTotal() + $quote->getBaseAwStorecreditAmountUsed() <= 0
                ) {
                    $result['success'] = false;
                }

                $quote->setUseStorecredit($this->getUseStorecreditFlagFromRequest());
                if ($quote->getUseStorecredit()) {
                    $storeCredit = Mage::getModel('aw_storecredit/storecredit')
                        ->loadByCustomerId($quote->getCustomerId());
                    if ($storeCredit) {
                        $quote->setStorecreditInstance($storeCredit);
                        if (!$quote->getPayment()->getMethod()) {
                            $quote->getPayment()->setMethod('free');
                        }
                        if (count(Mage::helper('aw_storecredit/totals')->getQuoteStoreCredit($quote->getId())) == 0) {
                            Mage::helper('aw_storecredit/totals')->addStoreCreditToQuote($storeCredit);
                        }
                        $result['messages'][] = $this->__('Store credit was applied.');
                    }
                    else {
                        $quote->setUseStorecredit(false);
                        $result['messages'][] = $this->__(
                            'Store Credit payment is not being used in your shopping cart.'
                        );
                    }
                } else {
                    if (count(Mage::helper('aw_storecredit/totals')->getQuoteStoreCredit($quote->getId())) >= 1) {
                        $storeCredit = Mage::getModel('aw_storecredit/storecredit')->loadByCustomerId($quote->getCustomerId());
                        if ($storeCredit) {
                            $quote->setStorecreditInstance(null);
                            Mage::helper('aw_storecredit/totals')->removeStoreCreditFromQuote($storeCredit->getId());
                        }
                    }
                    $quote->setUseStorecredit(false);
                    $result['messages'][] = $this->__('The store credit payment has been removed from the order.');
                }

                $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $this->getOnepage()->getQuote()->collectTotals()->save();

                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
            } catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $this->__('Cannot apply the Store Credit.');
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function removeStorecreditAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'        => false,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        $storecreditId = $this->getRequest()->getParam('awstorecredit_code', null);
        if (isset($storecreditId)) {
            try {
                $storecreditId = base64_decode($storecreditId);

                Mage::helper('aw_storecredit/totals')->removeStoreCreditFromQuote(trim($storecreditId));
                $result['messages'][] = Mage::helper('aw_storecredit')->__('Store Credit has been removed.');
                $result['success'] = true;

                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal(
                    $this->getOnepage()->getQuote())
                ;
            } catch (Mage_Core_Exception $e) {
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['messages'][] = Mage::helper('aw_storecredit')->__('Cannot remove gift card.');
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyEnterprisePointsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'        => true,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                $quote = $this->getOnepage()->getQuote();
                if (
                    !$quote
                    || !$quote->getCustomerId()
                    || $quote->getBaseGrandTotal() + $quote->getBaseRewardCurrencyAmount() <= 0
                ) {
                    $result['success'] = false;
                }

                $quote->setUseRewardPoints($this->getUseRewardPointsFlagFromRequest());
                if ($quote->getUseRewardPoints()) {
                    /* @var $reward Enterprise_Reward_Model_Reward */
                    $reward = Mage::getModel('enterprise_reward/reward')
                        ->setCustomer($quote->getCustomer())
                        ->setWebsiteId($quote->getStore()->getWebsiteId())
                        ->loadByCustomer()
                    ;

                    $minPointsBalance = (int)Mage::getStoreConfig(
                        Enterprise_Reward_Model_Reward::XML_PATH_MIN_POINTS_BALANCE,
                        $quote->getStoreId()
                    );

                    if ($reward->getId() && $reward->getPointsBalance() >= $minPointsBalance) {
                        $quote->setRewardInstance($reward);
                        if (!$quote->getPayment()->getMethod()) {
                            $quote->getPayment()->setMethod('free');
                        }
                        $result['messages'][] = $this->__('Reward points was applied.');
                    } else {
                        $quote->setUseRewardPoints(false);
                        $result['messages'][] = $this->__('Reward points will not be used in this order.');
                    }
                } else {
                    $quote->setUseRewardPoints(false);
                    $result['messages'][] = $this->__('The reward points have been removed from the order.');
                }

                $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $this->getOnepage()->getQuote()->collectTotals()->save();

                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
            } catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success'] = false;
                $result['messages'][] = $this->__('Cannot apply the %s.', Mage::helper('aw_onestepcheckout/enterprise_points')->getPointsUnitName());
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyPointsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'        => true,
            'points_applied' => false,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            $pointsAmount = (int)$this->getRequest()->getParam('points_amount');
            $oldPointsCode = Mage::getSingleton('checkout/session')->getData('points_amount');
            if (!strlen($pointsAmount) && !strlen($oldPointsCode)) {
                $result['success'] = false;
            } else {
                try {
                    if ($pointsAmount) {
                        Mage::getSingleton('checkout/session')->setData('use_points', 1);
                        Mage::getSingleton('checkout/session')->setData('points_amount', $pointsAmount);
                        $result['points_applied'] = true;
                        $result['messages'][] = $this->__('%s %s was applied.', $pointsAmount, Mage::helper('aw_onestepcheckout/points')->getPointsUnitName());
                    } else {
                        Mage::getSingleton('checkout/session')->setData('use_points', null);
                        Mage::getSingleton('checkout/session')->setData('points_amount', null);
                        $result['points_applied'] = false;
                        $result['messages'][] = $this->__('%s was canceled.', Mage::helper('aw_onestepcheckout/points')->getPointsUnitName());
                    }

                    if (Mage::helper('aw_onestepcheckout/referafriend')->isReferafriendEnabled()) {
                        //store current raf discount
                        $rafPercentDiscount = Mage::helper('aw_onestepcheckout/referafriend')->getPercentDiscountAmount();
                        $rafDiscount = Mage::helper('awraf')->getAppliedAmount();
                        // reset raf discount
                        Mage::helper('awraf')->clearAppliedAmount();
                        Mage::helper('awraf')->setReservedAmount(0);
                    }
                    $this->getOnepage()->getQuote()->collectTotals()->save();
                    // apply raf
                    if (Mage::helper('aw_onestepcheckout/referafriend')->isReferafriendEnabled()) {
                        $this->_applyAwRafAfterCoupon($rafPercentDiscount, $rafDiscount);
                    }
                    $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                    $this->getOnepage()->getQuote()->collectTotals()->save();
                    //fix for raf
                    Mage::getSingleton('checkout/session')->getMessages(true);

                    $result['blocks'] = $this->getUpdater()->getBlocks();
                    $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
                } catch (Mage_Core_Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $e->getMessage();
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $this->__('Cannot apply the %s.', Mage::helper('aw_onestepcheckout/points')->getPointsUnitName());
                    Mage::logException($e);
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyRafDiscountAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'          => true,
            'discount_applied' => false,
            'messages'         => array(),
            'blocks'           => array(),
            'grand_total'      => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            $rafDiscountAmount = (float)$this->getRequest()->getParam('raf_discount_amount');
            $rafDiscountAmount = $rafDiscountAmount/Mage::app()->getStore()->getCurrentCurrencyRate();
            $oldRafDiscountAmount = Mage::getSingleton('checkout/session')->getData('raf_discount_amount');
            if (!$rafDiscountAmount && !$oldRafDiscountAmount) {
                $result['success'] = false;
            } else {
                try {
                    if ($rafDiscountAmount > Mage::helper('aw_onestepcheckout/referafriend')->getMaxDiscount()) {
                        $rafDiscountAmount = Mage::helper('aw_onestepcheckout/referafriend')->getMaxDiscount();
                    }
                    if ($rafDiscountAmount) {
                        Mage::helper('awraf')->clearAppliedAmount();
                        Mage::helper('awraf')->setReservedAmount($rafDiscountAmount);
                        $result['discount_applied'] = true;
                        $discountAmountAsPrice = Mage::helper('core')->currency($rafDiscountAmount, true, false);
                        $result['messages'][] = $this->__('%s discount was applied.', $discountAmountAsPrice);
                    } else {
                        Mage::helper('awraf')->clearAppliedAmount();
                        Mage::helper('awraf')->setReservedAmount(0);
                        $result['discount_applied'] = false;
                        $result['messages'][] = $this->__('Discount was canceled.');
                    }
                    $this->getOnepage()->getQuote()->collectTotals()->save();
                    //fix for raf
                    Mage::getSingleton('checkout/session')->getMessages(true);

                    $result['blocks'] = $this->getUpdater()->getBlocks();
                    $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
                } catch (Mage_Core_Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $e->getMessage();
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = $this->__('Cannot apply the discount.');
                    Mage::logException($e);
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function placeOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'  => true,
            'messages' => array(),
        );
        try {
            //TODO: re-factoring. Move to helpers
            if ($this->getRequest()->isPost()) {
                $billingData = $this->getRequest()->getPost('billing', array());
                // save checkout method
                if (!$this->getOnepage()->getCustomerSession()->isLoggedIn()) {
                    if (isset($billingData['create_account'])) {
                        $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
                    } else {
                        $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
                    }
                }

                if (!$this->getOnepage()->getQuote()->getCustomerId() &&
                    Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER == $this->getOnepage()->getQuote()->getCheckoutMethod()
                ) {
                    if ($this->_customerEmailExists($billingData['email'], Mage::app()->getWebsite()->getId())) {
                        $result['success']    = false;
                        $result['messages'][] = $this->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
                    }
                }

                if ($result['success']) {
                    // save billing address
                    $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
                    if (isset($billingData['email'])) {
                        $billingData['email'] = trim($billingData['email']);
                    }
                    $saveBillingResult = $this->getOnepage()->saveBilling($billingData, $customerAddressId);

                    //save shipping address
                    if (!isset($billingData['use_for_shipping'])) {
                        $shippingData = $this->getRequest()->getPost('shipping', array());
                        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                        $saveShippingResult = $this->getOnepage()->saveShipping($shippingData, $customerAddressId);
                    }

                    // check errors
                    if (isset($saveShippingResult)) {
                        $saveResult = array_merge($saveBillingResult, $saveShippingResult);
                    } else {
                        $saveResult = $saveBillingResult;
                    }

                    //for compatibility with gift options when shipping method is single (not applying)
                    Mage::dispatchEvent(
                        'checkout_controller_onepage_save_shipping_method',
                        array(
                             'request' => $this->getRequest(),
                             'quote'   => $this->getOnepage()->getQuote()
                        )
                    );

                    //save payment method
                    $this->_savePayment();

                    if (isset($saveResult['error'])) {
                        $result['success'] = false;
                        if (!is_array($saveResult['message'])) {
                            $saveResult['message'] = array($saveResult['message']);
                        }
                        $result['messages'] = array_merge($result['messages'], $saveResult['message']);
                    } else {
                        // check delivery Date
                        $deliveryDate = $this->getRequest()->getPost('aw_deliverydate_date', null);
                        $deliveryNotice = $this->getRequest()->getPost('comments', null);
                        $deliverySaveResult = Mage::helper('aw_onestepcheckout/ddan')->applyDeliveryData($deliveryDate, $deliveryNotice);
                        if (isset($deliverySaveResult['error'])) {
                            $result['success'] = false;
                            $result['messages'] = $deliverySaveResult['message'];
                        } else {
                            // check agreements
                            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
                            $postedAgreements = array_keys($this->getRequest()->getPost('aw_osc_agreement', array()));
                            if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                                $result['success'] = false;
                                $result['messages'][] = $this->__('Please agree to all the terms and conditions before placing the order.');
                            } else {
                                //store p&r data for compatibility with paypal express checkout. See OSC-104
                                $usePoints = Mage::getSingleton('checkout/session')->getData('use_points');
                                $pointsAmount = Mage::getSingleton('checkout/session')->getData('points_amount');

                                if ($data = $this->getRequest()->getPost('payment', false)) {
                                    $this->getOnepage()->getQuote()->getPayment()->importData($data);
                                }
                                //reset p&r data for compatibility with paypal express checkout. See OSC-104
                                Mage::getSingleton('checkout/session')->setData('use_points', $usePoints);
                                Mage::getSingleton('checkout/session')->setData('points_amount', $pointsAmount);

                                //save data for use after order save
                                $data = array(
                                    'comments' => $this->getRequest()->getPost('comments', false),
                                    'is_subscribed' => $this->getRequest()->getPost('is_subscribed', false),
                                    'billing' => $this->getRequest()->getPost('billing', array()),
                                    'segments_select' => $this->getRequest()->getPost('segments_select', array())
                                );
                                Mage::getSingleton('checkout/session')->setData('aw_onestepcheckout_order_data', $data);

                                /* Compatibility for Mage_Authorizenet DPM */
                                if (@class_exists('Mage_Authorizenet_Model_Directpost_Session')) {
                                    Mage::getSingleton('authorizenet/directpost_session')->setQuoteId(
                                        $this->getOnepage()->getQuote()->getId()
                                    );
                                }

                                /* Compatibility for Ebizmarts_SagePaySuite */
                                $paymentHelper = Mage::helper('aw_onestepcheckout/payment');
                                $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
                                if ($paymentHelper->isEbizmartsSagePaySuiteMethod($paymentMethod)) {
                                    $redirectUrl = $this->_processEbizmartsSagePaySuite();
                                } else {
                                    $redirectUrl = $this
                                        ->getOnepage()
                                        ->getQuote()
                                        ->getPayment()
                                        ->getCheckoutRedirectUrl()
                                    ;
                                    if (!$redirectUrl) {
                                        $this->getOnepage()->saveOrder();

                                        /* Compatibility for Mage_Authorizenet DPM */
                                        if ($paymentMethod == 'authorizenet_directpost') {
                                            $dpm = Mage::helper('aw_onestepcheckout/payment_authorizenet_directpost');
                                            $dpmError = $dpm->process(
                                                $this->getRequest()->getPost('payment', false)
                                            );
                                            if ($dpmError) {
                                                throw new Exception($dpmError);
                                            }
                                        }
                                        $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $result['success'] = false;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']    = false;
            $result['messages'][] = $this->__('There was an error processing your order. Please contact us or try again later.');
            $result['messages'][] = $e->getMessage();
        }
        if ($result['success']) {
            $this->getOnepage()->getQuote()->save();
            if (isset($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _savePayment()
    {
        $data = $this->getRequest()->getPost('payment', array());
        if (!empty($data)) {
            // hack for AW_Points compatibility
            $session = Mage::getSingleton('checkout/session');
            $data['use_points'] = $session->getData('use_points');
            $data['points_amount'] = $session->getData('points_amount');
            $saveResult = $this->getOnepage()->savePayment($data);
            if (isset($saveResult['error'])) {
                throw new Exception($saveResult['message']);
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
        }
        return $this;
    }

    /**
     *
     */
    public function addProductToWishlistAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success' => true,
            'messages' => array()
        );
        $customerSession = Mage::getSingleton('customer/session');
        $wishlistSession = Mage::getSingleton('wishlist/session');
        $response = clone $this->getResponse();
        $wishlistControllerInstance = $this->_getCustomerWishlistController($this->getRequest(), $response);
        if (!is_null($wishlistControllerInstance) && method_exists($wishlistControllerInstance, 'addAction')) {
            $wishlistControllerInstance->addAction();
            $wishlistMessagesCollection = $wishlistSession->getMessages(true);
            $customerMessageCollection = $customerSession->getMessages(true);
            $successMessages = array_merge(
                $wishlistMessagesCollection->getItemsByType(Mage_Core_Model_Message::SUCCESS),
                $customerMessageCollection->getItemsByType(Mage_Core_Model_Message::SUCCESS)
            );
            if (count($successMessages) === 0) {
                //if something wrong
                $result['success'] = false;
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                if (!is_null($product->getId())) {
                    $referer = $product->getUrlModel()->getUrl($product, array());
                    $result['messages'][] =
                        $this->__(
                            'Product "%1$s" has not been added. Please add it <a href="%2$s">from product page</a>',
                            $product->getName(),
                            $referer
                        );
                }
            } else {
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                if (!is_null($product->getId())) {
                    $result['messages'][] =
                        $this->__(
                            'Product "%1$s" was successfully added to wishlist',
                            $product->getName()
                        );
                } else {
                    $result['messages'][] = $this->__('Product was successfully added to wishlist');
                }
            }
        } else {
            $result['success'] = false;
            $result['messages'][] = $this->__("Oops something's wrong");
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     *
     */
    public function addProductToCompareListAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success' => true,
            'messages' => array()
        );
        $catalogSession = Mage::getSingleton('catalog/session');
        $response = clone $this->getResponse();
        $productCompareControllerInstance = $this->_getProductCompareController($this->getRequest(), $response);
        if (!is_null($productCompareControllerInstance) && method_exists($productCompareControllerInstance, 'addAction')) {
            $productCompareControllerInstance->addAction();
            $messageCollection = $catalogSession->getMessages(true);
            $successMessages = $messageCollection->getItemsByType(Mage_Core_Model_Message::SUCCESS);
            if (count($successMessages) === 0) {
                //if something wrong
                $result['success'] = false;
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                if (!is_null($product->getId())) {
                    $referer = $product->getUrlModel()->getUrl($product, array());
                    $result['messages'][] =
                        $this->__(
                            'Product "%1$s" has not been added. Please add it <a href="%2$s">from product page</a>',
                            $product->getName(),
                            $referer
                        );
                }
            } else {
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                if (!is_null($product->getId())) {
                    $result['messages'][] =
                        $this->__(
                            'Product "%1$s" was successfully added to compare list',
                            $product->getName()
                        );
                } else {
                    $result['messages'][] = $this->__('Product was successfully added to compare list');
                }
            }
        } else {
            $result['success'] = false;
            $result['messages'][] = $this->__("Oops something's wrong");
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _getEmptyResponseArrayForGiftCards()
    {
        return array(
            'success'       => false,
            'messages'      => array(),
            'blocks'        => array(),
            'grand_total'   => '',
        );
    }

    /**
     * @return string|null
     */
    protected function _getAWGiftCardFromRequest($decodeGiftCardCode = false)
    {
        /* Break function if code isn't set or can't be decoded */
        if ((!$giftCardCode = $this->getRequest()->getParam('aw_giftcard_code'))
            || ($decodeGiftCardCode && (!$giftCardCode = base64_decode($giftCardCode)))
        ) {
            return;
        }
        return trim($giftCardCode);
    }

    public function applyAWGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = $this->_getEmptyResponseArrayForGiftCards();
        try {
            if (!$giftCardCode = $this->_getAWGiftCardFromRequest()) {
                throw new Exception($this->__('Gift Card is not valid.'));
            }

            Mage::helper('aw_onestepcheckout/awgiftcard')->applyGiftcard($giftCardCode);

            $result['success'] = true;
            $result['messages'][] = $this->__(
                'Gift Card "%s" has been added.',
                Mage::helper('core')->escapeHtml($giftCardCode)
            );
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $result['blocks'] = $this->getUpdater()->getBlocks();
            $result['grand_total'] = Mage::helper('aw_onestepcheckout')
                ->getGrandTotal($this->getOnepage()->getQuote());
        } catch (Mage_Core_Exception $e) {
            $result['messages'][] = $e->getMessage();
        } catch (Exception $e) {
            $result['messages'][] = $e->getMessage();
            $result['messages'][] = Mage::helper('core')->__('Cannot apply gift card.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function checkAWGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = $this->_getEmptyResponseArrayForGiftCards();
        try {
            if (!$giftCardCode = $this->_getAWGiftCardFromRequest()) {
                throw new Exception($this->__('Gift Card is not valid.'));
            }

            $coreHelper = Mage::helper('core');
            $gcStatus = Mage::helper('aw_onestepcheckout/awgiftcard')->checkGiftcard($giftCardCode);

            $result['success'] = true;

            if ($gcStatus['code']) {
                $result['messages'][] = $this->__('Code: %s', $coreHelper->escapeHtml($gcStatus['code']));
            }
            if ($gcStatus['status']) {
                $result['messages'][] = $this->__($gcStatus['status']['label'], $coreHelper->escapeHtml($gcStatus['status']['value']));
            }
            if ($gcStatus['state']) {
                $result['messages'][] = $this->__($gcStatus['state']['label'], $coreHelper->escapeHtml($gcStatus['state']['value']));
            }
            if ($gcStatus['balance']) {
                $result['messages'][] = $this->__('Balance: %s', $gcStatus['balance']);
            }
            if ($gcStatus['expires']) {
                $result['messages'][] = $this->__(
                    'Expires: "%s"',
                    $coreHelper->escapeHtml($coreHelper->formatDate($gcStatus['expires'], 'short'))
                );
            }
        } catch (Mage_Core_Exception $e) {
            $result['messages'][] = $e->getMessage();
        } catch (Exception $e) {
            $result['messages'][] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function removeAWGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = $this->_getEmptyResponseArrayForGiftCards();
        try {
            if (!$giftCardCode = $this->_getAWGiftCardFromRequest(true)) {
                throw new Exception($this->__('Gift Card is not valid.'));
            }

            Mage::helper('aw_onestepcheckout/awgiftcard')->removeGiftcard($giftCardCode);

            $this->getOnepage()->getQuote()->collectTotals()->save();
            $result['messages'][] = $this->__(
                'Gift Card "%s" was removed.',
                Mage::helper('core')->escapeHtml($giftCardCode)
            );
            $result['blocks'] = $this->getUpdater()->getBlocks();
            $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote());
            $result['success'] = true;
        } catch (Mage_Core_Exception $e) {
            $result['messages'][] = $e->getMessage();
        } catch (Exception $e) {
            $result['messages'][] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyEnterpriseGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = $this->_getEmptyResponseArrayForGiftCards();
        $giftcardCode = (string) $this->getRequest()->getParam('enterprise_giftcard_code');
        if (isset($giftcardCode)
            || !(strlen($giftcardCode) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH)
        ) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($giftcardCode)
                    ->addToCart()
                ;
                $result['success'] = true;
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Gift Card "%s" was added.',
                    Mage::helper('core')->escapeHtml($giftcardCode))
                ;
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal(
                    $this->getOnepage()->getQuote())
                ;
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent(
                    'enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $giftcardCode)
                );
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Cannot apply gift card.');
            }
        } else {
            $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function removeEnterpriseGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = $this->_getEmptyResponseArrayForGiftCards();
        $giftcardCode = (string) $this->getRequest()->getParam('enterprise_giftcard_code');
        if (isset($giftcardCode)
            || !(strlen($giftcardCode) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH)
        ) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($giftcardCode)
                    ->removeFromCart()
                ;
                $result['success'] = true;
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Gift Card "%s" was removed.',
                    Mage::helper('core')->escapeHtml($giftcardCode))
                ;
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getUpdater()->getBlocks();
                $result['grand_total'] = Mage::helper('aw_onestepcheckout')->getGrandTotal(
                    $this->getOnepage()->getQuote())
                ;
            } catch (Mage_Core_Exception $e) {
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Cannot remove gift card.');
            }
        } else {
            $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateBlocksAfterACPAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationEnabled() && !$this->_validateFormKey()) {
            return;
        }

        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => $this->getUpdater()->getBlocks(),
            'can_ship'    => !$this->getOnepage()->getQuote()->isVirtual(),
            'grand_total' => Mage::helper('aw_onestepcheckout')->getGrandTotal($this->getOnepage()->getQuote())
        );
        switch($this->getRequest()->getParam('action', 'add')) {
            case 'add':
                $result['messages'][] = $this->__('Product was successfully added to the cart');
                break;
            case 'remove':
                $result['messages'][] = $this->__('Product was successfully remove from the cart');
                break;
            default:
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return AW_Onestepcheckout_Model_Updater
     */
    public function getUpdater()
    {
        return Mage::getSingleton('aw_onestepcheckout/updater');
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        //TODO: show login block only for unregistered
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
            || !Mage::helper('checkout')->isCustomerMustBeLogged();
    }

    /**
     * @return AW_Onestepcheckout_AjaxController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        return false;
    }

    /**
     * helper
     * @return null|Mage_Core_Controller_Front_Action
     */
    private function _getFBIntegratorController($request, $response)
    {
        $fbIntegratorModuleName = 'AW_FBIntegrator';
        $controllerName = 'facebook';

        return $this->_createController($fbIntegratorModuleName, $controllerName, $request, $response);
    }

    /**
     * helper
     * @return null|Mage_Core_Controller_Front_Action
     */
    private function _getCustomerWishlistController($request, $response)
    {
        $fbIntegratorModuleName = 'Mage_Wishlist';
        $controllerName = 'index';

        return $this->_createController($fbIntegratorModuleName, $controllerName, $request, $response);
    }

    /**
     * helper
     * @return null|Mage_Core_Controller_Front_Action
     */
    private function _getProductCompareController($request, $response)
    {
        $fbIntegratorModuleName = 'Mage_Catalog';
        $controllerName = 'product_compare';

        return $this->_createController($fbIntegratorModuleName, $controllerName, $request, $response);
    }

    /**
     * helper
     * @param $moduleName
     * @param $controllerName
     * @param $request
     * @param $response
     *
     * @return Mage_Core_Controller_Front_Action|null
     */
    private function _createController($moduleName, $controllerName, $request, $response)
    {
        $router = Mage::app()->getFrontController()->getRouter('standard');
        $controllerFileName = $router->getControllerFileName($moduleName, $controllerName);
        if (!$router->validateControllerFileName($controllerFileName)) {
            return null;
        }
        $controllerClassName = $router->getControllerClassName($moduleName, $controllerName);
        if (!$controllerClassName) {
            return null;
        }

        if (!class_exists($controllerClassName, false)) {
            if (!file_exists($controllerFileName)) {
                return null;
            }
            include $controllerFileName;

            if (!class_exists($controllerClassName, false)) {
                return null;
            }
        }
        $controllerInstance = Mage::getControllerInstance(
            $controllerClassName,
            $request,
            $response
        );
        return $controllerInstance;
    }

    private function _applyPointsAfterCoupon()
    {
        $pointsAmount = Mage::getSingleton('checkout/session')->getData('points_amount');
        $spentRateModel = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
        if (!$spentRateModel->getId()) {
            return $this;
        }
        $moneyForPoints = $spentRateModel->exchange($pointsAmount);
        $subtotalWithDiscount = $this->getOnepage()->getQuote()->getSubtotalWithDiscount();
        if ($subtotalWithDiscount < $moneyForPoints) {
            $earnRateModel = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS);
            if (!$earnRateModel->getId()) {
                return $this;
            }
            $newPointsValue = $earnRateModel->exchange($subtotalWithDiscount);
            Mage::getSingleton('checkout/session')->setData('points_amount', $newPointsValue);
        }
        return $this;
    }

    private function _applyAwRafAfterCoupon($rafPercentDiscount, $rafDiscount)
    {
        //START destroy total collector for raf compatibility
        $reqisterKey = '_singleton/sales/quote_address_total_collector';
        if (Mage::registry($reqisterKey)) {
            Mage::unregister($reqisterKey);
        }
        $reqisterKey = '_singleton/checkout/session';
        if (Mage::registry($reqisterKey)) {
            Mage::unregister($reqisterKey);
        }
        $reqisterKey = '_singleton/checkout/type_onepage';
        if (Mage::registry($reqisterKey)) {
            Mage::unregister($reqisterKey);
        }
        //END destroy totals

        $subtotalWithDiscount = $this->getOnepage()->getQuote()->getSubtotalWithDiscount();
        $subtotalWithDiscount -= $rafPercentDiscount;
        if (Mage::helper('aw_onestepcheckout/points')->isPointsEnabled()) {
            $pointsAmount = Mage::getSingleton('checkout/session')->getData('points_amount');
            $spentRateModel = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
            if ($spentRateModel->getId()) {
                $moneyForPoints = $spentRateModel->exchange($pointsAmount);
                $subtotalWithDiscount -= $moneyForPoints;
            }
        }
        Mage::helper('awraf')->clearAppliedAmount();
        if ($subtotalWithDiscount < $rafDiscount) {
            Mage::helper('awraf')->setReservedAmount($subtotalWithDiscount);
        } else {
            Mage::helper('awraf')->setReservedAmount($rafDiscount);
        }
        return $this;
    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int $websiteId
     * @return false|Mage_Customer_Model_Customer
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    protected function _processEbizmartsSagePaySuite()
    {
        switch ($this->getOnepage()->getQuote()->getPayment()->getMethod()) {
            case 'sagepaypaypal':
                return Mage::getModel('core/url')->addSessionParam()->getUrl('sgps/paypalexpress/go', array('_secure' => true));
            case 'sagepayserver':
                $this->_forward('saveOrder', 'serverPayment', 'sgps', $this->getRequest()->getParams());
                break;
            case 'sagepaydirectpro':
                $this->_forward('saveOrder', 'directPayment', 'sgps', $this->getRequest()->getParams());
                break;
            case 'sagepayform':
                $this->_forward('saveOrder', 'formPayment', 'sgps', $this->getRequest()->getParams());
                break;
            default:
                return null;
        }
    }

    private function isFormkeyValidationEnabled()
    {
        return Mage::helper('aw_onestepcheckout/config')->isNeedToValidateFormkey();
    }

    /**
     * @return bool
     */
    private function getUseStorecreditFlagFromRequest()
    {
        return (bool)$this->getPaymentDataFieldFromRequest('use_storecredit');
    }

    /**
     * @return bool
     */
    private function getUseCustomerBalanceFlagFromRequest()
    {
        return (bool)$this->getPaymentDataFieldFromRequest('use_customer_balance');
    }

    /**
     * @return bool
     */
    private function getUseRewardPointsFlagFromRequest()
    {
        return (bool)$this->getPaymentDataFieldFromRequest('use_reward_points');
    }

    private function getPaymentDataFieldFromRequest($fieldName)
    {
        $fieldValue = null;
        $paymentData = $this->getRequest()->getParam('payment', array());
        if (isset($paymentData[$fieldName])) {
            $fieldValue = $paymentData[$fieldName];
        }
        return $fieldValue;
    }
}