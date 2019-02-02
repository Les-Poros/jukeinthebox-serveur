<?php

namespace jukeinthebox\models;

/**
 * Class A_joue_piste
 */
class A_joue_piste extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'a_joue_piste';
    protected $primaryKey = 'idAJouePiste';
    public $timestamps = false;

    public function piste() {
        return $this->belongsTo('jukeinthebox\models\Piste', 'idPiste');
    }

    public function artiste() {
        return $this->belongsTo('jukeinthebox\models\Artiste', 'idArtiste');
    }

}