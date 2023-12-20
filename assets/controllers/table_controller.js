import { Controller } from '@hotwired/stimulus';

import '../js/common/list';

export default class extends Controller {
    static targets = [];
    static values = {
        exportName: String,
        pageSize: Number,
        page: Number,
        sortName: Number,
        sortOrder: String,
        }
    sortName = null;
    sortOrder = null;

    connect() {
        this.sortName = this.sortNameValue;
        this.sortOrder = this.sortOrderValue;
        $(this.element).bootstrapTable({
            cache: false,
            showExport: true,
            iconsPrefix: 'fa',
            icons: {
                export: 'fa-download',
            },
            exportTypes: ['excel'],
            exportDataType: 'all',
            exportOptions: {
                fileName: this.exportNameValue,
                ignoreColumn: ['options']
            },
            showColumns: false,
            pagination: true,
            search: true,
            striped: true,
            sortStable: true,
            sortName: this.sortName,
            sortOrder: this.sortOrder,
            pageSize: this.hasPageSizeValue ? this.pageSizeValue : 10,
            pageList: [10, 25, 50, 100],
            sortable: true,
            locale: $('html').attr('lang') + '-' + $('html').attr('lang').toUpperCase(),
            onSort: (name, order) => {
                this.sortName = name;
                this.sortOrder = order;
            }
        });
        var $table = $(this.element);
        $(function() {
            $('#toolbar').find('select').change(function() {
                $table.bootstrapTable('destroy').bootstrapTable({
                    exportDataType: $(this).val(),
                });
            });
        });
        $table.on('page-change.bs.table',function(e) {
            $('.page-list').find('button').attr('data-bs-toggle','dropdown');
        }); 
        let $div = $('div.bootstrap-table.bootstrap4').removeClass('bootstrap4').addClass('bootstrap5');
        $('.page-list').find('button').attr('data-bs-toggle','dropdown');
        if ( this.pageValue !== null && $table.bootstrapTable("getOptions").totalPages >= this.pageValue ) {
            $table.bootstrapTable('selectPage', this.pageValue);
        }
    }

    updateTableParams(event) {
        event.preventDefault();
        const destination = event.currentTarget.parentElement.href;
        const url = event.currentTarget.parentElement.dataset.url;
        const page = $(this.element).bootstrapTable('getOptions').pageNumber != null ? $(this.element).bootstrapTable('getOptions').pageNumber : 1;
        const pageSize = $(this.element).bootstrapTable('getOptions').pageSize != null ? $(this.element).bootstrapTable('getOptions').pageSize : 10;
        this.params = new URLSearchParams({
            page: page,
            pageSize: pageSize,
        });
        if ( this.sortName != null && this.sortOrder != null ) {
            this.params.append('sortName', this.sortName);
            this.params.append('sortOrder', this.sortOrder);
        }
        const returnUrl = url + '?' + this.params.toString();
        let params2 = new URLSearchParams({
            returnUrl: returnUrl,
        });
        document.location.href= destination + '?' + params2.toString(); 
    }
}
     
