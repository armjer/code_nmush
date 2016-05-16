<?php

class Service_Locator
{
    protected static $_adapter;

    protected static $_services = array();

    public static function setAdapter($adapter)
    {
        self::$_adapter = $adapter;
    }
    /**
     * @static
     * @return Zend_Db_Adapter_Abstract
     */
    public static function getAdapter()
    {
        return self::$_adapter;
    }

    /**
     * @static
     * @return Service_Settings
     */
    public static function getSettings()
    {
        if (!isset(self::$_services['Settings'])) {
            $mapper = new Model_Mapper_Settings();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Settings'] = new Service_Settings($mapper);
        }
        return self::$_services['Settings'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_Pages
     */

    public static function getPages($lang = null)
    {
        if (!isset(self::$_services['Pages'])) {
            $mapper = new Model_Mapper_Pages();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Pages'] = new Service_Pages($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Pages'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Pages'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_Galleries
     */
    public static function getGalleries($lang = null)
    {
        if (!isset(self::$_services['Galleries'])) {
            $mapper = new Model_Mapper_Galleries();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Galleries'] = new Service_Galleries($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Galleries'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Galleries'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_Albums
     */
    public static function getAlbums($lang = null)
    {
        if (!isset(self::$_services['Albums'])) {
            $mapper = new Model_Mapper_Albums();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Albums'] = new Service_Albums($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Albums'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Albums'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_Navigation
     */
    public static function getNavigation($lang = null)
    {
        if (!isset(self::$_services['Navigation'])) {
            $mapper = new Model_Mapper_Navigation();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Navigation'] = new Service_Navigation($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Navigation'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Navigation'];
    }
    /**
     * @static
     * @param null $lang
     * @return Service_FeedbackPages
     */
    public static function getFeedbackPages($lang = null)
    {
        if (!isset(self::$_services['FeedbackPages'])) {
            $mapper = new Model_Mapper_FeedbackPages();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['FeedbackPages'] = new Service_FeedbackPages($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['FeedbackPages'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['FeedbackPages'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_Faq
     */
    public static function getFaq($lang = null)
    {
        if (!isset(self::$_services['Faq'])) {
            $mapper = new Model_Mapper_Faq();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Faq'] = new Service_Faq($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Faq'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Faq'];
    }

    /**
     * @static
     * @return Service_Links
     */
    public static function getLinks()
    {
        if (!isset(self::$_services['Links'])) {
            self::$_services['Links'] = $svce = new Service_Links(new Model_Mapper_Links());
            $svce->getMapper()->setAdapter(self::getAdapter());
        }
        return self::$_services['Links'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_Infoblocks
     */
    public static function getInfoblocks($lang = null)
    {
        if (!isset(self::$_services['Infoblocks'])) {
            $mapper = new Model_Mapper_Infoblocks();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Infoblocks'] = new Service_Infoblocks($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Infoblocks'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Infoblocks'];
    }

    /**
     * @static
     * @param null $lang
     * @return Service_News
     */

    public static function getNews($lang = null)
    {
        if (!isset(self::$_services['News'])) {
            $mapper = new Model_Mapper_News();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['News'] = new Service_News($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['News'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['News'];
    }

    public static function getKino($lang = null)
    {
        if (!isset(self::$_services['Kino'])) {
            $mapper = new Model_Mapper_Kino();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Kino'] = new Service_Kino($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Kino'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Kino'];
    }

    
    /**
     * @static
     * @param null $lang
     * @return Service_Articles
     */

    public static function getArticles($lang = null)
    {
        if (!isset(self::$_services['Articles'])) {
            $mapper = new Model_Mapper_Articles();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Articles'] = new Service_Articles($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Articles'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Articles'];
    }
    
    public static function getKinoteatrs($lang = null)
    {
        if (!isset(self::$_services['Kinoteatrs'])) {
            $mapper = new Model_Mapper_Kinoteatrs();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Kinoteatrs'] = new Service_Kinoteatrs($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Kinoteatrs'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Kinoteatrs'];
    }
    
     public static function getUslugi($lang = null)
    {
        if (!isset(self::$_services['Uslugi'])) {
            $mapper = new Model_Mapper_Uslugi();
            $mapper->setAdapter(self::getAdapter());
            self::$_services['Uslugi'] = new Service_Uslugi($mapper);
        }
        if($lang !== null){
            $srv = self::$_services['Uslugi'];
            $mapper = $srv->getMapper();
            $mapper->setLang($lang);
        }
        return self::$_services['Uslugi'];
    }

}
