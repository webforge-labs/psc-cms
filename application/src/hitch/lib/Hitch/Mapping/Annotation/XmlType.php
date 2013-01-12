<?php

/**
 * This file is part of the Hitch package
 *
 * (c) Marc Roulias <marc@lampjunkie.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hitch\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * XmlTypes maps a entity property to an XML node
 *
 * @author psc
 * @Annotation
 */
class XmlType extends Annotation
{
	public $name;
	public $type;
}
?>