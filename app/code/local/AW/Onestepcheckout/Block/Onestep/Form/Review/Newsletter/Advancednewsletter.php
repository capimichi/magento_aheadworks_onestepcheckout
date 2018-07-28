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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Newsletter_Advancednewsletter extends AW_Advancednewsletter_Block_Checkout_Subscribe
{
    protected $_customer = null;
    protected $_segmentsOfCustomer = null;

    public function canShow()
    {
        if (!Mage::helper('aw_onestepcheckout/newsletter')->isAdvancedNewsletterEnabled()) {
            return false;
        }
        if ($this->isSubscribedOnAllSegments()) {
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

    public function isSubscribedOnAllSegments()
    {
        $isSubscribedOnAllSegments = true;
        foreach ($this->getSegmentsOnCheckout() as $segment) {
            if (!$this->isCustomerSubscribedOnSegment($segment)) {
                $isSubscribedOnAllSegments = false;
            }
        }
        return $isSubscribedOnAllSegments;
    }

    public function isCustomerSubscribedOnSegment($segment)
    {
        if (is_null($this->_segmentsOfCustomer)) {
            $this->_segmentsOfCustomer = $this->getSegmentsCodesByEmail($this->getStoredEmail());
        }
        return in_array($segment->getCode(), $this->_segmentsOfCustomer);
    }

    public function getStoredEmail() {
        return $this->helper('advancednewsletter')->getCheckoutStoredEmail();
    }

    public function getIsSubscribed()
    {
        $data = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        if (isset($data['is_subscribed'])) {
            return $data['is_subscribed'];
        }
        return false;
    }

    public function getSegments()
    {
        $data = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        if (isset($data['segments_select'])) {
            return $data['segments_select'];
        }
        return array();
    }

    public function getSaveFormValuesUrl()
    {
        return Mage::getUrl('aw_onestepcheckout/ajax/saveFormValues');
    }
}