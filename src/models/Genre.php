<?php

namespace jukeinthebox\models;

/**
 * Class Genre
 */
class Genre extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'genre';
    protected $primaryKey = 'idGenre';
    public $timestamps = false;
    protected $fillable = ['nomGenre'];

    public function est_du_genre_piste() {
        return $this->hasMany('jukeinthebox\models\Est_du_genre_piste', 'idEstDuGenrePiste');
    }

}