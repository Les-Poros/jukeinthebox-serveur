<?php

namespace jukeinthebox\models;

/**
 * Class Contenu_Bibliotheque
 */
class Contenu_bibliotheque extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'contenu_bibliotheque';
    protected $primaryKey = 'idContenu_bibliotheque';
    public $timestamps = false;

    public function piste() {
        return $this->hasMany('jukeinthebox\models\Piste', 'idPiste');
    }

    public function bibliotheque() {
        return $this->belongsTo('jukeinthebox\models\Bibliotheque', 'idBibliotheque');
    }

}