<?php
class Model_Mapper_Articles extends Domain_Mapper_Translatable
{
    protected $_table           = 'tbl_articles';
    protected $_tableContent    = 'tbl_articles_content';
    protected $_tableCategories = 'tbl_articles_categories';

    protected $_entityClass     = 'Model_Article';

    protected $_identity        = 'id';

    protected $_map = array(
        'id'                => 'id',
        'lang'              => 'lang',
        'added'             => 'added',
        'enabled'           => 'enabled',
        'alias'             => 'alias',
        'category_id'       => 'category_id',
        'title'             => 'title',
        'metaTitle'         => 'metaTitle',
        'metaKeywords'      => 'metaKeywords',
        'metaDescription'   => 'metaDescription',
        'content'           => 'content',
        'content_short'     => 'content_short',
        'thumb'             => 'thumb',
        'text_link'         => 'text_link',

    );

    protected $_translated = array(
        'lang',
        'title',
        'metaTitle',
        'metaKeywords',
        'metaDescription',
        'content',
        'content_short',
        'text_link'
    );

    public function getLatest($limit = 10)
    {
        return (array)$this->_select()
            ->where('enabled = ?', 'Y')
            ->limit($limit)
            ->order('added DESC')
            ->query()->fetchAll();
    }

    public function getCategoriesList($with_count = false,$filter=NULL)
    {
        $list = array(0 => i18n::_('No category'));
        $query = $this->_adapter->select()
            ->from($this->_tableCategories)
        ;
        if(!$filter['nolang']){
            $query->where('lang = ? ',$this->getLang()); 
        }
        
        if($with_count){
            $query->joinLeft(
                $this->_table,
                $this->_adapter->quoteIdentifier($this->_table.'.category_id')
                    .'='.$this->_adapter->quoteIdentifier($this->_tableCategories.'.category_id'),
                array('articles' => "COUNT({$this->_table}.id)")
            );
                
            $uncategorised = $this->_adapter->select()
                ->from($this->_table,array('count(*)'))
                ->where('category_id <> 4')->query()->fetchColumn()
            ;
           
            if($filter['groupby']){
                $query->group($this->_tableCategories.'.category_id' );
            }
            if($filter['extra1']){
                $flt=  strip_tags(trim($filter['extra1']));
                $query->where($this->_tableCategories.'.extra1=?',$flt);
            }
            if($filter['no_extra1']){
                $flt=  strip_tags(trim($filter['extra1']));
                $query->where($this->_tableCategories.'.extra1<>?',$flt);
            }                        
            
            $list = array(0 => array(i18n::_('No category'),$uncategorised));
            
        }
        $hndr = $query->query();
        while($row = $hndr->fetch()){
            if($with_count){
                $list[$row['category_id']] = array($row['category_name'],$row['articles']);
            } else {
                $list[$row['category_id']] = $row['category_name'];
            }
        }
        
       
        return $list;
    }

    public function getCategoriesOnly($filter=NULL)
    {
        $query = $this->_adapter->select()
            ->from($this->_tableCategories)
        ;
        if(!$filter['nolang']){
            $query->where('lang = ? ',$this->getLang()); 
        }
        
        
       
        if($filter['groupby']){
            $query->group($this->_tableCategories.'.category_id' );
        }
        if($filter['extra1']){            
            $flt=  strip_tags(trim($filter['extra1']));
            $query->where($this->_tableCategories.'.extra1=?',$flt);
        }
        if($filter['no_extra1']){
            $flt=  strip_tags(trim($filter['extra1']));
            $query->where($this->_tableCategories.'.extra1<>?',$flt);
        }
        if($filter['category_id']){
            $flt=  intval(trim($filter['category_id']));
            $query->where($this->_tableCategories.'.category_id=?',$flt);
        }
                        
           
        
       $row = $query->query()->fetchAll();
       
       return $row;
    }

    
       
    public function getSimilarList(Model_Article $entity, $limit = 10)
    {
        return (array)$this->_select()
            ->where('category_id = ? ',$entity->category_id)
            ->where($this->_adapter->quoteIdentifier($this->_table.'.id').' != ?', $entity->id)
            ->limit($limit)
            ->query()->fetchAll();
    }

    public function createCategory(Model_ArticleCategory $entity)
    {
        $entity->category_id = intval($this->_adapter->select()
            ->from(
            $this->_tableCategories,
            array('MAX(category_id)')
        )->query()->fetchColumn())+1
        ;
        return $this->_adapter->insert(
            $this->_tableCategories,
            $entity->toArray()
        );
    }

    public function updateCategory(Model_ArticleCategory $entity)
    {
        $exist = $this->_adapter->select()
            ->from($this->_tableCategories)
            ->where('category_id',$entity->category_id)
            ->where('lang', $entity->lang)
            ->query()->fetchAll()
        ;
        if(count($exist) > 0 ){
            $this->_adapter->update(
                $this->_tableCategories,
                $entity->toArray(),
                array(
                    'category_id'   => $entity->category_id,
                    'lang'          => $entity->lang
                )
            );
        } else {
            $this->createCategory($entity);
        }
        $entity->markClean();
    }

    public function updateCategoryNew(Model_ArticleCategory $entity){
       $exist = $this->_adapter->select()
            ->from($this->_tableCategories)
            ->where('category_id=?',$entity->category_id)
            ->where('lang=?', $entity->lang)
            ->query()->fetchAll()
        ;
        if(count($exist) > 0 ){
            $set=array('category_name=?'   => $entity->category_name );
            $this->_adapter->update(
                $this->_tableCategories,
                $entity->toArray($set),                
                array(
                    'category_id=?'   => $entity->category_id,
                    'lang=?'          => $entity->lang)
                );
            
        } else {
            $this->createCategory($entity);
        }
        $entity->markClean();
    }
    
    public function deleteCategory(Model_ArticleCategory $entity)
    {
        $this->_adapter->update(
            $this->_table,
            array('category_id' => 0),
            array('category_id'   => $entity->category_id)
        );
        return $this->_adapter->delete(
            $this->_tableCategories,
            array(
                'category_id'   => $entity->category_id
            )
        );
    }

    /**
     * @param $alias
     * @return bool|Model_Article
     */
    public function findEnabledByAlias($alias)
    {
        $row  = $this->_select()
            ->where('alias = ?',$alias)
            ->where('enabled = ?', 'Y')
            ->query()->fetch()
        ;
        if($row)
            return new Model_Article($row);
        return false;
    }

    /**
     * @param Model_Article_Criteria $criteria
     * @return Zend_Db_Select
     */
    protected function _buildQueryByCriteria(Domain_Criteria_Abstract $criteria)
    {
        $select = $this->_select();

        if (null !== ($category = $criteria->getAvailableForCategory())) {
            if (0 <= $category) {
                $select->where('category_id = ?', $category);
            }
        }

        if(true !== ($forsite = $criteria->getShowedAll())) {
            $select->where('enabled = ?', 'Y');
        }

        return $select;
    }


}
