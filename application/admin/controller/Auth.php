<?php

/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;

/**
 * 系统权限管理控制器
 * Class Auth
 */
class Auth extends BasicAdmin
{

    /**
     * 默认数据模型
     *
     * @var string
     */
    public $table = 'SystemAuth';

    /**
     * 权限列表
     */
    public function index() 
    {
        $this->title = '会员组';
        return parent::_list($this->table);
    }

    /**
     * 权限授权
     *
     * @return string|array
     */
    public function apply() 
    {
        $auth_id = $this->request->get('id', '0');
        switch (strtolower($this->request->get('action', '0'))) {
        case 'getnode':
            $nodes = NodeService::get();
            $checked = Db::name('SystemAuthNode')->where('auth', $auth_id)->column('node');
            foreach ($nodes as $key => &$node) {
                $node['checked'] = in_array($node['node'], $checked);
                if (empty($node['is_auth']) && substr_count($node['node'], '/') > 1) {
                    unset($nodes[$key]);
                }
            }
            $this->success('获取节点成功！', '', $this->_filterNodes($this->_filterNodes(ToolsService::arr2tree($nodes, 'node', 'pnode', '_sub_'))));
            break;
        case 'save':
            $data = [];
            $post = $this->request->post();
            foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
                $data[] = ['auth' => $auth_id, 'node' => $node];
            }
            Db::name('SystemAuthNode')->where('auth', $auth_id)->delete();
            Db::name('SystemAuthNode')->insertAll($data);
            $this->success('节点授权更新成功！', '');
            break;
        default :
            $this->assign('title', '节点授权');
            return $this->_form($this->table, 'apply');
        }
    }

    /**
     * 节点数据拼装
     *
     * @param  array $nodes
     * @param  int   $level
     * @return array
     */
    protected function _filterNodes($nodes, $level = 1) 
    {
        foreach ($nodes as $key => &$node) {
            if (!empty($node['_sub_']) && is_array($node['_sub_'])) {
                $node['_sub_'] = $this->_filterNodes($node['_sub_'], $level + 1);
            } elseif ($level < 3) {
                unset($nodes[$key]);
            }
        }
        return $nodes;
    }

    /**
     * 权限添加
     */
    public function add() 
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 权限编辑
     */
    public function edit() 
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 权限禁用
     */
    public function forbid() 
    {
        if (DataService::update($this->table)) {
            $this->success("权限禁用成功！", '');
        }
        $this->error("权限禁用失败，请稍候再试！");
    }

    /**
     * 权限恢复
     */
    public function resume() 
    {
        if (DataService::update($this->table)) {
            $this->success("权限启用成功！", '');
        }
        $this->error("权限启用失败，请稍候再试！");
    }

    /**
     * 权限删除
     */
    public function del() 
    {
        if (DataService::update($this->table)) {
            $id = $this->request->post('id');
            Db::name('SystemAuthNode')->where('auth', $id)->delete();
            $this->success("权限删除成功！", '');
        }
        $this->error("权限删除失败，请稍候再试！");
    }
    /**
     * 列表数据处理
     *
     * @param array $data
     */
    protected function _index_data_filter(&$data) 
    {
        foreach ($data as &$vo) {
            $vo['ids'] = join(',', ToolsService::getArrSubIds($data, $vo['id']));
        }
        $data = ToolsService::arr2table($data);
    }
    /**
     * 表单数据前缀方法
     *
     * @param array $vo
     */
    protected function _form_filter(&$vo) 
    {
        if ($this->request->isGet()) {
            // 上级菜单处理
            $_menus = Db::name($this->table)->where('status', '1')->order('sort desc,id desc')->select();
            $_menus[] = ['title' => '顶级会员', 'id' => '0', 'pid' => '-1'];
            $menus = ToolsService::arr2table($_menus);
            foreach ($menus as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) {
                    unset($menus[$key]);
                    continue;
                }
                if (isset($vo['pid'])) {
                    $current_path = "-{$vo['pid']}-{$vo['id']}";
                    if ($vo['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
                        unset($menus[$key]);
                    }
                }
            }
            $this->assign('menus', $menus);
        }
    }
}
