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


class AW_Onestepcheckout_Block_Onestep_Related extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $_timerConfig = array(
        'block_html_id'                 => 'aw-onestepcheckout-related-redirect-timer-block',
        'timer_clock_html_id'           => 'aw-onestepcheckout-related-redirect-timer-clock',
        'redirect_now_action_html_id'   => 'aw-onestepcheckout-related-redirect-timer-action-redirect',
        'cancel_action_html_id'         => 'aw-onestepcheckout-related-redirect-timer-action-cancel',
        'title_text'                    => "You will be redirected to another page in %s second(s).",
        'description_text'              => "You can lose your order progress.",
        'redirect_now_action_text'      => "Redirect Now",
        'cancel_action_text'            => "Cancel",
    );

    public function canShow()
    {
        if (!Mage::helper('aw_onestepcheckout/config')->isRelatedProducts()) {
            return false;
        }
        return true;
    }

    public function isARP2Installed()
    {
        if (!Mage::helper('core')->isModuleEnabled('AW_Autorelated')) {
            return false;
        }
        return true;
    }

    public function getHelperTimerBlockHtml()
    {
        $block = $this->getLayout()->createBlock(
            'aw_onestepcheckout/onestep_helper_timer',
            'aw.onestepcheckout.relate.timer',
            $this->_timerConfig
        );
        return $block->toHtml();
    }

    public function getUrlToAddProductToWishlist()
    {
        return Mage::getUrl(
            'onestepcheckout/ajax/addProductToWishlist',
            array(
                '_secure'  => true,
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            )
        );
    }

    public function getUrlToAddProductToCompareList()
    {
        return Mage::getUrl(
            'onestepcheckout/ajax/addProductToCompareList',
            array(
                '_secure'  => true,
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            )
        );
    }

    public function getUrlToUpdateBlocksAfterACP()
    {
        return Mage::getUrl(
            'onestepcheckout/ajax/updateBlocksAfterACP',
            array(
                '_secure'=>true,
                'form_key' => Mage::getSingleton('core/session')->getFormKey()
            )
        );
    }
}