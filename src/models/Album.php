<?php

namespace jukeinthebox\models;

/**
 * Class Album
 */
class Album extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'album';
    protected $primaryKey = 'idAlbum';
    public $timestamps = false;

    public function a_joue_album() {
        return $this->hasMany('jukeinthebox\models\A_joué_album', 'idAJoueAlbum');
    }

    public function est_du_genre_album() {
        return $this->hasMany('jukeinthebox\models\Est_du_genre_album', 'idEstDuGenreAlbum');
    }

    public function fait_partie() {
        return $this->hasMany('jukeinthebox\models\Fait_partie', 'idFaitPartie');
    }

}