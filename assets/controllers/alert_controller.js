import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [''];
    static values = {
        confirmationText: String,
        redirect: Boolean,
    };

    confirm(event) {
        event.preventDefault();
        let url = event.currentTarget.dataset.url;
        let token = event.currentTarget.dataset.token;
        console.log(url, token, this.hasRedirectValue, this.redirectValue, token != null);
        import ('sweetalert2').then(async(Swal) => {
            Swal.default.fire({
                template: '#confirmation',
                html: this.confirmationTextValue,
            }).then((result) => {
                if ( result.isConfirmed ) {
                    this.dispatch('confirmed');
                    if ( this.hasRedirectValue && this.redirectValue ) {
                        if ( token != null ) {
                            let urlParams = new URLSearchParams({
                                '_token' : token,
                            });
                            console.log(`${url}?${urlParams.toString()}`);
                            document.location.href=`${url}?${urlParams.toString()}`;
                        }
                        document.location.href=url;
                    }
                }
            });
        });
    }
}
