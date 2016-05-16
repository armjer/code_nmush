<?php
/**
 * @property int            id
 * @property string         lang
 * @property string         alias              news alias
 * @property string         enabled            'Y' if node is enabled
 * @property string         title
 * @property int            category_id
 * @property string         metaTitle          seo meta-title
 * @property string         metaKeywords       seo meta-keywords
 * @property string         metaDescription    seo meta-description
 * @property string         content
 * @property string         content_short
 * @property string         added
 * @property string         thumb               thumbnail extension
 */
class Model_Article extends Domain_Entity_Abstract
{
    protected $_aData = array(
        'id'                => 0,
        'lang'              => '',
        'added'             => '',
        'alias'             => '',
        'enabled'           => 'Y',
        'category_id'       => 0,
        'title'             => '',
        'metaTitle'         => '',
        'metaKeywords'      => '',
        'metaDescription'   => '',
        'content'           => '',
        'content_short'     => '',
        'thumb'             => '',
        'text_link'         => '',

    );

    public function isEnabled()
    {
        return $this->enabled == 'Y';
    }

    public function setEnabled($state)
    {
        $this->enabled = (bool)$state ? 'Y' : 'N';
    }

    
}
