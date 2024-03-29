<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Keyston
 * Date: 8/12/11
 * Time: 3:08 PM
 */
define('WUFOO_HANDSHAKE', 'hello');
class Controller_Webhooks_WuFoo extends Controller
{


    private $post;

    private function values($mapping)
    {
        $values = array();
        foreach (
            $mapping as $key
            => $map
        ) {
            if (isset($this->post[$map])) {
                $values[$key] = $this->post[$map];
            }
        }
        return $values;
    }

    public function before()
    {
        set_time_limit(0);
        Log::$write_on_add = true;
        parent::before();
    }

    private function remap_post()
    {
        $structure = $this->request->post('FieldStructure');
        $structure = json_decode($structure);
        $post = array();
        foreach (
            $structure->Fields as $field
        ) {
            // check if competitor string
            $is_competitor = strpos($field->Title, 'Competitor #') !== FALSE;
            if (property_exists($field, 'SubFields')) { // sub fields, first, last names and address

                if ($is_competitor) {
                    // get number
                    $competitor_number = intval(preg_replace('/[^\d]+/i', '', $field->Title));
                }
                foreach (
                    $field->SubFields as $sub
                ) {
                    // starting to hate this shit, remap
                    $label = str_replace('Street', 'Address', $sub->Label);
                    if ($is_competitor) {
                        $label = "Competitor #$competitor_number $label";
                    }
                    $post[$label] = trim($this->request->post($sub->ID));

                }
            } else {
                /*$is_competitor ? $field->Title . ' Name' :*/
                $title = $field->Title;
                $post[$title] = trim($this->request->post($field->ID));
            }
            if ($field->Title == 'Company Address') {
                $post['Company Address'] = $post['Address'];
            }
            if ($field->Title == 'Almost there....') {
                break;
            }

        }
        $this->post = $post;
    }

    private function failed($phase, $error)
    {
        $errors = array();
        if ($error instanceof  Expection) {
            if ($error instanceof ORM_Validation_Exception) {
                $errors = $error->errors();
            } else {
                $errors = array('message' => $error->getMessage());

            }
        } elseif (is_array($error)) {
            $errors = $error;
        }

        $log = Log::instance();
        $log->add(
            Log::ALERT, array(
                'type' => 'wufoo',
                'phase' => $phase,
                'errors' => $errors
            )
        );
    }

    public function action_dump()
    {
        Log::instance()->add(
            Log::DEBUG, "Wufoo :post", array(
                "post" => json_encode($this->request->post())
            )
        );

    }

    public function action_automotive()
    {
        $post = json_decode(file_get_contents(dirname(__FILE__) . '/wufoo.test'), true);
        $this->request->post($post);
        $this->remap_post();
        $this->action_index();
    }

    public function action_zinc()
    {
        $post = json_decode(file_get_contents(dirname(__FILE__) . '/zinc.test'), true);
        // $request = Request::factory('http://staging.pickgrapevine.com/webhooks/wufoo');
        //$response = $request->post($post)->execute();
        //print_r($response);
        $this->request->post($post);

        $this->action_index();
    }

    public function action_restaurant()
    {
        $post = json_decode(file_get_contents(dirname(__FILE__) . '/zinc.test'), true);

        $this->request->post($post);
        //       $this->remap_post();
        $this->action_location();
    }

    public function action_company()
    {

        //  $post=json_decode(file_get_contents(dirname(__FILE__) . '/file'), true);
        //$this->request->post($post);
        $this->remap_post();


        $user_mapping = array(
            'username' => 'Account User Name',
            'password' => 'Password',
            'email' => 'Email',
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'phone' => 'Phone Number'
        );
        $db = Database::instance();
        $db->begin();

        try {

            // create company user
            $user = ORM::factory('user');
            $values = $this->values($user_mapping);

            $user->values($values);

            $user->save();
            // add login  role
            $user->add('roles', ORM::factory('role', array('name' => 'login')));
            $user->add('roles', ORM::factory('role', array('name' => 'company_owner')));
        } catch (Exception $e) {

            $this->failed('company.user_creation', $e);
            $db->rollback();
            return;
        }

        $company_mapping = array('name' => 'Company Name', 'email' => 'Email');

        try {

            // create company
            $company = ORM::factory('company');

            $company->values($this->values($company_mapping));
            $company->save();
            // add to companies_users
            $company->add('users', $user);

        } catch (Exception $e) {

            $this->failed('company_creation', $e);
            $db->rollback();
        }
        $db->commit();
        /* $company_mapping=array(
            'username'
        )*/
    }


    public function action_location()
    {

        //ini_set('mongo.default_host', '50.57.109.174');
        $this->remap_post();
        $this->action_dump();

        //        return;
        // $key = $this->request->post('HandshakeKey');
        //  if ($key != WUFOO_HANDSHAKE) {
        //      return;
        // }

        $user_mapping = array(
            'username' => 'Account User Name',
            'password' => 'Password',
            'email' => 'Location Email',
            'firstname' => 'First',
            'lastname' => 'Last',
            'phone' => 'Phone Number'
        );

        $db = Database::instance();
        $db->begin();

        try {

            // create company user
            $user = ORM::factory('user');
            $values = $this->values($user_mapping);

            $user->values($values);

            $user->save();
            // add login  role
            $user->add('roles', ORM::factory('role', array('name' => 'login')));
            $user->add('roles', ORM::factory('role', array('name' => 'location_owner')));
        } catch (Exception $e) {

            $this->failed('user_creation', $e);
            $db->rollback();
            return;
        }


        $company_values = $this->values(array('email' => 'Company Email'));


        $company = ORM::factory('company', array('email' => $company_values['email']));
        $company->add('users', $user);


        /*   try {

            // create company
            $company = ORM::factory('company');

            $company->values($this->values($company_mapping));
            $company->save();
            // add to companies_users
            $company->add('users', $user);
        } catch (Exception $e) {

            $this->failed('company_creation', $e);
            $db->rollback();
        }*/
        $location_mapping = array(

            'billing_method' => 'Confirm Payment Method?',
            'package' => 'Plan Package?',
            'billing_type' => 'Confirm Billing Type',

            'owner_name' => '',
            'owner_email' => 'Location Email',

            'owner_phone' => 'Phone Number',

            'industry' => 'Industry',
            'name' => 'Location Name',
            'address1' => 'Location Address',
            'address2' => 'Address Line 2',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip',
            'country' => 'Country',
            'phone' => 'Phone Number',
            'billable_email' => 'Billable Email'
        );
        $industry = '';
        $location_id = 0;
        try {

            // create location
            $location = ORM::factory('location');

            $values = $this->values($location_mapping);
            $values['industry'] = strtolower($values['industry']);

            $location_mapping['owner_name'] = $this->post['First'] . ' ' . $this->post['Last'];


            $industry = strtolower($values['industry']);
            $location->values($values);

            $location->company_id = $company->id;
            $location->save();
            $location_id = $location->id;
            // add to locations_users with level = 0
            $location->add('users', $user);

            // add to companies_locations
            $company->add('locations', $location);

            $company->save();


        } catch (Exception $e) {

            $this->failed('location_creation', $e);
            $db->rollback();
            return;
        }


        $industry_sites = array(
            'automotive'
            => array(

                'edmunds.com' => 'Edmunds Link',
                'dealerrater.com' => 'Dealer Rater Link'
            ),
            'common'
            => array(
                'judysbook.com' => 'Judy\'s Book',
                'yelp.com' => 'Yelp',
                'places.google.com' => 'Google Places',
                'citysearch.com' => 'CitySearch',
                'insiderpages.com' => 'InsiderPages',
                'local.yahoo.com' => 'Local.Yahoo.com (Yahoo! Local)',
                'superpages.com' => 'SuperPages',
                'yellopages.com' => 'YP.com (Yellow Pages)'


            ),
            'restaurant'
            => array(
                'urbanspoon.com' => 'Urbanspoon Link'
            )
        );

        $url_mapping = array_merge($industry_sites['common'], $industry_sites[$industry]);
        $url_values = $this->values($url_mapping);

        $this->add_to_queue($industry, $location_id, $url_values);
        $db->commit();


        // $this->failed('location_creation', $post);

        $dummy_user = ORM::factory('user', array('username' => 'grapevine'));
        if (!$dummy_user->loaded()) {
            // create dummy user
            $dummy_user->username = 'grapevine';
            $dummy_user->password = 'pickgrapevine2011';
            $dummy_user->email = 'dummy@grapevinebeta.com';
            $dummy_user->firstname = 'dummy';
            $dummy_user->lastname = 'user';
            $dummy_user->phone = '1111111111';
            $dummy_user->save();

        }


        $finder = new SiteFinder_Finder();
        $query = new SiteFinder_Query();
        $query->industry = $industry;

        $company = ORM::factory('company', array('name' => 'Unknown Company'));
        if ($company->loaded()) {
            $company->name = 'Unknown Company';
            $company->save();
        }


        for (
            $i = 1; $i <= 6; $i++
        ) {
            if (!$this->has_competitor($i)) {
                continue;
            }
            try {
                $competitor_values = $this->competitor_mapping($i, $industry);
                $competitor_location = ORM::factory('location', array('name' => $competitor_values['name']));
                $db->begin();
                if (!$competitor_location->loaded()) {
                    $query->zip = $competitor_values['zip'];
                    $query->state = $competitor_values['state'];
                    $query->city = $competitor_values['city'];
                    $query->address = $competitor_values['address1'];
                    $query->name = $competitor_values['name'];

                    // create a new company

                    // add to companies_users
                    // $company->add('users', $user);

                    // create location
                    $competitor_location = ORM::factory('location');


                    $competitor_places = Arr::get($competitor_values, 'places');
                    if (!empty($competitor_places)) {
                        unset($competitor_values['places']);
                    }
                    $competitor_location->values($competitor_values);
                    $competitor_location->save();
                    $sites = $finder->find($query);
                    $missing = Arr::get($sites, 'missing');
                    if ($missing) {

                        $this->failed(
                            'finding_competitor_sites',
                            array(
                                'location_id' => $competitor_location->id,
                                'missing_sites' => $missing,
                                'query' => (string)$query
                            )
                        );
                    }
                    if ($missing) {
                        unset($sites['missing']);
                    }
                    if (!empty($competitor_places)) {
                        $sites['places.google.com'] = array(
                            'url' => $competitor_places
                        );
                    }
                    if (count($sites)) {
                        $sites = array_map(create_function('$a', 'return $a["url"];'), $sites);
                        $this->add_to_queue($industry, $competitor_location->id, $sites);
                    }
                    // add to locations_users with level = 0
                    // $competitor_location->add('users', $dummy_user);

                    // add to companies_locations
                    //$company->add('locations', $competitor_location);
                    //$company->save();


                } else {
                    // TODO : make sure that the industry types are the same
                    //  $location->industry
                }
                $competitor = ORM::factory('location_setting')
                        ->values(
                    array(
                        'type' => 'competitor',
                        'value' => $competitor_location->id,
                        'location_id' => (int)$location_id
                    )
                )->create();
                $db->commit();
            } catch (Exception $e) {
                $this->failed("competitor #$i", $e);
                $db->rollback();

            }

        }
        $db->commit();


        $log = Log::instance();
        $log->add(
            Log::ALERT, array(
                'type' => 'wufoo',
                'phase' => 'finished',
                'message' => 'New Signup'

            )
        );

        echo "found";
    }

    private $_google_places
    = array(
        'Banner Chevrolet' => 'http://maps.google.com/maps/place?cid=7164700620406135697',
        'Bryan Chevrolet' => 'http://maps.google.com/maps/place?cid=3136380009086710021',
        'Levis Chevrolet Cadillac' => 'http://maps.google.com/maps/place?cid=1278532465134215193',
        'Hood Northlake Chevrolet' => 'http://maps.google.com/maps/place?cid=6484951478859394754',
        'Rainbow Chevrolet' => 'http://maps.google.com/maps/place?cid=11642145690285958729',
        'Mad Dogs British Pub‎' => 'http://maps.google.com/maps/place?cid=654048027772931410',
        'Fox and Hound' => 'http://maps.google.com/maps/place?cid=9043971452942647920',
        'Durty Nelly\'s Irish Pub' => 'http://maps.google.com/maps/place?cid=6152318583873696593',
        'Waxy Oconnors Irish Pub' => 'http://maps.google.com/maps/place?cid=11522522344775087955',
        'The Hangar' => 'http://maps.google.com/maps/place?cid=5339668012964186385',
        'Broadway 50 50' => 'http://maps.google.com/maps/place?cid=10437796302871203709',

    );

    private function get_google_places($name)
    {
        return Arr::get($this->_google_places, $name);
    }

    private function add_to_queue($industry, $location_id, $queue)
    {
        $queue_post = array(
            'industry' => $industry,
            'location' => $location_id, 'queue'
            => $queue

        );

        /**
         * @var $queue_response Response
         */
        
        $queue_response = Request::factory('webhooks/queue/add')
                ->post($queue_post)->execute();
        $queue_response = json_decode($queue_response->body(), true);

        if (count($queue_response['errors'])) {
            $this->failed('insert_into_queue', $queue_response['errors']);
            return false;
        }
        return true;
    }

    private function has_competitor($number)
    {

        $values = $this->values($this->get_competitor_mapping($number));

        $values = array_filter(array_map('trim', $values), 'strlen');
        $values = count($values);
        return $values >= 5;


    }

    private function competitor_mapping($number, $industry)
    {
        $mapping = $this->get_competitor_mapping($number);
        $extra = array(
            'billing_method' => 'unknown',
            'package' => 'unknown',
            'billing_type' => 'unknown',
            'owner_name' => 'dummy user',
            'owner_email' => 'dummy@grapevinebeta.com',
            'industry' => $industry
        );
        return array_merge($this->values($mapping), $extra);
    }

    private function get_competitor_mapping($number)
    {
        return array(
            'name' => "Competitor #$number Name",
            'address1' => "Competitor #$number Address",
            'address2' => "Competitor #$number Address Line 2",
            'city' => "Competitor #$number City",
            'state' => "Competitor #$number State",
            'zip' => "Competitor #$number Zip",
            'places' => "Competitor #$number Google Places"


        );
    }
}
