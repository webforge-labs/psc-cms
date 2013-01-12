<?php

namespace ePaper42;

use Hitch\Mapping\Annotation AS Hitch;

/**
 * @Hitch\XmlObject
 */
class Issue extends \Psc\XML\Object {
  
  /**
   * @Hitch\XmlAttribute(name="title")
   */
  protected $title;
  
  /**
   * @Hitch\XmlAttribute(name="key")
   */
  protected $key;
  
  /**
   * @Hitch\XmlAttribute(name="exportDate")
   */
  protected $exportDate;
  
  /**
   * @Hitch\XmlAttribute(name="cover")
   */
  protected $cover;
  
  /**
   * @Hitch\XmlList(name="category", type="ePaper42\Category", wrapper="categoryList")
   */
  protected $categories;
  
  /**
   * @Hitch\XmlList(name="page", type="ePaper42\Page", wrapper="pageList")
   */
  protected $pages;
  
  /**
   * @Hitch\XmlList(name="article", type="ePaper42\Article", wrapper="articleList")
   */
  protected $articles;
  
  /**
   * @Hitch\XmlList(name="clickPage", type="ePaper42\ClickPage", wrapper="clickPageList")
   */
  protected $clickPages;
}
?>