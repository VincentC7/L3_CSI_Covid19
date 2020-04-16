<?php

namespace L3_CSI_Covid19\DB;

use Illuminate\Database\Capsule\Manager as DB;

class Eloquant {

    public static function start(String $file){
        $db = new DB();
        $db->addConnection(parse_ini_file($file));
        $db->setAsGlobal();
        $db->bootEloquent();
    }
}