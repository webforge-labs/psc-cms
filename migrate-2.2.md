Migration to 2.2
====================

AbstractEntityController
- when you did extend hydrateEntity in some controller, you have to override hydrateEntityRevision now
- when you did extend processEntityFormRequest, you have to add a third parameter $revision to the method (and pass it)

Psc\Image\*
- when you used some of the Psc\Image\ Classes: add "pscheit/psc-cms-image" in the same version as pscheit/psc-cms to your dependencies and run update.