AWOnestepcheckoutShipment = Class.create();
AWOnestepcheckoutShipment.prototype = {
    initialize: function(config) {
        window.shippingMethod = {};
        window.shippingMethod.validator = null;
        this.container = $$(config.containerSelector).first();
        this.switchMethodInputs = $$(config.switchMethodInputsSelector);
        this.saveShipmentUrl = config.saveShipmentUrl;

        this.init();
        this.initObservers();
    },

    init: function() {
        var me = this;
        this.switchMethodInputs.each(function(element) {
            var methodCode = element.value;
            if (element.checked) {
                me.currentMethod = methodCode;
            }
        });
    },

    initObservers: function() {
        var me = this;
        this.switchMethodInputs.each(function(element) {
            element.observe('click', function(e) {
                me.switchToMethod(element.value);
            });
        })
    },

    switchToMethod: function(methodCode) {
        if (this.currentMethod !== methodCode) {
            AWOnestepcheckoutCore.updater.startRequest(this.saveShipmentUrl, {
                method: 'post',
                parameters: Form.serialize(this.container, true)
            });
            this.currentMethod = methodCode;
        }
    }
};

/* Giftwrap from Enterprise */
AWOnestepcheckoutShipmentEnterpriseGiftwrap = Class.create();
AWOnestepcheckoutShipmentEnterpriseGiftwrap.prototype = {
    initialize: function(config) {
        // init dom elements
        this.giftOptionsContainer = $$(config.giftOptionsContainerSelector).first();
        this.addPrintedCardCheckbox = $(config.addPrintedCardCheckboxSelector);
        this.giftWrapDesignSelects = $$(config.giftWrapDesignSelectsSelector);
        this.addGiftOptionsCheckboxes = $$(config.addGiftOptionsCheckboxesSelector);
        this.messageOptionsSelector = $$(config.messageOptionsSelector);
        this.extraOptionsCheckboxes = $$(config.extraOptionsSelector);
        if (!window.AWOSCGiftwrapEECheckboxState) {
            window.AWOSCGiftwrapEECheckboxState = {};
        }

        // init urls
        this.saveGiftOptionsUrl = config.saveGiftOptionsUrl;

        // init behaviour
        this.init();
    },
    init: function() {
        var me = this;

        document.observe("dom:loaded", function() {
            if (me.addGiftOptionsCheckboxes) {
                me.addGiftOptionsCheckboxes.each(function(element) {
                    if (element.checked == true) {
                        element.click()
                    }
                })
            }
        });

        if (this.addGiftOptionsCheckboxes) {
            this.addGiftOptionsCheckboxes.each(function(element) {
                element.observe('change', function(e) {
                    me.onChangeAddGiftOptionsCheckbox(element);
                })
            })
        }
        if (this.giftWrapDesignSelects) {
            this.giftWrapDesignSelects.each(function(element) {
                element.observe('change', function(e) {
                    me.onChangeGiftWrapDesignSelect();
                })
            })
        }
        if (this.messageOptionsSelector) {
            this.messageOptionsSelector.each(function(element) {
                if (element.down('input')) {
                    element.down('input').observe('change', function(e) {
                        me.onChangeGiftWrapMesssage();
                    })
                } else {
                    element.down('textarea').observe('change', function(e) {
                        me.onChangeGiftWrapMesssage();
                    })
                }

            })
        }

        if (this.addPrintedCardCheckbox) {
            this.addPrintedCardCheckbox.observe('change', this.addPrintedCard.bind(this))
        }
    },
    onChangeAddGiftOptionsCheckbox: function(el) {
        window.AWOSCGiftwrapEECheckboxState[el.id] = el.checked;
        if (!el.checked) {
            this.saveGiftOptions();
        }
    },
    onChangeGiftWrapDesignSelect: function() {
        this.saveGiftOptions();
    },
    onChangeGiftWrapMesssage: function() {
        this.saveGiftOptions();
    },
    addPrintedCard: function() {
        this.saveGiftOptions();
    },
    saveGiftOptions: function() {
        if (typeof window.AWOSCGiftwrapEEDoNotSave == 'undefined' || window.AWOSCGiftwrapEEDoNotSave === false) {
            window.AWOSCGiftwrapEESavedValues = Form.serialize(this.giftOptionsContainer, true);
            this.extraOptionsCheckboxes.each(function(element) {
                window.AWOSCGiftwrapEECheckboxState[element.id] = element.checked;
            });

            AWOnestepcheckoutCore.updater.startRequest(this.saveGiftOptionsUrl, {
                method: 'post',
                parameters: Form.serialize(this.giftOptionsContainer, true)
            });
        }
    },
    updateGiftOptions: function() {
        if (window.AWOSCGiftwrapEESavedValues) {
            window.AWOSCGiftwrapEEDoNotSave = true;
            for (var key in window.AWOSCGiftwrapEECheckboxState) {
                if (window.AWOSCGiftwrapEECheckboxState[key]) {
                    var checkboxEl = $(key);
                    if (checkboxEl && !checkboxEl.checked) {
                        checkboxEl.click()
                    }
                }
            }
            for (var key in window.AWOSCGiftwrapEESavedValues) {
                var el = $$('[name="' + key + '"]').first();
                if (el) {
                    el.value = window.AWOSCGiftwrapEESavedValues[key];
                    if (el.nodeName.toLowerCase() == 'select' && el.value) {
                        var id = el.id.sub('giftwrapping_','');
                        if (el.up('.options-items-container')) {
                            var parts = el.up('.options-items-container').id.sub('options-items-container-','').split('-');
                            id = parts[1];
                            if (giftWrapping.itemsInfo[id]) {
                                var addrId = parts[0];
                            } else {
                                var addrId = false;
                            }
                        }
                        giftWrapping.setDesign(el.value, id);
                        giftWrapping.setPrice(el.value, id, addrId);
                    }
                }
            }
            window.AWOSCGiftwrapEEDoNotSave = false;
        }
    },
    cleanGiftOptions: function() {
        if (window.AWOSCGiftwrapEESavedValues) {
            window.AWOSCGiftwrapEEDoNotSave = true;
            for (var key in window.AWOSCGiftwrapEECheckboxState) {
                if (window.AWOSCGiftwrapEECheckboxState[key]) {
                    var checkboxEl = $(key);
                    if (checkboxEl && checkboxEl.checked) {
                        checkboxEl.click()
                    }
                }
            }
            window.AWOSCGiftwrapEEDoNotSave = false;
        }
    }
};