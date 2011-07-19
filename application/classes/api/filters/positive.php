<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Keyston
 * Date: 7/18/11
 * Time: 2:45 AM
 */

    class Api_Filters_Positive extends Api_Filters_Base
    {

        public function test($doc)
        {
            return $doc['score'] >= 4;
        }

        public function name($doc)
        {
            return "Positive";
        }

        public function key($doc)
        {
            return "positive";
        }
          public function kind()
        {
            return "status";
        }
    }
