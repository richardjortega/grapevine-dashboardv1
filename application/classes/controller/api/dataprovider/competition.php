<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Keyston
 * Date: 7/22/11
 * Time: 8:32 AM
 */

    class Controller_Api_DataProvider_Competition extends Controller_Api_DataProvider_Base
    {


        public function action_index()
        {
            $fetcher = new Api_Fetchers_Ogsi($this->mongo, $this->location);
            $score = $fetcher->competition(array(2, 3, 4))->range($this->startDate, $this->endDate)->fetch();
            $this->apiResponse['ogsi']=$score;

           


        }
    }
