<?php

namespace jukeinthebox\models;

/**
 * Class StatGenres
 */
class StatGenres extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'statGenres';
    protected $primaryKey = 'idStatGenres';
    public $timestamps = false;

    public function jukebox() {
        return $this->belongsTo('jukeinthebox\models\Jukebox', 'idJukebox');
    }

    public function genre() {
        return $this->belongsTo('jukeinthebox\models\Genre', 'idGenre');
    }
}