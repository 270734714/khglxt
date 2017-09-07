<?php
/**
 * User: Jasmine2
 * Date: 2017-7-4 22:37
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;
use think\Request;

class Tools extends BasicAdmin
{
    /**
     * 更新手机号码归属地库
     */
    public function updateMobile()
    {
        $mobile = $this->request->post('id');
        $res = file_get_contents("https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?cb=jQuery110205710696752725024_1499178652665&resource_name=guishudi&query={$mobile}&_=1499178652667");
        $res = @mb_convert_encoding($res, 'utf-8', 'gbk');
        $res = json_decode(substr($res, 46, -2), 1);
        if($res['status'] == 0) {
            $data = $res['data'][0];
            Db::name('MobileRegion')->insert(
                [
                'mobile'    => $data['key'],
                'province'  => $data['prov'],
                'city'      => $data['city'],
                'corp'   => $data['type']
                ]
            );
        }

        $this->success('更新成功!', '');
    }

    public function updateMobileRegion()
    {
        ignore_user_abort(1);
        set_time_limit(0);
        for ($i = 1;$i<=9999;$i++){
            $mobile = sprintf("173%04d0000", $i);
            $res = file_get_contents("https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?cb=jQuery110205710696752725024_1499178652665&resource_name=guishudi&query={$mobile}&_=1499178652667");
            $res = @mb_convert_encoding($res, 'utf-8', 'gbk');
            $res = json_decode(substr($res, 46, -2), 1);
            if($res['status'] == 0 && isset($res['data'][0])) {
                $data = $res['data'][0];
                Db::name('MobileRegion')->insert(
                    [
                    'mobile'    => $data['key'],
                    'province'  => $data['prov'],
                    'city'      => $data['city'],
                    'corp'   => $data['type']
                    ]
                );
            }
        }

    }

    /**
     * 发表动态
     */
    public function comment(Request $request)
    {
        Db::name('v9_bpm_news')->insert(
            [
            'addtime'   => time(),
            'pid'       => $request->request('pid', 0),
            'userid'    => session('user.id'),
            'content'   => $request->request('content')
            ]
        );
        $this->success('发表成功^_^', '');
    }
    /**
     * 回复动态
     */
    public function commentForm(Request $request)
    {
        $id = $request->request('id', 0);
        return view(
            '', [
            'id'    => $id
            ]
        );
    }
    /**
     * 回复动态
     */
    public function commentList(Request $request)
    {
        $id = $request->request('id', 0);
        $news = Db::name('v9_bpm_news')->where(['id' => $id])->find();
        $child_news = Db::name('v9_bpm_news')->where(['pid' => $id])->select();
        return view(
            '', [
            'news'  => $news,
            'child_news'    => $child_news
            ]
        );
    }
    /**
     * 删除动态
     */
    public function delComment()
    {
        if (Db::name('v9_bpm_news')->where('id', 'in', input('id'))->delete()) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

}
