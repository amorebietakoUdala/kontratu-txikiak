import { Controller } from '@hotwired/stimulus';

import $ from 'jquery';

export default class extends Controller {
    page = 1;
    pageSize = 10;
    sortName = 7;
    sortOrder = 'desc';

    static targets = [''];
    static values = {
        confirmationText: String,
        redirect: Boolean,
    };

    confirm(event) {
        event.preventDefault();
        this.page = $('.page-item.active>a').text().trim();
        this.pageSize = $('.page-size').text().trim();
        if ( $('.asc').length ) {
            this.sortOrder = 'asc';
            this.sortName = $('.asc').closest('th').data('field');
        } else if ( $('.desc').length ) {
            this.sortName = $('.desc').closest('th').data('field');
            this.sortOrder = 'desc';
        }
        let url = event.currentTarget.dataset.url;
        let token = event.currentTarget.dataset.token;
        import ('sweetalert2').then(async(Swal) => {
            Swal.default.fire({
                template: '#confirmation',
                html: this.confirmationTextValue,
                allowOutsideClick: false,
            }).then((result) => {
                if ( result.isConfirmed ) {
                    this.dispatch('confirmed');
                    if ( this.hasRedirectValue && this.redirectValue ) {
                        if ( token != null ) {
                            let urlParams = new URLSearchParams({
                                '_token' : token,
                                'page': this.page,
                                'pageSize': this.pageSize,
                                'sortName': this.sortName,
                                'sortOrder': this.sortOrder,
                            });
                            console.log(`${url}?${urlParams.toString()}`);
                            document.location.href=`${url}?${urlParams.toString()}`;
                            return;
                        }
                        document.location.href=url;
                    }
                }
            });
        });
    }


}
