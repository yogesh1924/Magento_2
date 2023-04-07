<?php

namespace Magento\Coders\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ProductProfit extends \Magento\Ui\Component\Listing\Columns\Column
{
	protected $localeCurrency;
	protected $_productRepository;

	public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
		$this->_productRepository = $productRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
        	$fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $productId = $item['entity_id'];
                $product = $this->_productRepository->getById($productId);
				$stock = $product->getExtensionAttributes()->getStockItem();
				if($stock){
                   $productQty = $stock->getQty();
           		}

				if($product->getCost() && !empty($product->getCost())) {
                $product_cost = $product->getCost();
                $product_price = $product->getPrice();
				$profit_price = ($product_price - $product_cost) * $productQty;

				$store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            	$currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

                $item[$fieldName] = $currency->toCurrency(sprintf("%f", $profit_price));

				}
            }
        }
        return $dataSource;
    }
}
