<?php


namespace L3_CSI_Covid19\Model;

use Illuminate\Database\Eloquent\Model;

class Hospitalise extends Model {

    protected $table = "hospitalise";
    protected $primaryKey = "nohospitalisation";
    public $timestamps = false;

}