Migration to 2.2
====================

AbstractEntityController
- when you overlodded hydrateEntity in some controller, you have to override hydrateEntityInRevision now
- when you overloaded getEntity in some controller, you have to override getEntityInRevision now
- when you overloaded processEntityFormRequest, you have to add a third parameter $revision to the method (and pass it)
- (overload or call) createEmptyEntity has now a revision parameter which defaults to NULL
- you can overload onDelete for special operations while deleting

Psc\Image\*
- when you used some of the Psc\Image\ Classes: add "pscheit/psc-cms-image" in the same version as pscheit/psc-cms to your dependencies and run update.

EntityService:
- Signature has changed. Parameter #1 is not optional (dcPackage), Parameter #2 is a Psc\CMS\Controller\Factory

Entities:
- the entityName is now constructed in dash style. That means NavigationNode becomes navigation-node

ProjectMain:
- create a class YourNamespace\CMS\Container which extends \Psc\CMS\Roles\AbstractContainer (or implement Psc\CMS\Roles\Container)
- set containerClass to this class (per default its the projectNamespace\CMS\Container)
- override getContainer in your main if necessary (if you have ovrriden the constructor from Psc\CMS\Roles\AbstractContainer)

AcceptanceTests (MainFixture):
- use cmf.user and cmf.password from host config in your fixture for access for acceptance tests

ContentStreamConverter + Context:
- the abstract class \Psc\TPL\ContentStream\Converter needs now an implementation of the Psc\TPL\ContentStream\Context as first parameter. You can use a SimpleContainer for this

Projects (DB):
- ContentStream has another column "type" which defaults to "page-content"

SimpleContainer
- add getNavigationRepository
- add getRoleRepository

ContentStreamEntry:
serialize needs to be implemented: serialize($context, \Closure $serializeEntry)
doSerialize needs second parameter to be Closure $serializeEntry