<?php

namespace Makaira\Connect\Modifier\Common;

use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Type;
use Makaira\Connect\Type\Common\AssignedAttribute;
use Makaira\Connect\Modifier;

/**
 * Class AttributeModifier
 * @package Makaira\Connect\Type\ProductRepository
 */
class AttributeModifier extends Modifier
{
    private $selectAttributesQuery = "
                        ( SELECT
                            :productActive as `active`,
                            oxattribute.oxid as `oxid`,
                            oxattribute.oxtitle as `oxtitle`,
                            oxobject2attribute.oxpos as `oxpos`,
                            oxobject2attribute.oxvalue as `oxvalue`
                        FROM
                            oxobject2attribute
                            JOIN oxattribute ON oxobject2attribute.oxattrid = oxattribute.oxid
                        WHERE
                            oxobject2attribute.oxobjectid = :productId
                        ) UNION (
                        SELECT
                            oxactive as `active`,
                            oxattribute.oxid as `oxid`,
                            oxattribute.oxtitle as `oxtitle`,
                            oxobject2attribute.oxpos as `oxpos`,
                            oxobject2attribute.oxvalue as `oxvalue`
                        FROM
                            oxarticles
                            JOIN oxobject2attribute ON (oxarticles.oxid = oxobject2attribute.oxobjectid)
                            JOIN oxattribute ON oxobject2attribute.oxattrid = oxattribute.oxid
                        WHERE
                            oxarticles.oxparentid = :productId)
                        ";

    /**
     * Modify product and return modified product
     *
     * @param BaseProduct $product
     * @param DatabaseInterface $database
     * @return BaseProduct
     */
    public function apply(Type $product, DatabaseInterface $database)
    {
        $attributes = $database->query(
            $this->selectAttributesQuery, [
            'productActive' => $product->OXACTIVE,
            'productId'     => $product->id,
        ]
        );
        $product->attribute = array_map(
            function ($row) {
                return new AssignedAttribute($row);
            }, $attributes
        );

        return $product;
    }
}
