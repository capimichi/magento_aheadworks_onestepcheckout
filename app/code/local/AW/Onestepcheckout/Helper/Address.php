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

class AW_Onestepcheckout_Helper_Address extends Mage_Core_Helper_Data
{
    public function saveBilling($data = array(), $customerAddressId = null)
    {
        $address = $this->getQuote()->getBillingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array(
                        'error'   => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.'),
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            //fix for mage 1702
            if (@class_exists('Mage_Customer_Model_Form')) {
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
                $addressForm->setEntity($address);
                $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
                $addressForm->compactData($addressData);
                //unset billing address attributes which were not shown in form
                foreach ($addressForm->getAttributes() as $attribute) {
                    if (!isset($data[$attribute->getAttributeCode()])) {
                        $address->setData($attribute->getAttributeCode(), NULL);
                    }
                }
                $address->setCustomerAddressId(null);
                // Additional form data, not fetched by extractData (as it fetches only attributes)
                $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            } else {
                $address->addData($data);
            }
        }
        if (isset($data['email'])) {
            $address->setEmail($data['email']);
        }
        $address->implodeStreetAddress();

        if (!$this->getQuote()->isVirtual()) {
            if (isset($data['use_for_shipping'])) {
                $usingCase = 1;
            } else {
                $usingCase = 0;
            }

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    $shipping
                        ->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setShippingMethod($shippingMethod)
                    ;
                    break;
            }
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }
        return array();
    }

    public function saveShipping($data = array(), $customerAddressId = null)
    {
        $address = $this->getQuote()->getShippingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array(
                        'error'   => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.'),
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            $address->addData($data);
        }
        $address->implodeStreetAddress();
        $address->setSameAsBilling(0);
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        return array();
    }

    public function initAddress()
    {
        if ($this->getQuote()->getBillingAddress()->getCustomerAddressId()) {
            $data = array(
                'use_for_shipping' => true,
            );
            $this->saveBilling($data, $this->getQuote()->getBillingAddress()->getCustomerAddressId());
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->getQuote()->collectTotals();
            $this->getQuote()->save();
            return;
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($primaryBillingAddress = $customer->getPrimaryBillingAddress()) {
            $customerAddressId = $primaryBillingAddress->getId();
            $data = array(
                'use_for_shipping' => true,
            );
            $this->saveBilling($data, $customerAddressId);
        } else {
            if (!is_null($this->getQuote()->getBillingAddress()->getId())) {
                return;
            }
            $data = array(
                'country_id'       => Mage::getStoreConfig('general/country/default'),
                'use_for_shipping' => true,
            );
            $this->saveBilling($data);
        }
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function validateAddressData($data)
    {
        $validationErrors = array();
        $requiredFields = array(
            'country_id',
            'city',
            'postcode',
            'region_id',
        );
        foreach ($requiredFields as $requiredField) {
            if (!isset($data[$requiredField])) {
                $validationErrors[] = $this->__("Field %s is required", $requiredField);
            }
        }
        return $validationErrors;
    }

}