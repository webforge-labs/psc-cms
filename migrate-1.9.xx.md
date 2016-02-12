Migration to 1.10
====================

this migration is non BC breaking.

To update just use php 5.6 (upgrade to jessie on debian)

## Major changes

- removed setting mbstring internal encoding per ini_set in `Psc\CMS\Container`
