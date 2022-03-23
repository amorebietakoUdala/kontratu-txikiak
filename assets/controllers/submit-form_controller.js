import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form'];
    static values = {
    };

    // If controller is directly on form element it submits it directly.
    // Otherwise specify the form via form target
    submit(event) {
        event.preventDefault();
        if ( this.hasFormTarget ) {
            this.formTarget.submit();
        } else {
            event.currentTarget.submit();
        }
    }
}
