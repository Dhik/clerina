<?php

namespace App\Domain\SpentTarget\Models;

use Illuminate\Database\Eloquent\Model;

class SpentTarget extends Model
{
    // Specify the table name (optional if it follows the Laravel convention)
    protected $table = 'spent_targets';

    // Specify the primary key (optional if it follows the Laravel convention)
    protected $primaryKey = 'id';

    // Allow mass assignment for these columns
    protected $fillable = [
        'budget',
        'kol_percentage',
        'ads_percentage',
        'creative_percentage',
        'other_percentage',
        'affiliate_percentage',
        'month',
        'tenant_id',
        'activation_percentage',
        'free_product_percentage',
    ];

    // Set the data type of each column if necessary
    protected $casts = [
        'budget' => 'decimal:2',
        'kol_percentage' => 'decimal:2',
        'ads_percentage' => 'decimal:2',
        'creative_percentage' => 'decimal:2',
        'other_percentage' => 'decimal:2',
        'affiliate_percentage' => 'decimal:2',
        'activation_percentage' => 'decimal:2',
        'free_product_percentage' => 'decimal:2',
        'month' => 'string', // Or 'date' if stored as a date
    ];

    // Define relationships (if necessary)
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id'); // Assuming you have a Tenant model
    }
}
