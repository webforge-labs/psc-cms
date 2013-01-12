<?php

namespace Psc\Code\Generate;

/**
 * Zeigt an, dass dies eine Referenz auf eine (eigentlich geparste) GClass sein soll
 *
 * die Reference selbst ist aber meistens nicht geparst. Dies brauchen wir z. B. in GMethod oder in GParameter damit wir beim elevaten nicht eine Endlos-Rekursion hervorrufen
 * Die GClassReference hat möglicherweise nicht dieselben oder inkomplette Informationen wie die GClass selbst, die geparst wurde.
 */
class GClassReference extends GClass {
}
?>