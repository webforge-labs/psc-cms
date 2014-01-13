Migration to 1.6
====================

## Major changes

All Psc\Data\Type Classes have been aliased as Webforge\Types

Psc\Code\Generate\GClass

  - getName does not longer return FQN with \ on front. It returns the shortname of the class (to implement ClassInterface from Webforge\Common)
