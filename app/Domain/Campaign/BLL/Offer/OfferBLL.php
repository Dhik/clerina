<?php

namespace App\Domain\Campaign\BLL\Offer;

use App\Domain\Campaign\DAL\Offer\OfferDALInterface;
use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Models\Offer;
use App\Domain\Campaign\Requests\ChatProofRequest;
use App\Domain\Campaign\Requests\FinanceOfferRequest;
use App\Domain\Campaign\Requests\OfferRequest;
use App\Domain\Campaign\Requests\OfferStatusRequest;
use App\Domain\Campaign\Requests\OfferUpdateRequest;
use App\Domain\Campaign\Requests\ReviewOfferRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Yajra\DataTables\Utilities\Request;

class OfferBLL implements OfferBLLInterface
{
    public function __construct(protected OfferDALInterface $offerDAL)
    {
    }

    /**
     * Get offer datatable
     */
    public function getOfferDataTable(Request $request): Builder
    {
        $query = $this->offerDAL->getOfferDataTable();

        if (!empty($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }

    /**
     * Get offer by campaign id
     */
    public function getOfferByCampaignId(int $campaignId, Request $request): Builder
    {
        $query = $this->offerDAL->getOfferByCampaignId($campaignId);

        if (!empty($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }

    /**
     * Store new Offer
     * @throws Exception
     */
    public function storeOffer(int $campaignId, OfferRequest $request): Offer
    {
        $data = [
            'key_opinion_leader_id' => $request->input('key_opinion_leader_id'),
            'rate_per_slot' => $request->input('rate_per_slot'),
            'benefit' => $request->input('benefit'),
            'negotiate' => $request->input('negotiate'),
            'campaign_id' => $campaignId,
            'status' => OfferEnum::Pending,
            'created_by' => Auth::user()->id,
            'bank_name' => $request->input('bank_name'),
            'bank_account' => $request->input('bank_account'),
            'bank_account_name' => $request->input('bank_account_name'),
            'nik' => $request->input('nik')
        ];

        try {
            DB::beginTransaction();
            $offer = $this->offerDAL->storeOffer($data);
            $this->generateSignUrl($offer);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }

        return $offer;
    }

    /**
     * Update Offer
     */
    public function updateOffer(Offer $offer, OfferUpdateRequest $request): Offer
    {
        $data = [
            'benefit' => $request->input('benefit'),
            'negotiate' => $request->input('negotiate'),
            'bank_name' => $request->input('bank_name'),
            'bank_account' => $request->input('bank_account'),
            'bank_account_name' => $request->input('bank_account_name'),
            'nik' => $request->input('nik')
        ];

        return $this->offerDAL->updateOffer($offer, $data);
    }

    /**
     * Update Status Offer
     */
    public function updateStatusOffer(Offer $offer, OfferStatusRequest $request): Offer
    {
        $status = $request->input('status');
        $accSlot = $request->input('acc_slot');
        $rateTotalSlot = $offer->rate_per_slot * $accSlot;

        $data = [
            'status' => $request->input('status'),
            'approved_by'=> Auth::user()->id,
            'approved_at' => Carbon::now()
        ];

        if ($status === OfferEnum::Approved) {
            $data['acc_slot'] = $accSlot;
            $data['rate_total_slot'] = $rateTotalSlot;
            $data['rate_final_slot'] = $rateTotalSlot;
        }

        return $this->offerDAL->updateOffer($offer, $data);
    }

    /**
     * Review offering
     */
    public function reviewOffering(Offer $offer, ReviewOfferRequest $request): Offer
    {
        $rateFinalSlot = $request->input('rate_final_slot');
        $discount = $offer->rate_total_slot - $rateFinalSlot;
        $rateTax = $rateFinalSlot * 2.5 / 100;
        $finalAmount = $rateFinalSlot - $rateTax;

        $data = [
            'rate_final_slot' => $rateFinalSlot,
            'discount' => $discount,
            'pph' => $rateTax,
            'final_amount' => $finalAmount,
            'npwp' => $request->npwp ? 1 : 0
        ];

        return $this->offerDAL->updateOffer($offer, $data);
    }

    /**
     * Generate sign url and update to the offer
     */
    protected function generateSignUrl(Offer $offer): void
    {
        $data['sign_url'] = URL::signedRoute('sign.kol', ['offerId' => $offer->id]);
        $this->offerDAL->updateOffer($offer, $data);
    }

    /**
     * Store sign
     */
    public function storeSign(Offer $offer, Request $request): void
    {
        $this->offerDAL->updateOffer($offer, ['signed' => true, 'signed_at' => Carbon::now()]);
        $offer->clearMediaCollection('sign');
        $offer->addMediaFromBase64($request->input('signed'))
            ->toMediaCollection('sign', 'private');
    }

    /**
     * Upload chat proof
     */
    public function storeChatProof(Offer $offer, ChatProofRequest $request): void
    {
        if ($request->hasFile('images')) {
            $offer->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('chatProof', 'private');
                });
        }
    }

    /**
     * Update finance offer
     */
    public function updateFinanceOffer(Offer $offer, FinanceOfferRequest $request): Offer
    {
        $data = [
            'transfer_status' => $request->input('transfer_status'),
            'financed_by' => Auth::user()->id,
            'transfer_date' => null
        ];

        if ($request->input('transfer_status') === OfferEnum::Paid) {
            $data['transfer_date'] = Carbon::createFromFormat('d/m/Y', $request->input('transfer_date'));
        }

        return $this->offerDAL->updateOffer($offer, $data);
    }

    /**
     * Upload chat proof
     */
    public function storeTransferProof(Offer $offer, ChatProofRequest $request): void
    {
        if ($request->hasFile('images')) {
            $offer->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('transferProof', 'private');
                });
        }
    }
}
