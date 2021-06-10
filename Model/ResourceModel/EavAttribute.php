<?php
declare(strict_types=1);

namespace Bss\ResolveDuplicateProductUrl\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class EavAttribute
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * EavAttribute constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Check url key already ?exist
     *
     * @param string $urlKey
     * @return bool
     */
    public function isProductUrlKeyExists(string $urlKey): bool
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                [
                    'entity_varchar' => $this->getTable("catalog_product_entity_varchar")
                ],
                []
            )->joinInner(
                [
                    'eav_attr' => $this->getTable("eav_attribute")
                ],
                "`entity_varchar`.`attribute_id`=`eav_attr`.`attribute_id`",
                []
            )->joinInner(
                [
                    'attr_type' => $this->getTable("eav_entity_type")
                ],
                "`eav_attr`.`entity_type_id`=`attr_type`.`entity_type_id`",
                []
            );

        $select->where("`eav_attr`.`attribute_code`=?", "url_key");
        $select->where("`entity_varchar`.`value`=?", $urlKey);
        $select->where("`attr_type`.`entity_type_code`=?", "catalog_product");
        $select->limit(1);
        $select->columns(
            [
                'count' => new \Zend_Db_Expr("COUNT(distinct `entity_varchar`.`entity_id`)")
            ]
        );

        $result = $connection->fetchOne($select);

        return (bool) $result;
    }

    /**
     * Get tb name
     *
     * @param string $name
     * @return string
     */
    private function getTable(string $name): string
    {
        return $this->resourceConnection->getTableName($name);
    }
}
