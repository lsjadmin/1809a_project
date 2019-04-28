<?php

namespace App\Http\Controllers\Wei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class WeiController extends Controller
{
    //
    public function valid(){
        echo $_GET['echostr'];
    }
    public function wxEvent(){
        //接受微信服务器推送
        $content=file_get_contents("php://input");
        $time=date("Y-m-d H:i:s");
        $str=$time . $content ."\n";
        file_put_contents("logs/wx_event.log",$str,FILE_APPEND);
         // echo 'SUCCESS';
        $data = simplexml_load_string($content);
        // var_dump($data);
        //echo 'ToUserName:'.$data->ToUserName;echo"</br>";//微信号id
        //echo 'FromUserName:'.$data->FromUserName;echo"</br>";//用户openid
        //echo 'CreateTime:'.$data->CreateTime;echo"</br>";//时间
        //echo 'Event:'.$data->Event;echo"</br>";//事件类型
       //die;
        $MsgType=$data->MsgType;
        $openid=$data->FromUserName;
       // echo $openid;die;
        $wx_id=$data->ToUserName;
        $event=$data->Event;
        $MediaId=$data->MediaId;
        $token=accessToken();
        
        //把文本存到数据库 ,图片，语音存到数据库
        if($MsgType=='text'){
            $m_text=$data->Content;
            //把文字信息存到数据库
            $m_time=$data->CreateTime;
            $message=[
                'm_text'=>$m_text,
                'm_time'=>$m_time,
                'm_openid'=>$openid
            ];
            $res=DB::table('wx_message')->insert($message);
            if($res){
               // echo "成功";
            }else{
               // echo "失败";
            }
            //echo $Content;
        }
        
        if($event=='subscribe'){
            $EventKey=$data->EventKey;
            //echo "$EventKey";die;
            $str=substr($EventKey,8);
            echo $str;
            $arr=$this->getUserInfo($openid);
            //echo'<pre>';print_r($arr);echo'</pre>';
            $info=[
                'openid'=>$openid,
                'nickname'=>$arr['nickname'],
                'eventkey'=>$str,
            ];
            $res=DB::table('wx_user_code')->insert($info);
           
           
            //dd($res);
                $name="最新商品";
                $desc="最新商品";
               
                
                $url='https://1809lianshijie.comcto.com/detail/2';
            echo '<xml>
                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>
                  <item>
                    <Title><![CDATA['.$name.']]></Title>
                    <Description><![CDATA['.$desc.']]></Description>
                    <PicUrl><![CDATA['.'https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=2984185296,2196422696&fm=27&gp=0.jpg'.']]></PicUrl>
                    <Url><![CDATA['.$url.']]></Url>
                  </item>
                </Articles>
              </xml>';
            // $whereOpenid=[
            //     'openid'=>$openid
            // ];
            // $userName=DB::table('userwx')->where($whereOpenid)->first();
            // if($userName){
            //         echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
            //         <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
            //         <CreateTime>'.time().'</CreateTime>
            //         <MsgType><![CDATA[text]]></MsgType>
            //        <Content>![CDATA['.'欢迎回来'.$userName->nickname.']]</Content>
            //         </xml>
            //         ';
            // }else{
            //     $u=$this->getUserInfo($openid);
            //     $info=[
            //         'openid'=>$openid,
            //         'nickname'=>$u['nickname'],
            //         'subscribe_time'=>$u['subscribe_time']
            //     ];
            //     $res=DB::table('userwx')->insert($info);
            //     if($res){
            //         echo "ok";
            //     }else{
            //         echo "no";
            //     }
            //     echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
            //         <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
            //         <CreateTime>'.time().'</CreateTime>
            //         <MsgType><![CDATA[text]]></MsgType>
            //        <Content>![CDATA['.'欢迎关注'.$u['nickname'].']]</Content>
            //         </xml>
            //         ';
            // }
        }
       
        if($event=='SCAN'){
         
            //dd($res);
                $name="欢迎回来";
                $desc="欢迎回来";
               
                
                $url='https://1809lianshijie.comcto.com/detail/2';
               
                echo '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wx_id.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                      <item>
                        <Title><![CDATA['.$name.']]></Title>
                        <Description><![CDATA['.$desc.']]></Description>
                        <PicUrl><![CDATA['.'https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=2984185296,2196422696&fm=27&gp=0.jpg'.']]></PicUrl>
                        <Url><![CDATA['.$url.']]></Url>
                      </item>
                    </Articles>
                  </xml>';
           
        }

    }
                                
    //获取用户信息
    public function getUserInfo($openid){
       $access_token=accessToken();
        $a='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        //echo $a;
        $data=file_get_contents($a);
        $u=json_decode($data,true);
        return $u;
    }
    //获取自定义菜单
    public function a(){
        $access_token=accessToken();
        echo $access_token;
    }
    
    
}
                            
