<?php
/**
 * SimplerAuth
 *
 * @author     Kenji Suzuki https://github.com/kenjis
 * @copyright  2012 Kenji Suzuki
 * @license    MIT License http://www.opensource.org/licenses/mit-license.php
 */

Autoloader::add_core_namespace('SimplerAuth');

Autoloader::add_classes(array(
	'SimplerAuth\\Auth' => __DIR__.'/classes/auth.php',
));
