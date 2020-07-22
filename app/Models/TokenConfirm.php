<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TokenConfirm extends Model
{
    public $table='token_confirm';
    //添加进数据库
    public function add($data){
        $info=DB::table($this->table)->insert($data);
        return $info;
    }
    //获取确认数小于12
    public function get(){
        $info=DB::table($this->table)->where('confirm','<',12)->get();
        return $info;
    }

    //获取确认数大于12
    public function getStatus(){
        $info=DB::table($this->table)->where('confirm','>=',12)->where('type',2)->where('status',0)->orderBy('id', 'asc')->get();
        return $info;
    }

    //更新确认数
    public function update_confirm($id,$data){

        $info=DB::table($this->table)->where('id',$id)->update($data);
        return $info;
    }



}
