Migration to 2.2
====================

AbstractEntityController
- when you overlodded hydrateEntity in some controller, you have to override hydrateEntityInRevision now
- when you overloaded getEntity in some controller, you have to override getEntityInRevision now
- when you overloaded processEntityFormRequest, you have to add a third parameter $revision to the method (and pass it)
- (overload or call) createEmptyEntity has now a revision parameter which defaults to NULL

Psc\Image\*
- when you used some of the Psc\Image\ Classes: add "pscheit/psc-cms-image" in the same version as pscheit/psc-cms to your dependencies and run update.

EntityService:
- Signature has changed. Parameter #1 is not optional (dcPackage), Parameter #2 is a Psc\CMS\Controller\Factory

ProjectMain:
- create a class YourNamespace\CMS\SimpleContainer which extends \Psc\CMS\Roles\AbstractSimpleContainer (or implements SimpleContainer)
- set containerClass to this class (per default its the projectNamespace\CMS\SimpleContainer)
- override getContainer in your main if necessary (if you have ovrriden the constructor from AbstractSimpleContainer)