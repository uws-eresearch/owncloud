<?php

namespace OCA\crate_it\lib;

Interface Publisher {
    
    public function getCollection();

    public function publishCrate($package, $collection);

}

