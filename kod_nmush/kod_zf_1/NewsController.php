<?php

class Default_NewsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $alias = $this->getRequest()->getParam('alias', false);
        $news = Service_Locator::getNews()->findByAlias($alias);

        if (!$news) {
            throw new Exception('Requested object not found');
        }

        $this->view->headTitle()->prepend($news->metaTitle);
        $this->view->headMeta()->appendName('description', $news->metaDescription);
        $this->view->headMeta()->appendName('keywords', $news->metaKeywords);

        /** @var $parent Model_Page */
        $parent = Service_Locator::getPages()->getMapper()->findByField('template', 'news', true);
        if ($parent) {
            /** @var $node Model_Navigation */
            $node = Service_Locator::getNavigation()->getNodeByRelatedObject('page', $parent->id);
            /** @var $navigation Zend_Navigation_Container */
            $navigation = $this->view->navigation();
            $parentNode = $navigation->findOneBy('id', $node->id);
            $page = Zend_Navigation_Page_Uri::factory(array(
                'id' => $node->id . '_' . $news->id,
                'type' => 'uri',
                'label' => $news->title,
                'uri' => $this->view->url(array('alias' => $news->alias), 'newsItem')
            ));

            $page->setActive();
            $parentNode->addPage($page);
        }

        $albums = Array();
        $AlbumImages = Array();

        $db = new Service_TablesQuery();
        $albums = $db->tables('tbl_news_albums', array('id' => $news->id));

        $albumId = (!empty($albums)) ? intval($albums[0]['album_id']) : 0;
        $AlbumImages = $db->tables('tbl_albums', array('getalbumsimgs' => 1, 'id' => $albumId));

        $this->view->assign('AlbumImages', $AlbumImages);
        $this->view->assign('page', $news);
    }

}
