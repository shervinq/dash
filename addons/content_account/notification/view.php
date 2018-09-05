<?php
namespace content_account\notification;

class view
{

	public static function config()
	{
		$args =
		[
			'sort'  => \dash\request::get('sort'),
			'order' => \dash\request::get('order'),
		];

		if(!$args['order'])
		{
			$args['order'] = 'desc';
		}

		$search_string = \dash\request::get('q');

		$sortLink  = \dash\app\sort::make_sortLink(\lib\app\grade::$sort_field, \dash\url::this());
		$dataTable = \dash\app\notification::list($search_string, $args);

		\dash\data::sortLink($sortLink);
		\dash\data::dataTable($dataTable);

		$check_empty_datatable = $args;
		unset($check_empty_datatable['sort']);
		unset($check_empty_datatable['order']);

		// set dataFilter
		$dataFilter = \content_m\view::createFilterMsg($search_string, $check_empty_datatable);
		\dash\data::dataFilter($dataFilter);
	}
}
?>