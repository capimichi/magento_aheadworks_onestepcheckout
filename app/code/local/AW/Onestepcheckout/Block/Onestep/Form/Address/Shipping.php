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


class AW_Onestepcheckout_Block_Onestep_Form_Address_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    protected $_attributeValidationClasses = array(
        'company'   => '',
        'fax'       => '',
        'telephone' => 'required-entry',
        'region'    => '',
        'postcode'  => 'required-entry',
        'city'      => 'required-entry',
        'street'    => 'required-entry',
    );

    public function isVatAttributeVisible()
    {
        $helper = Mage::helper('customer/address');
        if (method_exists($helper, 'isVatAttributeVisible')) {
            return $helper->isVatAttributeVisible();
        }
        return false;
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressDetails = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
            if (isset($addressDetails[$type.'_address_id'])) {
                if (empty($addressDetails[$type.'_address_id'])) {
                    $addressId = 0;
                } else {
                    $addressId = $addressDetails[$type.'_address_id'];
                }
            } else {
                $addressId = $this->getQuote()->getBillingAddress()->getCustomerAddressId();
            }
            if (empty($addressId) && $addressId !== 0) {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

    public function getCustomerWidgetName()
    {
        return  $this->getLayout()
            ->createBlock('customer/widget_name')
            ->setObject($this->_getObjectForCustomerNameWidget())
            ->setFieldIdFormat('shipping:%s')
            ->setFieldNameFormat('shipping[%s]')
            ->setFieldParams('onchange="shipping.setSameAsBilling(false)"')
        ;
    }

    public function getEnterpriseAddressAttributesHtml()
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_Customer')) {
            $addressAttributes =  Mage::app()->getLayout()
                ->createBlock('enterprise_customer/form')
                ->setTemplate('aw_onestepcheckout/customer/form/attributes.phtml')
                ->setFormCode('customer_register_address')
                ->addRenderer('text', 'enterprise_customer/form_renderer_text', 'customer/form/renderer/text.phtml')
                ->addRenderer('textarea', 'enterprise_customer/form_renderer_textarea', 'customer/form/renderer/textarea.phtml')
                ->addRenderer('multiline', 'enterprise_customer/form_renderer_multiline', 'customer/form/renderer/multiline.phtml')
                ->addRenderer('date', 'enterprise_customer/form_renderer_date', 'customer/form/renderer/date.phtml')
                ->addRenderer('select', 'enterprise_customer/form_renderer_select', 'customer/form/renderer/select.phtml')
                ->addRenderer('multiselect', 'enterprise_customer/form_renderer_multiselect', 'customer/form/renderer/multiselect.phtml')
                ->addRenderer('boolean', 'enterprise_customer/form_renderer_boolean', 'customer/form/renderer/boolean.phtml')
                ->addRenderer('file', 'enterprise_customer/form_renderer_file', 'customer/form/renderer/file.phtml')
                ->addRenderer('image', 'enterprise_customer/form_renderer_image', 'customer/form/renderer/image.phtml')
            ;

            if ($addressAttributes) {
                $addressModel = new Mage_Customer_Model_Address(
                    Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values/shipping')
                );
                $addressAttributes->setEntity($addressModel);
                $addressAttributes->setFieldIdFormat('shipping:%1$s')->setFieldNameFormat('shipping[%1$s]');
                return $addressAttributes->setExcludeFileAttributes(true)->setShowContainer(false)->toHtml();
            }
        }
        return '';
    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getDataFromSession('country_id');
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle($this->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        return $select->getHtml();
    }

    public function getDataFromSession($path)
    {
        $formData = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values/shipping');
        if (!empty($formData[$path])) {
            return $formData[$path];
        }
        return null;
    }

    public function getAttributeValidationClass($attributeCode)
    {
        $helper = Mage::helper('customer/address');
        if (method_exists($helper, 'getAttributeValidationClass')) {
            return $helper->getAttributeValidationClass($attributeCode);
        }
        if (array_key_exists($attributeCode, $this->_attributeValidationClasses)) {
            return $this->_attributeValidationClasses[$attributeCode];
        }
        return '';
    }

    public function isUseBillingAsShipping()
    {
        return $this->getConfig()->isUseBillingAsShipping();
    }

    public function getConfig()
    {
        return Mage::helper('aw_onestepcheckout/config');
    }

    protected function _getObjectForCustomerNameWidget()
    {
        $formData = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_form_values');
        $address = Mage::getModel('sales/quote_address');
        if (isset($formData['shipping'])) {
            $address->addData($formData['shipping']);
        }
        if ($address->getFirstname() || $address->getLastname()) {
            return $address;
        }
        return $this->getQuote()->getCustomer();
    }
}