<?php

namespace Kellerworld\Laravelip;
use Illuminate\Support\Facades\DB;
class CheckIP
{
    public static function checkIP($ip)
    {
//        var_dump($ip);die;
        DB::connection()->enableQueryLog();
        $list=DB::table('blacklist')->where('ip',$ip)->get();
//        var_dump(DB::getQueryLog());
        var_dump($list[0]->ip);
//        var_dump(DB::table('blacklist')->where('ip',$ip)->get());
    }
}


