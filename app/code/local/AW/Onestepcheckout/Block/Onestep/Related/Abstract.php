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


abstract class AW_Onestepcheckout_Block_Onestep_Related_Abstract extends Mage_Checkout_Block_Cart_Crosssell
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 5;

    protected $_compareListProductIds = null;

    protected $_wishlistProductIds = null;

    /**
     * Copy-paste logic from Mage_Wishlist_IndexController->preDispatch
     * @return bool
     */
    public function isCanManageWishlist()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            return false;
        }
        return true;
    }

    public function isNotAddedToWishlist($_item)
    {
        return !in_array($_item->getId(), $this->_getWishlistProductIds());
    }

    public function isNotAddedToCompareList($_item)
    {
        return !in_array($_item->getId(), $this->_getCompareListProductIds());
    }

    protected function _getWishlistProductIds()
    {
        if (is_null($this->_wishlistProductIds)) {
            $this->_wishlistProductIds = array();
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                if ($customerId) {
                    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
                    if ($wishlist->getId() && $wishlist->getCustomerId() === $customerId) {
                        $this->_wishlistProductIds = $this->_getWishlistProductCollection($wishlist)->getAllIds();
                    }
                }
            }
        }
        return $this->_wishlistProductIds;
    }

    protected function _getCompareListProductIds()
    {
        if (is_null($this->_compareListProductIds)) {
            $this->_compareListProductIds = array();
            $collection = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $collection->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            }
            else {
                $collection->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }
            $this->_compareListProductIds = $collection->getAllIds();
        }
        return $this->_compareListProductIds;
    }

    protected function _getWishlistProductCollection(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        return Mage::getResourceModel('wishlist/product_collection')
            ->setStoreId($wishlist->getStore()->getId())
            ->addWishlistFilter($wishlist)
            ->addWishListSortOrder();
    }
}
