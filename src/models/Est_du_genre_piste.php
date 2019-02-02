<?php

namespace jukeinthebox\models;

/**
 * Class Est_du_genre_piste
 */
class Est_du_genre_piste extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'est_du_genre_piste';
    protected $primaryKey = 'idEstDuGenrePiste';
    public $timestamps = false;

    public function piste() {
        return $this->belongsTo('jukeinthebox\models\Piste', 'idPiste');
    }

    public function genre() {
        return $this->belongsTo('jukeinthebox\models\Genre', 'idGenre');
    }

}