<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jsonrpc\Eth;
use Illuminate\Support\Facades\DB;

class UpdateController extends Controller
{
    //测试
    public function updateErc(Request $request){
        try {
            $gao=$request->gao;
            $di=$request->di;
            $infura=new Eth(config('app.eth'));//geth网络
//        $infura=new Eth('https://mainnet.infura.io/v3/ca6382c272c94b5ab65937ce7213e94f');//infura网络
            //$gethrpc_data=$gethrpc->eth_getTransactionReceipt('0x7ce86d5b3eb7290747bcfed5fb7a228e7dfa0fc15e2fcf31726272911423c3b5');
            //erc20hash
            $erc20_transactions=DB::table('erc20_transactions')
                ->where([
                    ['erc20_block_number','>=',$di],
                    ['erc20_block_number','<',$gao],
                    ['erc20_token','=','0x55652ce84D686177c8946E8c78078c0d6CfA4b30'],
                ])
                ->get();
            //dd($address);
            $a=array();
            foreach ($erc20_transactions as $transaction){
                $gethrpc_data=$infura->eth_getTransactionReceipt($transaction->erc20_tx_hash);
                if(count($gethrpc_data['result']['logs'])>1){
                    foreach ($gethrpc_data['result']['logs'] as $key=>$value){
                        if($value["address"]==='0x55652ce84D686177c8946E8c78078c0d6CfA4b30'){
                            $to='0x'.substr($value['topics'][2],-40);
                            $amount=hexdec($value["data"]);
                            //判断地址是否在监听
                            if($to!=''){
                                $address=DB::table('accounts')->where('address',$to)->first();
                                if(!empty($address)){
                                    //$value["data"]=hexdec($value["data"]);
                                    DB::table('erc20_transactions')->where('erc20_tx_hash',$transaction->erc20_tx_hash)->update(['erc20_to'=>$to,'erc20_value'=>$amount]);
                                    //DB::table('erc20_transactions')->where('erc20_tx_hash',$transaction->erc20_tx_hash)->update(['erc20_to'=>$to]);
                                    $a[$key]['to']=$to;
                                    $a[$key]['hash']=$transaction->erc20_tx_hash;
                                    $url2 = 'http://127.0.0.1/api/receiveERC?erc20_tx_hash='.$transaction->erc20_tx_hash.'&erc20_to='.$to.'&erc20_token=0x55652ce84D686177c8946E8c78078c0d6CfA4b30';
                                    file_get_contents($url2);
                                }
                            }
                        }
                    }

                }
            }
            return $a;
        } catch (\Exception $exception) {
            return 0;
        }


    }


    //验证是否推送
    public function check(Request $request){
        $start=$request->start;
        $end=$request->end;
        $data = DB::select('SELECT
                    erc20_token,
                    erc20_tx_hash,
                    erc20_to,
                    apiAddress
                FROM
                    (
                        SELECT
                            erc20_token,
                            erc20_tx_hash,
                            erc20_to,
                            platformName
                        FROM
                            erc20_transactions
                        INNER JOIN accounts ON erc20_to = accounts.address
                        WHERE
                            (
                                erc20_block_number >= ?
                                AND ? >= erc20_block_number
                            )
                        AND (
                            erc20_to IN (
                                SELECT
                                    address
                                FROM
                                    accounts
                            )
                        )
                        AND (
                            erc20_token IN (
                                "0x55652ce84D686177c8946E8c78078c0d6CfA4b30"
                            )
                        )
                    ) transact
                INNER JOIN platforms ON transact.platformName = platforms.platformName', [$start,$end]);
        $all_hash=array();
        foreach ($data as $value){
            $all_hash[]=$value->erc20_tx_hash;
        }
        $send_hash=DB::table('token_confirm')->select('hash')->where('block','>=',$start)->where('block','<=',$end)->get();
        $all_send_hash=array();
        foreach ($send_hash as $value){
            $all_send_hash[]=$value->hash;
        }
        $result=array_diff($all_hash,$all_send_hash);
        $error_hash=array();
        foreach ($result as $value){
            try {
                $url = 'http://127.0.0.1/api/receiveERC?erc20_tx_hash='.$value;
                $urls[]= $url;
                file_get_contents($url);
                $error_hash[]= $value;
//                $url = 'https://portal.prancegoldholdings.com/erc_api?hash='.$value->hash.'&to='.$value->to.'&api_key=Rd5m4Vy42zERBPTb';
//                $task_message = file_get_contents($url);
//                DB::table('token_boss_get')->insert(array('hash'=>$value->hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$value->to,'data'=>$task_message));
            } catch (\Exception $exception) {
                $error_hash[]= $value;
            }
        }
        return $error_hash;

    }

    public function aatest(){
        $infura=new Eth(config('app.eth'));//geth网络
        $gethrpc_data=$infura->eth_getTransactionReceipt('0xcfce17d29ae02473d315779df02d42862b5a03b79c31ace0ecf8dced1c521857');
        if(count($gethrpc_data['result']['logs'])>1){
            foreach ($gethrpc_data['result']['logs'] as $key=>$value){

                if($value["address"]==='0x55652ce84D686177c8946E8c78078c0d6CfA4b30'){
                    $to='0x'.substr($value['topics'][2],-40);
                }
                //判断地址是否在监听
                if($to!=''){
                    $address=DB::table('accounts')->where('address',$to)->first();
                    if(!empty($address)){
                        $value["data"]=hexdec($value["data"]);
                        DB::table('erc20_transactions')->where('erc20_tx_hash',$transaction->erc20_tx_hash)->update(['erc20_to'=>$to,'erc20_value'=>$value["data"]]);

                    }
                }
            }

        }

    }



}
