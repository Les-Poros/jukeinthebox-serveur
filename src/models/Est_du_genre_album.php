<?php

namespace jukeinthebox\models;

/**
 * Class Est_du_genre_album
 */
class Est_du_genre_album extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'est_du_genre_album';
    protected $primaryKey = 'idEstDuGenreAlbum';
    public $timestamps = false;

    public function album() {
        return $this->belongsTo('jukeinthebox\models\Album', 'idAlbum');
    }

    public function genre() {
        return $this->belongsTo('jukeinthebox\models\Genre', 'idGenre');
    }

}