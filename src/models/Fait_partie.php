<?php

namespace jukeinthebox\models;

/**
 * Class Fait_partie
 */
class Fait_partie extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'fait_partie';
    protected $primaryKey = 'idFaitPartie';
    public $timestamps = false;

    public function piste() {
        return $this->belongsTo('jukeinthebox\models\Piste', 'idPiste');
    }

    public function album() {
        return $this->belongsTo('jukeinthebox\models\Album', 'idAlbum');
    }

}