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


class AW_Onestepcheckout_Model_Observer
{
    public function controllerActionPredispatchCheckout($observer)
    {
        $controllerInstance = $observer->getControllerAction();
        //for compatibility with AW_Mobile
        if ((Mage::helper('core')->isModuleEnabled('AW_Mobile') &&
            !Mage::helper('awmobile')->getDisabledOutput() &&
            (
                Mage::getSingleton('customer/session')->getShowDesktop() === false ||
                Mage::helper('awmobile')->getTargetPlatform() == AW_Mobile_Model_Observer::TARGET_MOBILE
            )) ||
            (Mage::helper('core')->isModuleEnabled('AW_Mobile2') &&
                (Mage::helper('aw_mobile2')->isIphoneTheme() || Mage::helper('aw_mobile2')->isIpadTheme())
            ) ||
            (
                Mage::helper('core')->isModuleEnabled('AW_Mobile3') && Mage::helper('aw_mobile3')->isCanShowMobileVersion()
            )
        ) {
            //no redirect if in mobile theme
            return;
        }
        if (
            $controllerInstance instanceof Mage_Checkout_OnepageController &&
            $controllerInstance->getRequest()->getActionName() !== 'success' &&
            $controllerInstance->getRequest()->getActionName() !== 'failure' &&
            $controllerInstance->getRequest()->getActionName() !== 'saveOrder' &&
            Mage::helper('aw_onestepcheckout/config')->isEnabled()
        ) {
            $controllerInstance->getResponse()->setRedirect(
                Mage::getUrl('onestepcheckout/index', array('_secure'=>true))
            );
            $controllerInstance->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * @param $observer
     * submit order after
     */
    public function checkoutSubmitAllAfter($observer)
    {
        $oscOrderData = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_order_data');
        if (!is_array($oscOrderData)) {
            $oscOrderData = array();
        }
        // add customer comment
        if (!Mage::helper('aw_onestepcheckout/ddan')->isDDANEnabled()) {
            if (array_key_exists('comments', $oscOrderData)) {
                $comment = $oscOrderData['comments'];
                if ($lastOrderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId()) {
                    $order = Mage::getModel('sales/order')->load($lastOrderId);
                    $order
                        ->addStatusHistoryComment(Mage::helper('aw_onestepcheckout')->__('Comment by customer: %s', $comment))
                        ->setIsVisibleOnFront(true)
                        ->save()
                    ;
                }
            }
        }

        // subscribe to newsletter
        if (array_key_exists('is_subscribed', $oscOrderData) && $oscOrderData['is_subscribed']) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getId()) {
                $data = array(
                    'email'       => $customer->getEmail(),
                    'first_name'  => $customer->getFirstname(),
                    'last_name'   => $customer->getLastname(),
                    'customer_id' => $customer->getId(),
                );
            } else {
                $billing = $oscOrderData['billing'];
                $data = array(
                    'email'      => $billing['email'],
                    'first_name' => $billing['firstname'],
                    'last_name'  => $billing['lastname'],
                );
            }
            if (array_key_exists('segments_select', $oscOrderData)) {
                $data['segments_codes'] = $oscOrderData['segments_select'];
            }
            $data['store_id'] = Mage::app()->getStore()->getId();
            Mage::helper('aw_onestepcheckout/newsletter')->subscribeCustomer($data);
        }

        //clear saved values
        Mage::getSingleton('checkout/session')->setData('aw_onestepcheckout_form_values', array());
        Mage::getSingleton('checkout/session')->setData('aw_onestepcheckout_order_data', array());
    }

    /**
     * Compatibility with Paypal Hosted Pro
     * @param $observer
     */
    public function controllerActionPostdispatchOnestepcheckoutAjaxPlaceOrder($observer)
    {
        $paypalObserver = Mage::getModel('paypal/observer');
        if (!method_exists($paypalObserver, 'setResponseAfterSaveOrder')) {
            return $this;
        }
        $controllerAction = $observer->getEvent()->getControllerAction();
        $result = Mage::helper('core')->jsonDecode(
            $controllerAction->getResponse()->getBody(),
            Zend_Json::TYPE_ARRAY
        );
        if ($result['success']) {
            $paypalObserver->setResponseAfterSaveOrder($observer);
            $result = Mage::helper('core')->jsonDecode(
                $controllerAction->getResponse()->getBody(),
                Zend_Json::TYPE_ARRAY
            );
            $result['is_hosted_pro'] = true;
            $controllerAction->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Change template for Authorize.net Direct Post (DPM)
     * @param $observer
     */
    public function coreLayoutBlockCreateAfterOnestepcheckout($observer)
    {
        if (Mage::app()->getRequest()->getControllerModule() !== 'AW_Onestepcheckout') {
            return $observer;
        }

        $block = $observer->getBlock();
        if ($block instanceof Mage_Authorizenet_Block_Directpost_Form) {
            $block->setTemplate('aw_onestepcheckout/onestep/form/payment/authorizenet/directpost.phtml');
        }
    }

    /**
     * Fix for bug in Mage_Core_Model_Layout_Update::getFileLayoutUpdatesXml
     *
     * @param $observer
     *
     * @todo Move to AW_Lib
     */
    public function coreLayoutUpdateUpdatesGetAfter($observer)
    {
        /* @var Mage_Core_Model_Config_Element $updateRoot */
        $updateRoot = $observer->getUpdates();
        $nodeListToRemove = array();
        foreach ($updateRoot->children() as $updateKey => $updateNode) {
            if ($updateNode->file) {
                if (strpos($updateKey, 'aw_onestepcheckout') !== false) {
                    $module = $updateNode->getAttribute('module');
                    if ($module) {
                        if (!Mage::helper('core')->isModuleOutputEnabled($module)) {
                            $nodeListToRemove[] = $updateKey;
                        } elseif ($module == 'Braintree_Payments') {
                            $moduleVersion = Mage::getConfig()->getModuleConfig($module)->version;
                            if (version_compare($moduleVersion, '2.0.0', '>=')) {
                                $nodeListToRemove[] = 'aw_onestepcheckout_braintree_payments';
                            } else {
                                $nodeListToRemove[] = 'aw_onestepcheckout_braintree_payments_v2';
                            }
                        }
                    }
                }
            }
        }

        foreach ($nodeListToRemove as $nodeKey) {
            unset($updateRoot->$nodeKey);
        }
    }
}