<?php

class Site_Model_Event_Comments extends Breathbath_Bd_Table implements Site_Model_LikesData
{

    protected $_name = 'event_comments';

    protected static $_instance = null;

    /**
     * @return Site_Model_Event_Comments
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    public function getByEventId($id, $limit=null, $offset=1)
    {
        $select = $this->prepareCommentSelect();
        $select->
            where('c.event_id = ?', $id)->
            order('added DESC');

        if(is_null($limit))
            $comments = $this->fetchFromSelect($select);
        else
            $comments = $this->getPaginatedResults($select, $limit, $offset);
        return $comments;
    }

    protected function prepareCommentSelect()
    {
        $userFields = array(
            'u_name'    =>  'u.name',
            'u_gender'  =>  'u.gender',
            'u_avatar'  =>  'u.avatar',
            'u_surname' =>  'u.surname'
        );

        $countryFields = array(
            'country_name'      =>  'co.name',
            'en_country_name'   =>  'co.en_name',
            'de_country_name'   =>  'co.de_name',
        );

        $cityFields = array(
            'city_name'         =>  'ci.city_name',
            'en_city_name'      =>  'ci.en_city_name',
            'de_city_name'      =>  'ci.de_city_name',
        );

        $select = $this->getAdapter()->select()->
            from(array('c'  =>   $this->_name))->
            join(array('u'  =>  'users'), 'u.id = c.user_id', $userFields)->            
            join(array('co' =>  'country'), 'co.id = u.country_id', $countryFields)->
            join(array('ci' =>  'city'), 'ci.id = u.city_id', $cityFields);

        return $select;
    }

    public function getCommentByIdWithDetails($id)
    {        
        $select = $this->prepareCommentSelect();
        $select->where('c.id = ?', $id);
        $comment = $this->getAdapter()->fetchRow($select);
        return $comment;
    }

    public function validateEventComment($eventId, $userId, $text, &$resultCode)
    {
        if (empty($userId)) {
            $resultCode = Breathbath_Messanges::ERROR_MUST_LOGIN_ADD_COMMENT;
            return false;
        }

        if (empty($eventId)) {
            $resultCode = Breathbath_Messanges::ERROR_ADDING_COMMENT;
            return false;
        }

        if (empty($text)) {
            $resultCode = Breathbath_Messanges::ERROR_COMMENT_EMPTY;
            return false;
        }

        $maxLength = 3000;
        if (mb_strlen($text) > $maxLength) {
            $resultCode = Breathbath_Messanges::ERROR_COMMENT_TOO_LONG;
            return false;
        }

        return true;
    }

    public function addComment($eventId, $userId, $text, &$newId, &$resultCode)
    {
        $text = trim($text);

        if (!$this->validateEventComment($eventId, $userId, $text, $resultCode)) {
            return false;
        }

        $newId = $this->insert(array(
            'event_id'  =>  $eventId,
            'user_id'   =>  $userId,
            'text'      =>  $text,
            'added'     =>  date('Y-m-d H:i:s')
        ));

        $resultCode = Breathbath_Messanges::COMMENT_ADDED;
        return true;
    }


    public function getItemById($id)
    {
        $item = $this->find($id);
        if (!$item) {
            return null;
        }
        return $item->current();
    }

    public function getAuthorId($item)
    {
        if(isset($item['user_id'])){
            return $item['user_id'];
        }
        return null;
    }
}
