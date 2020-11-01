<?php

/*
 * Output all PHP errors to the browser. Comment out when ready for production.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 * Technically we don't have to instantiate the $module object, since the EM loader does that for us.
 * However, doing so helps phpStorm with code checking.
 */
$module = new Yale\DOMCovXplorations\DOMCovXplorations();

/*
 * Builds a complete REDCap UI around the plugin.
 */
$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

?>

<!-- process and load the base JS library -->

<script><?= $module->getJs() ?></script>

<div id="domcovx-canvas">

   <div id="domcovx-ribbon" class="domcovx-section">

      <div class="domcovx-float-row">
         <div class="domcovx-float-left">TL</div>
         <div class="domcovx-float-right">TR</div>
      </div>

      <div class="domcovx-float-row">
         <p>
            Welcome to the DOM-CovXplorations page. I am using this page to work out layout and style options.
            The layout follows that proposed for EMs 2 and 3.
         </p>
         <p>
            The background colors are just so that the boundaries of the sections can be seen.
            The sections will all have the same background color (transparent) once we start coding for real.
         </p>
      </div>

   </div>

   <div id="domcovx-hsection-left" class="domcovx-hsection domcovx-section">

      <div class="domcovx-float-row">
         <div class="domcovx-float-left">TL</div>
         <div class="domcovx-float-right">TR</div>
      </div>

      <div class="domcovx-float-row">
         This section will contain major project-related functions not related to the selection list to the right.
         For example: sync features, detailed project metrics.
      </div>

   </div> <!-- div#domcovx-hsection-left -->

   <div id="domcovx-hsection-center" class="domcovx-hsection domcovx-section">

      <div class="domcovx-float-row">
         <div class="domcovx-float-left">TL</div>
         <div class="domcovx-float-right">TR</div>
      </div>

      <div class="domcovx-float-row">
         <p>This 'galley' will mainly contain controls for managing the list to the right.</p>
         <p>(maybe too squashed?)</p>
      </div>

      <div class="domcovx-float-row">
         <div class="domcovx-vertical-radio-group">
            <div class="domcovx-vertical-radio-group-label">sort by:</div>
            <div class="domcovx-vertical-radio-group-options">
               <label><input type="radio" name="domcovx-sort-options" value="name" checked />variable name</label><br />
               <label><input type="radio" name="domcovx-sort-options" value="domain" />domain</label><br />
               <label><input type="radio" name="domcovx-sort-options" value="var_number" />variable #</label><br />
            </div>
         </div>
      </div>

   </div> <!-- div#domcovx-hsection-left -->

   <div id="domcovx-hsection-right" class="domcovx-hsection domcovx-section">

      <div class="domcovx-float-row">
         <div class="domcovx-float-left">TL</div>
         <div class="domcovx-float-right">TR</div>
      </div>

      <div class="domcovx-float-row">
         Below is a scrollable content box, which is a div having the 'domcovx-scrollable-content' class.
         It takes up the entire width of the container, and resizes vertically as the browser is resized.
         The vertical offset from the top of its container is preserved.
      </div>

      <div class="domcovx-float-row">
         <p>
            Below is a start for the DOM-CovX variable selection filter that we will implement for all three External Modules.
            The list is imported from the DOM-CovX data dictionary REDCap project using the REDCap API.
         </p>
         <p>
            In this version, comma-separated search terms are ANDed together in the matching logic.
            These data dictionary fields are searched: variable_name, variable_domain, pretty_name and short_description.
         </p>
         <p>
            If a search term is enclosed in square brackets a domain match is carried out.
            For example: '[labs],albumin' matches all variables in the labs domain having the term 'insulin'
            in any of the fields indicated above.
         </p>
      </div>

      <div class="domcovx-float-row">
         <form id="domcovx-search-form">
            <input type="text" id="domcovx-search-input" placeholder="Enter one or more search terms, separated by commas." />
            <button type="submit" class="domcovx-search-button">SEARCH <span class="fa fa-search"></span></button>
         </form>
      </div>

      <div class="domcovx-scrollable-content-header">

         <div class="domcovx-listrow-description-3 domcovx-selection-summary">
            DESCRIPTION OF THIS HERE LIST
         </div>

         <div class="domcovx-listrow-options-3">
            <div class="domcovx-listrow-options-input">
               requested
            </div>
            <div class="domcovx-listrow-options-input">
               approved
            </div>
            <div class="domcovx-listrow-options-input">
               declined
            </div>
         </div>

      </div>

      <div id="domcovx-selections" class="domcovx-scrollable-content"></div>

   </div> <!-- div#domcovx-hsection-right -->

   <!-- messages feature -->
   <div id="domcovx-messages" class="domcovx-draggable">

      <div class="domcovx-float-row">
         <div class="domcovx-float-left">TL</div>
         <div class="domcovx-float-right">TR</div>
      </div>

      <div class="domcovx-float-row">
         <p>
            The MESSAGES feature will be rendered in this box, perhaps by a trait or even a small auxiliary EM shared by EMs 2 and 3.
         </p>
         <p>This is a draggable element, just for fun.</p>
      </div>

   </div>


</div> <!-- domcovx-canvas -->

