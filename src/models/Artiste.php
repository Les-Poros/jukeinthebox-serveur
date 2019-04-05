<?php

namespace jukeinthebox\models;

/**
 * Class Artiste
 */
class Artiste extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'artiste';
    protected $primaryKey = 'idArtiste';
    public $timestamps = false;
    protected $fillable = ['nomArtiste', 'prénomArtiste'];

    public function a_joue_album() {
        return $this->hasMany('jukeinthebox\models\A_joué_album', 'idAJoueAlbum');
    }

    public function a_joue_piste() {
        return $this->hasMany('jukeinthebox\models\A_joué_piste', 'idAJouePiste');
    }

}