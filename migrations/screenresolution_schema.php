<?php
/**
*
* @package phpBB Extension - Screen resolution in viewtopic
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\screenresolution\migrations;

class screenresolution_schema extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return 	array(
			'add_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_resolution' => array('VCHAR:9', '0x0'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return 	array(
			'drop_columns' => array(
				$this->table_prefix . 'users' => array('user_resolution'),
			),
		);
	}
}
