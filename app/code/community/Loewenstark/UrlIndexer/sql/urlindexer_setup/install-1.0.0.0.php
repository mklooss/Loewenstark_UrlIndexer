<?php

$this->startSetup();

$this->run("ALTER TABLE `{$this->getTable('core_url_rewrite')}` ADD INDEX `IDX_CATEGORY_REWRITE` (`category_id`, `is_system`, `product_id`, `store_id`, `id_path`);");

$this->endSetup();