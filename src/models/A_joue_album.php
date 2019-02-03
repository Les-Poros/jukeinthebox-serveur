<?php

namespace jukeinthebox\models;

/**
 * Class A_joue_album
 */
class A_joue_album extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'a_joué_album';
    protected $primaryKey = 'idAJoueAlbum';
    public $timestamps = false;

    public function album() {
        return $this->belongsTo('jukeinthebox\models\Album', 'idAlbum');
    }

    public function artiste() {
        return $this->belongsTo('jukeinthebox\models\Artiste', 'idArtiste');
    }

}