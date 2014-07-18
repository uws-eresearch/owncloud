<?php

namespace OCA\crate_it\Manager;

class CrateManager {

    /**
     * @var API
     */
    private $api;
    
    public function __construct($api){
        $this->api = $api;
    }

    public function getCrateList() {
        \OCP\Util::writeLog("crate_it", 'Getting a list of crates for user: '.$this->api->getUserId(), \OCP\Util::DEBUG);
        
        $cratelist = array();
        if ($handle = opendir($this->getCrateRoot())) {
            $filteredlist = array('.', '..', 'packages', '.Trash');
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, $filteredlist)) {
                    array_push($cratelist, $file);
                }
            }
            closedir($handle);
        }
        return $cratelist;
    }

    private function getCrateRoot() {
        $base_dir = \OC::$SERVERROOT . '/data/' . $this->api->getUserId();
        return $base_dir . '/crates';
    }

}
