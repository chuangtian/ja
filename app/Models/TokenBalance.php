<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TokenBalance extends Model
{
    public $table='token_balance';
    //添加进数据库
    public function add($data){
        $info=DB::table($this->table)->insert($data);
        return $info;
    }
    //获取余额
    public function get($address,$contract){
        $info=DB::table($this->table)->where('address',$address)->where('contract',$contract)->first();
        return $info;
    }
    //更新余额
    public function updateBalance($id,$dalance){
        $info=DB::table($this->table)->where('id',$id)->update(array('balance'=>$dalance));
        return $info;
    }
    //更新余额
    public function updateBalance2($address,$dalance){
        $info=DB::table($this->table)->where('address',$address)->update(array('balance'=>$dalance));
        return $info;
    }
}
