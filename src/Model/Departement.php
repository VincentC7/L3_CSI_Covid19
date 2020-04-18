<?php


namespace L3_CSI_Covid19\Model;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model {

    protected $table = "patient";
    protected $primaryKey = "nodep";
    public $timestamps = false;

}