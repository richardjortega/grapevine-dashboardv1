<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Keyston
 * Date: 7/18/11
 * Time: 7:21 AM
 */

    class Controller_Api_DataProvider_ScoreBoard extends Controller_Api_DataProvider_Base
    {


        public function fetch_overall()
        {
            $reviews = $this->db->selectCollection('metrics');
            $date = new MongoDate(mktime(0, 0, 0, 1, 1, 1970));
            $dist = new Api_Fetchers_Distribution($this->mongo, array($this->location));
            $doc = $dist->range($date)->period('overall')->fetch();

            $ogsi = new Api_Fetchers_Ogsi($this->mongo, $this->location);
            // TODO keyston : fetch locations compentition from mysql
            $ogsi->competition(array(2, 3, 4))->range($date)->period('overall');

            return array(
                'ogsi' => $ogsi->fetch(),
                'rating' => $doc,

                'reviews' => $doc['count']

            );


        }

        public function action_overall()
        {
            $this->apiResponse = array('overall' => $this->fetch_overall());
        }

        public function action_current()
        {
            $this->apiResponse = array('current' => $this->fetch_current());
        }

        public function action_index()
        {
            $this->apiResponse = array(
                'overall' => $this->fetch_overall(),
                'current' => $this->fetch_current()
            );

        }

        public function fetch_current()
        {
            /*
            $reviews = $this->db->selectCollection('metrics');


            $map
                    = "function(){
                        if(typeof this.aggregates[$this->location] !='undefined'){
                            emit($this->location,this.aggregates[$this->location]);
                        }
                    }";

            $reduce
                    = 'function(key,values){
                        var results={negative:0,positive:0,neutral:0,points:0,count:0};
                        values.forEach(function(value){
                                for(var type in value){
                                 results[type]+=value[type];
                                }
                          });

                        return results;
                    }';
            $finalize
                    = 'function(key,results){
                    results.score = (results.points/results.count).toFixed(1);
                    return results;
            }';
            $results = $this->db->command(
                array(
                    'mapreduce' => 'metrics',
                    'query'
                    => array(
                        'type' => 'scoreboard',
                        'date'
                        => array(
                            '$gte' => new MongoDate(mktime(0, 0, 0, 1, 1, 1970)), '$lte' => $this->endDate
                        ),

                        'period' => 'day'
                    ),
                    'map' => $map,
                    'reduce' => $reduce,
                    'out' => array('inline' => TRUE),
                    'finalize' => $finalize
                )
            );

            // fetch single results
            $result = $results['results'][0];*/
            $dist = new Api_Fetchers_Distribution($this->mongo, array($this->location));
            $dist->range($this->startDate, $this->endDate);

            $values = $dist->fetch();


            $ogsi = new Api_Fetchers_Ogsi($this->mongo, $this->location);
            // TODO keyston : fetch locations compentition from mysql
            $ogsi->competition(array(2, 3, 4))->range($this->startDate, $this->endDate);
            $response = array(
                'ogsi' => $ogsi->fetch(),
                'rating' => $values,
                'reviews' => $values['count']

            );


            return $response;
        }
    }
