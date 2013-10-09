<?php

namespace Psc\CMS;

use Psc\Net\HTTP\FrontController;
use Psc\Net\HTTP\Request;
use Psc\CMS\Service\EntityService;
use Psc\PSC;
use Psc\Code\Event\Event;
use Psc\Code\Event\CallbackSubscriber;
use Psc\Doctrine\DCPackage;
use Psc\Doctrine\ModelCompiler;
use Psc\Environment;
use Psc\UI\DropContentsList;
use Psc\UI\ContentTabs;
use Psc\TPL\TPL;
use Psc\TPL\Template;
use Psc\CMS\AbstractTabsContentItem2 as TCI;
use Psc\UI\Tabs2;
use Psc\CMS\Controller\Factory as ControllerFactory;
use Psc\CMS\Roles\Container as ContainerRole;
use Webforge\CMS\EnvironmentContainer;
use Webforge\Framework\Project as WebforgeProject;

/**
 * @TODO fix GLOBALS container
 */
class ProjectMain extends \Psc\Object implements DropContentsListCreater{

  /**
   * @var Psc\Environment
   */  
  protected $environment;

  /**
   * @var Webforge\CMS\EnvironmentContainer
   */
  protected $environmentContainer;
  
  /**
   * @var Webforge\Framework\Project
   */
  protected $project;
  
  /**
   * @var Psc\CMS\Controller\AuthController
   */
  protected $authController;
  
  /**
   * @var Psc\HTML\Page
   */
  protected $mainHTMLPage;
  
  /**
   * @var array
   */
  protected $dropContents; // auf der rechten seite

  /**
   * @var Psc\UI\Tabs
   */
  protected $tabs; // main tabs
  
  /**
   * @var Psc\TPL\TPL
   */
  protected $welcomeTemplate;

  /**
   * @var Psc\CMS\RightContent
   */
  protected $rightContent;

  /**
   * @var string
   */
  protected $rightContentClass;
  
  
  /**
   * @var int
   */
  protected $debugLevel;
  
  /**
   * @var Psc\Net\FrontController
   */
  protected $frontController;
  
  /**
   * @var Psc\Net\x
   */
  protected $mainService;
  
  /**
   * @var Psc\CMS\Service\EntityService
   */
  protected $entityService;

  /**
   * @var Psc\CMS\Roles\Container
   */
  protected $container;

  /**
   * @var string
   */
  protected $containerClass;

  /**
   * @var Psc\CMS\Controller\Factory
   */
  protected $controllerFactory;

  /**
   * @var Psc\Net\HTTP\Request
   */
  protected $request;
  
  /**
   * Doctrine Package
   * 
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * Der Default Connection Name für das DC Package
   *
   * kann im Production Mode per Request überschrieben werden. siehe initRequest()
   * @var string
   */
  protected $con = NULL;
  
  /**
   * @var Psc\Doctrine\ModelCompiler
   */
  protected $modelCompiler;
  
  /**
   * @var Psc\Session\Session
   */
  public $session; // just a hack for tests
  
  public function __construct(WebforgeProject $project = NULL, DCPackage $dc = NULL, RightContent $rightContent = NULL, EntityService $entityService = NULL, $debugLevel = 5, FrontController $frontController = NULL, \Psc\Environment $env = NULL) {
    $this->environment = $env ?: PSC::getEnvironment();
    $this->project = $project ?: PSC::getProject();

    $this->dc = $dc;                              // getter injection, wegen EntityManager
    $this->debugLevel = $debugLevel;
    $this->entityService = $entityService;        // getter injection
    $this->frontController = $frontController;    // getter injection wegen services für requesthandler und debuglevel
    $this->rightContent = $rightContent;          // getter injection
  }
  
  public function init(Request $request = NULL) {
    $this->dropContents = array();
    
    // das machen wir schon bevor um z. B. Connection auslesen zu können
    $this->initRequest($request);

    /* services zum requesthandler hinzufügen */
    $this->initServices(); // das könnte auch in den getter vom frontcontroller?
    // hier nicht init rightcontent das ist lahm und für jeden api request doof
    return $this;
  }

  
  public function initRequest(Request $request = NULL) {
    $this->request = $request ?: Request::infer(); // infer() von $_GET usw ...
    
    $con = $this->request->getHeaderField('X-Psc-Cms-Connection');
    if (!empty($con)) {
      $this->setConnectionName($con);
    }

    if ($this->request->hasHeaderField('X-Psc-Cms-Debug-Level')) {
      $this->setDebugLevel((int) $this->request->getHeaderField('X-Psc-Cms-Debug-Level'));
    }
    
    // so rum überschreiben wir den X-Psc-Cms-Connection Header (was ja safe ist)
    if ($this->isTesting()) {
      $this->setConnectionName('tests');
    }
  }
  
  /**
   * Erstellt das DCPackage
   *
   * wird vom getter aufgerufen, damit es immer da ist
   */
  public function initDoctrinePackage() {
    $module = $GLOBALS['env']['container']->getModule('Doctrine');
    $this->dc = new DCPackage($module, $module->getEntityManager($this->getConnectionName()));
    $this->attachEntityMetaListener(); // ganz wichtig das hier zu machen, weil das sonst das doctrinepackage initialisiert
  }
  
  public function attachEntityMetaListener() {
    $manager = $this->getDoctrinePackage()->getModule()->getManager();
    $that = $this;
    $manager->bind(new CallbackSubscriber(
                     function (Event $e) use ($that) {
                       $that->initEntityMetaFor($e->getTarget());
                     }
                   ),
                   'Psc.Doctrine.initEntityMeta'
                  );
  }
  
  /**
   * Intialisiert zusätzliche Services für den RequestHandler
   *
   * standardmäßig wird der EntityService hinzugefügt
   */
  public function initServices() {
    $this->getFrontController()->getRequestHandler()->addService($entityService = $this->getEntityService());
    
    // wir mappen den users controller auf den in Psc
    $entityService->setControllerClass('User', 'Psc\CMS\Controller\UserEntityController');
  }
  
  /**
   * @return Psc\CMS\RightContent
   */
  public function initRightContent() {
    return $this->rightContent->populateLists($this);
  }
  
  public function initEntityMetaFor(EntityMeta $meta) {
    if ($meta->getEntityName() === 'user')
      $meta
        ->setGridLabel($this->trans('entities.user.grid'))
        ->setNewLabel($this->trans('entities.user.insert'))
        ->setLabel($this->trans('entities.user'))
        ->setTCIColumn('email');
  }

  protected function trans($key, Array $parameters = array(), $domain = 'cms') {
    return $this->getContainer()->getTranslationContainer()->getTranslator()->trans($key, $parameters, $domain);
  }
  
  public function getEntityMeta($entityName) {
    return $this->getDoctrinePackage()->getModule()->getEntityMeta($entityName);
  }
  
  /**
   * @return array
   */
  public function getEntityMetas() {
    return $this->getDoctrinePackage()->getModule()->getEntityMetas();
  }
  
  /**
   * Hydriert ein einzelnes Entity
   * 
   * @param string $entityName kann der FQN oder Shortname sein
   * @return Psc\CMS\Entity
   */
  public function hydrateEntity($entityName, $identifier) {
    return $this->getDoctrinePackage()->getRepository(
      $this->getDoctrinePackage()->getModule()->getEntityName($entityName)
    )->hydrate($identifier);
  }
  
  public function handleAPIRequest(\Psc\Net\HTTP\Request $request = NULL) {
    // lädt Request aus $_GET, $_POST, etc
    $frontController = $this->getFrontController()->init($request ?: $this->request);

    // erstellt eine Response und gibt diese aus (setzt auch Header)
    return $frontController->process();
  }
  
  
  /**
   * @param Net\HTTP\FrontController $FrontController
   * @chainable
   */
  public function setFrontController(\Psc\Net\HTTP\FrontController $FrontController) {
    $this->frontController = $FrontController;
    return $this;
  }

  /**
   * @return Net\HTTP\FrontController
   */
  public function getFrontController() {
    if (!isset($this->frontController)) {
      $this->frontController =
        new FrontController(
          new \Psc\Net\HTTP\RequestHandler(
            $this->getMainService()
          ),
          $this->debugLevel // 5 zeigt den log, aber schmeisst keine eigenen exceptions
        );        
    }
    
    return $this->frontController;
  }
  
  protected function getMainService() {
    if (!isset($this->mainService)) {
      $this->mainService = new \Psc\CMS\Service\CMSService($this->project);
    }
    
    return $this->mainService;
  }
  
  public function isTesting() {
    return FALSE; // extend with something useful
  }
  
  
  protected function initLocale($language) {
    if ($language == 'en') {
      $r = setlocale(LC_ALL,'en_US.UTF-8', 'en_US', 'en','English_United States.1252'); // letzte spalte ist vista
    } elseif ($language == 'fr') {
      $r = setlocale(LC_ALL,'fr_FR.UTF-8', 'fr_FR', 'fr','French_France.1252');
    } elseif ($language == 'cn') {
      $r = setlocale(LC_ALL,'zh_CN.UTF-8', 'zh_CN', 'zh');
    } else {
      $r = setlocale(LC_ALL,'de_DE.UTF-8', 'de_DE@euro', 'de_DE', 'deu_deu','German_Germany.1252');
    }
    
    if ($r === FALSE) {
      throw new Exception('kann locale nicht setzen!(en_US.UTF-8 fr_FR.UTF-8 zh_CN.UTF-8 de_DE.UTF-8 needed)');
    }
  }
  
  /**
   * @param Psc\CMS\Service\EntityService $entityService
   * @chainable
   */
  public function setEntityService(\Psc\CMS\Service\EntityService $entityService) {
    $this->entityService = $entityService;
    return $this;
  }

  /**
   * @return Psc\CMS\Service\EntityService
   */
  public function getEntityService() {
    if (!isset($this->entityService)) {
      $this->entityService = new EntityService($this->getDoctrinePackage(), $this->getControllerFactory(), $this->getProject());
    }
    
    return $this->entityService;
  }

  /**
   * @return Psc\CMS\Controller\Factory
   */
  public function getControllerFactory() {
    if (!isset($this->controllerFactory)) {
      $this->controllerFactory = $this->getContainer()->getControllerFactory();
    }

    return $this->controllerFactory;
  }

  /**
   * @return Psc\CMS\Roles\SimpleContainer
   */
  public function getContainer() {
    if (!isset($this->container)) {
      $container = $this->getContainerClass();
      $this->container = new $container(
        $this->getProject()->getNamespace().'\\Controllers',
        $this->getDoctrinePackage(), 
        $languages = $this->retrieveLanguages(), 
        $languages[0]
      );
    }

    return $this->container;
  }

  /**
   * @param string $language
   * @chainable
   */
  public function setLanguage($language) {
    $this->getContainer()->setLanguage($language);
    $this->initLocale($language);
    
    return $this;
  }

  /**
   * Returns the two char languages for the project
   * 
   * @return array the first entry is always set and is the defaultLanguage
   */
  protected function retrieveLanguages() {
    return $this->project->getConfiguration()->req('languages');
  }

  /**
   * @return string fqn
   */
  public function getContainerClass() {
    if (!isset($this->containerClass)) {
      $this->containerClass = $this->project->getNamespace()."\CMS\SimpleContainer";
    }

    return $this->containerClass;
  }
  
  /**
   * @param Psc\Doctrine\DCPackage $dc
   * @chainable
   */
  public function setDoctrinePackage(\Psc\Doctrine\DCPackage $dc) {
    $this->dc = $dc;
    return $this;
  }

  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    if (!isset($this->dc)) {
      $this->initDoctrinePackage();
    }
    return $this->dc;
  }

  /**
   * @param string $con
   * @chainable
   */
  public function setConnectionName($con) {
    // damit das safer ist, updaten wir das doctrine package, wenn das hier gesetzt wird
    if (isset($this->dc) && $con != $this->con) {
      $this->dc->setEntityManager($this->dc->getModule()->getEntityManager($con));
    }
    
    $this->con = $con;
    return $this;
  }

  /**
   * Gibt den DefaultConnection name für den EntityManager zurück, der in das Doctrine Package infiltriert wird
   * 
   * @return string
   */
  public function getConnectionName() {
    return $this->con;
  }
  
  /**
   * 
   * @param int $debugLevel
   * @chainable
   */
  public function setDebugLevel($debugLevel) {
    $this->debugLevel = $debugLevel;
    $this->getFrontController()->setDebugLevel($debugLevel);
    return $this;
  }

  /**
   * @return int
   */
  public function getDebugLevel() {
    return $this->debugLevel;
  }

  /**
   * @return Doctrine\ORM\EntityManager
   */
  public function getEntityManager($con = NULL) {
    return $this->getDoctrinePackage()->getEntityManager();
  }


  
  /**
   * @param Psc\Doctrine\ModelCompiler $modelCompiler
   * @chainable
   */
  public function setModelCompiler(\Psc\Doctrine\ModelCompiler $modelCompiler) {
    $this->modelCompiler = $modelCompiler;
    return $this;
  }

  /**
   * @return Psc\Doctrine\ModelCompiler
   */
  public function getModelCompiler() {
    if (!isset($this->modelCompiler)) {
      $this->modelCompiler = new ModelCompiler($this->getDoctrinePackage()->getModule());
      $this->modelCompiler->setOverwriteMode(TRUE);
    }
    return $this->modelCompiler;
  }
  
  /**
   * @return AuthController
   */
  public function auth($redirect = '/') {
    /* Auth */
    $authController = $this->getAuthController();
    $authController->setHTMLPage($this->createHTMLPage());
    $authController->setRedirect($redirect);
    $authController->run();

    if (isset($this->frontController) && $this->getUser()) { // dpi bereits erfolgt, trotzdem save machen
      $this->frontController->getRequestHandler()->setContextInfo('eingeloggter User: '.$this->getUser()->getEmail());
    }
    
    return $authController;
  }

  // ganz wichtig, das hier zu überschreiben, damit der authcontroller auf jeden fall den richtigen EntityManager bekommt
  public function getAuthController() {
    if (!isset($this->authController)) {
      $this->authController = new \Psc\CMS\Controller\AuthController(
        new Auth(
          $this->session,
          NULL,
          $this->getUserManager()
        )
      );
      $this->authController->setUserClass($this->getUserClass($this->getProject()));
      $this->authController->setHTMLPage($this->createHTMLPage());
    }
      
    return $this->authController;
  }

  public function getUserManager() {
    return new UserManager(
      $this->getDoctrinePackage()->getEntityManager()->getRepository(
        $this->getUserClass($this->getProject())
      )
    );
  }

  public function getEnvironmentContainer() {
    if (!isset($this->environmentContainer)) {
      $this->environmentContainer = new EnvironmentContainer();
    }

    return $this->environmentContainer;
  }
  
  public function getProject() {
    return $this->project;
  }

  public function getUserClass(WebforgeProject $project) {
    return $this->getDoctrinePackage()->getModule()->getEntityName('User');
  }
  
  public function getUser() {
    return $this->getAuthController()->getUser();
  }
  
  /**
   * @return DropContentsList
   */
  public function newDropContentsList($label) {
    $list = new DropContentsList();
    $this->addDropContentsList($list,$label);
    return $list;
  }

  /**
   * @chainable
   */
  public function addDropContentsList(DropContentsList $list, $label) {
    $this->dropContents[$label] =& $list;
    return $this;
  }
  
  public function createHTMLPage() {
    $page = new \Psc\HTML\FrameworkPage();
    $page->addCMSDefaultCSS();
    $page->addCMSRequireJS($assetModus = $this->project->isDevelopment() ? 'development' : 'built');
    $page->setTitleForProject($this->project);
    $page->setLanguage($this->getLanguage());
    return $page;
  }
  
  public function getMainHTMLPage(Array $vars = array()) {
    if (!isset($this->mainHTMLPage)) {
      $this->mainHTMLPage = $this->createHTMLPage();
      $this->addMarkup($this->mainHTMLPage, $vars);
    }
    
    return $this->mainHTMLPage;
  }
  
  public function addMarkup(\Psc\HTML\Page $page, Array $vars = array()) {
    $translator = $this->getContainer()->getTranslationContainer()->getTranslator();
    $page->body->content = TPL::get(
      array('CMS','main'),
      array_merge(
        array('page'=>$page,
          'authController'=>$this->getAuthController(),
          'user'=>$this->getUser(),
          'tabs'=>$this->getContentTabs(),
          'main'=>$this,
          'trans'=>function($key, Array $parameters = array()) use ($translator) {
            return $translator->trans($key, $parameters, 'cms');
          },
          'cms'=>$this,
        ), $vars)
    );
  }
  
  /**
   * @return Psc\UI\Accordion
   */
  public function getRightAccordion() {
    return $this->getRightContent()->getAccordion($this->getDropContents());
  }


  /**
   * @param Psc\CMS\RightContent $rightContent
   * @chainable
   */
  public function setRightContent(\Psc\CMS\RightContent $rightContent) {
    $this->rightContent = $rightContent;
    return $this;
  }

  /**
   * @return Psc\CMS\RightContent
   */
  public function getRightContent() {
    if (!isset($this->rightContent)) {
      if (isset($this->rightContentClass)) {
        $c = $this->rightContentClass;
        $this->rightContent = new $c($this->dc, $this->getContainer()->getTranslationContainer());
      } else {
        $this->rightContent = new RightContent($this->dc, $this->getContainer()->getTranslationContainer());
      }
      $this->initRightContent();
    }
    return $this->rightContent;
  }
  
  /**
   * @return Psc\HTML\HTMLInterface
   */
  public function createTemplateTabLink(array $tpl, $label) {
    // siehe auch Template
    $link = new \Psc\UI\Link(
      '/cms/tpl/'.implode('/', $tpl),
      $label
    );
      
    // siehe Psc.UI.Main (js) bei attachHandlers()
    return $link->html()
      ->addClass('\Psc\tabs-item') // wichtig für den on handler
      ->guid(implode('-',$tpl)) // wird die id für den tab
      ->publishGUID();
  }
  
  public function getContentTabs() {
    if (!isset($this->tabs)) {
      //$this->tabs = new ContentTabs();
      $this->tabs = new Tabs2(array(), $this->getWelcomeTemplate());
    }
    
    return $this->tabs;
  }
  
  public function getWelcomeTemplate() {
    if (!isset($this->welcomeTemplate)) {
      $translator = $this->getTranslator();

      $tpl = $this->welcomeTemplate = new Template('welcome');
      $this->welcomeTemplate->setVar('cms', $this);
      $this->welcomeTemplate->setVar('project', $this->getProject());
      $this->welcomeTemplate->setVar('main', $this);
      $this->welcomeTemplate->setLanguage($this->getLanguage());
      $this->welcomeTemplate->setVar('trans', function($key, Array $parameters = array()) use ($tpl, $translator) {
        return $translator->trans($key, $parameters, 'cms');
      });
      $this->welcomeTemplate->mergeI18n(
        array(
          'de'=>array('title'=>$translator->trans('welcome.tabTitle', array(), 'cms', 'de')),
          'en'=>array('title'=>$translator->trans('welcome.tabTitle', array(), 'cms', 'en')),
          'fr'=>array('title'=>$translator->trans('welcome.tabTitle', array(), 'cms', 'fr'))
        )
      );
    }

    return $this->welcomeTemplate;
  }

  /**
   * @return Webforge\Translation\Translator
   */
  public function getTranslator() {
    return $this->getContainer()->getTranslator();
  }
  
  /**
   * @param array $dropContents
   * @chainable
   */
  public function setDropContents(Array $dropContents) {
    $this->dropContents = $dropContents;
    return $this;
  }

  /**
   * @return array
   */
  public function getDropContents() {
    return $this->dropContents;
  }

  public function setContainerClass($fqn) {
    $this->containerClass = $fqn;
    return $this;
  }

  public function getLanguages() {
    return $this->getContainer()->getLanguages();
  }

  public function getLanguage() {
    return $this->getContainer()->getLanguage();
  }

  /**
   * @chainable
   */
  public function setContainer(ContainerRole $container) {
    $this->container = $container;
    return $this;
  }
}
