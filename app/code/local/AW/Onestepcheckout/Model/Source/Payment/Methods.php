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


class AW_Onestepcheckout_Model_Source_Payment_Methods
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $paymentMethodsOptionArray = array(
            array(
                'label' => '',
                'value' => '',
            )
        );
        $paymentMethodsList = Mage::getModel('payment/config')->getActiveMethods();
        ksort($paymentMethodsList);
        foreach ($paymentMethodsList as $paymentMethodCode => $paymentMethod) {
            if ($paymentMethodCode == 'googlecheckout') {
                continue;
            }
            $paymentMethodsOptionArray[] = array(
                'label' => $paymentMethod->getTitle(),
                'value' => $paymentMethodCode,
            );
        }
        return $paymentMethodsOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $paymentMethodsArray = array();
        $paymentMethodsList = Mage::getModel('payment/config')->getActiveMethods();
        ksort($paymentMethodsList);
        foreach ($paymentMethodsList as $paymentMethodCode => $paymentMethod) {
            $paymentMethodsArray[$paymentMethodCode] = $paymentMethod->getTitle();
        }
        return $paymentMethodsArray;
    }
}