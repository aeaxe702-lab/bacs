<?php

namespace app\back\validate;


use app\lib\exception\ParameterException;
use think\facade\Request;
use think\Validate;

class BaseValidate extends Validate
{
    /**
     * 公共的检验参数合法性入口，参数合法则返回规则中存在的参数，参数不合法则抛出异常
     */
    public function goCheck($params = []){
        //对参数进行校验
        $params = empty($params) ? Request:: param() : $params;
        //批量对参数进行校验
        $result = $this -> check($params);
        if(!$result){
            //校验不通过抛出异常
            $e = new ParameterException([
                'msg'   =>  $this -> getError()
            ]);
            throw $e;
        }else{
            //校验通过，返回根据规则过滤后的参数
            return $this -> getDataByRule($params);
        }
    }


    /**
     *  验证单个值是否为正整数
     */
    protected function isInt($value){
        if(is_int($value + 0) && is_numeric($value) && ($value + 0) >= 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 查询每个主键是否都为正整数（仅处理主键用逗号隔开的字符串）
     */
    protected function allIsInt ($value){
        $ids = explode(',',$value);
        if(is_array($ids)){
            foreach($ids as $id){
                 if(!$this -> isInt($id)){
                     return false;
                 }
            }
        }else{
            return false;
        }
        return true;
    }


    /**
     * 是否为时间戳
     * @param $value
     */
    protected function isTimestamp ($value){
        return strtotime(date('Y-m-d H:i:s', $value)) == $value;
    }


    /**
     * 根据验证规则获取数据
     */
    private function getDataByRule($arrays){
        $newArray = [];
        foreach($this -> rule as $key => $value){
            if(isset($arrays[$key])){
                if(is_array($arrays[$key])){
                    $newArray[$key] = $arrays[$key];
                }else{
                    $newArray[$key] = trim($arrays[$key]);
                }
            }
        }
        return $newArray;
    }
}