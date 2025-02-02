<?php
namespace content_support\ticket\tags;

class model
{

	public static function post()
	{
		if(\dash\request::post('action') === 'remove')
		{
			$term_id = \dash\request::get('edit');
			$term_id = \dash\coding::decode($term_id);
			if(!$term_id)
			{
				\dash\notif::error(T_("Invalid term id"));
				return false;
			}

			$load_term = \dash\db\terms::get(['id' => $term_id, 'limit' => 1]);
			if(!isset($load_term['type']))
			{
				\dash\notif::error(T_("Term id not found"));
				return false;
			}

			switch ($load_term['type'])
			{

				case 'support_tag':
					\dash\permission::access('cpTagSupportDelete');
					break;
			}

			\dash\log::set('removeTerm', ['code' => $term_id]);

			$remove = \dash\db\terms::remove($term_id);
			if($remove)
			{
				\dash\notif::warn(T_("Data successfully removed"));
				\dash\redirect::to(\dash\url::that());
			}
			else
			{
				\dash\notif::error(T_("This term or tag used in post and can not delete it!"));
			}
			return;
		}

		$post             = [];
		$post['title']    = \dash\request::post('title');
		$post['desc']     = \dash\request::post('desc');
		$post['excerpt']  = \dash\request::post('excerpt');
		$post['parent']   = \dash\request::post('parent');
		$post['language'] = \dash\request::post('language');;
		$post['slug']     = \dash\request::post('slug');
		$post['type']     = 'support_tag';
		$post['status']   = \dash\request::post('status');


		if(\dash\request::post('color') && (\dash\request::get('type') === 'support_tag' || \dash\permission::supervisor() ))
		{
			$color = \dash\request::post('color');
			if($color && !in_array($color, ['primary','secondary','success','danger','warning','info', 'light', 'dark', 'pain']))
			{
				\dash\notif::error(T_("Invalid tag color"), 'color');
				return false;
			}

			$post['color'] = $color;
		}

		if(\dash\request::post('icon') && (\dash\request::get('type') === 'help' || \dash\permission::supervisor() ))
		{
			$icon = \dash\request::post('icon');
			$post['icon'] = $icon;
		}

		$myType = \dash\request::get('type');

		if(\dash\request::get('edit'))
		{
			if($myType)
			{
				switch ($myType)
				{
					case 'cat':
					case 'category':
						\dash\permission::access('cpCategoryEdit');
						break;

					case 'help_tag':
						\dash\permission::access('cpTagHelpEdit');
						break;

					case 'support_tag':
						\dash\permission::access('cpTagSupportEdit');
						break;

					case 'tag':
					default:
						\dash\permission::access('cpTagEdit');
						break;
				}
			}
			else
			{
				\dash\permission::access('cpTagEdit');
			}

			$post['id'] = \dash\request::get('edit');
			\dash\app\term::edit($post);
		}
		else
		{
			if($myType)
			{
				switch ($myType)
				{
					case 'cat':
					case 'category':
						\dash\permission::access('cpCategoryAdd');
						break;

					case 'support_tag':
						\dash\permission::access('cpTagSupportAdd');
						break;

					case 'help_tag':
						\dash\permission::access('cpTagHelpAdd');
						break;

					case 'tag':
					default:
						\dash\permission::access('cpTagAdd');
						break;
				}
			}
			else
			{
				\dash\permission::access('cpTagAdd');
			}

			\dash\app\term::add($post);
		}

		if(\dash\engine\process::status())
		{
			if(\dash\request::get('edit'))
			{
				\dash\redirect::pwd();
				\dash\notif::ok(T_("Term successfully edited"));
			}
			else
			{
				\dash\notif::ok(T_("Term successfully added"));
				\dash\redirect::pwd();
			}
		}
	}

}
?>