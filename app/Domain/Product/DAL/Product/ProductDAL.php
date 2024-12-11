<?php

namespace App\Domain\Product\DAL\Product;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Product\Models\Product;

/**
 * @property Product model
 */
class ProductDAL extends BaseDAL implements ProductDALInterface
{
    public function __construct(Product $product)
    {
        $this->model = $product;
    }
}
