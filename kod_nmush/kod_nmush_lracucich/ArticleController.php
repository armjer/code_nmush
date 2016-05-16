<?php

class Default_ArticleController extends Zend_Controller_Action
{
    public function indexAction()
    {
                
        $alias = $this->getRequest()->getParam('alias', false);
        $article = Service_Locator::getArticles()->findByAlias($alias);

        if(!$article){
            throw new Exception('Requested object not found');
        }

        $this->view->headTitle()->prepend($article->metaTitle);
        $this->view->headMeta()->appendName('description', $article->metaDescription);
        $this->view->headMeta()->appendName('keywords', $article->metaKeywords);

        /** @var $parent Model_Page */
        $parent = Service_Locator::getPages()->getMapper()->findByField('template','articles',true);
        if($parent){
            /** @var $node Model_Navigation */
            $node = Service_Locator::getNavigation()->getNodeByRelatedObject('page',$parent->id);
            /** @var $navigation Zend_Navigation_Container */
            $navigation = $this->view->navigation();
            $parentNode = $navigation->findOneBy('id',$node->id);
            $page = Zend_Navigation_Page_Uri::factory(array(
                'id'        => $node->id.'_'.$article->id,
                'type'      => 'uri',
                'label'     => $article->title,
                'uri'       => $this->view->url(array('alias' => $article->alias),'articleItem')
            ));
            $page->setActive();
            $parentNode->addPage($page);
            //$this->view->navigation()->setContainer($container);
        }        

        $langOrigin=$this->getRequest()->getParam('lang', Zend_Registry::get('lang'));   
        $negLang=($langOrigin=='ru')?'ua':'ru';
        $text_link="<a href='/$negLang/info/$alias.html' > {$article->text_link} </a> ";

       
        $this->view->assign('page', $article);
        $this->view->assign('text_link', $text_link );
    }

}
