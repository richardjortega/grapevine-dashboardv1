<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Keyston
 * Date: 7/21/11
 * Time: 5:45 PM
 */

    class Api_Fetchers_Metrics implements Iterator
    {

        /**
         * @var Mongo
         */
        private $mongo;
        private $_type;
        private $_date;
        private $_location;
        private $db = 'auto';
        private $_period = 'day';
        private $_single = false;
        /**
         * @var MongoCursor
         */
        private $_cursor;

        function __construct(Mongo $mongo, $db = 'auto')
        {
            $this->mongo = $mongo;
            $this->db = $db;
        }

        public function type($type)
        {
            $this->_type = $type;
            return $this;
        }

        public function range(MongoDate $start, MongoDate $end = null)
        {
            if (is_null($end)) {
                $this->_date = $start;
            } else {
                $this->_date = array('$gte' => $start, '$lte' => $end);

            }
            return $this;
        }

        public function single($value)
        {
            $this->_single = $value;
            return $this;
        }


        public function location($id)
        {
            $this->_location = $id;
            return $this;
        }

        public function period($period)
        {
            $this->_period = $period;
        }

        public function fetch()
        {
            $metrics = $this->mongo->selectDB($this->db)->selectCollection('metrics');


            if ($this->_single) {

            } else {
                $this->_cursor = $metrics->find(
                    array(
                        'type' => $this->_type,
                        'date' => $this->_date,
                        'period' => $this->_period
                    ), array("aggregates.{$this->_location}" => 1)
                );
            }

        }

        public function count()
        {
            return $this->call('count');
        }

        public function rewind()
        {

            $this->call('rewind');

        }

        public function current()
        {
            return $this->call('current');


        }

        public function key()
        {
            return $this->call('key');

        }

        public function next()
        {
            return $this->call('next');

        }

        public function valid()
        {
            return $this->call('valid');
        }

        private function call($method)
        {
            if (is_null($this->_cursor)) {
                $this->fetch();
            }
            $val = call_user_func(array($this->_cursor, $method));
            if (is_array($val) && isset($val['aggregates'])) {
                return $val['aggregates'][$this->_location];
            }
            return $val;

        }
    }