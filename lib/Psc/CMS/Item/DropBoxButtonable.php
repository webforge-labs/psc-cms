<?php

namespace Psc\CMS\Item;

/**
 * Ein DropBoxButtonable kann zu einem Psc.UI.DropBoxButton umgewandelt werden
 *
 * er öffnet einen Tab, wenn er auf die Leiste gedroppt wird (tabOpenable)
 * wird als Button mit Full-Label dargestellt (buttonable)
 *
 * JS-Bridge:
 * Psc.CMS.DropBoxButtonable
 */
interface DropBoxButtonable extends TabOpenable, Buttonable, Identifyable {
}
?>