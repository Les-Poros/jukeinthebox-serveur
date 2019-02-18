<?php

namespace jukeinthebox\models;

/**
 * Class Bibliotheque
 */
class Bibliotheque extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'bibliotheque';
    protected $primaryKey = 'idBibliotheque';
    public $timestamps = false;

    public function contenu_bibliotheque() {
        return $this->hasMany('jukeinthebox\models\Contenu_bibliotheque', 'idContenu_bibliotheque');
    }

    public function jukebox() {
        return $this->belongsTo('jukeinthebox\models\Jukebox', 'idJukebox');
    }

}