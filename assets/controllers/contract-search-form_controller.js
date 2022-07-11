import { Controller } from '@hotwired/stimulus';

import $ from 'jquery';

import '../js/common/select2';

export default class extends Controller {
    static targets = ['form', 'userSelect', 'notifiedSelect', 'startDateInput', 'endDateInput'];
    static values = {
        locale: String,
    };

    connect() {
        if ( this.userSelectTarget ) {
            const options = {
              language: this.localeValue,
            };
            $(this.userSelectTarget).select2(options);
        }
    }

    // If controller is directly on form element it submits it directly.
    // Otherwise specify the form via form target
    submit(event) {
        event.preventDefault();
        let url = event.currentTarget.dataset.url;
        if ( this.hasFormTarget ) {
            if ( null != url) {
                $(this.formTarget).attr('action',url);
            }
           this.formTarget.submit();
        } else {
           event.currentTarget.submit();
        }
    }

    reset(event) {
        $(this.userSelectTarget).val('').trigger('change');
        $(this.notifiedSelectTarget).val('');
        $(this.startDateInputTarget).val('');
        $(this.endDateInputTarget).val('');
    }
}
