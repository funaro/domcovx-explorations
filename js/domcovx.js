/*
 * DOM-CovX JS library
 * Version 0.0.1 October 2020
 */

/*
 * The tags below are replaced with their values by the EM getJs() loader
 */
var domcovx = {
   padding: 10,
   canvas_min_height: 700,
   scrollbar_width: 20,
   cssUrl: 'DOMCOVX_CSS_URL',
   serviceUrl: 'DOMCOVX_SERVICE_URL',
   username: 'DOMCOVX_USERNAME',
   project_id: DOMCOVX_PROJECT_ID,
   super_user: DOMCOVX_SUPER_USER
};

// https://stackoverflow.com/questions/574944/how-to-load-up-css-files-using-javascript
// only attach if not already found in the DOM

(function() {
   var cssId = 'domcovx-stylesheet';
   if (!document.getElementById(cssId)) {
      var head = document.getElementsByTagName('HEAD')[0];
      var link = document.createElement('link');
      link.rel = 'stylesheet';
      link.type = 'text/css';
      link.id = cssId;
      link.href = domcovx.cssUrl;
      head.appendChild(link);
   }
})();

domcovx.addListeners = function() {

   /*
    * radio toggle (allows for reset to all unselected)
    */
   $('div.domcovx-listrow-options-input').on('click', function () {

      var radio = $(this).children('input').first();

      radio.prop('checked', !radio.prop('checked') );

   });

   /*
    * sort options radio group
    */
   $('input[type=radio][name=domcovx-sort-options]').on('click', function () {
      domcovx.requestListContent();
   });

   /*
    * The search bar is implemented as a form, mainly to take advantage of form submit behavior
    * ( e.g., submit triggered by hitting Enter, so no additional keypress handlers needed ).
    *
    * The routine below handles the form-submit by issuing an AJAX call. The page is NOT refreshed.
    */
   $('form#domcovx-search-form').submit(function (event) {

      var params = {
         request: "getListDataFilteredBySearchTerms",
         search_terms: $('input#domcovx-search-input').val(),
         sort: $('input[name=domcovx-sort-options]:checked').val()
      }

      /*
       * Call our AJAX requester function
       */
      domcovx.requestListContent();

      //console.log(params);

      /*
       * Stop the form from submitting the normal way and refreshing the page
       */
      event.preventDefault();

   });


   /*
    * Use jQuery-UI (provided by REDCap) to provide dragging support
    * to any element having the domcovx-draggable class.
    */
   $('.domcovx-draggable').draggable();

}; // addListeners

domcovx.installMessageFeature = function() {

   /*
    * position the message div at the bottom left.
    */
   $('div#domcovx-messages')
      .css({'position':'absolute', 'left':'0', 'top':'auto', 'bottom':'var(--domcovx-padding)'})
   ;

}

/*
 * Load the content list
 */

domcovx.requestListContent = function() {

   var params = {
      request: "getListData",
      sort: $('input[name=domcovx-sort-options]:checked').val(),
      search_terms: $('input#domcovx-search-input').val(),
   }

   domcovx.requestService( params, domcovx.buildListContent, true );

};

domcovx.buildListContent = function( data ) {
   //console.log( data );
   var html = "";
   if ( !data.length ) {
      html = "<div class='domcovx-list-bupkis'>No variables were selected.</div>";
   } else {
      for (var i = 0; i < data.length; i++) {

         html +=
            "<div class='domcovx-listrow'>" +
            "<div class='domcovx-listrow-description-3'>" +
            "<span class='domcovx-list-field_name'>" + data[i].variable_name + "</span>" + " " +
            "<span class='domcovx-list-domain'>" + data[i].variable_domain + "</span>" + " " +
            "<span class='domcovx-list-element_label'>" + data[i].pretty_name + "</span>" + " " +
            "<span class='domcovx-list-description'>" + data[i].short_description + "</span>" +
            "</div>" +
            "<div class='domcovx-listrow-options-3'>" +
            "<div class='domcovx-listrow-options-input'><input type='radio' name='option-" + i + "' id='option-" + i + "-requested' value='requested' /></div>" +
            "<div class='domcovx-listrow-options-input'><input type='radio' name='option-" + i + "' id='option-" + i + "-approved' value='approved' /></div>" +
            "<div class='domcovx-listrow-options-input'><input type='radio' name='option-" + i + "' id='option-" + i + "-declined' value='declined' /></div>" +
            "</div>" +
            "</div>\n"
         ;
      }

   }

   /*
    * update the list content
   */
   $('div#domcovx-selections').html( html );

   /*
    * update the selection summary notification(s)
   */
   $('div.domcovx-selection-summary').html( data.length + " DOM-CovX Variable(s)" );


}

domcovx.requestService = function( params, doneFn, json ) {

   json = json || false;

   var request = $.ajax({
      url: domcovx.serviceUrl,
      type: "POST",
      dataType: ( json ) ? "json":"html",
      data: params
   }).done(
      doneFn
   ).fail(function(jqXHR, textStatus, errorThrown) {
      console.log(jqXHR);
      alert('AJAX error: ' + errorThrown);
   });

}

domcovx.wResize = function() {

   var WH = $(window).height();
   var WW = $(window).width();
   var canvas_height = (WH < 700+domcovx.scrollbar_width) ? 700 : WH;

   $('div#domcovx-canvas').height( canvas_height );

   $('div#domcovx-hsection-center')
      .css( 'left', domcovx.padding + $('div#domcovx-hsection-left').width() + 'px' );

   $('div#domcovx-hsection-right')
      .css( 'left', 2*domcovx.padding + $('div#domcovx-hsection-left').width() + $('div#domcovx-hsection-center').width() + 'px');

   $("div.domcovx-hsection").each(function () {

      $(this).height( canvas_height - $(this).position().top - domcovx.padding );

   });

   $("div.domcovx-scrollable-content").each(function () {

      $(this).height( $(this).parent().height() - $(this).position().top - 4 );

   });

   $('div#center').css('padding-bottom', '0');

}

// Hope we don't ever need this function!
// There are some latency issues regarding the css attachment and load.
domcovx.resizeOrDieTrying = function() {

   domcovx.wResize();

   var P = $('div#domcovx-hsection-right').position().left;

   console.log( 'resizeOrDieTrying', P );

   if ( P < 100 || P > 1000 ) {
      setTimeout(domcovx.resizeOrDieTrying, 100); // check again in 100ms
   }

}

$(window).resize(function () {

   domcovx.wResize();

});

$(document).ready(function () {

   domcovx.addListeners();

   $('div#subheader').remove();

   setTimeout(domcovx.wResize, 200); // just this once, a 200ms delay for the css to attach and load

   domcovx.installMessageFeature(); // install message support, if the message div is defined

   domcovx.requestListContent(); // populate the content list

});


