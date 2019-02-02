<?php

namespace jukeinthebox\models;

/**
 * Class File
 */
class File extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'file';
    protected $primaryKey = 'idFile';
    public $timestamps = false;

    public function piste() {
        return $this->belongsTo('jukeinthebox\models\Piste', 'idPiste');
    }

}