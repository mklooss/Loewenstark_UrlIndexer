<?php

/**
 * @loadFixture default
 */
class Loewenstark_UrlIndexer_Test_Model_Catalog_Url extends EcomDev_PHPUnit_Test_Case
{
	private $expectedDuplicateIndexationResult = array(
		'product/1' => 'awesome-product.html',
		'product/2' => 'awesome-product-2.html',
		'product/3' => 'awesome-product-2-3.html',
	);

	/**
	 * @loadFixture duplicateURLKey
	 */
	public function testMagentoCatalogURLsAreInfinitlyCreatedOnReindex()
	{
		Mage::getConfig()->setNode('global/models/catalog/rewrite', 'false', true);

		$urlMock = $this->getMock('Mage_Catalog_Model_Url', array('generateUniqueIdPath'));
		$counter = 1;
		$urlMock->expects($this->any())
			->method('generateUniqueIdPath')
			->will($this->returnCallback(function () use (&$counter) {
				return $counter++;
			}));
		$this->replaceByMock('model', 'catalog/url', $urlMock);

		$this->reindex(5);

		$this->assertCoreURLRewriteEquals(array(
			'product/1' => 'awesome-product.html',
			'product/2' => 'awesome-product-9.html',
			'product/3' => 'awesome-product-10.html',
			'2' => 'awesome-product-2.html',
			'3' => 'awesome-product-2-3.html',
			'5' => 'awesome-product-3.html',
			'6' => 'awesome-product-4.html',
			'8' => 'awesome-product-5.html',
			'9' => 'awesome-product-6.html',
			'11' => 'awesome-product-7.html',
			'12' => 'awesome-product-8.html',
		));

		Mage::getConfig()->setNode('global/models/catalog/rewrite', 'url', true);
		Mage::getConfig()->setNode('global/models/catalog/rewrite/url', 'Loewenstark_UrlIndexer_Model_Catalog_Url', true);
	}

	/**
	 * @loadFixture duplicateURLKey
	 */
	public function testIfTwoProductsHasTheSameURLKeyNoExtraCoreURLRewriteIsCreatedOnMultipleReindex()
	{
		$this->reindex(10);

		$this->assertCoreURLRewriteEquals($this->expectedDuplicateIndexationResult);
	}

	/**
	 * @loadFixture duplicateURLKey
	 */
	public function testSuccessiveConflictsAreCorrectlyHandled()
	{
		$this->reindex(1);

		$this->assertCoreURLRewriteEquals($this->expectedDuplicateIndexationResult);
	}

	/**
	 * @loadFixture duplicateURLKey
	 */
	public function testIfAnExistingProductChangeURLKeyToAnExistingOneNoExtraURLRewriteIsCreatedOnMultipleReindex()
	{
		$product = Mage::getModel('catalog/product')->load(1)
			->setUrlKey('awesome-product-2')
			->save();
		$product->clearInstance();
		$this->reindex();

		$expected = array_merge(
			$this->expectedDuplicateIndexationResult,
			array(
				'product/1' => 'awesome-product-2.html',
				'product/2' => 'awesome-product.html',
			)
		);

		$this->assertCoreURLRewriteEquals($expected);
	}

	/**
	 * @loadFixture duplicateURLKey
	 * @loadFixture badCoreURLRewrite
	 */
	public function testIfCoreURLRewriteAlreadyContainsExtraEntriesNoMoreAreCreatedOnReindex()
	{
		$this->markTestIncomplete('Understand why Magento does not work as expected during these tests?');
		$this->reindex(10);

		$expected = array(
			'product/1' => 'awesome-product.html',
			'product/2' => 'awesome-product-3.html',
			'product/3' => 'awesome-product-2-4.html',
			'15374200_1419328330' => 'awesome-product-2.html',
			'15374200_1419328331' => 'awesome-product-2-3.html',
			'TODO_GENERATE_NAME' => 'awesome-product-4.html',
		);

		$this->assertCoreURLRewriteEquals($expected);
	}

	private function reindex($howManyTimes = 2)
	{
		$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_url');
		do {
			$process->reindexAll();
		} while (--$howManyTimes);

	}

	private function assertCoreURLRewriteEquals($expected)
	{
		$actual = array();
		foreach (Mage::getResourceModel('core/url_rewrite_collection')->load() as $rewrite) {
			$actual[$rewrite->getIdPath()] = $rewrite->getRequestPath();
		}

		$this->assertEquals($expected, $actual);
	}
}
