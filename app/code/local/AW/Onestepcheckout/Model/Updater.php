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


class AW_Onestepcheckout_Model_Updater
{
    const TARGET_LAYOUT_FULL_ACTION_NAME = 'aw_onestepcheckout_index_index';

    const ADDRESS_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.address';
    const SHIPPING_METHOD_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.shippingmethod';
    const PAYMENT_METHOD_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.paymentmethod';
    const PAYMENT_METHOD_CENTINEL_BLOCK_NAME = 'aw_onestepcheckout.onestep.centinel.frame';
    const REVIEW_CART_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.cart';
    const REVIEW_COUPON_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.coupon';
    const REVIEW_POINTS_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.points';
    const REVIEW_REFER_A_FRIEND_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.referafriend';
    const REVIEW_STORECREDIT_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.storecredit';

    const REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.enterprise.giftcard';
    const REVIEW_AW_GIFTCARD_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.aw.giftcard';
    const REVIEW_ENTERPRISE_POINTS_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.enterprise.points';
    const REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME = 'aw_onestepcheckout.onestep.form.review.enterprise.storecredit';

    const TOP_LINK_BLOCK_NAME         = 'top.links';
    const RELATED_PRODUCTS_BLOCK_NAME = 'aw_onestepcheckout.onestep.related';

    protected $_map = array(
        'saveAddress'                => array(
            'shipping_method'               => self::SHIPPING_METHOD_BLOCK_NAME,
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'saveShippingMethod'         => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'saveEnterpriseGiftOptions'   => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'savePaymentMethod'          => array(
            'centinel_frame'                => self::PAYMENT_METHOD_CENTINEL_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyCoupon'                => array(
            'shipping_method'               => self::SHIPPING_METHOD_BLOCK_NAME,
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyAWGiftcard'   => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
        ),
        'checkAWGiftcard'   => array(
            'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
        ),
        'removeAWGiftcard'  => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
        ),
        'applyEnterpriseGiftcard'   => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'removeEnterpriseGiftcard'   => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyEnterpriseStorecredit' => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyStorecredit' => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
        ),
        'removeStorecredit' => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyEnterprisePoints'      => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyPoints'                => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'applyRafDiscount'           => array(
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        ),
        'addProductToWishlist'       => array(
            'top_links'                     => self::TOP_LINK_BLOCK_NAME,
            'related'                       => self::RELATED_PRODUCTS_BLOCK_NAME
        ),
        'addProductToCompareList'    => array(
            'related'                       => self::RELATED_PRODUCTS_BLOCK_NAME
        ),
        'updateBlocksAfterACP'       => array(
            'related'                       => self::RELATED_PRODUCTS_BLOCK_NAME,
            'address'                       => self::ADDRESS_BLOCK_NAME,
            'shipping_method'               => self::SHIPPING_METHOD_BLOCK_NAME,
            'payment_method'                => self::PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'                   => self::REVIEW_CART_BLOCK_NAME,
            'review_coupon'                 => self::REVIEW_COUPON_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::REVIEW_ENTERPRISE_GIFTCARD_BLOCK_NAME,
			'review_aw_giftcard'    		=> self::REVIEW_AW_GIFTCARD_BLOCK_NAME,
            'review_enterprise_storecredit' => self::REVIEW_ENTERPRISE_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'      => self::REVIEW_ENTERPRISE_POINTS_BLOCK_NAME,
            'review_points'                 => self::REVIEW_POINTS_BLOCK_NAME,
            'review_referafriend'           => self::REVIEW_REFER_A_FRIEND_BLOCK_NAME,
            'review_storecredit'            => self::REVIEW_STORECREDIT_BLOCK_NAME,
        )
    );

    /**
     * @param null $controller
     *
     * @return array
     */
    public function getBlocks($layout = null, $fullTargetActionName = null)
    {
        if (is_null($layout)) {
            $layout = Mage::app()->getLayout();
        }
        if (is_null($fullTargetActionName)) {
            $fullTargetActionName = self::TARGET_LAYOUT_FULL_ACTION_NAME;
        }
        $this->_initLayout($layout, $fullTargetActionName);

        $actionName = Mage::app()->getRequest()->getActionName();
        if (!array_key_exists($actionName, $this->_map)) {
            return array();
        }
        if (!is_array($this->_map[$actionName])) {
            return array();
        }
        $blocks = array();
        foreach($this->_map[$actionName] as $key => $blockName) {
            $block = $layout->getBlock($blockName);
            if ($block) {
                $blocks[$key] = $block->toHtml();
            }
        }
        return $blocks;
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->_map;
    }

    protected function _initLayout($layout, $fullTargetActionName)
    {
        /* -- START-- copypaste from Mage_Core_Controller_Varien_Action -- START -- */
        $update = $layout->getUpdate();
        $update->addHandle('default');//load default handle
        $update->addHandle('STORE_'.Mage::app()->getStore()->getCode());// load store handle
        $package = Mage::getSingleton('core/design_package');
        $update->addHandle(
            'THEME_'.$package->getArea().'_'.$package->getPackageName().'_'.$package->getTheme('layout')
        ); // load theme handle
        $update->addHandle(strtolower($fullTargetActionName));// load action handle
        Mage::dispatchEvent(
            'controller_action_layout_load_before',
            array('action' => Mage::app()->getFrontController()->getAction(), 'layout' => $layout)
        );
        $update->load();
        $layout->generateXml();
        $layout->generateBlocks();
        /* -- END -- copypaste from Mage_Core_Controller_Varien_Action -- END -- */
    }
}