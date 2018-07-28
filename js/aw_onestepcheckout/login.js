AWOnestepcheckoutLogin = Class.create();
AWOnestepcheckoutLogin.prototype = {
    initialize: function(config){
        var me = this;
        this.container = $$(config.containerSelector).first();
        this.forgotPasswordLinkArray = $$(config.forgotPasswordLinkSelector);
        this.backToLoginLinkArray = $$(config.backToLoginLinkSelector);
        this.loginForm = $$(config.loginFormSelector).first();
        this.forgotPasswordForm = $$(config.forgotPasswordFormSelector).first();
        this.forgotPasswordSuccessBlock = $$(config.forgotPasswordSuccessBlockSelector).first();
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.overlayConfig = config.overlayConfig;
        this.jsErrorMsg = config.jsErrorMsg;

        this.forgotPasswordLinkArray.each(function(link){
            link.observe('click', me.onClickOnForgotPasswordLink.bind(me));
        });
        this.backToLoginLinkArray.each(function(link){
            link.observe('click', me.onClickOnBackToLoginLink.bind(me));
        });

        this.loginForm = new VarienForm(this.loginForm);
        this.forgotPasswordForm = new VarienForm(this.forgotPasswordForm);

        this.loginForm.form.select('button[type=submit]').each(function(btn){
            btn.observe('click', me.onLoginFormSubmit.bind(me));
        });
        this.forgotPasswordForm.form.select('button[type=submit]').each(function(btn){
            btn.observe('click', me.onForgotPasswordFormSubmit.bind(me));
        });

        this.currentVisibleBlock = this.loginForm.form;
        this.forgotPasswordForm.form.hide();
        this.forgotPasswordSuccessBlock.hide();

        this.container.select('input, button, a').each(function(el){
            el.setAttribute('tabindex', '-1');
        });

        this.currentVisibleBlock.select('input, button, a').each(function(el){
            el.removeAttribute('tabindex');
        });
    },

    moveToBlock: function(block, forward){
        this.forgotPasswordForm.form.hide();
        this.loginForm.form.hide();
        this.forgotPasswordSuccessBlock.hide();
        block.show();
    },

    addLoader: function(){
        AWOnestepcheckoutCore.addLoaderOnBlock(this.container, this.overlayConfig);
    },

    removeLoader: function(){
        AWOnestepcheckoutCore.removeLoaderFromBlock(this.container, this.overlayConfig);
    },

    showError: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.errorMessageBoxCssClass, this.currentVisibleBlock);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.container, {
            style: {
                height: this.currentVisibleBlock.getHeight() + 'px'
            },
            duration: 0.6,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },

    removeErrorOnBlock: function(block){
        AWOnestepcheckoutCore.removeMsgFromBlock(block, this.errorMessageBoxCssClass);
    },

    onClickOnForgotPasswordLink: function(e) {
        this.removeErrorOnBlock(this.forgotPasswordForm.form); //remove error blocks
        this.forgotPasswordForm.form.setStyle({paddingBottom: '0px'});
        this.forgotPasswordForm.validator.reset(); //remove js validation errors

        this.forgotPasswordForm.form.show();
        this.loginForm.form.hide();
    },

    onClickOnBackToLoginLink: function(e) {
        this.removeErrorOnBlock(this.loginForm.form); //remove error blocks
        this.loginForm.form.setStyle({paddingBottom: '0px'});
        this.loginForm.validator.reset(); //remove js validation errors

        this.forgotPasswordSuccessBlock.hide();
        this.forgotPasswordForm.form.hide();
        this.loginForm.form.show();
    },

    onLoginFormSubmit: function(e){
        //remove old advices
        this.loginForm.form.select('.validation-advice').each(function(adviceEl){
            adviceEl.remove();
        });
        if (this.loginForm.validator.validate()) {
            this.addLoader();
            this.loginForm.form.setStyle({
                paddingBottom: '0px'
            });
            this.removeErrorOnBlock(this.loginForm.form);
            var params = awOSCLoginBlock.loginForm.form.serialize(true);
            if (AWOnestepcheckoutCore.validateParams(params)) {
                new Ajax.Request(this.loginForm.form.getAttribute('action'), {
                    method: 'post',
                    parameters: params,
                    onComplete: this._onAjaxLoginCompleteFn.bind(this)
                });
            }
        }
        e.stop();
    },

    onForgotPasswordFormSubmit: function(e){
        //remove old advices
        this.forgotPasswordForm.form.select('.validation-advice').each(function(adviceEl){
            adviceEl.remove();
        });
        if (this.forgotPasswordForm.validator.validate()) {
            this.addLoader();
            this.forgotPasswordForm.form.setStyle({
                paddingBottom: '0px'
            });
            this.removeErrorOnBlock(this.forgotPasswordForm.form);
            var params = awOSCLoginBlock.forgotPasswordForm.form.serialize(true);
            if (AWOnestepcheckoutCore.validateParams(params)) {
                new Ajax.Request(this.forgotPasswordForm.form.getAttribute('action'), {
                    method: 'post',
                    parameters: params,
                    onComplete: this._onAjaxForgotPasswordCompleteFn.bind(this)
                });
            }
        }

        e.stop();
    },

    _onAjaxLoginCompleteFn: function(transport){
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            this.showError(this.jsErrorMsg, this.removeLoader.bind(this));
            return;
        }
        if (json.success) {
            document.location.reload();
        } else {
            if (json.redirect_to) {
                document.location.href = json.redirect_to;
                return;
            }
            var errorMsg = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                errorMsg = json.messages;
            }
            this.showError(errorMsg, this.removeLoader.bind(this));
        }
    },

    _onAjaxForgotPasswordCompleteFn: function(transport){
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            this.showError(this.jsErrorMsg, this.removeLoader.bind(this));
            return;
        }
        if (json.success) {
            this.removeLoader();
            this.moveToBlock(this.forgotPasswordSuccessBlock, true);
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                errorMsg = json.messages;
            }
            this.showError(errorMsg, this.removeLoader.bind(this));
        }
    },
};