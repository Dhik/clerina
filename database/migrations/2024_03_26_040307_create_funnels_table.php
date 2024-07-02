<?php

use App\Domain\Funnel\Enums\FunnelTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('funnels', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', FunnelTypeEnum::Types);
            $table->unsignedBigInteger('social_media_id')->nullable();
            $table->bigInteger('spend')->nullable();
            $table->bigInteger('reach')->nullable();
            $table->bigInteger('cpr')->nullable();
            $table->bigInteger('impression')->nullable();
            $table->bigInteger('cpm')->nullable();
            $table->decimal('frequency', 30, 2)->nullable();
            $table->bigInteger('cpv')->nullable();
            $table->bigInteger('play_video')->nullable();
            $table->bigInteger('link_click')->nullable();
            $table->bigInteger('cpc')->nullable();
            $table->bigInteger('engagement')->nullable();
            $table->bigInteger('cpe')->nullable();
            $table->decimal('ctr', 30, 2)->nullable();
            $table->bigInteger('cplv')->nullable();
            $table->bigInteger('cpa')->nullable();
            $table->bigInteger('atc')->nullable();
            $table->bigInteger('initiated_checkout_number')->nullable();
            $table->bigInteger('purchase_number')->nullable();
            $table->bigInteger('cost_per_ic')->nullable();
            $table->bigInteger('cost_per_atc')->nullable();
            $table->bigInteger('cost_per_purchase')->nullable();
            $table->decimal('roas', 30, 2)->nullable();
            $table->timestamps();

            $table->foreign('social_media_id')
                ->references('id')
                ->on('social_media')
                ->onDelete('set null');

            $table->index('date');
            $table->index('social_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnels');
    }
};
