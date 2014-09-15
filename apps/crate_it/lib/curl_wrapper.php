<?php

namespace OCA\crate_it\lib;

interface CurlWrapper {

    public function setOption($name, $value);

    public function execute();

    public function getInfo($name);

    public function getStatus();

    public function close();

}
