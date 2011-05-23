<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Account extends Controller_Template {
    
    protected $_menuView;
    
    protected $_contentView;

    public function before() {
        parent::before();
        $this->template->body = View::factory('account/body');
        $this->_menuView = View::factory('account/menu');
    }
    
    public function after() {
        $this->template->body->menu = $this->_menuView;
        $this->template->body->content = $this->_contentView;
        parent::after();
    }

    public function action_index()
    {
        $this->response->body('hello, world!');
    }
    
    public function action_logout() {
        Auth::instance()->logout();
        $this->request->redirect(url::base());
    }
    
    public function action_general()
    {
        $this->_contentView = View::factory('account/general');
    }
    
    public function action_users()
    {
        $this->_contentView = View::factory('account/users');
        
        // temporary
        $usersData = array(
            array('id' => 1, 'name' => 'Jacek Kromski', 'role' => 'admin'),
            array('id' => 2, 'name' => 'Tomasz Jaśkowski', 'role' => 'admin'),
            array('id' => 3, 'name' => 'Richard Ortega', 'role' => 'admin'),
            array('id' => 4, 'name' => 'John Kowalski', 'role' => 'user'),
        );
        $this->_contentView->users = $usersData;
    }
    
    public function action_alerts()
    {
        
    }
    
    public function action_reporting()
    {
        
    }
    
    public function action_competitors()
    {
        
    }

    public function action_socials()
    {
        
    }
    
    public function action_billing()
    {
        
    }
    
    

} // End Welcome