<?php

/*
 * DOMCovX SERVICES
 * Version 0.0.1 October 2020
 *
 * Processes AJAX calls from DOM-CovX clients
 *
 * Required POST parameter: 'request', others as needed by request.
 */

/*
 * Output all PHP errors to the browser. Comment out when ready for production.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 * Technically we don't have to instantiate the $module object, since the EM loader does that for us.
 * However, doing so helps phpStorm with code checking.
 *
 * The following statement can be commented out when ready for deployment.
 */
$module = new Yale\DOMCovXplorations\DOMCovXplorations();

$request = $_POST['request']; // always passed

if ( $request === "getListData"  ) exit(domcovx_getMetaDataSelections());
// elseif ( $request === "foo" ) return bar();
// ...
else exit("go away");

/*
 * domcovx_getMetaDataSelections
 *
 * Fetches, filters and sorts the DOM-CovX metadata
 * according to POST params 'search_terms' and 'sort'.
 */
function domcovx_getMetaDataSelections(){
   global $module;

   $sort = $_POST['sort'];

   /*
    * get the metadata via API call
    */

   $host = $module->getProjectSetting('metadata-project-host');
   $token = $module->getProjectSetting('metadata-project-apitoken');

   $params = array(
      'token' => $token,
      'content' => 'record',
      'format' => 'json',
      'fields' => ['variable_name','pretty_name','data_type','short_description','variable_domain']
   );

   /*
    * The second param requests that results be returned as assoc arrays not stdclass objects.
    * (json_encode converts assoc arrays to JSON objects)
    */
   $xx = json_decode( REDCapAPI($host, $params), true );

   if ( $_POST['search_terms'] ) {

      $search_terms = explode(",", $_POST['search_terms']);

      $n_search_terms = count( $search_terms );

      $search_result = [];

      foreach ($xx as $x) {

         $string = implode("|", $x);

         // AND logic for now
         $n_matches = 0;
         foreach ($search_terms as $search_term) {
            if ( substr($search_term,0,1) === "[" ) {
               if ( strtolower(substr($search_term, 1, strlen($search_term)-2) ) === strtolower($x['variable_domain']) ) {
                  $n_matches++;
               }
            }
            elseif (stripos($string, $search_term) !== false) {
               $n_matches++;
            }
         }
         $selected = ( $n_matches === $n_search_terms );

         if ($selected) $search_result[] = $x;
      }

      return domcovx_sortVariableSelectionsToJSON( $sort, $search_result );

   } else {

      return domcovx_sortVariableSelectionsToJSON( $sort, $xx );

   }

}

/*
 * domcovx_sortVariableSelectionsToJSON
 *
 * Takes the sort spec and an array of variable selections and
 * uses the bizarre array_multisort() to order on one or two columns.
 *
 * See: http://www.nusphere.com/kb/phpmanual/function.array-multisort.htm
 *
 * sort - a string identifying the sort order, as passed from the UI
 *        currently: 'name', 'field_order' or 'domain'
 * a - the array of results to be sorted
 *
 * returns JSON string
 */
function domcovx_sortVariableSelectionsToJSON( $sort, & $a ) {

   // no sort if field order requested.. I presume the API returns in this order?
   if ( $sort !== 'var_number' ) {

      if ($a) {

         $key1column = null;
         $key2column = null;

         if ($sort === "domain") {
            $key1column = array_column($a, 'variable_domain');
            $key2column = array_column($a, 'variable_name');
         } else {
            $key1column = array_column($a, 'variable_name');
         }

         /*
          * Two or three arrays are "multisorted": one or two columns individually extracted from the full array,
          * and the full array itself. Multisort has a kind of cascading behavior in which each
          * array is first rearranged according to the sorted order of the preceding array, matched using row keys assigned
          * before sorting begins. Then any rows having duplicate keys in the preceding array are sorted
          * in the current array according to its key. And so on. In this way the last array (the full array) is sorted
          * on two keys. Not sure how the full array is sorted for rows having dupe keys in the last but one array -
          * perhaps the first column? The pre-assigned row key (ie, no sort)?
          */
         if ($key2column) {
            $rc = array_multisort($key1column, SORT_ASC, $key2column, SORT_ASC, $a);
         } else {
            $rc = array_multisort($key1column, SORT_ASC, $a);
         }

         if (!$rc) exit('multisort bombed');

      }

   }

   return json_encode( $a );
}

/*
 * REDCapAPI
 *
 * calls the REDCap API and returns the response
 * host - the REDCap host, e.g. https://redcap.trantor.gov
 * params - parameters as required by the API
 *
 */
function REDCapAPI( $host, $params ){

   $url = $host . "/api/";

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_VERBOSE, 0);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($ch, CURLOPT_AUTOREFERER, true);
   curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
   curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
   curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

   $response = curl_exec($ch);

   if (curl_errno($ch)) {
      $error_message = curl_error($ch);
   }

   curl_close($ch);

   if ( $error_message ) exit( $error_message );

   return $response;
}

/*
 * printablesOnly
 *
 * replaces unprintables with spaces, removes tags, other stuff
 * supposedly works on UTF-8 as well as ASCII
 * https://stackoverflow.com/questions/1176904/php-how-to-remove-all-non-printable-characters-in-a-string
 */
function printablesOnly($s) {
   $s = preg_replace('/\n/', ';', trim(strip_tags($s)));
   $s = preg_replace('/[\x00-\x1F\x7F-\xFF]/u', ' ', $s);
   $s = preg_replace('/\s+/', ' ', $s); // removes multiple whitespaces
   $s = preg_replace('/\"/', '\'', $s); // double quotes->single
   return( $s );
}
