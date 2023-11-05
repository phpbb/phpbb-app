<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\db\migration\data\v330;

class ban_table_p1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\default_data_type_ids');
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'bans'	=> array(
					'COLUMNS'		=> array(
						'ban_id'				=> array('ULINT', null, 'auto_increment'),
						'ban_mode'				=> array('VCHAR', ''),
						'ban_item'				=> array('STEXT_UNI', ''),
						'ban_start'				=> array('TIMESTAMP', 0),
						'ban_end'				=> array('TIMESTAMP', 0),
						'ban_reason'			=> array('VCHAR_UNI', ''),
						'ban_reason_display'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'ban_id',
					'KEYS'			=> array(
						'ban_end'	=> array('INDEX', 'ban_end'),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'bans',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'old_to_new'))),
		);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'new_to_old'))),
		);
	}

	public function old_to_new($start)
	{
		$start = (int) $start;
		$limit = 500;
		$processed_rows = 0;

		$sql = 'SELECT *
			FROM ' . $this->table_prefix . "banlist";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$bans = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$processed_rows++;

			if ($row['ban_exclude'])
			{
				continue;
			}

			$row['ban_userid'] = (int) $row['ban_userid'];
			$item = $mode = '';
			if ($row['ban_ip'] !== '')
			{
				$mode = 'ip';
				$item = $row['ban_ip'];
			}
			else if ($row['ban_email'] !== '')
			{
				$mode = 'email';
				$item = $row['ban_email'];
			}
			else if ($row['ban_userid'] !== 0)
			{
				$mode = 'user';
				$item = $row['ban_userid'];
			}

			if ($mode === '' || $item === '')
			{
				continue;
			}

			$bans[] = [
				'ban_mode'				=> $mode,
				'ban_item'				=> $item,
				'ban_start'				=> $row['ban_start'],
				'ban_end'				=> $row['ban_end'],
				'ban_reason'			=> $row['ban_reason'],
				'ban_reason_display'	=> $row['ban_give_reason'],
			];
		}
		$this->db->sql_freeresult($result);

		if ($processed_rows > 0)
		{
			$this->db->sql_multi_insert($this->table_prefix . 'bans', $bans);
		}
		else if ($processed_rows < $limit)
		{
			return;
		}

		return $limit + $start;
	}

	public function new_to_old($start)
	{
		$start = (int) $start;
		$limit = 500;
		$processed_rows = 0;

		$sql = 'SELECT *
			FROM ' . $this->table_prefix . "bans";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$bans = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$processed_rows++;

			$bans[] = [
				'ban_userid'		=> ($row['ban_mode'] === 'user') ? (int) $row['ban_item'] : 0,
				'ban_ip'			=> ($row['ban_mode'] === 'ip') ? $row['ban_item'] : '',
				'ban_email'			=> ($row['ban_mode'] === 'email') ? $row['ban_item'] : '',
				'ban_start'			=> $row['ban_start'],
				'ban_end'			=> $row['ban_end'],
				'ban_exclude'		=> false,
				'ban_reason'		=> $row['ban_reason'],
				'ban_give_reason'	=> $row['ban_reason_display'],
			];
		}
		$this->db->sql_freeresult($result);

		if ($processed_rows > 0)
		{
			$this->db->sql_multi_insert($this->table_prefix . 'banlist', $bans);
		}
		else if ($processed_rows < $limit)
		{
			return;
		}

		return $limit + $start;
	}
}
