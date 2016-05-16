<?php
/**
 * @property int            id
 * @property string         lang
 * @property string         alias              news alias
 * @property string         enabled            'Y' if node is enabled
 * @property string         title
 * @property string         metaTitle          seo meta-title
 * @property string         metaKeywords       seo meta-keywords
 * @property string         metaDescription    seo meta-description
 * @property string         content
 * @property string         content_short
 * @property string         added
 * @property string         thumb               thumbnail extension
 * @property string         mgDescriptin
 * @property int            akcia
 * @property string         akcia_text
 */
class Model_News extends Domain_Entity_Abstract
{
    protected $_aData = array(
        'id'                => 0,
        'lang'              => '',
        'added'             => '',
        'alias'             => '',
        'enabled'           => 'Y',
        'title'             => '',
        'metaTitle'         => '',
        'metaKeywords'      => '',
        'metaDescription'   => '',
        'content'           => '',
        'content_short'     => '',
        'thumb'             => '',
        'imgDescriptin'     => '', 
        'akcia'         => '',
        'akcia_text'    => '',

    );
    
    protected $_albums;
    public function getAlbums()
    {
        if (null === $this->_albums) {
            $this->_albums = Service_Locator::getNews()->getNewsAlbums($this);
        }
        return $this->_albums;
    }

    public function isEnabled()
    {
        return $this->enabled == 'Y';
    }

    public function setEnabled($state)
    {
        $this->enabled = (bool)$state ? 'Y' : 'N';
    }

    /**
     * @param Model_Link $that
     * @return mixed
     */
    public function equalsTo($that)
    {
        $this->ensureComparable($that);
        return $this->id = $that->id;
    }
}
