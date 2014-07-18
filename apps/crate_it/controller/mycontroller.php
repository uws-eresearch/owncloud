<?php

namespace OCA\crate_it\Controller;
use \OCA\AppFramework\Controller\Controller;

class MyController extends Controller {
    
    /**
     * @var API
     */
    protected $api;
    
    /**
     * @var MyService
     */
    private $myservice;
    
    public function __construct($api, $request, $myservice){
        parent::__construct($api, $request);
        $this->api = $api;
        $this->myservice = $myservice;
    }
    
    /**
     * Hello
     * @IsAdminExemption
     * @IsSubAdminExemption
     * @CSRFExemption
     */
    public function index() {       
        \OCP\Util::writeLog('hello', "**HELLOCONTROLLER**", 3);         
        $model = array('msg' => $this->myservice->getmsg());
        \OCP\Util::writeLog('hello', $model['msg'], 3);      
        return $this->render('hello', $model);
    }

    
}
    