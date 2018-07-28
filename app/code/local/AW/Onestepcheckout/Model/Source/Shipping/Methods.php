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


class AW_Onestepcheckout_Model_Source_Shipping_Methods
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $shippingMethodsOptionArray = array(
            array(
                'label' => '',
                'value' => '',
            )
        );
        $carrierMethodsList = Mage::getSingleton('shipping/config')->getActiveCarriers();
        ksort($carrierMethodsList);
        foreach ($carrierMethodsList as $carrierMethodCode => $carrierModel) {
            foreach ($carrierModel->getAllowedMethods() as $shippingMethodCode => $shippingMethodTitle) {
                $shippingMethodsOptionArray[] = array(
                    'label' => $this->_getShippingMethodTitle($carrierMethodCode) . ' - ' . $shippingMethodTitle,
                    'value' => $carrierMethodCode . '_' . $shippingMethodCode,
                );
            }
        }
        return $shippingMethodsOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $shippingMethodsArray = array();
        $carrierMethodsList = Mage::getSingleton('shipping/config')->getActiveCarriers();
        ksort($carrierMethodsList);
        foreach ($carrierMethodsList as $carrierMethodCode => $carrierModel) {
            foreach ($carrierModel->getAllowedMethods() as $shippingMethodCode => $shippingMethodTitle) {
                $shippingCode = $carrierMethodCode . '_' . $shippingMethodCode;
                $shippingTitle = $this->_getShippingMethodTitle($carrierMethodCode) . ' - ' . $shippingMethodTitle;
                $shippingMethodsArray[$shippingCode] = $shippingTitle;
            }
        }
        return $shippingMethodsArray;
    }

    protected function _getShippingMethodTitle($shippingMethodCode)
    {
        if (!$shippingMethodTitle = Mage::getStoreConfig("carriers/$shippingMethodCode/title")) {
            $shippingMethodTitle = $shippingMethodCode;
        }
        return $shippingMethodTitle;
    }
}