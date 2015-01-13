<?php

class PS_CLI_CMS {

	public static function list_categories() {
		$context = Context::getContext();

			$out = new Cli\Table();
			$out->setHeaders(Array(
				'id',
				'parent',
				'level_depth',
				'active',
				'created',
				'updated',
				'position',
				'name',
				'lang',
				'description',
				'link_rewrite'
				)
			);

		$categories = CMSCategory::getCategories($context->language->id, false);

		//$categories[0][1] cannot be deleted so safe to assume it exists
		self::_table_recurse_categories($categories, $categories[0][1], 1, $out);

		$out->display();

		return;

	}

	//fills $table with categories infos
	private static function _table_recurse_categories($categories, $current, $id_category = 1, &$table) {

		$table->addRow(Array(
			$current['infos']['id_cms_category'],
			$current['infos']['id_parent'],
			$current['infos']['level_depth'],
			$current['infos']['active'],
			$current['infos']['date_add'],
			$current['infos']['date_upd'],
			$current['infos']['position'],
			$current['infos']['name'],
			Language::getIsoById($current['infos']['id_lang']),
			$current['infos']['description'],
			$current['infos']['link_rewrite']
			)
		);

		if (isset($categories[$id_category])) {
			foreach (array_keys($categories[$id_category]) as $key) {
				self::_table_recurse_categories($categories, $categories[$id_category][$key], $key, $table);
			}
		}	
	}

	public static function create_category($parent, $name, $linkRewrite) {
		echo "Not implemented\n";
		return false;
	}

	public static function list_pages() {

		$context = Context::getContext();

		$table = new Cli\Table();

		$table->setHeaders( Array(
			'id',
			'category',
			'position',
			'active',
			'indexation',
			'lang',
			'title',
			'rewrite',
			'keywords'
			)
		);

		//$pages = CMS::listCms($context->language->id);
		$pages = CMS::getCMSPages($context->language->id);

		foreach ($pages as $page) {
			$table->addRow(Array(
				$page['id_cms'],
				$page['id_cms_category'],
				$page['position'],
				$page['active'],
				$page['indexation'],
				Language::getIsoById($page['id_lang']),
				$page['meta_title'],
				$page['link_rewrite'],
				$page['meta_keywords']
				)
			);
		}

		$table->display();

		return true;
	}
}

?>
