<?php
/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * 助手函数
 */


use service\DataService;
use service\NodeService;
use Wechat\Loader;
use think\Db;
use think\Cache;
require_once 'html_helper.php';
/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function p($data, $replace = false, $pathname = NULL) {
    is_null($pathname) && $pathname = RUNTIME_PATH . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . "\n";
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}
function get_random_string($prefix = '', $length = 32) {
    $ss = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = $prefix;
    for($i = 0;$i < $length - strlen($prefix);$i++) {
        $str .= $ss[random_int(0,61)];
    }
    return $str;
}
/**
 * 获取微信操作对象
 * @param string $type
 * @return \Wechat\WechatReceive|\Wechat\WechatUser|\Wechat\WechatPay|\Wechat\WechatScript|\Wechat\WechatOauth|\Wechat\WechatMenu
 */
function & load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        $config = [
            'token'          => sysconf('wechat_token'),
            'appid'          => sysconf('wechat_appid'),
            'appsecret'      => sysconf('wechat_appsecret'),
            'encodingaeskey' => sysconf('wechat_encodingaeskey'),
            'mch_id'         => sysconf('wechat_mch_id'),
            'partnerkey'     => sysconf('wechat_partnerkey'),
            'ssl_cer'        => sysconf('wechat_cert_cert'),
            'ssl_key'        => sysconf('wechat_cert_key'),
            'cachepath'      => CACHE_PATH . 'wxpay' . DS,
        ];
        $wechat[$index] = Loader::get($type, $config);
    }
    return $wechat[$index];
}

/**
 * 安全URL编码
 * @param array|string $data
 * @return string
 */
function encode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(serialize($data)));
}

/**
 * 安全URL解码
 * @param string $string
 * @return string
 */
function decode($string) {
    $data = str_replace(['-', '_'], ['+', '/'], $string);
    $mod4 = strlen($data) % 4;
    !!$mod4 && $data .= substr('====', $mod4);
    return unserialize(base64_decode($data));
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node) {
    return NodeService::checkAuthNode($node);
}

/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是false为获取值，否则为更新
 * @return string|bool
 */
function sysconf($name, $value = false) {
    static $config = [];
    if ($value !== false) {
        $config = [];
        $data = ['name' => $name, 'value' => $value];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        foreach (Db::name('SystemConfig')->select() as $vo) {
            $config[$vo['name']] = $vo['value'];
        }
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * array_column 函数兼容
 */
if (!function_exists("array_column")) {

    function array_column(array &$rows, $column_key, $index_key = null) {
        $data = [];
        foreach ($rows as $row) {
            if (empty($index_key)) {
                $data[] = $row[$column_key];
            } else {
                $data[$row[$index_key]] = $row[$column_key];
            }
        }
        return $data;
    }

}
/**
 * send_sms 发送短信
 */
if (!function_exists("send_sms")) {

    function send_sms($mobile, $content) {
        $channel = sysconf('site_sms_type');
        $channel = "\\sms\\" . $channel;
        $sms_channel = new $channel('other');
        return $sms_channel->send($mobile, $content);
    }
}

/**
 * send_sms 发送短信,验证码
 */
if (!function_exists("send_sms_captcha")) {

    function send_sms_captcha($mobile, $content) {
        $channel = sysconf('sms_type_captcha');
        $channel = "\\sms\\" . $channel;
        $sms_channel = new $channel('captcha');
        return $sms_channel->send($mobile, $content);
    }
}
/**
 * api_encrypt API加密 , 私钥
 */
if (!function_exists("api_encrypt")) {

    function api_encrypt($in) {
        $json = \GuzzleHttp\json_encode($in);
        $un_encrypt = base64_encode($json);
        $key    = sysconf('api_key');
        $iv     = sysconf('api_iv');
        if(!$key || !$iv){
            return "未配置接口秘钥";
        }
        $encrypted = openssl_encrypt($un_encrypt, 'aes-128-cbc', $key,false, $iv);
        return base64_encode($encrypted);
    }
}

/**
 * api_decrypt  api解密 ,私钥
 */
if (!function_exists("api_decrypt")) {

    function api_decrypt($in) {
        $un_decrypt = base64_decode($in);
        $key    = sysconf('api_key');
        $iv     = sysconf('api_iv');
        $decrypted = openssl_decrypt($un_decrypt, 'aes-128-cbc', $key, false, $iv);
        if($decrypted){
            return \GuzzleHttp\json_decode(base64_decode($decrypted), 1);
        } else {
            return [];
        }
    }
}

/**
 * get_dictionary  取字典值
 */
if (!function_exists("get_dictionary")) {
    function get_dictionary($key, $category) {
        if(!Cache::has('dic')){
            $dic = Db::table('system_dictionary')->order('category desc,id asc')->select();
            $_dic = [];
            foreach ($dic as $item){
                $_dic[$item['category']][$item['value']] = $item['desc'];
            }
            Cache::set('dic', $_dic, 0);
        } else {
            $dic = Cache::get('dic');
        }
        if(isset($dic[$category][$key])){
            return $dic[$category][$key];
        }
        return 0;
    }
}
/**
 * get_all_dictionary_by_category  取分类下的所有字典值
 */
if (!function_exists("get_all_dictionary_by_category")) {
    function get_all_dictionary_by_category($category) {
        if(!Cache::has('dic')){
            $dic = Db::table('system_dictionary')->order('category desc,id asc')->select();
            $_dic = [];
            foreach ($dic as $item){
                $_dic[$item['category']][$item['value']] = $item['desc'];
            }
            Cache::set('dic', $_dic, 0);
        } else {
            $dic = Cache::get('dic');
        }
        if(isset($dic[$category])){
            return $dic[$category];
        }
        return [];
    }
}
/**
 * get_mime 取文件后缀名
 */
if (!function_exists('get_mime')) {
    function get_mime($mime){
        $all = require_once APP_PATH . DS . 'mimes.php';
        $r = '';
        foreach ($all as $key => $item){
            if(is_array($item)){
                if(in_array($mime,$item)){
                    $r = $key;
                    break;
                }
            } elseif($item == $mime){
                $r = $key;
                break;
            }
        }
        return $r;
    }
}
/**
 * get_auth_name 取会员组名称
 */
if (!function_exists('get_auth_name')) {
    function get_auth_name($id){
        if(Cache::has('system_auth_' . $id)){
            $data = Cache::get('system_auth' . $id);
        } else {
            $data = \think\Db::table('system_auth')->where(['id' => $id])->find();
            Cache::set('system_auth' . $id, $data, 3600);
        }
        if($data){
            return $data['title'];
        }
        return 'not set';
    }
}

/**
 * mobile_mask 手机号码加密
 */
if (!function_exists('mobile_mask')) {
    function mobile_mask($mobile){
        if(strlen($mobile) == 11){
            return substr($mobile,0,3) . "****" . substr($mobile,-4,4);
        }
    }
}
/**
 * getContentCategory 取内容分类
 */
if (!function_exists('getContentCategory')) {
    function getContentCategory($key){
        if(Cache::has('content_category')){
            $data = Cache::get('content_category');
        } else {
            $data = \think\Db::table('v9_bpm_category')->column('name','id');
            Cache::set('content_category', $data, 86400);
        }
        if(isset($data[$key])){
            return $data[$key];
        }
        return 'not set';
    }
}
/**
 * getBankByCode 取银行名称
 */
if (!function_exists('getBankByCode')) {
    function getBankByCode($key){
        if(Cache::has('banklist')){
            $data = Cache::get('banklist');
        } else {
            $data = \think\Db::table('v9_gn_banklist')->column('bank_name', 'bank_code');
            Cache::set('banklist', $data, 3600);
        }
        if(isset($data[$key])){
            return $data[$key];
        }
        return 'not set';
    }
}
/**
 * getIpRegion 获取ip对应地址
 */
if (!function_exists('getIpRegion')) {
    function getIpRegion($ip){
        $ipr = new \Ip2Region();
        $result = $ipr->binarySearch($ip);
        $result = isset($result['region']) ? $result['region'] : '';
        return str_replace(['|0|0|0|0', '|'], ['', ' '], $result);
    }
}
/**
 * getMobileRegion 获取手机号码归属地
 */
if (!function_exists('getMobileRegion')) {
    function getMobileRegion($mobiles){
        $mobile = substr($mobiles,0,7);
        if(Cache::has('mobile_list_' . $mobile)){
            $data = Cache::get('mobile_list' . $mobile);
        } else {
            $data = \think\Db::table('mobile_region')->where(['mobile' => $mobile])->find();
            if($data)
                Cache::set('mobile_list' . $mobile, $data);
        }
        if(count($data) > 0){
            return sprintf("%s|%s%s",$data['corp'],$data['province'],$data['city']);
        }
        return '<a data-tips-text="点击更新" data-update="'.$mobiles.'" data-field="update" data-action="'.url('@admin/tools/updateMobile').'" href="javascript:void(0)"><i class="fa fa-refresh"></i></a>';
    }
}
/**
 * getIdRegion 获取身份证归属地
 */
if (!function_exists('getIdRegion')) {
    function getIdRegion($sfz){
        $id = substr($sfz,0,6);
        if(Cache::has('sfz_list')){
            $data = Cache::get('sfz_list');
        } else {
            $data = \think\Db::table('sfz_region')->column('desc','zone');
            if($data)
                Cache::set('sfz_list', $data);
        }
        if(isset($data[$id])){
            return $data[$id];
        }
        return '';
    }
}

/**
 * getSexLabel 获取性别 1,男,0,女
 */
if (!function_exists('getSexLabel')) {
    function getSexLabel($key){
        if($key === 1){
            return '男 <i class="fa fa-mars-stroke" style="color: blue"></i>';
        }
        return '女 <i class="fa fa-venus" style="color: hotpink"></i>';
    }
}


/**
 * getNickname 获取Nickname
 */
if (!function_exists('getNickname')) {
    function getNickname($userid){
        if(Cache::has('nickname_' . $userid)){
            return Cache::get('nickname_' . $userid);
        } else {
            $nickname = Db::name('system_user')->where('id' , '=' ,$userid)->value('nickname');
            if(strlen($nickname) > 0){
                Cache::set('nickname_' . $userid, $nickname, 86400);
                return $nickname;
            } else {
                return $userid;
            }
        }
    }
}

/**
 * getNickname 获取Nickname
 */
if (!function_exists('checkIp')) {
    function checkIp($ip, $range){
        if(!strripos($range, '/')){
            return ip2long($ip) === ip2long($range);
        }
        list ($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }
}

/**
 * getObjectiveLabel 获取目标产品 22,钱包,23, 风控 24 SAAS 25 其他
 */
if (!function_exists('getObjectiveLabel')) {
    function getObjectiveLabel($key){
        if($key === '22'){
            return '钱包 <i style="color: blue"></i>';
        }else if($key === '23'){
            return '风控 <i style="color: blue"></i>';
        }else if($key === '24'){
            return 'SAAS <i style="color: blue"></i>';
        }
        return '其他 <i style="color: hotpink"></i>';
    }
}

/**
 * getSource 获取客户来源 18,朋友介绍,19, 客户介绍 20 微信 21 其他
 */
if (!function_exists('getSource')) {
    function getSource($key){  
        if($key === '18'){
            return '朋友介绍 <i  style="color: blue"></i>';
        }else if($key === '19'){
            return '客户介绍 <i style="color: blue"></i>';
        }else if($key === '20'){
            return '微信 <i style="color: blue"></i>';
        }
        return '其他 <i style="color: hotpink"></i>';
    }
}

/**
 * getTypes 获取客户类型 14,直接客户,15, 介绍人 16 合作公司 17 代理
 */
if (!function_exists('getTypes')) {
    function getTypes($key){  
        if($key === '14'){
            return '直接客户 <i  style="color: blue"></i>';
        }else if($key === '15'){
            return '介绍人 <i style="color: blue"></i>';
        }else if($key === '16'){
            return '合作公司 <i style="color: blue"></i>';
        }
        return '代理 <i style="color: hotpink"></i>';
    }
}


if (!function_exists('getFollowup')) {
    function getFollowup($key){  
        if($key === '9'){
            return '促成订单 <i  style="color: blue"></i>';
        }else if($key === '10'){
            return '客户关怀 <i style="color: blue"></i>';
        }else if($key === '11'){
            return '售后服务 <i style="color: blue"></i>';
        }
    }
}



if (!function_exists('getFollowupName')) {
    function getFollowupName($key){  
       // echo $key;die;
       $followUpName = Db::name('v9_gn_customer')->where('id',$key)->find();
       return $followUpName['name'];
    }
}


if (!function_exists('getDisposals')) {
    function getDisposals($key){  
        if($key === '1'){
            return '一天后跟进 <i  style="color: blue"></i>';
        }else if($key === '2'){
            return '两天后跟进 <i style="color: blue"></i>';
        }else if($key === '3'){
            return '三天后跟进 <i style="color: blue"></i>';
        }else if($key === '4'){
            return '四天后跟进 <i style="color: blue"></i>';
        }else if($key === '5'){
            return '五天后跟进 <i style="color: blue"></i>';
        }else if($key === '6'){
            return '六天后跟进 <i style="color: blue"></i>';
        }else if($key === '7'){
            return '七天后跟进 <i style="color: blue"></i>';
        }else if($key === '8'){
            return '放弃跟进 <i style="color: blue"></i>';
        }
    }
}

if (!function_exists('getFollowdate')) {
    function getFollowdate($key){  
        return strtotime("+".$key." day");
    }
}


/**
 * getStatus 获取客户类型 12,跟进中,13, 放弃跟进
 */
if (!function_exists('getStatus')) {
    function getStatus($key){  
        if($key === '12'){
            return '跟进中 <i  style="color: blue"></i>';
        }else if($key === '13'){
            return '放弃跟进 <i style="color: blue"></i>';
        }
        return '未跟进 <i style="color: hotpink"></i>';
    }
}


/**
 * getFollowupType 获取客户服务记录服务类型
 */
if (!function_exists('getFollowupType')) {
    function getFollowupType($key){  
        if($key == '30'){
            return '远程演示 <i  style="color: blue"></i>';
        }else if($key == '31'){
            return '上门演示 <i style="color: blue"></i>';
        }else if($key == '32'){
            return '约公司演示 <i style="color: blue"></i>';
        }else if($key == '33'){
            return '合同办理 <i style="color: blue"></i>';
        }else if($key == '34'){
            return '部署跟进 <i style="color: blue"></i>';
        }else if($key == '35'){
            return '系统测试 <i style="color: blue"></i>';
        }else if($key == '36'){
            return '远程培训 <i style="color: blue"></i>';
        }else if($key == '37'){
            return '上门培训 <i style="color: blue"></i>';
        }else if($key == '38'){
            return '系统交付 <i style="color: blue"></i>';
        }else if($key == '39'){
            return '经营跟进 <i style="color: blue"></i>';
        }else if($key == '40'){
            return '缓慢跟进 <i style="color: blue"></i>';
        }else if($key == '41'){
            return '回款催促 <i style="color: blue"></i>';
        }
    }
}

//服务表服务状态替换

if (!function_exists('getServstutas')) {
    function getServstutas($key){
        if($key == '26'){
            return '服务中 <i  style="color: blue"></i>';
        }else if($key == '27'){
            return '服务完成 <i style="color: blue"></i>';
        }if($key == '28'){
            return '确认完成 <i  style="color: blue"></i>';
        }else if($key == '29'){
            return '服务取消 <i style="color: blue"></i>';
        }
    }
}  

//二维数组转化为字符串，中间用,隔开  
function arr_to_str($arr){
    $t = '';  
    foreach ($arr as $v){  
        $v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名  
        $temp[] = $v;  
    }  
    foreach($temp as $v){  
        $t.=$v.",";  
    }  
    return substr($t,0,-1);  //利用字符串截取函数消除最后一个逗号    
}  

//服务表服务状态替换

if (!function_exists('getServCom')) {
    function getServCom($key){
        if($key == '0'){
            return '<i  style="color: red">未确认</i>';
        }else if($key == '1'){
            return '<i style="color: green">已确认</i>';
        }
    }
}  

//字符串长度截取
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){  
    if(function_exists("mb_substr")){  
          if($suffix)  
          return mb_substr($str, $start, $length, $charset)."...";  
          else
               return mb_substr($str, $start, $length, $charset);  
     }  
     elseif(function_exists('iconv_substr')) {  
         if($suffix)  
              return iconv_substr($str,$start,$length,$charset)."...";  
         else
              return iconv_substr($str,$start,$length,$charset);  
     }  
     $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef]
              [x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";  
     $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";  
     $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";  
     $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";  
     preg_match_all($re[$charset], $str, $match);  
     $slice = join("",array_slice($match[0], $start, $length));  
     if($suffix) return $slice."…";  
     return $slice;
}