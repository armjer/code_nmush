<?php
/**
 * @method __construct(Domain_Mapper_Abstract $mapper = null)
 * @method Model_Mapper_Articles getMapper()
 * @method Service_Articles setMapper(Model_Mapper_Articles $mapper)
 * @method Model_Article findByIdentity($identity)
 */
class Service_Articles extends Service_Translatable
{
    /** @var Model_Mapper_Articles */
    protected $_mapper;

    protected $_storage = 'articles';

    public function persist(Model_Article $entity)
    {
        if(!$entity->id){
            $entity->added = date('Y-m-d H:i:s');
            $this->getMapper()->insert($entity);
        } else {
            $this->getMapper()->update($entity);
        }
    }

    public function getCategories($with_count = false,$groupby=NULL)
    {
        return $this->getMapper()->getCategoriesList($with_count,$groupby);
    }
    
    public function getCategoriesOnly($with_count = false,$groupby=NULL)
    {
        return $this->getMapper()->getCategoriesOnly($with_count,$groupby);
    }

    public function createCategoty(Model_ArticleCategory $entity)
    {
        $this->getMapper()->createCategory($entity);
    }

    public function updateCategotyNew(Model_ArticleCategory $entity)
    {
        $this->getMapper()->updateCategoryNew($entity);
    }
    
    public function updateCategoty(Model_ArticleCategory $entity)
    {
        $this->getMapper()->updateCategory($entity);
    }

    public function deleteCategoty(Model_ArticleCategory $entity)
    {
        $this->getMapper()->deleteCategory($entity);
    }

    /**
     * @param $alias
     * @return bool|Model_Article
     */
    public function findByAlias($alias)
    {
        return $this->getMapper()->findEnabledByAlias($alias);
    }

    public function findEnabled()
    {
        return $this->getMapper()->findByField('enabled','Y');
    }

    /**
     * @param Model_Article_Criteria $criteria
     * @param string $order
     * @param string $direction
     * @return Domain_Collection
     */
    public function findByCriteria(Model_Article_Criteria $criteria, $order = 'added', $direction=NULL )
    {
        $setDirection='asc';
        if(isset($direction) && $direction ){
            $setDirection=$direction;
        }
        
        return new Domain_Collection(
            $this->getMapper(),
            $this->getMapper()->findByCriteria($criteria, $order, $direction)
        );
    }    
   
    public function findByCriteriaWhere(Model_Article_Criteria $criteria, $order = 'added', $direction=NULL )
    {
        $setDirection='asc';
        if(isset($direction) && $direction ){
            $setDirection=$direction;
        }
        
        return new Domain_Collection(
            $this->getMapper(),
            $this->getMapper()->findByCriteriaWhere($criteria, $order, $direction)
        );
    }    
    
    public function findCountByCriteria(Model_Article_Criteria $criteria, $order = 'added', $direction=NULL )
    {
        $setDirection='asc';
        if(isset($direction) && $direction ){
            $setDirection=$direction;
        }
        
        return new Domain_Collection(
            $this->getMapper(),
            $this->getMapper()->findByCriteria($criteria, $order, $direction)
        );
    }    

}