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


class AW_Onestepcheckout_Helper_Checkout extends Mage_Core_Helper_Data
{
    public function getDeleteUrl($item)
    {
        $awOscIndexPageUrl = Mage::getUrl('aw_onestepcheckout/index/index');
        $awOscEncodedPageUrl = Mage::helper('core/url')->getEncodedUrl($awOscIndexPageUrl);
        return Mage::getModel('core/url')->getUrl(
            'checkout/cart/delete',
            array(
                'id'                                                      => $item->getId(),
                'form_key'                                                => Mage::getSingleton('core/session')->getFormKey(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $awOscEncodedPageUrl,
            )
        );
    }
}