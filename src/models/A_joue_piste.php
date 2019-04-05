<?php

namespace jukeinthebox\models;

/**
 * Class A_joue_piste
 */
class A_joue_piste extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'a_joué_piste';
    protected $primaryKey = 'idAJouePiste';
    public $timestamps = false;
    protected $fillable = ['idPiste', 'idArtiste'];

    public function piste() {
        return $this->belongsTo('jukeinthebox\models\Piste', 'idPiste');
    }

    public function artiste() {
        return $this->belongsTo('jukeinthebox\models\Artiste', 'idArtiste');
    }

}