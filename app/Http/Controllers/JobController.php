<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jsonrpc\Eth;
use ERC20\ERC20;
use EthereumRPC\EthereumRPC;
use Illuminate\Support\Facades\DB;
use App\Models\TokenConfirm;

class JobController extends Controller
{
    //测试
    public function test(Request $request){


//        $gethrpc=new Eth(config('app.eth'));
//		$bal=$gethrpc->eth_getBalance('0x4c04ab9adb2d06ef43b777949f886d3c977f10a7','latest');
//        $to_bal=hexdec($bal['result']);
//
//
//        $amoubt=bcdiv(bcsub('5600000000000000',$to_bal),'1000000000000000000',18);
//        $a = $this->sendETH2('0x4c04ab9adb2d06ef43b777949f886d3c977f10a7','company','0xeeAA8D2d1DC695A9C696A9713487aaf58174a574');
//        //$a = $this->sendERC2('0x4c04ab9adb2d06ef43b777949f886d3c977f10a7', 'company', '155000000000','0x55652ce84D686177c8946E8c78078c0d6CfA4b30');
//        dd($a);

        //$gethrpc=new Eth(config('app.eth'));//测试网络
        $gethrpc=new Eth('http://148.66.50.26:2406');//测试网络
        $a = $this->sendERC2('0x840dcdc6c0cd40a44b6d71ed90120e8d06e12b92', '4dMi91yzn1CSI4Va', '1000000','0xdac17f958d2ee523a2206206994597c13d831ec7');
        dd($a);

//        $infura=new Eth('https://mainnet.infura.io/v3/ca6382c272c94b5ab65937ce7213e94f');//infura网络
//        $infura_data=$infura->eth_blockNumber();
//        $gethrpc=new Eth(config('app.eth'));//geth网络
//        //获取价格
//        $ethGasPrice=$gethrpc->eth_gasPrice();
//        $gasPrice3 = hexdec($ethGasPrice['result']);
//        $gasPrice2= bcdiv(bcmul($gasPrice3,'2',18), "1000000000000000000",18);
//
//        dd($gasPrice2);
        //$gethrpc_data=$gethrpc->eth_getTransactionByHash('0x7ce86d5b3eb7290747bcfed5fb7a228e7dfa0fc15e2fcf31726272911423c3b5');

        $result=$gethrpc->personal_newAccount('4dMi91yzn1CSI4Va');
        dd($result);
        $result = $gethrpc->personal_unlockAccount('0x995fabadae89c2cfff38610d352dadfc561f69dc','4dMi91yzn1CSI4Vm');
        //$blockNumberInfo=$this->blockNumber();
        dd($result);
        $gethrpc=new Eth(config('app.eth'));//测试网络
        $confirmModel=new TokenConfirm();
        $erc20_data=$confirmModel->getStatus();
        dd($erc20_data);
        foreach ($erc20_data as $value){
            //dd($value->hash);
            $keyinfo=DB::table('accounts')->where('address',$value->to)->where('platformName','ja')->first();
            //dd($erc20_data);
            if($keyinfo){
                //try {
//                    $url2 = config('app.eth_api').'/findERC20TransactionByHash?apikey=Rd5m4Vy42zERBPTb&erc20_tx_hash='.$value->hash;
//                    dd($url2);
//                    $task_message = json_decode(file_get_contents($url2),true)['txResults'][0];
                    //判断余额 查询余额
                    $bal=$gethrpc->eth_getBalance($value->to,'latest');
                    $to_bal=hexdec($bal['result']);

                    if ($to_bal>=3000000000000000){
                        $a=$this->sendERC($value->to,'ErCPGsysPa$$',$value->amount,$value->token);
                        if($a==200){
                            $confirmModel->update_confirm($value->id,array('status'=>1));
                        }
                    }else{
                        $time=strtotime('-24 hour');
                        $time2=strtotime($value->update_time);
                        if($time>$time2){
                            $confirmModel->update_confirm($value->id,array('fee'=>0,'confirm'=>1));
                        }
                    }
//                } catch (\Exception $exception) {
//
//                }
            }

        }
        return 1;


        $gethrpc=new Eth(config('app.eth'));//测试网络
        $bal=$gethrpc->eth_getBlockByNumber("0x".dechex(9699413),false);
        //$bal=$gethrpc->eth_getBlockByHash("0xbeda694a1fff6c4cca36a26ca418853610a15514583f01c74fa26fcc3ecc6dd7",false);

        dd($bal,"0x".dechex(9699413));
//        $to_bal=hexdec($bal['result']);
//
//        $amoubt=bcdiv(bcsub('580000000000000',$to_bal),'1000000000000000000',18);
        $a=$this->sendETH('0x908e21d3C7B8D2Dec5016701B266425B7c901C6c','ErCPGsysPa$$','0xE78D4E88Fc7948e7692AeC0ACE983f1e088182CD');
        dd($a);
        //$response = $this->curl_post('http://106.13.232.160:81/api/update/test', array('a'=>'rr','b'=>'qq'));
        //dd($response);
        //$gethrpc=new Eth('http://127.0.0.1:2406');//本机
        $gethrpc=new Eth(config('app.eth'));//测试网络
        $result=$gethrpc->personal_newAccount('company');
        //$result = $gethrpc->personal_unlockAccount('0x40bd8d76074d2ed3a185f10f7cefb96a4e6c024b','test');//解锁
        dd($result);
    }
    //接收推送写入数据库
    public function receiveERC(Request $request){
        $date2= date("Y-m-d H:i:s", strtotime("-5 minute"));
        $ercHash=$request->input('erc20_tx_hash',false);
        $info=DB::table('token_confirm')->where('update_time','>',$date2)->where('hash',$ercHash)->first();
        if($info){
            return 4;
        }
        if($ercHash){

            $this->token_hash($request);
            $erc20_data=DB::table('erc20_transactions')->where('erc20_tx_hash',$ercHash)->first();
            $data['hash']=$ercHash;
            $data['confirm']=1;
            $data['update_time']=date('Y-m-d H:i:s');
            $data['block']=$erc20_data->erc20_block_number;
            $data['type']=2;
            $data['from']=$erc20_data->erc20_from;
            $data['to']=$erc20_data->erc20_to;
            $data['amount']=$erc20_data->erc20_value;
            $data['token']=$erc20_data->erc20_token;
            //$response = $this->curl_post('https://tu.prancegoldholdings.com/erc_api', array('hash'=>$ercHash,'to'=>$task_message['erc20_to'],'apikey'=>'Rd5m4Vy42zERBPTb'));
//            $url2 = 'https://portal.prancegoldholdings.com/erc_api?hash='.$ercHash.'&to='.$erc20_data->erc20_to.'&api_key=Rd5m4Vy42zERBPTb';
//            $task_message2 = file_get_contents($url2);
//            //$task_message2 = json_decode(file_get_contents($url2),true);
//            DB::table('token_boss_get')->insert(array('hash'=>$ercHash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$erc20_data->erc20_to,'data'=>$task_message2));

//            $url2 = 'https://portal.prancegoldholdings.com/erc_api?hash='.$ercHash.'&to='.$erc20_data->erc20_to.'&api_key=Rd5m4Vy42zERBPTb';
//            $task_message2 = file_get_contents($url2);
//            //$task_message2 = json_decode(file_get_contents($url2),true);
//            DB::table('token_boss_get')->insert(array('hash'=>$ercHash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$erc20_data->erc20_to,'data'=>$task_message2));
            $this->getApi($ercHash);
            //测试服测试
            if($erc20_data->erc20_to===config('app.erc20Address')){
                return 2;
            }
        }else{
            $ethHash=$request->input('tx_hash',false);
            $internal=$request->input('internal',0);
            $tx_to=$request->input('tx_to',false);
            if($ethHash){
                if($internal==1 and $tx_to){
                    //测试服
                    $uri = config('app.eth_api_wai')."/api?module=account&action=txlistinternal&txhash=$ethHash&apikey=3FVDDCH2IJRZYUDDSA69WA8EAUAGC8HZXQ";
                    //正式服
                    //$uri = "https://api.etherscan.io/api?module=account&action=txlistinternal&txhash=$ethHash&apikey=3FVDDCH2IJRZYUDDSA69WA8EAUAGC8HZXQ";
                    $task_message = json_decode(file_get_contents($uri), true);
                    if($task_message['status']==='1'){
                        $data['hash']=$ethHash.'+'.$tx_to;
                        $data['confirm']=1;
                        $data['update_time']=date('Y-m-d H:i:s');
                        $data['block']=$task_message['result'][0]['blockNumber'];
                        $data['type']=1;
                        $data['from']=$task_message['result'][0]['from'];
                        $data['to']=$tx_to;
                        $data['amount']=0;
                        foreach ($task_message['result'] as $value){
                            if($value['to']==$tx_to){
                                $data['amount']=bcadd($data['amount'],$value['value']);
                            }
                        }
                        if($tx_to===config('app.getFeeAddress')){
                            return 2;
                        }
                        if($tx_to===config('app.ethAddress')){
                            return 2;
                        }
                    }else{
                        return 0;
                    }
                }else{
                    //测试服测试
                    $url2 = config('app.eth_api').'/findTransactionByHash?apikey=123456&txHash='.$ethHash;
                    $task_message = json_decode(file_get_contents($url2), true)['txResults'][0];
                    $data['hash']=$ethHash;
                    $data['confirm']=1;
                    $data['update_time']=date('Y-m-d H:i:s');
                    $data['block']=$task_message['tx_block_number'];
                    $data['type']=1;
                    $data['from']=$task_message['tx_from'];
                    $data['to']=$task_message['tx_to'];
                    $data['amount']=$task_message['tx_value'];
                    if($task_message['tx_to']===config('app.getFeeAddress')){
                        return 2;
                    }
                    if($task_message['tx_to']===config('app.ethAddress')){
                        return 2;
                    }
                    if($task_message['tx_from']===config('app.getFeeAddress')){
                        return 2;
                    }
                }
            }else{
                return 0;
            }
        }
		try {
			$confirmModel=new TokenConfirm();
			$confirmModel->add($data);
		} catch (\Exception $exception) {
            //dd($exception);
			return $ercHash;
        }
        return 1;
    }

    //更新确认块
    public function updateBlock(){
        //$gethrpc=new Eth('http://127.0.0.1:2406');//本机
        $gethrpc=new Eth(config('app.eth'));//测试网络
        $blockNumber=$gethrpc->eth_blockNumber();
        $blockNumber=hexdec($blockNumber["result"]);
        $confirmModel=new TokenConfirm();
        $confirm_data=$confirmModel->get();

        foreach ($confirm_data as $value){
            $confirm=$blockNumber-$value->block+1;
            $confirmModel->update_confirm($value->id,array('confirm'=>$confirm));
            DB::beginTransaction(); //开启事务
            if($confirm>=12){
                if($value->type==2){

                    /*
                     * 下面加推送
                     * */
                    //$response = $this->curl_post('https://tu.prancegoldholdings.com/erc_api', array('hash'=>$value->hash,'to'=>$value->to,'apikey'=>'Rd5m4Vy42zERBPTb'));
//                    $url2 = 'https://portal.prancegoldholdings.com/erc_api?hash='.$value->hash.'&to='.$value->to.'&api_key=Rd5m4Vy42zERBPTb';
//                    $task_message=$this->curl_get($url2);
////                    dd($task_message);
////                    $task_message = file_get_contents($url2);
////                    if($task_message!='Invalid Request'){
////                        $task_message = json_decode(file_get_contents($url2),true);
////                    }
//
//                    $b=array('hash'=>$value->hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$value->to,'data'=>$task_message);
//                    $a=DB::table('token_boss_get')->insert(array('hash'=>$value->hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$value->to,'data'=>$task_message));

                    $this->getApi($value->hash);
                    //测试服测试

//                    $url2 = 'http://141.193.156.178:36/findERC20TransactionByHash?apikey=123456&erc20_tx_hash='.$value->hash;
//                    $task_message = json_decode(file_get_contents($url2),true)['txResults'][0];
                    //判断余额 查询余额
                    $bal=$gethrpc->eth_getBalance($value->to,'latest');
                    $to_bal=hexdec($bal['result']);

                    if ($to_bal<3000000000000000){
                        $amoubt=bcdiv(bcsub('3000000000000000',$to_bal),'1000000000000000000',18);
                        $a=$this->sendETH(config('app.getFeeAddress'),config('app.getFeeAddressPassword'),$value->to,$amoubt);
                        if($a==200){
                            $confirmModel->update_confirm($value->id,array('fee'=>1));
                            DB::commit();  //提交
                        }else{
                            DB::rollback();  //回滚
                        }
                    }else{
                        try {
                            $a=$this->sendERC($value->to,'ErCPGsysPa$$',$value->amount,$value->token);
                            if($a==200){
                                $confirmModel->update_confirm($value->id,array('status'=>1));
                                DB::commit();  //提交
                            }else{
                                DB::rollback();  //回滚
                            }
                        } catch (\Exception $exception) {

                        }

                    }
                    //dd($task_message,$url2,$a,$b);
                }else{
//                    $url2 = 'http://141.193.156.178:36/findTransactionByHash?apikey=123456&txHash='.$value->hash;
//                    $task_message = json_decode(file_get_contents($url2),true)['txResults'][0];

                    if($value->amount>=1000000000000000){
                        $eth_data["from"]=$value->to;
                        $eth_data["to"]=config('app.ethAddress');
                        $eth_data["value"]='0x'.dechex($value->amount);
                        $result3 = $gethrpc->eth_estimateGas($eth_data);
                        $gasPrice = $gethrpc->eth_gasPrice();
                        $gasPrice=bcdiv(hexdec($gasPrice["result"]),"1000000000000000000",18);
                        $result3=bcmul($gasPrice,hexdec($result3["result"]),18);    //eth_estimateGas x gas_price
                        $result2=bcdiv($value->amount,"1000000000000000000",18);
                        $amount=bcsub($result2,$result3,18);
                        //dd($amount,$result2,$result3);
                        $a=$this->sendETH($value->to,'test',config('app.ethAddress'),$amount);
                        if($a==200){
                            $confirmModel->update_confirm($value->id,array('status'=>1));
                            DB::commit();  //提交
                        }else{
                            DB::rollback();  //回滚
                        }
                    }else{
                        $confirmModel->update_confirm($value->id,array('status'=>1));
                        DB::commit();  //提交
                    }
                }

            }else{
                DB::commit();  //提交
            }
        }

        $erc20_data=$confirmModel->getStatus();

        foreach ($erc20_data as $value){
            //dd($value->hash);
            $keyinfo=DB::table('accounts')->where('address',$value->to)->where('platformName','ja')->first();
            //dd($erc20_data);
            if($keyinfo){
                try {
//                    $url2 = config('app.eth_api').'/findERC20TransactionByHash?apikey=Rd5m4Vy42zERBPTb&erc20_tx_hash='.$value->hash;
//                    $task_message = json_decode(file_get_contents($url2),true)['txResults'][0];
                    //判断余额 查询余额
                    $bal=$gethrpc->eth_getBalance($value->to,'latest');
                    $to_bal=hexdec($bal['result']);

                    if ($to_bal>=3000000000000000){
                        $a=$this->sendERC($value->to,'ErCPGsysPa$$',$value->amount,$value->token);
                        if($a==200){
                            $confirmModel->update_confirm($value->id,array('status'=>1));
                        }
                    }else{
                        $time=strtotime('-24 hour');
                        $time2=strtotime($value->update_time);
                        if($time>$time2){
                            $confirmModel->update_confirm($value->id,array('fee'=>0,'confirm'=>1));
                        }
                    }
                } catch (\Exception $exception) {

                }
            }

        }
        return 1;
    }


    //发送erc20
    public function sendERC($from,$password,$amount,$contract){
        $data['from']=$from;
        $data['password']=$password;
        $data['to']=config('app.erc20Address');
        $data['amount']=$amount;

        //jsonrpc
        $gethrpc=new Eth(config('app.eth'));//测试网络
        //$gethrpc=new Eth('http://127.0.0.1:2406');//本机
        $balance=$gethrpc->eth_getBalance($data['from'],'latest');
        $balance=hexdec($balance["result"]);
        if($balance<80000000000000){
            $data['code']=402;
            $data['message']='以太坊不足';
            return $data;
        }
        //获取价格
        $ethGasPrice=$gethrpc->eth_gasPrice();
        $gasPrice3 = hexdec($ethGasPrice['result']);
        //erc20 链接钱包
        $geth = new EthereumRPC(config('app.eth_ip'),config('app.eth_port'));//测试网络
        //$geth = new EthereumRPC("127.0.0.1",2406);//本机
        $erc20 = new ERC20($geth);
        //合同
        //$contract = $contract; // ERC20 contract address
        $payer = $data['from']; // Sender's Ethereum account
        $payee = $data['to']; // Recipient's Ethereum account
        $amount=$data['amount'];

        //计算转出金额
        $token = $erc20->token($contract);
        $data["data"] = $token->encodedTransferData($payee,$amount);
        $gasPrice2= bcdiv(bcmul($gasPrice3,'2',18), "1000000000000000000",18);
        $transaction = $geth->personal()->transaction($payer, $contract)->gas(60000,'0.000000050')->amount("0")->data($data["data"]); // Our encoded ERC20 token transfer data from previous step
        //dd($transaction,$data["data"],$gasPrice2,$amount);
        $res = $transaction->send($data['password']); // Replace "secret" with actual passphrase of SENDER's ethereum
        DB::table('token_transactions')->insert(array('hash'=>$res,'update_time'=>date('Y-m-d H:i:s')));
        DB::table('token_transactions_details')->insert(array('hash'=>$res,'update_time'=>date('Y-m-d H:i:s')));
        $r_data['code']=200;
        $r_data['message']=$res;
        return 200;
    }

    //发送eth
    public function sendETH($from,$password,$to='',$amount=0){
        $data['from']=$from;
        if($to===''){
            $data['to']=config('app.ethAddress');
        }else{
            $data['to']=$to;
        }
//        $password=$password;
        //全部转出
        //$result = $client->eth_accounts();
        $gethrpc=new Eth(config('app.eth'));//测试网络
        $gasPrice = $gethrpc->eth_gasPrice();
        $gasPrice=bcdiv(hexdec($gasPrice["result"]),"1000000000000000000",18);
        //$result1 = $client->eth_getBalance("0xb3b910d79399eb74f7f04dc4568893450bf843e2","latest");
        $result2 = $gethrpc->eth_getBalance($data['from'],"latest");
        $data["from"]=$data['from'];
        $data["to"]=$data['to'];
        if($amount==0){
            $data["value"]=$result2['result'];
            $result3 = $gethrpc->eth_estimateGas($data);
            $result3=bcmul($gasPrice,hexdec($result3["result"]),18);    //eth_estimateGas x gas_price
            $result2=bcdiv(hexdec($result2['result']),"1000000000000000000",18);
            $aaa=bcsub($result2,$result3,18);
            $value=dechex(bcmul($aaa,"1000000000000000000"));//减掉要消耗的费用并转十六进制
        }else{
            $value=dechex(bcmul($amount,"1000000000000000000"));//减掉要消耗的费用并转十六进制
        }
        $gasPrice2 = $gethrpc->eth_gasPrice();
        $gasp='0x'.dechex(bcadd(hexdec($gasPrice2["result"]),'15000000000'));
        $estimateGas = $gethrpc->eth_estimateGas($data);
        $data["value"]="0x".$value;
        $data["gasPrice"]=$gasp;
        $data["gas"]=$estimateGas['result'];

        $gethrpc->personal_unlockAccount($data["from"],$password);//解锁
        //dd($amount);
        $result = $gethrpc->eth_sendTransaction($data);//发送
        //dd($result);
        DB::table('token_transactions')->insert(array('hash'=>$result['result'],'update_time'=>date('Y-m-d H:i:s')));
        DB::table('token_transactions_details')->insert(array('hash'=>$result['result'],'update_time'=>date('Y-m-d H:i:s')));
        return 200;
    }

    //获取erc详情
    public function findERC20TransactionByHash(Request $request){

        $hash=$request->input('erc20_tx_hash','');
        $apikey=$request->input('apikey','');
        //判断apikey
        $keyinfo=DB::table('apikeys')->where('apikey',$apikey)->first();
        //dd($keyinfo);
        if($keyinfo){
            $info=DB::table('erc20_transactions')->where('erc20_tx_hash',$hash)->first();
            if(!$info){
                $data['code']=403;
                $data['message']='hash没有找到';
                return $data;
            }
            $gethrpc=new Eth(config('app.eth'));//测试网络
            $blockNumber=$gethrpc->eth_blockNumber();
            $blockNumber=hexdec($blockNumber["result"]);
            $info->block_confirmations=$blockNumber-$info->erc20_block_number+1;
            $info->time_stamp=$blockNumber-$info->erc20_block_number;

            $gethrpc=new Eth(config('app.eth'));//测试网络
            $task_message=$gethrpc->eth_getBlockByNumber("0x".dechex($info->erc20_block_number),false);
            $info->time_stamp=hexdec($task_message['result']['timestamp']);
            //获取token单位
            $tokeninfo=DB::table('erc20_contracts1')->where('erc20_address',$info->erc20_token)->first();
            if(!$tokeninfo){
                $data['code']=403;
                $data['message']='token小数点没有设置bcdiv';
                return $data;
            }
            $c='1';
            for ($i=0;$i<$tokeninfo->decimals;$i++){
                $c.='0';
            }

            $info->erc20_value=bcdiv($info->erc20_value,$c,$tokeninfo->decimals);
            $data['code']=200;
            $data['data']=$info;
            return $data;
        }else{
            $data['code']=403;
            $data['message']='apikey错误';
            return $data;
        }

    }
    public function findERC20TransactionByHash_bk(Request $request){

        $hash=$request->input('erc20_tx_hash','');
        $apikey=$request->input('apikey','');
        //判断apikey
        $keyinfo=DB::table('apikeys')->where('apikey',$apikey)->first();
        //dd($keyinfo);
        if($keyinfo){
            $info=DB::table('erc20_transactions')->where('erc20_tx_hash',$hash)->first();
            if(!$info){
                $data['code']=403;
                $data['message']='hash没有找到';
                return $data;
            }
            $gethrpc=new Eth(config('app.eth'));//测试网络
            $blockNumber=$gethrpc->eth_blockNumber();
            $blockNumber=hexdec($blockNumber["result"]);
            $info->block_confirmations=$blockNumber-$info->erc20_block_number+1;
            $info->time_stamp=$blockNumber-$info->erc20_block_number;

            $uri = config('app.eth_api_wai')."/api?module=block&action=getblockreward&blockno=".$info->erc20_block_number."&apikey=3FVDDCH2IJRZYUDDSA69WA8EAUAGC8HZXQ";
            $task_message = json_decode(file_get_contents($uri), true);
            $info->time_stamp=$task_message['result']['timeStamp'];
            //获取token单位
            $tokeninfo=DB::table('erc20_contracts1')->where('erc20_address',$info->erc20_token)->first();
            if(!$tokeninfo){
                $data['code']=403;
                $data['message']='token小数点没有设置bcdiv';
                return $data;
            }
            $c='1';
            for ($i=0;$i<$tokeninfo->decimals;$i++){
                $c.='0';
            }

            $info->erc20_value=bcdiv($info->erc20_value,$c,$tokeninfo->decimals);
            $data['code']=200;
            $data['data']=$info;
            return $data;
        }else{
            $data['code']=403;
            $data['message']='apikey错误';
            return $data;
        }
    }

    public function curl_post($url , $data=array()){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;

    }

    public function curl_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        if($output!='Invalid Request'){
            $output = json_decode($output, true);
        }
        return $output;
    }


    public function getFee(){
//        die('1');
        ini_set('max_execution_time',1000);
        //$start=date("Y-m-d H:i:s",strtotime('-10 hour'));
        $start=date("Y-m-d H:i:s",strtotime('-2 day'));
        $end=date("Y-m-d H:i:s",strtotime('-1 day'));
        $data=DB::table('token_confirm')->where("update_time",">=",$start)->where("update_time","<=",$end)->get();
//        print_r($data);
        //$data=DB::table('token_confirm')->where("update_time","<=",$end)->get();
        $toAddress=array();
        foreach ($data as $value){

            if(in_array($value->to,$toAddress)){

            }else{

                $toAddress[]=$value->to;
                $keyinfo=DB::table('accounts')->where('address',$value->to)->where('platformName','ja')->first();
                if($keyinfo){
                    $uri = config('app.eth_api_wai')."/api?module=account&action=tokenbalance&contractaddress=0x55652ce84D686177c8946E8c78078c0d6CfA4b30&address=$value->to&tag=latest&apikey=3FVDDCH2IJRZYUDDSA69WA8EAUAGC8HZXQ";
                    $task_message = json_decode(file_get_contents($uri), true);
                    if($task_message['status']=='1'){
                        if($task_message['result']>0){
                            DB::table('token_confirm')->where('id',$value->id)->update(array('confirm'=>2,'status'=>0,'fee'=>0,'amount'=>$task_message['result']));
                        }
                    }
                    //sleep(1);
                }
            }
        }
        return 1;
    }

    private function blockNumber(){

        try {
            $infura=new Eth('https://mainnet.infura.io/v3/ca6382c272c94b5ab65937ce7213e94f');//infura网络
            $infura_data=$infura->eth_blockNumber();
            $gethrpc=new Eth(config('app.eth'));//geth网络
            $gethrpc_data=$gethrpc->eth_blockNumber();
            $small=hexdec($infura_data['result'])-5;
            $big=hexdec($infura_data['result'])+5;
            if($small<=hexdec($gethrpc_data['result']) and $big>=hexdec($gethrpc_data['result']) ){
                return 1;
            }
            //$result=$gethrpc->personal_newAccount('company');
            //$result = $gethrpc->personal_unlockAccount('0x40bd8d76074d2ed3a185f10f7cefb96a4e6c024b','test');//解锁
            return 0;
        } catch (\Exception $exception) {
            return 0;
        }

    }

    public function token_hash(Request $request){

        try {
            $data['hash']=$request->input('erc20_tx_hash',false);
            if($data['hash']) {
                $data['to']=$request->input('erc20_to',false);
                $data['token']=$request->input('erc20_token',false);
                $data['created_at']=date("Y-m-d H:i:s");
                DB::table('token_hashs')->insert($data);
            }
            return 1;
        } catch (\Exception $exception) {
            return 0;
        }

    }
    //推送
    public function push(){
        $date= date("Y-m-d H:i:s", strtotime("-30 minute"));
        $date2= date("Y-m-d H:i:s", strtotime("-5 minute"));
        $data=DB::table('token_hashs')->where('created_at','>=',$date)->where('created_at','<=',$date2)->get();
        $urls=array();
        foreach ($data as $value){
			$keyinfo=DB::table('token_confirm')->where('hash',$value->hash)->first();
			if($keyinfo->confirm>=12){
				try {
                $url = 'http://127.0.0.1/api/receiveERC?erc20_tx_hash='.$value->hash.'&erc20_to='.$value->to.'&erc20_token=0x55652ce84D686177c8946E8c78078c0d6CfA4b30';
                $urls[]= $url;
                file_get_contents($url);
//                $url = 'https://portal.prancegoldholdings.com/erc_api?hash='.$value->hash.'&to='.$value->to.'&api_key=Rd5m4Vy42zERBPTb';
//                $task_message = file_get_contents($url);
//                DB::table('token_boss_get')->insert(array('hash'=>$value->hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$value->to,'data'=>$task_message));
				} catch (\Exception $exception) {

				}
			}
            
        }
        $date2= date("Y-m-d H:i:s", strtotime("-30 day"));
        DB::table('token_boss_get')->where('update_time','<',$date2)->delete();
        return $urls;

    }

    //推送
    public function sendErc20(){
        $data['from']='0x4c04ab9adb2d06ef43b777949f886d3c977f10a7';
        $data['password']='company';
        $data['to']='0x05105c27636daef0f274e90e69f5f917cf978e27';
        $data['amount']='751000000000';
        $contract='0x55652ce84D686177c8946E8c78078c0d6CfA4b30';
        //jsonrpc
        $gethrpc=new Eth(config('app.eth'));//测试网络
        //$gethrpc=new Eth('http://127.0.0.1:2406');//本机
        $balance=$gethrpc->eth_getBalance($data['from'],'latest');
        $balance=hexdec($balance["result"]);
        if($balance<80000000000000){
            $data['code']=402;
            $data['message']='以太坊不足';
            return $data;
        }
        //获取价格
        $ethGasPrice=$gethrpc->eth_gasPrice();
        $gasPrice3 = hexdec($ethGasPrice['result']);
        //erc20 链接钱包
        $geth = new EthereumRPC(config('app.eth_ip'),config('app.eth_port'));//测试网络
        //$geth = new EthereumRPC("127.0.0.1",2406);//本机
        $erc20 = new ERC20($geth);
        //合同
        //$contract = $contract; // ERC20 contract address
        $payer = $data['from']; // Sender's Ethereum account
        $payee = $data['to']; // Recipient's Ethereum account
        $amount=$data['amount'];

        //计算转出金额
        $token = $erc20->token($contract);
        $data["data"] = $token->encodedTransferData($payee,$amount);
        $gasPrice2= bcdiv(bcmul($gasPrice3,'2',18), "1000000000000000000",18);
        $transaction = $geth->personal()->transaction($payer, $contract)->gas(60000,'0.000000015')->amount("0")->data($data["data"]); // Our encoded ERC20 token transfer data from previous step
        //dd($transaction,$data["data"],$gasPrice2,$amount);
        $res = $transaction->send($data['password']); // Replace "secret" with actual passphrase of SENDER's ethereum
        DB::table('token_transactions')->insert(array('hash'=>$res,'update_time'=>date('Y-m-d H:i:s')));
        DB::table('token_transactions_details')->insert(array('hash'=>$res,'update_time'=>date('Y-m-d H:i:s')));
        $r_data['code']=200;
        $r_data['message']=$res;
        return $r_data;
    }


    public function getApi($hash){
        //$hash='0xaf67ac4758c2c8fbff8269a6556f2e93d5e7288db8084ae1a289e80b655b7cc1';
        try {
            $info=DB::table('erc20_transactions')->where('erc20_tx_hash',$hash)->first();
            if(!$info){
                DB::table('token_boss_get')->insert(array('hash'=>$hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>'','data'=>'hash找不到'));
                return 0;
            }
            $gethrpc=new Eth(config('app.eth'));//测试网络
            $blockNumber=$gethrpc->eth_blockNumber();
            $blockNumber=hexdec($blockNumber["result"]);
            $info->block_confirmations=$blockNumber-$info->erc20_block_number+1;

            $task_message=$gethrpc->eth_getBlockByNumber("0x".dechex($info->erc20_block_number),false);
            $info->time_stamp=hexdec($task_message['result']['timestamp']);
            //获取token单位
            $tokeninfo=DB::table('erc20_contracts1')->where('erc20_address',$info->erc20_token)->first();
            if(!$tokeninfo){
                DB::table('token_boss_get')->insert(array('hash'=>$hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>'','data'=>'token小数点没有设置bcdiv'));
                return 0;
            }
            $c='1';
            for ($i=0;$i<$tokeninfo->decimals;$i++){
                $c.='0';
            }
            $info->erc20_value=bcdiv($info->erc20_value,$c,$tokeninfo->decimals);
            $key=md5($info->erc20_to.$info->erc20_token.$info->erc20_tx_hash.$info->block_confirmations.$info->time_stamp.$info->erc20_value.'Rd5m4Vy42zERBPTb'.'secret');
            $url2 = 'https://client.rcmfx.com/erc_api?hash='.$info->erc20_tx_hash.'&to='.$info->erc20_to.'&api_key='.$key.'&time_stamp='.$info->time_stamp.'&block_confirmations='.$info->block_confirmations.'&token='.$info->erc20_token.'&value='.$info->erc20_value;
            $task_message2 = file_get_contents($url2);
            //dd($url2,$task_message2);
            //$task_message2 = json_decode(file_get_contents($url2),true);
            DB::table('token_boss_get')->insert(array('hash'=>$info->erc20_tx_hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>$info->erc20_to,'data'=>$task_message2));
            return 1;
        } catch (\Exception $exception) {
            DB::table('token_boss_get')->insert(array('hash'=>$hash,'update_time'=>date('Y-m-d H:i:s'),'to'=>'','data'=>'发生错误'));
            return 1;

        }

    }

    public function  noce($to){
        $gethrpc=new Eth(config('app.eth'));

        $bal=$gethrpc->eth_getTransactionCount($to,'latest');
        return $bal['result'];
    }

    //发送eth
    public function tsendeth(Request $request){

        $add=DB::table('token_confirm')->select('to')->where("update_time",">=",'2020-06-10 03:01:00')->where("update_time","<=",'2020-07-15 23:30:00')->get();
        $a=array();
        foreach ($add as $value){
            $a[]=$value->to;

        }
        $a=array_unique($a);
        //dd($a);
        $b=array();
        $c=0;
        foreach ($a as $value){
            $c++;
            try {
                $b[] = $this->sendETH2($value, 'ErCPGsysPa$$', '0xeeAA8D2d1DC695A9C696A9713487aaf58174a574');
            } catch (\Exception $exception) {

            }
        }
        dd($c,$b);
//        $a = $this->sendETH2(config('app.getFeeAddress'), config('app.getFeeAddressPassword'), '0xe542682543098ba8f023061eb1034ae38e1feda5', '0.00282');
//        dd($a);
        $to=$request->to;
        $gethrpc=new Eth(config('app.eth'));
        $gasPrice2 = $gethrpc->eth_gasPrice();
        $gasp='0x'.dechex(bcadd(hexdec($gasPrice2["result"]),'10000000000'));
        dd($gasp);

        $bal=$gethrpc->eth_getBalance($to,'latest');
        $to_bal=hexdec($bal['result']);
        if ($to_bal<3000000000000000) {
            $amoubt = bcdiv(bcsub('3000000000000000', $to_bal), '1000000000000000000', 18);
            dd($amoubt,$to_bal,$to);
            $a = $this->sendETH2(config('app.getFeeAddress'), config('app.getFeeAddressPassword'), $to, $amoubt);
            dd($a,$to_bal,$to);
        }
        dd($to_bal,$to);
    }

    //发送usdt
    public function tsenduset(Request $request){

        $gethrpc=new Eth(config('app.eth'));

        $to='0xb6ac1f9a097fa9aaad90fa9d71887875101fa79e';
        $bal=$gethrpc->eth_getBalance($to,'latest');
        $to_bal=hexdec($bal['result']);
        if ($to_bal<3000000000000000) {
            $amoubt = bcdiv(bcsub('3000000000000000', $to_bal), '1000000000000000000', 18);
            //dd($amoubt,$to_bal,$to);
            //$a = $this->sendETH2(config('app.getFeeAddress'), config('app.getFeeAddressPassword'), $to, $amoubt);
            //dd($a,$to_bal,$to);
        }
        dd($to_bal,$to);
    }

    //发送eth
    public function sendETH2($from,$password,$to='',$amount=0){
        $data['from']=$from;
        if($to===''){
            $data['to']=config('app.ethAddress');
        }else{
            $data['to']=$to;
        }
//        $password=$password;
        //全部转出
        //$result = $client->eth_accounts();
        $gethrpc=new Eth(config('app.eth'));//测试网络
        //$gasPrice = $gethrpc->eth_gasPrice();
        $gasPrice['result'] = '0xba43b7400';
        $gasPrice=bcdiv(hexdec($gasPrice["result"]),"1000000000000000000",18);
        //$result1 = $client->eth_getBalance("0xb3b910d79399eb74f7f04dc4568893450bf843e2","latest");
        $result2 = $gethrpc->eth_getBalance($data['from'],"latest");
        $data["from"]=$data['from'];
        $data["to"]=$data['to'];
        if($amount==0){
            $data["value"]=$result2['result'];
            $result3 = $gethrpc->eth_estimateGas($data);
            $result3=bcmul($gasPrice,hexdec($result3["result"]),18);    //eth_estimateGas x gas_price
            $result2=bcdiv(hexdec($result2['result']),"1000000000000000000",18);
            $aaa=bcsub($result2,$result3,18);
            $value=dechex(bcmul($aaa,"1000000000000000000"));//减掉要消耗的费用并转十六进制
        }else{
            $value=dechex(bcmul($amount,"1000000000000000000"));//减掉要消耗的费用并转十六进制
        }
        $gasPrice2 = $gethrpc->eth_gasPrice();
        $estimateGas = $gethrpc->eth_estimateGas($data);
        $data["value"]="0x".$value;
        //$data["value"]="0x13bc628d255ff12d1";
        //$data["gasPrice"]=$gasPrice2['result'];
        $data["gasPrice"]='0xba43b7400';
        $data["gas"]=$estimateGas['result'];
        //$noce=$this->noce($from);
        //$data["nonce"]="0x6c7e";
        //$data["nonce"]=$noce;
        //dd($data,$gasPrice2,$gasPrice);
        //dd($data);
        $gethrpc->personal_unlockAccount($data["from"],$password);//解锁
        //dd($data);
        $result = $gethrpc->eth_sendTransaction($data);//发送
        return $result['result'];
        dd($result);
        DB::table('token_transactions')->insert(array('hash'=>$result['result'],'update_time'=>date('Y-m-d H:i:s')));
        DB::table('token_transactions_details')->insert(array('hash'=>$result['result'],'update_time'=>date('Y-m-d H:i:s')));
        return 200;
    }

    //发送USDT
    //发送erc20
    public function sendERC2($from,$password,$amount,$contract){
        $data['from']=$from;
        $data['password']=$password;
        $data['to']='0x1837227302d369d9501DdC3C647E6B21E8b46291';
        $data['amount']=$amount;

        //jsonrpc
        //$gethrpc=new Eth(config('app.eth'));//测试网络
        $gethrpc=new Eth('http://148.66.50.26:2406');//测试网络
        //$gethrpc=new Eth('http://127.0.0.1:2406');//本机
        $balance=$gethrpc->eth_getBalance($data['from'],'latest');
        $balance=hexdec($balance["result"]);
        if($balance<80000000000000){
            $data['code']=402;
            $data['message']='以太坊不足';
            return $data;
        }
        //获取价格
        $ethGasPrice=$gethrpc->eth_gasPrice();
        $gasPrice3 = hexdec($ethGasPrice['result']);
        //erc20 链接钱包
        //$geth = new EthereumRPC(config('app.eth_ip'),config('app.eth_port'));//测试网络
        $geth = new EthereumRPC("148.66.50.26",2406);//本机
        $erc20 = new ERC20($geth);
        //合同
        //$contract = $contract; // ERC20 contract address
        $payer = $data['from']; // Sender's Ethereum account
        $payee = $data['to']; // Recipient's Ethereum account
        $amount=$data['amount'];

        //计算转出金额
        $token = $erc20->token($contract);
        //dd($payee,$amount);
        $data["data"] = $token->encodedTransferData($payee,$amount);
        $gasPrice2= bcdiv(bcmul($gasPrice3,'2',18), "1000000000000000000",18);
        $transaction = $geth->personal()->transaction($payer, $contract)->gas(60000,'0.000000050')->amount("0")->data($data["data"]); // Our encoded ERC20 token transfer data from previous step
        $transaction->nonce=11670;
        //dd($transaction,$data["data"],$gasPrice2,$amount);
        $res = $transaction->send($data['password']); // Replace "secret" with actual passphrase of SENDER's ethereum
        DB::table('token_transactions')->insert(array('hash'=>$res,'update_time'=>date('Y-m-d H:i:s')));
        DB::table('token_transactions_details')->insert(array('hash'=>$res,'update_time'=>date('Y-m-d H:i:s')));
        $r_data['code']=200;
        $r_data['message']=$res;
        dd($r_data);
        return 200;
    }


}
