<?php

class User_Cards extends Tez_Db_Mixed
{
    protected $_name = 'user_cards';
    
    
    public function addNewDefault($data,$userId){
    	$this->updateField('default', 0, $userId,'id_user');
    	
    	$data['id_user'] = $userId;
    	$data['default'] = 1;
    	$data['type'] = $this->getTypeByNumber($data['number']);
    	return $this->save($data);
    }
    
    public function setDefault($id,$userId){
    	$this->updateField('default', 0, $userId,'id_user');
    	
    	$this->updateById(array('default'=>1), $id);
    }
    
    public function getAllForUser($userId){
		return $this->getRows($this->e('id_user = ?',$userId),'default desc');
    }
    
    public function getForUser($userId,$index){
    	$rows = $this->getRows($this->e('id_user = ?',$userId),'default desc');
    	
    	if (!empty($rows[$index])){
    		return $rows[$index];
    	}
    	
    	if (!$index){
    		$user = Users::getInstance()->getRowById($userId);
    		$fields = $this->getAdapter()->describeTable($this->_name);
    		unset($fields['id']);
    		$fields = array_keys($fields);
    		return Tez_Array::filter($user, $fields);
    	}
    	return array();
    }
    
    public function getTypeByNumber($number){
    	$validCards = array('American_Express', 'Unionpay', 'Diners_Club', 'Discover', 'JCB', 'Laser', 'Maestro', 'Mastercard', 'Solo', 'Visa');
		$type = '';
		foreach ($validCards as $type) {
			$validator = new Zend_Validate_CreditCard();
			$validator->setType($type);
			if ($validator->isValid($number)){
				return $type;
			}
		}
    }
}