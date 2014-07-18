<?php

namespace OCA\crate_it\Service;

class MyService {
    
    /**
     * @var API
     */
    private $api;
    
    /**
     * @var MyManager
     */
    private $mymanager;
    
    public function __construct($api, $mymanager){
        $this->api = $api;
        $this->mymanager = $mymanager;
    }
    
    public function getmsg()
    {
        return $this->mymanager->getmsg();   
    }
    
    
}
