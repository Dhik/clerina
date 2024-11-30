<?php

namespace App\Domain\SpentTarget\Models;

use Illuminate\Database\Eloquent\Model;

class SpentAmount extends Model
{
    protected $table = 'spent_amounts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'date',
        'activation_spent',
        'creative_spent',
        'free_product_spent',
        'other_spent',
        'tenant_id',
    ];
    protected $casts = [
        'activation_spent' => 'decimal:2',
        'creative_spent' => 'decimal:2',
        'free_product_spent' => 'decimal:2',
        'other_spent' => 'decimal:2',
        'date' => 'date', 
    ];

    // Define relationships (if necessary)
    // Example: if this model belongs to another model, you can define the relationship here
    // public function someRelation()
    // {
    //     return $this->belongsTo(OtherModel::class);
    // }
}
