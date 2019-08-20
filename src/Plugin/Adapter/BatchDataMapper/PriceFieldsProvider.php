<?php

namespace Xsarus\ElasticsearchPrice\Plugin\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\PriceFieldsProvider as BasePriceFieldsProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PriceFieldsProvider
 * @package Xsarus\ElasticsearchPrice\Plugin\Adapter\BatchDataMapper
 */
class PriceFieldsProvider
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PriceFieldsProvider constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param BasePriceFieldsProvider $subject
     * @param array $result
     * @param array $productIds
     * @param $storeId
     * @return array
     */
    public function afterGetFields(
        BasePriceFieldsProvider $subject,
        $result,
        array $productIds,
        $storeId
    ) {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if ($storeId != $websiteId) {
            foreach ($result as $productId => $fields) {
                foreach ($fields as $key => $value) {
                    if (preg_match('/price_(\d+)_' . $storeId . '/', $key, $matches)) {
                        $newKey = preg_replace('/price_(\d+)_' . $storeId . '/', 'price_$1_' . $websiteId, $key);
                        unset($result[$productId][$key]);
                        $result[$productId][$newKey] = $value;
                    }
                }
            }
        }
        
        foreach ($result as $productId => $fields) {
            foreach ($fields as $key => $value) {
                $result[$productId][$key] = (float)$value;
            }
        }
        
        return $result;
    }
}
