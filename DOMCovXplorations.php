<?php

namespace Yale\DOMCovXplorations;

use ExternalModules\AbstractExternalModule;

/*
 * Load any traits or classes here.
 *
 * Note that while external classes can't be instantiated within the EM class,
 * loading them here makes them available for any PHP script running in the EM (i.e. plugins).
 * Also, static methods in loaded classes can be used by the EM class without instantiating.
 */
require_once "traits/trait_yes3Fn.php";

class DOMCovXplorations extends AbstractExternalModule  {

   public $project_id = null;
   public $username = USERID; // a REDCap constant; see redcap_info() output on the dev doc page
   public $super_user = SUPER_USER; // ditto
   public $server_name = SERVER_NAME; // ditto
   public $emRoot = "";
   public $jsUrl = "";
   public $serviceUrl = "";
   public $cssUrl = "";

   // PHP 'use' mechanism for traits:
   use ye3Fn;

   public function __construct() {

      parent::__construct(); // call parent (AbstractExternalModule) constructor

      $this->project_id = $this->getProjectId(); // defined in AbstractExternalModule; will return project_id or null

      $this->emRoot = $this->getUrl(""); // EM code location
      $this->serviceUrl = $this->getUrl("services/domcovx_services.php?pid={$this->project_id}");
      $this->cssUrl = $this->getUrl("css/domcovx.css");
      $this->jsUrl = $this->getModulePath()."js/domcovx.js";

   } // __construct

   public function getJs() {

      $js = file_get_contents($this->jsUrl);
      $js = str_replace('DOMCOVX_USERNAME', $this->username, $js);
      $js = str_replace('DOMCOVX_SUPER_USER', $this->super_user, $js);
      $js = str_replace('DOMCOVX_PROJECT_ID', $this->project_id, $js);
      $js = str_replace('DOMCOVX_CSS_URL', $this->cssUrl, $js);
      $js = str_replace('DOMCOVX_SERVICE_URL', $this->serviceUrl, $js);

      return $js;
   }


   // show or hide links
   function redcap_module_link_check_display($project_id, $link) {

      //if ( $link['name']==="sim" ) return false;

      // no rules yet; always show
      return $link;
   } // redcap_module_link_check_display


}