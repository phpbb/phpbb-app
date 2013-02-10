<?php
/**
*
* migrator [English]
*
* @package language
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'CONFIG_ALREADY_EXIST'				=> 'The config setting "%s" unexpectedly already exists.',
	'CONFIG_NOT_EXIST'					=> 'The config setting "%s" unexpectedly does not exist.',

	'GROUP_NOT_EXIST'					=> 'The group "%s" unexpectedly does not exist.',

	'MIGRATION_NOT_FULFILLABLE'			=> 'The migration "%1$s" is not fulfillable, missing migration "%2$s".',

	'MODULE_ALREADY_EXIST'				=> 'The module "%s" unexpectedly already exists.',
	'MODULE_ERROR'						=> 'An error occured while creating a module: %s',
	'MODULE_INFO_FILE_NOT_EXIST'		=> 'A required module info file is missing: %2$s',
	'MODULE_NOT_EXIST'					=> 'A required module does not exist: %s',
	'MODULE_NOT_REMOVABLE'				=> 'Module %1$s was unable to be removed: %2$s',

	'PERMISSION_ALREADY_EXIST'			=> 'The permission setting "%s" unexpectedly already exists.',
	'PERMISSION_NOT_EXIST'				=> 'The permission setting "%s" unexpectedly does not exist.',

	'ROLE_NOT_EXIST'					=> 'The permission role "%s" unexpectedly does not exist.',
));
