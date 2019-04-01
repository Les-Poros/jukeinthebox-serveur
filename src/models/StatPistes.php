<?php

namespace jukeinthebox\models;

/**
 * Class StatPistes
 */
class StatPistes extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'statPistes';
    protected $primaryKey = 'idStatPistes';
    public $timestamps = false;

    public function jukebox() {
        return $this->belongsTo('jukeinthebox\models\Jukebox', 'idJukebox');
    }

    public function piste() {
        return $this->belongsTo('jukeinthebox\models\Piste', 'idPiste');
    }
}