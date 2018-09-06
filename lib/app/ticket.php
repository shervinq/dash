<?php
namespace dash\app;

class ticket
{

	public static $sort_field =
	[
		'id',
		'plus',
		'minus',
		'datecreated',
		'status',
		'mobile',
		'author',
		'email',
	];


	public static function get($_id)
	{

		if(!$_id || !is_numeric($_id))
		{
			return false;
		}
		$get = \dash\db\comments::get(['id' => $_id, 'limit' => 1]);
		if(is_array($get))
		{
			return self::ready($get);
		}
		return false;
	}


	public static function add($_args)
	{
		$content = null;
		if(isset($_args['content']))
		{
			$content = \dash\safe::safe($_args['content'], 'sqlinjection');
		}

		\dash\app::variable($_args);

		// check args
		$args = self::check();

		if($args === false || !\dash\engine\process::status())
		{
			return false;
		}

		$args['content']    = $content;

		if(isset($args['user_id']) && is_numeric($args['user_id']))
		{
			$check_duplicate =
			[
				'user_id' => $args['user_id'],
				'content' => $args['content'],
				'limit'   => 1,
			];

			if(isset($args['post_id']) && $args['post_id'])
			{
				$check_duplicate['post_id'] = $args['post_id'];
			}

			if(isset($args['parent']) && $args['parent'])
			{
				$check_duplicate['parent'] = $args['parent'];
			}

			$check_duplicate = \dash\db\comments::get($check_duplicate);

			if(isset($check_duplicate['id']))
			{
				\dash\notif::error(T_("This text is duplicate and you are sended something like this before!"), 'content');
				return false;
			}
		}

		$args['visitor_id'] = \dash\utility\visitor::id();
		$args['ip']         = \dash\server::ip(true);

		if(\dash\url::subdomain())
		{
			$args['subdomain'] = \dash\url::subdomain();
		}

		$comment_id = \dash\db\comments::insert($args);

		if(!$comment_id)
		{
			\dash\notif::error(T_("No way to add new data"));
			return false;
		}
		\dash\log::db('addComment', ['data' => $comment_id, 'datalink' => \dash\coding::encode($comment_id)]);

		$return       = [];
		$return['id'] = $comment_id;
		return $return;
	}


	public static function edit($_args, $_id)
	{
		$content = null;
		if(isset($_args['content']))
		{
			$content = \dash\safe::safe($_args['content'], 'sqlinjection');
		}

		\dash\app::variable($_args);
		// check args

		if(!$_id || !is_numeric($_id))
		{
			\dash\notif::error(T_("Can not access to edit comment"));
			return false;
		}

		$args = self::check($_id);

		if($args === false || !\dash\engine\process::status())
		{
			return false;
		}
		$args['content'] = $content;

		if(!\dash\app::isset_request('status')) unset($args['status']);
		if(!\dash\app::isset_request('content')) unset($args['content']);
		if(!\dash\app::isset_request('author')) unset($args['author']);
		if(!\dash\app::isset_request('type'))   unset($args['type']);
		if(!\dash\app::isset_request('user_id')) unset($args['user_id']);
		if(!\dash\app::isset_request('post_id')) unset($args['post_id']);
		if(!\dash\app::isset_request('meta'))   unset($args['meta']);
		if(!\dash\app::isset_request('mobile')) unset($args['mobile']);
		if(!\dash\app::isset_request('title')) unset($args['title']);
		if(!\dash\app::isset_request('file')) unset($args['file']);
		if(!\dash\app::isset_request('parent')) unset($args['parent']);

		if(isset($args['status']) && $args['status'] === 'deleted')
		{
			\dash\permission::check('cpCommentsDelete');
		}
		\dash\log::db('editComment', ['data' => $_id, 'datalink' => \dash\coding::encode($_id)]);
		return \dash\db\comments::update($args, $_id);
	}


	public static function list($_string = null, $_args = [])
	{

		$default_meta =
		[
			'pagenation' => true,
			'sort'       => null,
			'order'      => null,
			'join_user'  => false,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_meta, $_args);

		if($_args['sort'] && !in_array($_args['sort'], self::$sort_field))
		{
			$_args['sort'] = null;
		}

		$result            = \dash\db\comments::search($_string, $_args);
		$temp              = [];

		foreach ($result as $key => $value)
		{
			$check = self::ready($value);
			if($check)
			{
				$check = \dash\app::fix_avatar($check);
				$temp[] = $check;
			}
		}

		return $temp;
	}


	public static function check($_id = null, $_option = [])
	{


		$default_option =
		[
			'meta' => [],
		];

		if(!is_array($_option))
		{
			$_option = [];
		}

		$_option = array_merge($default_option, $_option);

		$content = \dash\app::request('content');

		if(!$content && \dash\app::isset_request('content'))
		{
			\dash\notif::error(T_("Please fill the content box"), 'content');
			return false;
		}

		$author = \dash\app::request('author');
		if($author && mb_strlen($author) >= 100)
		{
			$author = substr($author, 0, 99);
		}

		$type = \dash\app::request('type');
		if($type && mb_strlen($type) >= 50)
		{
			$type = substr($type, 0, 49);
		}

		$meta = \dash\app::request('meta');
		if($meta && (is_array($meta) || is_object($meta)))
		{
			$meta = json_encode($meta, JSON_UNESCAPED_UNICODE);
		}

		$mobile = \dash\app::request('mobile');
		if($mobile && mb_strlen($mobile) > 15)
		{
			$mobile = substr($mobile, 0, 14);
		}

		$user_id = \dash\app::request('user_id');
		if($user_id && !is_numeric($user_id))
		{
			$user_id = null;
		}

		$status = \dash\app::request('status');
		if($status && !in_array($status, ['approved','awaiting','unapproved','spam','deleted','filter','close', 'answered']))
		{
			\dash\notif::error(T_("Invalid status"), 'status');
			return false;
		}


		$title = \dash\app::request('title');
		if($title && mb_strlen($title) > 400)
		{
			\dash\notif::error(T_("Title is out of range!"));
			return false;
		}

		$file = \dash\app::request('file');
		$parent = \dash\app::request('parent');
		if(\dash\app::isset_request('parent') && \dash\app::request('parent') && !is_numeric($parent))
		{
			\dash\notif::error(T_("Invalid parent"));
			return false;
		}


		$args            = [];
		$args['status']  = $status ? $status : 'awaiting';
		$args['author']  = $author;
		$args['type']    = $type;
		$args['user_id'] = $user_id;

		$args['meta']    = $meta;
		$args['mobile']  = $mobile;
		$args['title']   = $title;
		$args['file']    = $file;
		$args['parent']    = $parent;

		return $args;
	}


	/**
	 * ready data of classroom to load in api
	 *
	 * @param      <type>  $_data  The data
	 */
	public static function ready($_data)
	{
		$result = [];
		foreach ($_data as $key => $value)
		{

			switch ($key)
			{
				case 'status':
					$color = null;
					switch ($value)
					{
						case 'awaiting':
							$color = null;
							break;

						case 'unapproved':
							$color = 'warning';
							break;

						case 'spam':
						case 'deleted':
						case 'filter':
							$color = 'negative';
							break;

						case 'close':
							$color = 'disabled';
							break;

						case 'answered':
							$color = 'positive';
							break;
					}

					if(isset($_data['plus']) && $_data['plus'])
					{
						if($value === 'awaiting')
						{
							$color = 'active';
						}
					}

					$result['rowColor'] = $color;
					$result[$key]       = $value;

					break;

				case 'user_id':
				case 'term_id':
					if(isset($value))
					{
						$result[$key] = \dash\coding::encode($value);
					}
					else
					{
						$result[$key] = null;
					}
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}
}
?>