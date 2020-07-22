<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jsonrpc\Eth;
use ERC20\ERC20;
use EthereumRPC\EthereumRPC;
use Illuminate\Support\Facades\DB;
use App\Models\TokenConfirm;

class DowController extends Controller
{
    //更新数据
    public function test(){
        $gethrpc=new Eth(config('app.eth'));

        //查询数据库
        $info=DB::table('token_transactions_details')->where('amount','0')->orderBy('id', 'asc')->get();

        foreach ($info as $value){
            $bal=$gethrpc->eth_getTransactionByHash($value->hash);
            $number_time=$gethrpc->eth_getBlockByNumber($bal['result']['blockNumber'],false);

            if(!empty($number_time['error'])){
                $info=DB::table('token_transactions_details')->where('id',$value->id)->delete();
                return 1;
            }
            if($bal['result']['input']==='0x'){
                $eth_data['update_time']=date('Y-m-d H:i:s',hexdec($number_time["result"]['timestamp']));
                $eth_data['from']=$bal['result']['from'];
                $eth_data['to']=$bal['result']['to'];
                $eth_data['amount']=bcdiv(hexdec($bal['result']['value']),'1000000000000000000','18');
                $eth_data['fee']=bcdiv(bcmul(hexdec($bal['result']['gas']),hexdec($bal['result']['gasPrice'])),'1000000000000000000','18');
                $info=DB::table('token_transactions_details')->where('id',$value->id)->update($eth_data);

            }else{
                $erc_data['update_time']=date('Y-m-d H:i:s',hexdec($number_time["result"]['timestamp']));
                $erc_data['from']=$bal['result']['from'];
                $erc_data['to']='0x'.substr($bal['result']['input'],34,40);
                $erc_data['token']=$bal['result']['to'];
                $erc_data['type']=2;
                $erc_data['amount']=bcdiv(hexdec(substr($bal['result']['input'],74,64)),'1000000','6');
                $gas_use=$gethrpc->eth_getTransactionReceipt($value->hash);
                $erc_data['fee']=bcdiv(bcmul(hexdec($gas_use['result']['gasUsed']),hexdec($bal['result']['gasPrice'])),'1000000000000000000','18');
                $info=DB::table('token_transactions_details')->where('id',$value->id)->update($erc_data);
            }

        }
      return 1;


    }

    public function download(Request $request){
        $startTime=$request->input('startTime',date('Y-m').'-1 00:00:00');
        $endTime=$request->input('endTime',date('Y-m',strtotime(" +1 month")).'-1 00:00:00');

        /**导出excel**/
        header("Content-type:application/vnd.ms-excel");   //声明内容类型为excel
        header("Content-Disposition:attachment;filename=transactions_details.xls");  //content-disposition设置attachment为弹窗下载，inline时会内嵌浏览器显示，当然对jpg等文件有效，excel文件不能内嵌，可自行翻阅文档了解；filename定义文件名称与扩展名
        echo "hash\t";
        echo "time\t";
        echo "from\t";
        echo "to\t";
        echo "type\t";
        echo "amount\t";
        echo "fee\t";
        echo "token";
        //查询数据库
        $info=DB::table('token_transactions_details')->where('update_time','>=',$startTime)->where('update_time','<',$endTime)->orderBy('id', 'asc')->get();
        foreach ($info as $value){
            echo "\n";
            echo $value->hash." \t";
            echo $value->update_time." \t";
            echo $value->from." \t";
            echo $value->to." \t";
            if($value->type==1){
                echo "ETH\t";
            }else{
                echo "ERC\t";
            }
            echo $value->amount." \t";
            echo $value->fee." \t";
            echo $value->token." \t";
        }


    }







}
