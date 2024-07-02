<?php

namespace App\Console\Commands;

use App\Domain\Marketing\BLL\Marketing\MarketingBLL;
use App\Domain\Marketing\Models\Marketing;
use Illuminate\Console\Command;

/**
 * @property MarketingBLL $marketingBLL
 */
class CreateAllMarketingCommand extends Command
{
    protected $signature = 'marketing:create-all';

    protected $description = 'Command description';

    public function __construct(MarketingBLL $marketingBLL)
    {
        parent::__construct();

        $this->marketingBLL = $marketingBLL;
    }

    public function handle(): void
    {
        $chunkSize = 1000;

        Marketing::distinct('date')->chunk($chunkSize, function ($marketings) {
            foreach ($marketings as $marketing) {
                $this->marketingBLL->syncMarketingRecap($marketing->date);
            }
        });
    }
}
