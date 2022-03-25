import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form'];
    static values = {
    };

    // If controller is directly on form element it submits it directly.
    // Otherwise specify the form via form target
    submit(event) {
        event.preventDefault();
        console.log(event);
        let url = event.currentTarget.dataset.url;
        console.log(event.explicitOriginalTarget);
        if ( this.hasFormTarget ) {
            if ( null != url) {
                $(this.formTarget).attr('action',url);
                console.log(this.formTarget);
            }
           this.formTarget.submit();
        } else {
           event.currentTarget.submit();
        }
    }
}
