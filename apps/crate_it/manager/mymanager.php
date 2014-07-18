<?php

namespace OCA\crate_it\Manager;

class MyManager {
    
    public function __construct(){
         \OCP\Util::writeLog('mymanager', "created!", 3);       
    }

    public function getmsg()
    {
        return "Hellooo Wooorld!!!";
    }
}
