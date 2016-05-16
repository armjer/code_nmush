<?php
class Dictionary_Grid extends Admin_Localized
{
	protected $_name = 'dictionaries';
	protected $_side = '';
    protected $_langTextFields = array('value');
    protected $_langTextTable = 'dictionary_texts';
    protected static $_lang;

    
    public static function setLang ($lang){
        self::$_lang = $lang;
    }
    
    
    public function init(){
        parent::init();
        
		$this->currentSelect->where($this->e('side=?', $this->_side));
		$this->currentSelect->columns($this->_getStatusFields());
    }
    
    public function save ( $data){
	    $data['name'] = $data['_name'];
	    $data['side'] = $this->_side;
	    $id = parent::save ($data);

	    return $id;
	}
	
	public function getSide (){
	    return $this->_side;
	}
	
	public function export (){
	    Dictionaries::export($this->_side);
	}

	public function calculateStatus($id){
        
        $rows = Dictionary_Texts::getInstance()->getForLangs($id);
        $status = '';
        foreach ($rows as $lang => $row) {
        	if(!empty($row) && Tez_Translit::isTranslated($row['value'], $lang)){
        		$status .= $lang.',';
        	}
        }
        $status = rtrim($status, ',');
        
        $this->updateField('status', $status, $id);
    }
	
    protected function _getStatusFields(){
    	$fields = array('status');
    	$langs = Languages::getLangs();
		
		foreach ($langs as $lang=>$name){
			$fields[] = "if(status like '%$lang%', 1, 0) as status$lang";
		}
		return $fields;
    }
    
    protected function _getLangFieldForJoin(){
        $fields = array();
        foreach ($this->_langTextFields as $k=>$field) {
        	$fields[] = "IF({$this->_langTextTable}.`$field` is not null, {$this->_langTextTable}.`$field`, {$this->_name}.`$field`) as $field";
        }
        return $fields;
    }
	
}