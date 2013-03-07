Migration to 2.2
====================

AbstractEntityController
- when you overlodded hydrateEntity in some controller, you have to override hydrateEntityRevision now
- when you overloaded getEntity in some controller, you have to override getEntityRevision now
- when you overloaded processEntityFormRequest, you have to add a third parameter $revision to the method (and pass it)
- (overload or call) createEmptyEntity has now a revision parameter 

Psc\Image\*
- when you used some of the Psc\Image\ Classes: add "pscheit/psc-cms-image" in the same version as pscheit/psc-cms to your dependencies and run update.