<?php

namespace Kellerworld\Laravelip;
use Illuminate\Support\Facades\DB;
class CheckIP
{
    /*
     * 获取ip
     * 检查黑名单中是否存在
     * 存在则抛出异常
     * 不存在则忽略
     */
    public static function checkIP($ip)
    {
        //---------throw a exception
//        throw new \App\Exceptions\CustomException('Something Went Wrong.');
//        DB::connection()->enableQueryLog();
        $list=DB::table('blacklist')->where('ip',$ip)->get();
//        var_dump(DB::getQueryLog());
        var_dump($list[0]->ip);
        if(empty($list)){
            self::addBlacklist($ip);
        }else{

        }
//        var_dump(DB::table('blacklist')->where('ip',$ip)->get());
    }
    public function addBlacklist($ip){
        DB::table('blacklist')->where('ip',$ip)->get();
        DB::table('blacklist')->insert(
            [
                'ip' => $ip,
                'created_at' => date('Y-m-d H:i:s',time()),
                'updated_at' => date('Y-m-d H:i:s',time())
            ]
        );
    }
    public function isNonUS($ip){

    }
}


