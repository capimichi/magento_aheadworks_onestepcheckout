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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Newsletter_Simple extends Mage_Core_Block_Template
{
    protected $_customer = null;
    protected $_subscriptionObject = null;

    public function canShow()
    {
        if (!Mage::helper('aw_onestepcheckout/newsletter')->isMageNewsletterEnabled()) {
            return false;
        }
        if ($this->isSubscribed()) {
            return false;
        }
        return true;
    }

    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

    public function getSubscriptionObject()
    {
        if (is_null($this->_subscriptionObject)) {
            $this->_subscriptionObject = Mage::getModel('newsletter/subscriber');
            if ($this->getCustomer()->getId()) {
                $this->_subscriptionObject->loadByCustomer($this->getCustomer());
            }
        }
        return $this->_subscriptionObject;
    }

    public function isSubscribed()
    {
        if (!is_null($this->getSubscriptionObject())) {
            return $this->getSubscriptionObject()->isSubscribed();
        }
        return false;
    }

    public function getIsSubscribed()
    {
        $data = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        if (isset($data['is_subscribed'])) {
            return $data['is_subscribed'];
        }
        return false;
    }

    public function getSaveFormValuesUrl()
    {
        return Mage::getUrl('aw_onestepcheckout/ajax/saveFormValues');
    }
}