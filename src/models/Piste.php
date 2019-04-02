<?php

namespace jukeinthebox\models;
use jukeinthebox\models\A_joue_piste;

/**
 * Class Piste
 */
class Piste extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'piste';
    protected $primaryKey = 'idPiste';
    public $timestamps = false;

    public function a_joue_piste() {
        return $this->hasMany('jukeinthebox\models\A_joue_piste', 'idPiste');
    }

    public function est_du_genre_piste() {
        return $this->hasMany('jukeinthebox\models\Est_du_genre_piste', 'idPiste');
    }

    public function fait_partie() {
        return $this->hasMany('jukeinthebox\models\Fait_partie', 'idFaitPartie');
    }

    public function file() {
        return $this->hasMany('jukeinthebox\models\File', 'idFile');
    }

    public function contenu_bibliotheque() {
        return $this->hasMany('jukeinthebox\models\Contenu_bilbiotheque', 'idContenu_bilbiotheque');
    }

}