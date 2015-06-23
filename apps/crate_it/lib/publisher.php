<?php

namespace OCA\crate_it\lib;

interface Publisher {
    
    public function getCollection();

    public function publishCrate($package, $collection);

}

