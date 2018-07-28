AWOnestepcheckoutForm = Class.create();
AWOnestepcheckoutForm.prototype = {
    initialize: function(config) {
        this.form = new VarienForm(config.formId);
        this.cartContainer = $$(config.cartContainerSelector).first();
        // validate shipping and payment
        this.shippingMethodName = config.shippingMethodName;
        this.shippingMethodAdviceSelector = config.shippingMethodAdvice;
        this.shippingValidationMessage = config.shippingValidationMessage;
        this.shippingMethodWrapperSelector = config.shippingMethodWrapperSelector;
        this.paymentMethodName = config.paymentMethodName;
        this.paymentMethodAdviceSelector = config.paymentMethodAdvice;
        this.paymentValidationMessage = config.paymentValidationMessage;
        this.paymentMethodWrapperSelector = config.paymentMethodWrapperSelector;
        //place button functionality
        this.placeOrderUrl = config.placeOrderUrl;
        this.successUrl = config.successUrl;
        this.placeOrderButton = $(config.placeOrderButtonSelector);
        this.granTotalAmount = this.placeOrderButton.select(config.granTotalAmountSelector).first(),
            this.granTotalAmountProcess = this.placeOrderButton.select(config.granTotalAmountProcessSelector).first(),
            this.pleaseWaitNotice = $$(config.pleaseWaitNoticeSelector).first(),
            this.disabledClassName = config.disabledClassName;
        this.popup = new AWOnestepcheckoutUIPopup(config.popup);

        Event.fire(document, 'aw_osc:onestepcheckout_form_init_before', {form: this});
        this.initOverlay(config.overlayId);

        if (this.placeOrderButton) {
            this.placeOrderButton.observe('click', this.placeOrder.bind(this));
            this.placeOrderButton.observe('mouseover', this.mouseOver.bind(this));
        }

        var me = this;
        var origFn = this.cartContainer.addActionBlocksToQueueBeforeFn || Prototype.emptyFunction;
        this.cartContainer.addActionBlocksToQueueBeforeFn = function(){
            origFn();
            //update place order button
            me.showPriceChangeProcess();
            me.disablePlaceOrderButton();
        };
        var origFn = this.cartContainer.removeActionBlocksFromQueueAfterFn || Prototype.emptyFunction;
        this.cartContainer.removeActionBlocksFromQueueAfterFn = function(response){
            origFn();
            if ('grand_total' in response) {
                me.granTotalAmount.update(response.grand_total);
            }
            me.enablePlaceOrderButton();
            me.hidePriceChangeProcess();
        };
        Event.fire(document, 'aw_osc:onestepcheckout_form_init_after', {form: this});
    },

    placeOrder: function() {
        if (this.validate()) {
            this.showOverlay();
            this.showPleaseWaitNotice();
            this.disablePlaceOrderButton();
            this._sendPlaceOrderRequest();
        }
    },

    mouseOver: function() {
        var focusEl = $$("*:focus").first();
        if (focusEl) {
            var x, y;
            if (window.pageXOffset !== undefined) { // All browsers, except IE9 and earlier
                x = window.pageXOffset;
                y = window.pageYOffset;
            } else { // IE9 and earlier
                x = document.documentElement.scrollLeft;
                y = document.documentElement.scrollTop;
            }
            focusEl.blur();
            focusEl.focus();
            window.scrollTo(x, y);
        }
    },

    _sendPlaceOrderRequest: function() {

        if (typeof EwayPayment != 'undefined' && EwayPayment.isEwayRapidMethod(window.payment.currentMethod)) {
            var currentForm = eCrypt.doEncrypt();
        } else {
            var currentForm = this.form.form;
        }

        var params = Form.serialize(currentForm, true);
        if (AWOnestepcheckoutCore.validateParams(params)) {
            new Ajax.Request(this.placeOrderUrl, {
                method: 'post',
                parameters: params,
                onComplete: this.onComplete.bindAsEventListener(this)
            });
        } else {
            this.hideOverlay();
            this.hidePleaseWaitNotice();
            this.enablePlaceOrderButton();
        }
    },

    showPriceChangeProcess: function() {
        this.disablePlaceOrderButton();
        this.granTotalAmount.hide();
        this.granTotalAmountProcess.show();
    },

    hidePriceChangeProcess: function() {
        this.enablePlaceOrderButton();
        this.granTotalAmount.show();
        this.granTotalAmountProcess.hide();
    },

    disablePlaceOrderButton: function() {
        this.placeOrderButton.addClassName(this.disabledClassName);
        this.placeOrderButton.disabled = true;
    },

    enablePlaceOrderButton: function() {
        this.placeOrderButton.removeClassName(this.disabledClassName);
        this.placeOrderButton.disabled = false;
    },

    showPleaseWaitNotice: function() {
        new Effect.Morph(this.pleaseWaitNotice, {
            style: {
                'top': '0px'
            },
            'duration': 0.2
        });
    },

    hidePleaseWaitNotice: function() {
        var newTop = this.pleaseWaitNotice.getHeight() + parseInt(this.pleaseWaitNotice.getStyle('marginTop'));
        new Effect.Morph(this.pleaseWaitNotice, {
            style: {
                'top': '-' + newTop + 'px'
            },
            'duration': 0.2
        });
    },

    initOverlay: function(overlayId) {
        this.overlay = new Element('div');
        this.overlay.setAttribute('id', overlayId);
        this.overlay.setStyle({'display':'none'});
        if (navigator.userAgent.indexOf("MSIE 8.0") == -1) {
            document.body.appendChild(this.overlay);
        } else {
            var me = this;
            Event.observe(window, 'load', function(e){
                document.body.appendChild(me.overlay);
            });
        }
    },

    showOverlay: function() {
        this.overlay.show();
    },

    hideOverlay: function() {
        this.overlay.hide();
    },

    onComplete: function(transport) {
        if (transport && transport.responseText) {
            try{
                response = eval('(' + transport.responseText + ')');
            } catch (e) {
                response = {};
            }
            if (response.redirect) {
                setLocation(response.redirect);
                return;
            }
            if (response.success) {
                setLocation(this.successUrl);
            } else if("is_hosted_pro" in response && response.is_hosted_pro) {
                this.popup.showPopupWithDescription(response.update_section.html);
                var iframe = this.popup.contentContainer.select('#hss-iframe').first();
                iframe.observe('load', function(){
                    $('hss-iframe').show();
                    $('iframe-warning').show();
                });
            } else {
                if (response.blocks) {
                    for (var blockId in response.blocks) {
                        var obj = $(blockId);
                        if (obj) {
                            obj.update(response.blocks[blockId]);
                        }
                    }
                } else {
                    var msg = response.messages || response.message;
                    if (typeof(msg) == 'object') {
                        msg = msg.join("\n");
                    }
                    if (msg) {
                        alert(msg);
                    }
                }
                this.enablePlaceOrderButton();
                this.hidePleaseWaitNotice();
                this.hideOverlay();
            }
        }
    },
    validate: function() {
        var result = this.form.validator.validate();
        var formData = Form.serialize(this.form.form, true);
        // check shipping
        this.shippingMethodAdvice = $$(this.shippingMethodAdviceSelector).first();
        this.shippingMethodWrapper = $$(this.shippingMethodWrapperSelector).first();
        var shippingValidation = true;
        if (this.shippingMethodAdvice && this.shippingMethodWrapper) {
            if (!formData[this.shippingMethodName]) {
                shippingValidation = false;
                this.shippingMethodAdvice.update(this.shippingValidationMessage).show();
                this.shippingMethodWrapper.addClassName('validation-failed');
            } else {
                shippingValidation = true;
                this.shippingMethodAdvice.update('').hide();
                this.shippingMethodWrapper.removeClassName('validation-failed');
            }
        }
        // check payment
        this.paymentMethodAdvice = $$(this.paymentMethodAdviceSelector).first();
        this.paymentMethodWrapper = $$(this.paymentMethodWrapperSelector).first();
        var paymentValidation = true;
        if (this.paymentMethodAdvice && this.paymentMethodWrapper) {
            if (!formData[this.paymentMethodName]) {
                paymentValidation = false;
                this.paymentMethodAdvice.update(this.paymentValidationMessage).show();
                this.paymentMethodWrapper.addClassName('validation-failed');
            } else {
                paymentValidation = true;
                this.paymentMethodAdvice.update('').hide();
                this.paymentMethodWrapper.removeClassName('validation-failed');
            }
        }
        return (result && shippingValidation && paymentValidation);
    }
};