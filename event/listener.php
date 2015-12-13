<?php
/**
*
* @package phpBB Extension - Screen resolution in viewtopic
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\screenresolution\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request			$request
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param \phpbb\user						$user
	 * @param \phpbb\template\template			$template
	 */
	public function __construct(\phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\template\template $template)
	{
		$this->request = $request;
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_add_modify_data' 		=> 'user_add_modify_data',
			'core.page_header'					=> 'page_header',
			'core.viewtopic_post_rowset_data'	=> 'viewtopic_post_rowset_data',
			'core.viewtopic_modify_post_row'	=> 'viewtopic_modify_post_row',
			'core.login_box_redirect'			=> 'login_box_redirect',
			'core.viewtopic_cache_guest_data'	=> 'viewtopic_cache_guest_data',
		);
	}

	public function user_add_modify_data($event)
	{
		$user_row = $event['user_row'];
		$user_row = array_merge($user_row, array(
			'user_resolution'			=> '0x0',
		));
		$event->offsetSet('user_row', $user_row);
	}

	public function page_header($event)
	{
		$res = $this->request->get_super_global(\phpbb\request\request::COOKIE);
		$this->template->assign_vars(array(
			'S_USER_RESOLUTION'		=> (!isset($res['users_resolution'])) ? true : false,
		));
	}

	public function viewtopic_post_rowset_data($event)
	{
		$rowset_data = $event['rowset_data'];
		$row = $event['row'];
		$rowset_data = array_merge($rowset_data, array(
			'user_resolution'		=> $row['user_resolution'],
		));
		$event['rowset_data'] = $rowset_data;
	}

	public function viewtopic_cache_guest_data($event)
	{
		$user_cache_data = $event['user_cache_data'];
		$row = $event['row'];
		$user_cache_data = array_merge($user_cache_data, array(
			'user_resolution'		=> $row['user_resolution'],
		));
		$event['user_cache_data'] = $user_cache_data;
	}

	public function viewtopic_modify_post_row($event)
	{
		$this->user->add_lang_ext('dmzx/screenresolution', 'screenresolution');
		$row = $event['row'];
		$post_row = $event['post_row'];
		$post_row = array_merge($post_row, array(
			'USER_RESOLUTION'		=> ($row['user_resolution'] !== '0x0') ? $row['user_resolution'] : false,
		));
		$event['post_row'] = $post_row;
	}

	public function login_box_redirect($event)
	{
		$res = $this->request->get_super_global(\phpbb\request\request::COOKIE);
		$users_resolution = (isset($res['users_resolution'])) ? $res['users_resolution'] : '0x0';

		preg_match("#[0-9]{3,4}x[0-9]{3,4}#", $users_resolution, $user_res_res);

		if(!empty($user_res_res[0]))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_resolution = "' . $this->db->sql_escape($user_res_res[0]) . '"
				WHERE user_id = ' . $this->user->data['user_id'];
			$this->db->sql_query($sql);
		}
	}
}
