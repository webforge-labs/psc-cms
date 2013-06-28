Migration to 1.3
====================

AbstractEntityController
- constructor first parameter is a translationContainer. (it is recommended to use the ControllerFactory to create instances of your controllers)

SimpleContainerController
- constructor first parameter is a translationContainer. (it is recommended to use the ControllerFactory to create instances of your controllers)

GridPanel, FormPanel, EntityGridPanel, PanelButtons
- all require an instance of an `Psc\CMS\Translation\Container`

ControllerDependenciesProvider
- added Dependency to `Psc\CMS\Translation\Container`

RightContent
- added Dependency to `Psc\CMS\Translation\Container`