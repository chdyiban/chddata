<?php

namespace app\face\model;

use think\Model;

class Face extends Model
{
    protected $table = 'dp_face_index';
    
    public function addFiles2Db($data){

        $this->insertAll($data);
    }
}