<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Keyston
 * Date: 7/18/11
 * Time: 2:04 AM
 */

    class Controller_Api_DataProvider_Base extends Controller
    {
        /**
         * @var
         * */
        protected $apiResponse;
        protected $apiRequest;
        protected $filterResponse = array();
        protected $activeFilters = array();
        protected $filterEnabled = false;
        /**
         * @var Mongo
         */
        protected $mongo;
        /**
         * @var MongoDB
         */
        protected $db;

        /**
         * @var Api_Filters_Base[]
         */
        protected $filters;

        /**
         * @var String
         */
        protected $id;

        protected $startDate;
        protected $endDate;


        public function before()
        {
            parent::before();
            $this->filters = array(
                new Api_Filters_Neutral(),
                new Api_Filters_Positive(),
                new Api_Filters_Negative(),
                new Api_Filters_Alert(),
                new Api_Filters_Flagged(),
                new Api_Filters_Completed()

            );

            $this->id = $this->request->query('id');
            $range = $this->request->post('range');
            if (!empty($range)) {
                Session::instance()->set(
                    'viewingRange', $range
                );
            } else {
                $range = array('date' => 'now', 'period' => '1m');
            }
            $period = $range['period'];
            switch ($period) {
            case '1m':
                $period = "+1 month";
                break;
            case '3m':
                $period = "+3 months";
                break;
            case '6m':
                $period = "+6 months";
                break;
            case '1y':
                $period = "+1 year";
                break;
            default:
                $period = "+1 month";
            }
            $start = strtotime($range['date']);
            $this->startDate = new MongoDate($start);
            $this->endDate = new MongoDate(strtotime($period, $start));

            //
            $filters = $this->request->post('filters');
            $this->activeFilters = array();
            if (isset($filters['source'])) {
                $this->activeFilters = $filters['source'];
            }
            if (isset($filters['status'])) {
                $this->activeFilters = array_merge($this->activeFilters, $filters['status']);
            }
            $this->filterEnabled = count($this->activeFilters) ? true : false;


            //
            $this->query = array('date' => array('$gte' => $this->startDate, '$lte' => $this->endDate));


            $this->mongo = new Mongo();
            $this->db = $this->mongo->auto;


        }

        

        public function after()
        {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($this->apiResponse));
            parent::after();
        }


        

        protected function matches_filter($doc)
        {

            $allow = false;
            foreach (
                $this->filters as $filter
            ) {

                $name = $filter->name();
                if (!isset($this->filterResponse[$name])) {
                    $this->filterResponse[$name] = array(
                        'total' => 0,
                        'value' => $filter->key(),
                        'active' => isset($this->activeFilters[$filter->key()])
                    );
                }
                if ($filter->test($doc)) {

                    $this->filterResponse[$name]['total']++;
                    if ($this->filterEnabled) {
                        $allow = true;
                    }
                }
            }

            return $this->filterEnabled ? $allow : true;

        }


        /**
         * @param $name
         * @param $query
         * @param $fields
         * @param $limit
         * @return MongoCursor
         */
        protected function query($name, $query, $fields, $limit = -1)
        {
            $collection = new MongoCollection($this->mongo->selectDB('auto'), $name);
            $cursor = $collection->find($query, $fields);
            if ($limit) {
                $skip = 1; //intval($this->request->post('page', 1));
                $skip = ($skip - 1) * $limit;
                $cursor->limit($limit)->skip($skip);
            }
            return $cursor;
        }
    }