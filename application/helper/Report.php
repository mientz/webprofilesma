<?php
namespace Wijaya\WebApp\Helper;

class Report {

    public function success($method, $data=null, $include=null){
        return [
            'status'=>'success',
            'method'=>$method,
            'data'=>$data,
            'include'=>$include
        ];
    }

    public function fail($method, $message=null){
        return [
            'status'=>'fail',
            'method'=>$method,
            'message'=>$message
        ];
    }

    public function error($method, $message=null){
        return [
            'status'=>'fail',
            'method'=>$method,
            'message'=>$message
        ];
    }
}
