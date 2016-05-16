<?php
/*
 *  Модель выбора способа оплаты
 * 
 */
class Model_Pay_Service  {    
	
    protected  $parameters;    
    public static $_instance = null;    
    protected static $_adapter = null; 
    
    public static function getInstance($_adapter = null){
        if(null === self::$_instance){
            self::$_instance = new self();
        }
        
        if(!is_null($_adapter) && $_adapter instanceof Model_Pay_Adapter_Interface){
            self::$_adapter = $_adapter;
        }
        return self::$_instance;
    }
    
    public function getAdapter(){
        return self::$_adapter;
    }
    
    
    public function setAdapter($adapter){
        if($adapter instanceof Model_Pay_Adapter_Interface){
            self::$_adapter = $adapter;
        }
    }
    
    
    public static function getAdapterByService($service_name){
        $all_services = array(
            'paypal'=>'Model_Pay_Adapter_Paypal',
            'privat24'=>'Model_Pay_Adapter_Privat24',
            'webmoney'=>'Model_Pay_Adapter_WebMoney',
            'liqpay'=>'Model_Pay_Adapter_Liqpay',
            'banktransfer'=>'Model_Pay_Adapter_BankTransfer',
            'allcharge'=>'Model_Pay_Adapter_Allcharge'
        );        
        if(isset($all_services[$service_name])){  
            return new $all_services[$service_name];
        }
        return false;
    }
    
    
    public function response($post) {
        return self::$_adapter->response($post);
    } 
    
    public function request($data){
        return self::$_adapter->request($data);
    }
    
    
    public static function getServiceName($type = ''){
        $types = array(15=>'paypal', 25=>'privat24', 26=>'webmoney',  27=>'liqpay',31=>'allcharge');
        
        if($type)
            return isset($types[$type]) ? $types[$type] : false;
        
        return $types;
    }

}
