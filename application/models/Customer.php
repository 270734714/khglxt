<?php
/**
 * User: Jasmine2
 * Date: 2017-6-8
 * Time: 10:20
 */

namespace app\models;


use think\Model;

/**
 * Class Customer
 * @package app\models
 * @property Wallet wallet
 */
class Customer extends Model
{
    //public $table = 'v9_gn_customer';


    // public static function getCustomer($id){
    //     $customer = self::where(['id' => $id,'del' => 0])->find();
    //     return $customer;
    // }

    // public function wallet(){
    //     return $this->hasOne(Wallet::class,'cid','id');
    // }
}