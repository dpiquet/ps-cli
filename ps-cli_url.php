<?php

class PS_CLI_URL {

	public static function list_rewritings() {

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'id',
			'page',
			'title',
			'url_rewrite'
			)
		);

		$pages = Meta::getMetasByIdLang(PS_CLI_UTILS::$LANG);

		foreach($pages as $page) {
			$table->addRow(Array(
				$page['id_meta'],
				$page['page'],
				$page['title'],
				$page['url_rewrite']
				)
			);
		}

		$table->display();

		return;
	}

}
