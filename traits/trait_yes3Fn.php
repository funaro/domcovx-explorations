<?php
/*
 * Version 1.1.0 August 2020. Rewritten to use native REDCap functions throughout.
 */

/*
 * NAMESPACE: it seems that trait files must be edited to match the EM namespace
 */
namespace Yale\DOMCovXplorations;

/*
 * Table to hold debug log messages. Must be created by dba, see logDebugMessage() below.
 */
define('DEBUG_LOG_TABLE', "ydcclib_debug_messages");

trait ye3Fn {

   // calls the generic REDCap query function, which is located in Config/init_functions.php
   // db_query returns mysqli_query() on success, or triggers a fatal redcap fail
   public function runQuery($sql) {
      return db_query($sql);
   }

   // like runQuery, but returns identity value
   public function runInsertQuery($sql) {
      $stmt = db_query($sql);
      if ($stmt == false) {
         return 0;
      } else {
         return db_insert_id();
      }
   } // runInsertQuery

   private function sql_limit_1( $sql ){
      if ( stripos($sql, "LIMIT 1") === false ) {
         return $sql . " LIMIT 1";
      } else {
         return $sql;
      }
   }

   public function fetchValue($sql) {
      $stmt = db_query( $this->sql_limit_1($sql) );
      if ( !$stmt ){
         return null;
      } else {
         $x = mysqli_fetch_array($stmt, MYSQLI_NUM);
         if ( !$x ) return null;
         else return $x[0];
      }
   }

   public function fetchRecord($sql) {
      $r = array();
      $stmt = db_query( $this->sql_limit_1($sql) );
      if ($stmt) {
         $r = db_fetch_assoc($stmt);
         db_free_result($stmt);
      }
      return $r;
   }

   public function fetchRecords($sql) {
      $r = array();
      $stmt = db_query($sql);
      if ($stmt) {
         while ($row = db_fetch_assoc($stmt)) {
            $r[] = $row;
         }
         db_free_result($stmt);
      }
      return $r;
   }

   /*
    * The q_ functions return escaped and quoted strings suitable for queries.
    * Date and Time formats are enforced, and "null" is returned for zero-length arguments.
    */

   public function sql_string($x) {
      if (strlen($x) == 0) {
         return "null";
      } else if (is_numeric($x)) {
         return "'" . $x . "'";
      } else {
         return "'" . db_real_escape_string($x) . "'";
      }
   }

   public function sql_datetime_string($x) {
      if (!$x) {
         return "null";
      } else {
         return "'" . strftime("%F %T", strtotime($x)) . "'";
      }
   }

   public function sql_date_string($x) {
      if (!$x) {
         return "null";
      } else {
         $d = strtotime($x);
         // if this didn't work, could be due to mm-dd-yyyy which doesn't fly
         if (!$d) {
            $date = str_replace('-', '/', $x);
            $d = strtotime($date);
         }
         if ($d) {
            return "'" . strftime("%F", $d) . "'";
         } else {
            return "null";
         }
      }
   }

   public function sql_timestamp_string() {
      return "'" . strftime("%F %T") . "'";
   }

   public function tableExists($table_name){
      $dbname = $this->fetchValue("SELECT DATABASE() AS DB");
      if ( !$dbname ) return false;
      $sql = "SELECT COUNT(*) FROM information_schema.tables"
            ." WHERE table_schema=".$this->sql_string($dbname)
            ." AND table_name=".$this->sql_string($table_name)
            ;
      return $this->fetchValue($sql);
   }

   /*
    * LOGGING DEBUG INFO
    * Call this function to log messages intended for debugging, for example an SQL statement.
    * The log database must exist and its name stored in the DEBUG_LOG_TABLE constant.
    * Required columns: project_id(INT), debug_message_category(VARCHAR(100)), debug_message(TEXT).
    * (best to add an autoincrement id field). Sample table-create query:
    *

         CREATE TABLE ydcclib_debug_messages
         (
             debug_id               INT AUTO_INCREMENT PRIMARY KEY,
             project_id             INT                                 NULL,
             debug_message_category VARCHAR(100)                        NULL,
             debug_message          TEXT                                NULL,
             debug_timestamp        TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP
         );

    */

   public function logDebugMessage($project_id, $msg, $msgcat="") {

      if ( !$this->tableExists(DEBUG_LOG_TABLE) ) return false;

      $sql = "INSERT INTO `".DEBUG_LOG_TABLE."` (project_id, debug_message, debug_message_category) VALUES ("
         .$this->sql_string($project_id).","
         .$this->sql_string($msg).","
         .$this->sql_string($msgcat)
         .");";

      return $this->runQuery($sql);
   }

}
