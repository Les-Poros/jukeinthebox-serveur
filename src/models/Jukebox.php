<?php

namespace jukeinthebox\models;

/**
 * Class Jukebox
 */
class Jukebox extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'jukebox';
    protected $primaryKey = 'idJukebox';
    public $timestamps = false;

    public function file() {
        return $this->hasMany('jukeinthebox\models\File', 'idFile');
    }

    public function bibliotheque() {
        return $this->belongsTo('jukeinthebox\models\Bibliotheque', 'idBibliotheque');
    }

}