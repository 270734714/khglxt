<?php
/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

if (!function_exists('getRadioList')) {
    /**
     * @param $data array 数据
     * @param $name string 表单名
     * @param $label string label 名
     * @param null $default 默认选中
     * @return string Html Radio 结构
     */
    function getRadioList($data,$name,$label,$default = null){
        $str = '<div class="layui-form-item">
        <label class="layui-form-label">'.$label.'</label>
        <div class="layui-input-block">';
        foreach ($data as $k => $v){
            if($default === $k){
                $str .= "<input type=\"radio\" name=\"$name\" value=\"$k\" checked title=\"$v\">";
            } else {
                $str .= "<input type=\"radio\" name=\"$name\" value=\"$k\" title=\"$v\">";
            }
        }
        $str .= '</div></div>';
        return $str;
    }
}

if (!function_exists('getTypeName')) {
    /**
     * @param $name
     * @return string
     */
    function getTypeName($name){
        $types = config('category_type');
        return isset($types[$name])?$types[$name]:'<span class="layui-btn-mini layui-btn layui-btn-danger">not set</span>';
    }
}

if (!function_exists('getSelectList')) {
    /**
     * @param array $data  数据
     * @param string $name  表单名称
     * @param string $title  显示值
     * @param string $key  key值
     * @param string $label  label名称
     * @param null $default 默认选中
     * @param bool $spl 是否是多级列表
     * @return string
     */
    function getSelectList($data,$name,$key = 'id',$title,$label,$default = null,$spl = false,$selectOption=''){
        \think\Log::error($default);
        $str = '<div class="layui-form-item">
        <label class="layui-form-label">'.$label.'</label>
        <div class="layui-input-block">
            <select name=\''.$name.'\' class=\'layui-select full-width\' style=\'display:none\' '.$selectOption.'><option value="0">--请选择--</option>';
        foreach ($data as $k => $v){
            if($default == $v[$key]){
                if($spl)
                    $str .= "<option selected  value='".$v[$key]."'>".$v['spl'].$v[$title]."</option>";
                else
                    $str .= "<option selected  value='".$v[$key]."'>".$v[$title]."</option>";
            } else {
                if($spl)
                    $str .= "<option value='".$v[$key]."'>".$v['spl'].$v[$title]."</option>";
                else
                    $str .= "<option value='".$v[$key]."'>".$v[$title]."</option>";
            }
        }
        $str .= '</select></div></div>';
        return $str;
    }
}
if (!function_exists('aciButton')) {
    function aciButton($uri,$title,$content,$class, $options = [],$no_value_options = [])
    {
        if(auth($uri)){
            if(is_array($class)){
                $class = implode(' ', $class);
            }
            if(is_array($no_value_options)){
                $no_value_options = implode(' ', $no_value_options);
            }
            $_op = '';
            foreach ($options as $k => $v){
                $_op .= $k . '=' . $v . " ";
            }
            unset($options);
            $str = "<button class='layui-btn {$class}' {$_op} data-title='{$title}' {$no_value_options}>{$content}</button>";
            return $str;
        } else {
            return '';
        }
    }
}
if (!function_exists('aciA')) {
    function aciA($uri,$title,$content,$class, $options = [],$no_value_options = [],$params = '')
    {
        if(auth($uri)){
            if(is_array($class)){
                $class = implode(' ', $class);
            }
            if(is_array($no_value_options)){
                $no_value_options = implode(' ', $no_value_options);
            }
            $_op = '';
            foreach ($options as $k => $v){
                $_op .= $k . '=' . $v . " ";
            }
            unset($options);
            $str = "<a class='{$class}' {$_op} data-title='{$title}' {$no_value_options} data-open='".$uri.$params."'>{$content}</a>";
            return $str;
        } else {
            return '';
        }
    }
}