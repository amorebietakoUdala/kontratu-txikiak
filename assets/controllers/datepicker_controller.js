import { Controller } from '@hotwired/stimulus';

//import $ from 'jquery';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.eu.js';

export default class extends Controller {
   static values = {
      selector: String,
      startView: String,
      locale: String,
   }

   connect() {
      const options = {
         autoclose: true,
         format: "yyyy-mm-dd",
         language: global.locale,
         weekStart: 1,
         startView: this.startViewValue,
     }
      $(this.selectorValue).datepicker(options);
      let placeholder = this.localeValue === 'es' ? "AAAA-MM-DD" : "UUUU-HH-EE";
      $(this.selectorValue).attr("placeholder",placeholder);
   }


}

