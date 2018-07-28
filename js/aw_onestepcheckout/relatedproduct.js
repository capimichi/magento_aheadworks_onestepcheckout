AWOnestepcheckoutRelated = Class.create();
AWOnestepcheckoutRelated.prototype = {
    initialize: function(config){
        this.container = $$(config.containerSelector).first();
        this.productContainer = $$(config.productContainerSelector).first();
        this.useForShippingCheckboxContainer = $$(config.useForShippingCheckboxContainerSelector).first();
        this.urlToAddProductToWishlist = config.urlToAddProductToWishlist;
        this.urlToAddProductToCompareList = config.urlToAddProductToCompareList;
        this.urlToUpdateBlocksAfterACP = config.urlToUpdateBlocksAfterACP;
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.overlayConfig = config.overlayConfig;
        this.jsErrorMsg = config.jsErrorMsg;
        this.errorOnAddToCartMsg = config.errorOnAddToCartMsg;

        this.timer = new AWOnestepcheckoutHelperTimer(Object.extend(config.timer, {
            overlayConfig: config.overlayConfig
        }));

        this.initObservers();
    },

    initObservers: function() {
        if (!this.productContainer) {
            return;
        }
        var me = this;
        this.productContainer.select('a').each(function(link){
            link.observe('click', function(e){
                if (e.isLeftClick() || Prototype.Browser.IE) {
                    me.onActionClick(link);
                    e.stop();
                }
            });
        });

        this.productContainer.select('button').each(function(btn){
            btn.setAttribute('_onclick', btn.getAttribute('onclick'));
            btn.removeAttribute('onclick');
            btn.observe('click', function(e){
                me.onActionClick(btn);
                e.stop();
            });
        });
    },

    onActionClick: function(element) {
        var originalActionSource = "";
        var originalActionFn = null;
        if (element.tagName.toUpperCase() === "A" && element.getAttribute('href') !== null
            && element.getAttribute('href') !== '#') {
            originalActionSource = element.getAttribute('href');
            originalActionFn = function(){
                window.location.href = element.getAttribute('href');
            };
        } else if (element.tagName.toUpperCase() === "BUTTON" && element.getAttribute('_onclick') !== null) {
            originalActionSource = element.getAttribute('_onclick');
            originalActionFn = function(){
                eval(element.getAttribute('_onclick'));
            };
        } else {
            return;
        }

        if (this._isActionAddToWishlist(originalActionSource)) {
            var productId = originalActionSource.match("add/product/([0-9]+)/")[1];
            this._onClickOnAddToWishlist(productId);
            return;
        }
        if (this._isActionAddToCompareList(originalActionSource)) {
            var productId = originalActionSource.match("add/product/([0-9]+)/")[1];
            this._onClickOnAddToCompare(productId);
            return;
        }
        if (this._isActionAddToCart(originalActionSource)) {
            if (typeof(AW_AjaxCartPro) !== "undefined") {
                this._onClickOnAddToCartViaACP(originalActionFn);
                return;
            }
        }
        //if not a "add to compare list", "add to wishlist", "add to cart via ACP" action
        this.timer.setTimerAction(function(){
            try {
                originalActionFn();
            }
            catch(ex) { /*error*/ }
        });
        this.timer.showTimer();
        this.timer.startTimer();
    },

    addLoader: function(){
        AWOnestepcheckoutCore.addLoaderOnBlock(this.container, this.overlayConfig);
    },

    removeLoader: function(){
        AWOnestepcheckoutCore.removeLoaderFromBlock(this.container, this.overlayConfig);
    },

    showError: function(msg){
        AWOnestepcheckoutCore.showMsg(msg, this.errorMessageBoxCssClass, this.productContainer);
        //add observers
        var me = this;
        this.productContainer.select("." + this.errorMessageBoxCssClass + " a").each(function(link){
            link.observe('click', function(e){
                if (e.isLeftClick() || Prototype.Browser.IE) {
                    me.onActionClick(link);
                    e.stop();
                }
            });
        });
    },

    showSuccess: function(msg){
        AWOnestepcheckoutCore.showMsg(msg, this.successMessageBoxCssClass, this.productContainer);
        //add observers
        var me = this;
        this.productContainer.select("." + this.successMessageBoxCssClass + " a").each(function(link){
            link.observe('click', function(e){
                if (e.isLeftClick() || Prototype.Browser.IE) {
                    me.onActionClick(link);
                    e.stop();
                }
            });
        });
    },

    removeMsg: function(){
        AWOnestepcheckoutCore.removeMsgFromBlock(this.container, this.errorMessageBoxCssClass);
        AWOnestepcheckoutCore.removeMsgFromBlock(this.container, this.successMessageBoxCssClass);
    },

    _isActionAddToWishlist: function(str){
        if (str.indexOf('wishlist/index/add/product/') !== -1) {
            return true;
        }
        return false;
    },

    _isActionAddToCompareList: function(str){
        var match = str.match('compare/add/product/');
        if (str.indexOf('compare/add/product/') !== -1) {
            return true;
        }
        return false;
    },

    _isActionAddToCart: function(str){
        if (
            (str.indexOf('options=cart') !== -1) ||
            ((str.indexOf('.html') !== -1) && (str.indexOf('.html?') === -1)) ||
            (str.indexOf('checkout/cart/add') !== -1)
        ) {
            return true;
        }
        return false;
    },

    _onClickOnAddToWishlist: function(productId) {
        this.addLoader();
        this.removeMsg();
        new Ajax.Request(this.urlToAddProductToWishlist, {
            parameters: {product: productId},
            onComplete: this._onAjaxCompleteFn.bind(this)
        });
    },

    _onClickOnAddToCompare: function(productId) {
        this.addLoader();
        this.removeMsg();
        new Ajax.Request(this.urlToAddProductToCompareList, {
            parameters: {product: productId},
            onComplete: this._onAjaxCompleteFn.bind(this)
        });
    },

    _onClickOnAddToCartViaACP: function(originalActionFn) {
        var me = this;
        /* DISABLE confirmation popups*/
        Object.extend(AW_AjaxCartPro.config.data, {
            addProductConfirmationEnabled: 0,
            removeProductConfirmationEnabled: 0
        });
        var clearFnArray = [];

        /* ---------------------------------------- */
        /* ON CLICK TO CANCEL ON OPTIONS BLOCK*/
        var originalCancelBtnOnClickFn = AW_AjaxCartProUI.blocks.options._cancelBtnOnClick.bind(AW_AjaxCartProUI.blocks.options);
        clearFnArray.push(function(){
            AW_AjaxCartProUI.blocks.options._cancelBtnOnClick = originalCancelBtnOnClickFn;
        })
        AW_AjaxCartProUI.blocks.options._cancelBtnOnClick = function(event){
            me.removeLoader();
            var action = AWOnestepcheckoutCore.updater._getActionFromUrl(me.urlToUpdateBlocksAfterACP);
            AWOnestepcheckoutCore.updater.removeActionBlocksFromQueue(action);
            originalCancelBtnOnClickFn(event);
            clearFnArray.each(function(fn){
                fn();
            });
        };

        /* ---------------------------------------- */
        /* ON SUCCESS ADD TO CART */
        var originalAfterFireFn = AW_AjaxCartProUI.blocks.progress.afterFire;
        clearFnArray.push(function(){
            AW_AjaxCartProUI.blocks.progress.afterFire = originalAfterFireFn;
        })
        AW_AjaxCartProUI.blocks.progress.afterFire = function(parameters){
            new Ajax.Request(me.urlToUpdateBlocksAfterACP, {
                onComplete: me._onAjaxCompleteFn.bind(me)
            });
            originalAfterFireFn(parameters);
            clearFnArray.each(function(fn){
                fn();
            });
        };

        /* ---------------------------------------- */
        /* ON ERROR ON ADDING TO CART */
        var observerObject = AW_AjaxCartPro.config.actionsObservers.clickOnAddToCartInCategoryList;
        clearFnArray.push(function(){
            observerObject.fireOriginal = originalFireOriginalFn;
        })
        var originalFireOriginalFn = observerObject.fireOriginal.bind(observerObject);
        observerObject.fireOriginal = function(parameters){
            me.showError(me.errorOnAddToCartMsg);
            me.removeLoader();
            var action = AWOnestepcheckoutCore.updater._getActionFromUrl(me.urlToUpdateBlocksAfterACP);
            AWOnestepcheckoutCore.updater.removeActionBlocksFromQueue(action);
            clearFnArray.each(function(fn){
                fn();
            });
        };

        /* ---------------------------------------- */
        /* RUN ACP */
        this.addLoader();
        this.removeMsg();
        var action = AWOnestepcheckoutCore.updater._getActionFromUrl(this.urlToUpdateBlocksAfterACP);
        AWOnestepcheckoutCore.updater.addActionBlocksToQueue(action);
        originalActionFn();//request to add to cart via ACP
    },



    _onAjaxCompleteFn: function(transport) {
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            this.showError(this.jsErrorMsg);
            this.removeLoader();
            return;
        }
        if (json.success) {
            if ("blocks" in json) {
                this._updateBlocksFromJSONResponse(json.blocks);
                var action = AWOnestepcheckoutCore.updater._getActionFromUrl(transport.request.url);
                AWOnestepcheckoutCore.updater.removeActionBlocksFromQueue(action, json);
                if ("can_ship" in json && json.can_ship) {
                    this.useForShippingCheckboxContainer.removeClassName('no-display')
                }
            }
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                this.showSuccess(json.messages);
            }
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                errorMsg = json.messages;
            }
            this.showError(errorMsg);
        }
        if (window.awOSCShipmentEnterpriseGiftWrap) {
            window.awOSCShipmentEnterpriseGiftWrap.cleanGiftOptions();
        }
        this.removeLoader();
    },

    _updateBlocksFromJSONResponse: function(json) {
        if (json.related) {
            var storage = new Element('div');
            storage.innerHTML = json.related;
            var newBlock = storage.select('#' + this.productContainer.getAttribute('id')).first();
            if (newBlock) {
                this.productContainer.update(newBlock.innerHTML);
            } else {
                this.productContainer.update("");
            }
            this.initObservers();
        }
        if (json.top_links) {
            var storage = new Element('div');
            storage.innerHTML = json.top_links;
            var topLinksClassName = storage.down().getAttribute('class');
            if (topLinksClassName) {
                var targetBlock = $$(".quick-access ." + topLinksClassName).first();
                if (targetBlock) {
                    targetBlock.innerHTML = storage.down().innerHTML;
                }
            }
        }
    }
};