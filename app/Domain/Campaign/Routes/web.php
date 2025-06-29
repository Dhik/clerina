<?php

use App\Domain\Campaign\Controllers\CampaignContentController;
use App\Domain\Campaign\Controllers\CampaignController;
use App\Domain\Campaign\Controllers\KeyOpinionLeaderController;
use App\Domain\Campaign\Controllers\OfferController;
use App\Domain\Campaign\Controllers\StatisticController;
use App\Domain\Campaign\Controllers\BudgetController;
use App\Domain\Campaign\Controllers\BriefController;
use App\Domain\Campaign\Controllers\BriefContentController;
use App\Domain\Campaign\Controllers\LiveDataController;
use App\Domain\Campaign\Controllers\LiveShopeeController;
use App\Domain\Campaign\Controllers\LiveTiktokController;
use App\Domain\Campaign\Controllers\LiveShopeeProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
        Route::prefix('campaign')
            ->group(function () {
                Route::get('/', [CampaignController::class, 'index'])->name('campaign.index');
                Route::get('/get', [CampaignController::class, 'get'])->name('campaign.get');
                Route::get('/summary', [CampaignController::class, 'getCampaignSummary'])->name('campaign.summary');
                Route::get('/total', [CampaignController::class, 'getCampaignTotal'])->name('campaign.total');
                Route::get('/download', [CampaignController::class, 'downloadVideo'])->name('campaign.download');
                Route::get('/nas', [CampaignController::class, 'listFiles'])->name('campaign.nas');
                Route::get('/titles', [CampaignController::class, 'getCampaignsTitles'])->name('campaign.titles');
                Route::get('/create', [CampaignController::class, 'create'])->name('campaign.create');
                Route::post('/store', [CampaignController::class, 'store'])->name('campaign.store');
                Route::get('/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaign.edit');
                Route::get('/{campaign}/refresh', [CampaignController::class, 'refresh'])->name('campaign.refresh');
                Route::get('/bulk-refresh', [CampaignController::class, 'bulkRefresh'])->name('campaign.bulkRefresh');
                Route::get('/refresh-all', [CampaignController::class, 'refreshAllCampaigns'])->name('campaign.refreshAll');
                Route::put('/{campaign}/update', [CampaignController::class, 'update'])->name('campaign.update');
                Route::get('/{campaign}/show', [CampaignController::class, 'show'])->name('campaign.show');
                Route::get('/{campaign}/statistic', [CampaignContentController::class, 'statistics'])->name('campaign.statistics');
                Route::delete('/{campaign}', [CampaignController::class, 'destroy'])->name('campaign.destroy');
                
            });

                Route::prefix('brief')
                    ->group(function () {
                        Route::get('/', [BriefController::class, 'index'])->name('brief.index');
                        Route::get('/data', [BriefController::class, 'data'])->name('brief.data');
                        Route::get('/create', [BriefController::class, 'create'])->name('brief.create');
                        Route::post('/', [BriefController::class, 'store'])->name('brief.store');
                        Route::get('/{brief}', [BriefController::class, 'show'])->name('brief.show');
                        Route::get('/{brief}/edit', [BriefController::class, 'edit'])->name('brief.edit');
                        Route::put('/{brief}', [BriefController::class, 'update'])->name('brief.update');
                        Route::delete('/{brief}', [BriefController::class, 'destroy'])->name('brief.destroy');
                    });
        
        Route::prefix('brief_contents')
            ->group(function () {
                Route::get('/chart/{id_brief}', [BriefContentController::class, 'chartData'])->name('brief_contents.chart');
                Route::post('/', [BriefContentController::class, 'store'])->name('brief_contents.store');
                Route::get('/{id_brief}/data', [BriefContentController::class, 'data'])->name('brief_contents.data');
                Route::get('/{id}/kpi', [BriefContentController::class, 'getKPI'])->name('brief_contents.get-kpi');
                Route::delete('/{id}', [BriefContentController::class, 'destroy'])->name('brief_contents.destroy');
            });

        Route::prefix('live_data')->group(function () {
            Route::get('/', [LiveDataController::class, 'index'])->name('live_data.index');
            Route::get('/create', [LiveDataController::class, 'create'])->name('live_data.create');
            Route::get('/dashboard', [LiveDataController::class, 'dashboard'])->name('live_data.dashboard');
            Route::get('/chart-data', [LiveDataController::class, 'chartData'])->name('live_data.chart-data');
            Route::post('/', [LiveDataController::class, 'store'])->name('live_data.store');
            Route::get('/data', [LiveDataController::class, 'data'])->name('live_data.data');
            Route::get('/chart', [LiveDataController::class, 'chartData'])->name('live_data.chart');
            Route::get('/{liveData}', [LiveDataController::class, 'show'])->name('live_data.show');
            Route::get('/{liveData}/edit', [LiveDataController::class, 'edit'])->name('live_data.edit');
            Route::put('/{liveData}', [LiveDataController::class, 'update'])->name('live_data.update');
            Route::delete('/{liveData}', [LiveDataController::class, 'destroy'])->name('live_data.destroy');
        });

        Route::prefix('live_shopee')->group(function () {
            Route::get('/', [LiveShopeeController::class, 'index'])->name('live_shopee.index');
            Route::get('/get-data', [LiveShopeeController::class, 'get_live_shopee'])->name('live_shopee.get_data');
            Route::get('/get-details-by-date', [LiveShopeeController::class, 'get_live_shopee_details_by_date'])->name('live_shopee.get_details_by_date');
            Route::get('/line-data', [LiveShopeeController::class, 'get_line_data'])->name('live_shopee.line_data');
            Route::get('/funnel-data', [LiveShopeeController::class, 'get_funnel_data'])->name('live_shopee.funnel_data');
            Route::post('/import', [LiveShopeeController::class, 'import_live_shopee'])->name('live_shopee.import');
            Route::delete('/delete', [LiveShopeeController::class, 'delete_live_shopee'])->name('live_shopee.delete');
        });

        Route::prefix('live_shopee_product')->group(function () {
            Route::get('/', [LiveShopeeProductController::class, 'index'])->name('live_shopee_product.index');
            Route::get('/get-data', [LiveShopeeProductController::class, 'get_live_shopee_product'])->name('live_shopee_product.get_data');
            Route::get('/get-details-by-date', [LiveShopeeProductController::class, 'get_live_shopee_product_details_by_date'])->name('live_shopee_product.get_details_by_date');
            Route::get('/line-data', [LiveShopeeProductController::class, 'get_line_data'])->name('live_shopee_product.line_data');
            Route::get('/funnel-data', [LiveShopeeProductController::class, 'get_funnel_data'])->name('live_shopee_product.funnel_data');
            Route::post('/import', [LiveShopeeProductController::class, 'import_live_shopee_product'])->name('live_shopee_product.import');
            Route::delete('/delete', [LiveShopeeProductController::class, 'delete_live_shopee_product'])->name('live_shopee_product.delete');
        });

        Route::prefix('live_tiktok')->group(function () {
            Route::get('/', [LiveTiktokController::class, 'index'])->name('live_tiktok.index');
            Route::get('/get-data', [LiveTiktokController::class, 'get_live_tiktok'])->name('live_tiktok.get_data');
            Route::get('/line-data', [LiveTiktokController::class, 'get_line_data'])->name('live_tiktok.line_data');
            Route::get('/funnel-data', [LiveTiktokController::class, 'get_funnel_data'])->name('live_tiktok.funnel_data');
            Route::post('/', [LiveTiktokController::class, 'store'])->name('live_tiktok.store');
            Route::get('/{id}/edit', [LiveTiktokController::class, 'edit'])->name('live_tiktok.edit');
            Route::put('/{id}', [LiveTiktokController::class, 'update'])->name('live_tiktok.update');
            Route::delete('/{id}', [LiveTiktokController::class, 'destroy'])->name('live_tiktok.destroy');
        });
        
        Route::prefix('products')
            ->group(function () {
                Route::get('/', [CampaignContentController::class, 'showDistinctProducts'])->name('campaignContent.showDistinctProducts');
                Route::get('/get', [CampaignContentController::class, 'getProductDataTable'])->name('campaignContent.getProduct');
                Route::get('/{productName}', [CampaignContentController::class, 'showProductDetails'])->name('campaignContent.showProductDetails');
                Route::get('/{productName}/statistics', [CampaignContentController::class, 'getProductStatistics'])->name('campaignContent.getProductStatistics');
            });

        Route::prefix('campaignContent')
            ->group(function () {
                Route::get('/getDataTable/{campaignId}', [CampaignContentController::class, 'getCampaignContentDataTable'])
                    ->name('campaignContent.getDataTable');
                Route::get('/select/{campaignId}', [CampaignContentController::class, 'selectApprovedInfluencer'])
                    ->name('campaignContent.select');
                Route::get('/update-shopee-video-links', [CampaignContentController::class, 'updateAllShopeeVideoLinks']);
                Route::get('/getJson/{campaignId}', [CampaignContentController::class, 'getCampaignContentJson'])
                    ->name('campaignContent.getJson');
                Route::post('/store/{campaignId}', [CampaignContentController::class, 'store'])
                    ->name('campaignContent.store');
                Route::get('/getDataTableForRefresh/{campaignId}', [CampaignContentController::class, 'getCampaignContentDataTableForRefresh'])
                    ->name('campaignContent.getDataTableForRefresh');                
                Route::put('/update/{campaignContent}', [CampaignContentController::class, 'update'])
                    ->name('campaignContent.update');
                Route::get('/update/fyp/{campaignContent}', [CampaignContentController::class, 'updateFyp'])
                    ->name('campaignContent.update.fyp');
                Route::get('/update/deliver/{campaignContent}', [CampaignContentController::class, 'updateDeliver'])
                    ->name('campaignContent.update.deliver');
                Route::get('/payment/deliver/{campaignContent}', [CampaignContentController::class, 'updatePayment'])
                    ->name('campaignContent.update.payment');
                Route::get('/export/{campaign}', [CampaignContentController::class, 'export'])
                    ->name('campaignContent.export');
                Route::get('/downloadTemplate', [CampaignContentController::class, 'downloadTemplate'])
                    ->name('campaignContent.template');
                Route::get('/downloadTemplateKOL', [CampaignContentController::class, 'downloadTemplateKOL'])
                    ->name('campaignContent.template_kol');
                Route::post('/import/{campaign}', [CampaignContentController::class, 'import'])
                    ->name('campaignContent.import');
                Route::post('/import_kol/{campaign}', [CampaignContentController::class, 'import_from_KOL'])
                    ->name('campaignContent.import_kol');
                Route::delete('/{campaignContent}', [CampaignContentController::class, 'destroy'])
                    ->name('campaignContent.destroy');
                    
            });

        Route::prefix('kol')
            ->group(function () {
                Route::get('/', [KeyOpinionLeaderController::class, 'index'])->name('kol.index');
                Route::get('/monitor', [KeyOpinionLeaderController::class, 'monitor_index'])->name('kol.monitor');
                Route::get('/monitor_get', [KeyOpinionLeaderController::class, 'monitor_get'])->name('kol.monitor_get');
                Route::get('/monitor_kpi', [KeyOpinionLeaderController::class, 'monitor_kpi'])->name('kol.monitor_kpi');
                Route::get('/get', [KeyOpinionLeaderController::class, 'get'])->name('kol.get');
                Route::get('/chart', [KeyOpinionLeaderController::class, 'chart'])->name('kol.chart');
                Route::get('/average-rate', [KeyOpinionLeaderController::class, 'averageRate'])->name('kol.averageRate');
                Route::get('/select', [KeyOpinionLeaderController::class, 'select'])->name('kol.select');
                Route::get('/refreshFollowersFollowing/{username}', [KeyOpinionLeaderController::class, 'refreshFollowersFollowing'])->name('keyOpinionLeader.refreshFollowersFollowing');
                Route::get('/{keyOpinionLeader}/edit-data', [KeyOpinionLeaderController::class, 'getEditData'])->name('kol.edit-data');
		        Route::get('{username}/refresh_follow', [KeyOpinionLeaderController::class, 'refreshFollowersFollowingSingle'])->name('kol.refresh_follow');
                Route::get('/create', [KeyOpinionLeaderController::class, 'create'])->name('kol.create');
                Route::get('/create-excel', [KeyOpinionLeaderController::class, 'createExcelForm'])->name('kol.create-excel');
                Route::get('/export', [KeyOpinionLeaderController::class, 'export'])->name('kol.export');
                Route::get('/bulk-usernames', [KeyOpinionLeaderController::class, 'getBulkUsernames'])->name('kol.bulk-usernames');
                Route::get('/import', [KeyOpinionLeaderController::class, 'importKeyOpinionLeaders'])->name('kol.import');
                Route::post('/store', [KeyOpinionLeaderController::class, 'store'])->name('kol.store');
                Route::post('/storeExcel', [KeyOpinionLeaderController::class, 'storeExcel'])->name('kol.store-excel');
                Route::get('/showJson/{keyOpinionLeader}', [KeyOpinionLeaderController::class, 'showJson'])->name('kol.show.json');
                Route::get('/{keyOpinionLeader}/edit', [KeyOpinionLeaderController::class, 'edit'])->name('kol.edit');
                Route::put('/{keyOpinionLeader}/update', [KeyOpinionLeaderController::class, 'update'])->name('kol.update');
                Route::get('/{keyOpinionLeader}/get-level-data', [KeyOpinionLeaderController::class, 'getLevelData'])->name('kol.get-level-data');
                Route::post('/{keyOpinionLeader}/update-level', [KeyOpinionLeaderController::class, 'updateLevel'])->name('kol.update-level');
                Route::get('/kpi', [KeyOpinionLeaderController::class, 'getKpiData'])->name('kol.kpi');
                Route::get('/{keyOpinionLeader}/show', [KeyOpinionLeaderController::class, 'show'])->name('kol.show');
            });

        Route::prefix('offer')
            ->group(function () {
                Route::get('/', [OfferController::class, 'index'])->name('offer.index');
                Route::get('/get', [OfferController::class, 'getOfferDataTable'])->name('offer.get');
                Route::get('/getByCampaignId/{campaignId}', [OfferController::class, 'getByCampaignId'])->name('offer.getByCampaignId');
                Route::post('/store/{campaignId}', [OfferController::class, 'store'])->name('offer.store');
                Route::put('/update/{offer}', [OfferController::class, 'update'])->name('offer.update');
                Route::put('/updateStatus/{offer}', [OfferController::class, 'updateStatus'])->name('offer.updateStatus');
                Route::put('/reviewOffering/{offer}', [OfferController::class, 'reviewOffering'])->name('offer.reviewOffering');
                Route::put('/financeOffer/{offer}', [OfferController::class, 'financeOffering'])->name('offer.financeOffering');

                // Chat Proof
                Route::post('/uploadChatProof/{offer}', [OfferController::class, 'uploadChatProof'])->name('offer.uploadChatProof');
                Route::get('/previewChatProof/{mediaId}/{filename}', [OfferController::class, 'previewChatProof'])->name('offer.previewCharProof');
                Route::delete('/deleteChatProof/{offer}/{media}', [OfferController::class, 'deleteChatProof'])->name('offer.deleteChatProof');

                // Transfer Proof
                Route::post('/uploadTransferProof/{offer}', [OfferController::class, 'uploadTransferProof'])->name('offer.uploadTransferProof');
                Route::delete('/deleteTransferProof/{offer}/{media}', [OfferController::class, 'deleteTransferProof'])->name('offer.deleteTransferProof');

                Route::get('/{offer}/show', [OfferController::class, 'show'])->name('offer.show');
                Route::get('/{campaign}/export', [OfferController::class, 'export'])->name('offer.export');
            });

            Route::prefix('budgets')->group(function () {
                Route::get('/', [BudgetController::class, 'index'])->name('budgets.index');
                Route::get('/create', [BudgetController::class, 'create'])->name('budgets.create');
                Route::get('/{id}/campaigns', [BudgetController::class, 'showCampaigns'])->name('budgets.showCampaigns');
                Route::post('/store', [BudgetController::class, 'store'])->name('budgets.store');
                Route::get('/edit/{id}', [BudgetController::class, 'edit'])->name('budgets.edit');
                Route::put('/update/{id}', [BudgetController::class, 'update'])->name('budgets.update');
                Route::delete('/destroy/{id}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
                Route::get('/data', [BudgetController::class, 'show'])->name('budgets.data');
            });

        Route::prefix('statistic')
            ->group(function () {
                Route::get('/refresh/{campaignContent}', [StatisticController::class, 'refresh'])
                    ->name('statistic.refresh');

                Route::get('/bulkRefresh/{campaign}', [StatisticController::class, 'bulkRefresh'])
                    ->name('statistic.bulkRefresh');

                Route::get('/card/{campaignId}', [StatisticController::class, 'card'])
                    ->name('statistic.card');

                Route::get('/chart/{campaignId}', [StatisticController::class, 'chart'])
                    ->name('statistic.chart');
                Route::get('/chart-detail/{campaignContentId}', [StatisticController::class, 'chartDetailContent'])
                    ->name('statistic.chartDetail');

                Route::post('/{campaignContent}', [StatisticController::class, 'store'])
                    ->name('statistic.store');

            });
    });

Route::get('/sign-kol', [OfferController::class, 'signKOL'])->name('sign.kol');
Route::post('/sign-store/{offer}', [OfferController::class, 'postSignKOL'])->name('sign.store');
Route::get('/preview-sign/{offer}', [OfferController::class, 'previewSign'])->name('sign.preview');
