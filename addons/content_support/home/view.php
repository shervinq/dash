<?php
namespace content_support\home;

class view
{

	public static function config()
	{
		\dash\data::page_title(T_("Help center"));
		\dash\data::page_desc(T_("Easily manage your tickets and monitor or track them to get best answer until fix your problem"));
		\dash\data::page_pictogram('life-ring');

		\dash\data::badge_text(T_('Tickets'));
		\dash\data::badge_link(\dash\url::here(). '/ticket'. \dash\data::accessGet());


		if(\dash\data::isHelpCenter())
		{
			\dash\data::display_supportAdmin('content_support/home/article.html');
			self::help_center();
		}
		else
		{
			\dash\data::display_supportAdmin('content_support/home/dashboard.html');
			self::helpDashboard();
		}
	}

	public static function help_center()
	{
		$master = \dash\data::moduelRow();
		if(!isset($master['id']))
		{
			return;
		}
		$subchildPost = \dash\db\posts::get(['type' => 'help', 'parent' => $master['id'], 'status' => 'publish']);
		if(is_array($subchildPost))
		{
			$subchildPost = array_map(['\dash\app\posts', 'ready'], $subchildPost);
			\dash\data::subchildPost($subchildPost);
		}

		$master = \dash\app\posts::ready($master);
		\dash\data::datarow($master);


		// set back link
		\dash\data::badge_text(T_('Return to help center'));
		\dash\data::badge_link(\dash\url::here());

		// set page title
		if(isset($master['title']))
		{
			\dash\data::page_title($master['title']);
		}
		// set page desc
		if(isset($master['excerpt']))
		{
			\dash\data::page_desc($master['excerpt']);
		}
		// set page desc
		if(isset($master['meta']['icon']))
		{
			\dash\data::page_pictogram($master['meta']['icon']);
		}



	}



	public static function helpDashboard()
	{

		$get_posts_term =
		[
			'type'     => 'help',
			'parent'   => null,
			'language' => \dash\language::current(),
		];

		if(\dash\permission::check('supportShowDraftHelpCenter'))
		{
			$get_posts_term['status']   = ["NOT IN", "('deleted')"];
		}
		else
		{
			$get_posts_term['status']   = 'publish';
		}

		$search = \dash\request::get('q');

		if($search)
		{

			$get_search = $get_posts_term;
			unset($get_search['parent']);
			$dataTable = \dash\app\posts::list($search, $get_search);
			\dash\data::dataTable($dataTable);
		}

		$pageList = \dash\db\posts::get($get_posts_term);
		$pageList = array_map(['\dash\app\posts', 'ready'], $pageList);

		\dash\data::listCats($pageList);

		$randomArticles = \dash\app\posts::random_post(['type' => 'help', 'limit' => 10, 'status' => 'publish']);

		\dash\data::randomArticles($randomArticles);

		$randomFAQ = \dash\db\posts::get_posts_term(['type' => 'help', 'limit' => 10, 'tag' => 'faq', 'random' => true], 'help_tag');
		\dash\data::randomFAQ($randomFAQ);

	}
}
?>