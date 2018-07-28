window.payment = window.payment || {};
window.payment.switchMethod = Prototype.emptyFunction;
window.payment.form = new Element('div', {});
window.payment.save = Prototype.emptyFunction;

Event.observe(document, 'aw_osc:onestepcheckout_form_init_before', function(e){
    var form = e.memo.form;
    var formSendPlaceOrderRequestOriginalFn = form._sendPlaceOrderRequest;
    form._sendPlaceOrderRequest = function() {
        window.payment.save();
    };
    window.payment.save = formSendPlaceOrderRequestOriginalFn.bind(form);
});