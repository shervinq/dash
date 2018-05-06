<?php
namespace content_cp\permission\add;


class view
{
	public static function config()
	{
		\dash\data::page_title(T_("Permissions"));
		\dash\data::page_desc(T_("Set and config permission of users and allow them to do something."));


		\dash\data::perm_list(
			[
				'news'=>
				[
					'cp_news_view' =>
					[
						'title' => 'مشاهده خبر',
						'cat' => 'cp',
						'subcat' => 'news',
						'check' => false,
						'verify' => false,
						'require' => null,
					],
					'cp_news_add'=>
					[
						'title' => 'افزودن خبر',
						'cat' => 'cp',
						'subcat' => 'news',
						'check' => false,
						'verify' => false,
						'require' => ['cp_news_view'],
					],
					'cp_news_remove'=>
					[
						'title' => 'افزودن خبر',
						'cat' => 'cp',
						'subcat' => 'news',
						'check' => false,
						'verify' => false,
						'require' => ['cp_news_view'],
					],
				],
				'terms'=>
				[
					'cp_terms_view' =>
					[
						'title' => 'مشاهده کلیدواژه',
						'cat' => 'cp',
						'subcat' => 'terms',
						'check' => false,
						'verify' => false,
						'require' => null,
					],
					'cp_terms_add'=>
					[
						'title' => 'افزودن کلیدواژه',
						'cat' => 'cp',
						'subcat' => 'terms',
						'check' => false,
						'verify' => false,
						'require' => ['cp_terms_view'],
					],
				]
			]
		);

	}
}
?>