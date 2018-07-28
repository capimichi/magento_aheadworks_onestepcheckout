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


class AW_Onestepcheckout_Model_Source_Enableddisabled
{
    const DISABLED_CODE = 0;
    const ENABLED_CODE  = 1;
    const DISABLED_LABEL = 'Disabled';
    const ENABLED_LABEL  = 'Enabled';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ENABLED_CODE,
                'label' => Mage::helper('aw_onestepcheckout')->__(self::ENABLED_LABEL),
            ),
            array(
                'value' => self::DISABLED_CODE,
                'label' => Mage::helper('aw_onestepcheckout')->__(self::DISABLED_LABEL),
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::ENABLED_CODE  => Mage::helper('aw_onestepcheckout')->__(self::ENABLED_LABEL),
            self::DISABLED_CODE => Mage::helper('aw_onestepcheckout')->__(self::DISABLED_LABEL),
        );
    }
}