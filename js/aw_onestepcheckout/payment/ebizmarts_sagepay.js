// Copy-paste from Ebizmarts_SagePaySuite
changecsvclass = function (obj) {

    var methodCode = 'sagepaydirectpro';

    if ((typeof FORM_KEY) != 'undefined') {
        methodCode = 'sagepaydirectpro_moto';
    }

    var ccTypeContainer = $(methodCode + '_cc_type');
    var ccCVNContainer = $(methodCode + '_cc_cid');
    var ccCVNRequiredStar = ccCVNContainer.up('li').down('em');

    if (ccTypeContainer) {
        if (ccTypeContainer.value == 'LASER' && ccCVNContainer.hasClassName('required-entry')) {
            if (ccCVNContainer) {
                ccCVNContainer.removeClassName('required-entry');
            }
            if (ccCVNRequiredStar) {
                ccCVNRequiredStar.hide();
            }
        }
        if (ccTypeContainer.value != 'LASER' && !ccCVNContainer.hasClassName('required-entry')) {
            if (ccCVNContainer) {
                ccCVNContainer.addClassName('required-entry');
            }
            if (ccCVNRequiredStar) {
                ccCVNRequiredStar.show();
            }
        }
    }
}