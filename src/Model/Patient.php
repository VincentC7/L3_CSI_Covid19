<?php

namespace L3_CSI_Covid19\Model;


use Illuminate\Database\Eloquent\Model;

class Patient extends Model {

    protected $table = "patient";
    protected $primaryKey = "num_secu";
    public $timestamps = false;

}