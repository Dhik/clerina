<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\Offer\OfferBLLInterface;
use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Exports\KeyOpinionLeaderExport;
use App\Domain\Campaign\Exports\OfferExport;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\Offer;
use App\Domain\Campaign\Requests\ChatProofRequest;
use App\Domain\Campaign\Requests\FinanceOfferRequest;
use App\Domain\Campaign\Requests\OfferRequest;
use App\Domain\Campaign\Requests\OfferStatusRequest;
use App\Domain\Campaign\Requests\OfferUpdateRequest;
use App\Domain\Campaign\Requests\ReviewOfferRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class OfferController extends Controller
{
    public function __construct(protected OfferBLLInterface $offerBLL) {}

    /**
     * Return offer datatable
     * @throws Exception
     */
    public function getOfferDataTable(Request $request): JsonResponse
    {
        $this->authorize('viewOffer', Offer::class);

        $query = $this->offerBLL->getOfferDataTable($request);

        return DataTables::of($query)
            ->addColumn('campaign_title', function ($row) {
                return '<a href="' . route("campaign.show", $row->campaign->id) . '" target="_blank">' .
                    $row->campaign->title
                    . '</a>';
            })
            ->addColumn('created_by_name', function ($row) {
                return '<a href="' . route("users.show", $row->createdBy->id) . '" target="_blank">' .
                    $row->createdBy->name
                    . '</a>';
            })
            ->addColumn('key_opinion_leader_username', function ($row) {
                return '<a href="' . route("kol.show", $row->keyOpinionLeader->id) . '" target="_blank">' .
                    $row->keyOpinionLeader->username
                    . '</a>';
            })
            ->addColumn('key_opinion_leader_cpm', function ($row) {
                return number_format($row->keyOpinionLeader->cpm, '2', ',', '.');
            })
            ->addColumn('key_opinion_leader_average_view', function ($row) {
                return number_format($row->keyOpinionLeader->average_view, '0', ',', '.');
            })
            ->addColumn('rate_formatted', function ($row) {
                return number_format($row->rate_per_slot, '0', ',', '.');
            })
            ->addColumn('status_label', function ($row) {
                return $this->statusLabel($row);
            })
            ->addColumn('actions', function ($row) {
                return $this->actionsHtml($row);
            })
            ->rawColumns(['actions', 'created_by_name', 'key_opinion_leader_username', 'status_label', 'campaign_title'])
            ->toJson();
    }

    /**
     * Get offer by campaign id for datatable
     * @throws Exception
     */
    public function getByCampaignId(int $campaignId, Request $request): JsonResponse
    {
        $this->authorize('viewOffer', Offer::class);

        $query = $this->offerBLL->getOfferByCampaignId($campaignId, $request);

        return DataTables::of($query)
            ->addColumn('created_by_name', function ($row) {
                return '<a href="' . route("users.show", $row->createdBy->id) . '" target="_blank">' .
                    $row->createdBy->name
                    . '</a>';
            })
            ->addColumn('key_opinion_leader_username', function ($row) {
                return '<a href="' . route("kol.show", $row->keyOpinionLeader->id) . '" target="_blank">' .
                    $row->keyOpinionLeader->username
                    . '</a>';
            })
            ->addColumn('key_opinion_leader_cpm', function ($row) {
                return number_format($row->keyOpinionLeader->cpm, '2', ',', '.');
            })
            ->addColumn('key_opinion_leader_average_view', function ($row) {
                return number_format($row->keyOpinionLeader->average_view, '0', ',', '.');
            })
            ->addColumn('rate_formatted', function ($row) {
                return number_format($row->rate_per_slot, '0', ',', '.');
            })
            ->addColumn('status_label', function ($row) {
                return $this->statusLabel($row);
            })
            ->addColumn('actions', function ($row) {
                return $this->actionsHtml($row);
            })
            ->rawColumns(['actions', 'created_by_name', 'key_opinion_leader_username', 'status_label'])
            ->make();
    }

    protected function actionsHtml($row): string
    {
        $actionsHtml = '
                        <div class="btn-group">
                            <a href=' . route("offer.show", $row->id) . ' type="button" class="btn btn-info btn-sm" target="_blank">' . trans("labels.detail") . '</a>
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu" role="menu" style="">';

        if (Gate::allows('updateOffer', $row)) {
            $actionsHtml .= '
                <button class="dropdown-item btnUpdateOffer">' . trans("labels.update") . '</button>';
        }

        if (Gate::allows('approveRejectOffer', $row)) {
            $actionsHtml .= '
                <button class="dropdown-item btnUpdateStatus">' . trans("labels.approve_reject") . '</button>';
        }

        // Check if the status is "approved" to show the "Review Offering" button
        if ($row->status === 'approved' && Gate::allows('reviewOffer', $row)) {
            $actionsHtml .= '
                <button class="dropdown-item btnReviewOffer">' . trans("labels.review") . ' ' . trans("labels.offering") . '</button>';
        }


        $actionsHtml .= '
            <a class="dropdown-item" href="' . route('offer.show', $row->id) . '?tab=sign" target="_blank">' . trans("labels.view_sign") . '</a>';


        if (Gate::allows('financeOffer', $row)) {
            $actionsHtml .= '
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="' . route('offer.show', $row->id) . '?tab=finance" target="_blank">' . trans('labels.finance') . '</a>';
        }

        $actionsHtml .= '
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">' . trans('labels.delete') . '</a>
                    </div>
                </div>';

        return $actionsHtml;
    }

    /**
     * Return status label
     */
    protected function statusLabel($row): string
    {
        if ($row->status === OfferEnum::Approved) {
            return '<span class="badge bg-success">' . ucfirst($row->status) . '</span>';
        }

        if ($row->status === OfferEnum::Rejected) {
            return '<span class="badge bg-danger">' . ucfirst($row->status) . '</span>';
        }

        return '<span class="badge bg-warning">' . ucfirst($row->status) . '</span>';
    }

    /**
     * Return index page for offer
     */
    public function index(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewOffer', Offer::class);

        $negotiates = OfferEnum::Negotiation;
        $statuses = OfferEnum::Status;
        return view('admin.offer.index', compact('negotiates', 'statuses'));
    }

    /**
     * Store new Offer
     */
    public function store(int $campaignId, OfferRequest $request): JsonResponse
    {
        $this->authorize('createOffer', Offer::class);

        return response()->json($this->offerBLL->storeOffer($campaignId, $request));
    }

    /**
     * Update offer
     */
    public function update(Offer $offer, OfferUpdateRequest $request): JsonResponse
    {
        $this->authorize('updateOffer', $offer);

        $this->authorize('updateOffer', $offer);

        return response()->json($this->offerBLL->updateOffer($offer, $request));
    }

    /**
     * Update status offer
     */
    public function updateStatus(Offer $offer, OfferStatusRequest $request): JsonResponse
    {
        $this->authorize('approveRejectOffer', Offer::class);

        return response()->json($this->offerBLL->updateStatusOffer($offer, $request));
    }

    /**
     * Review offer
     */
    public function reviewOffering(Offer $offer, ReviewOfferRequest $request): JsonResponse
    {
        $this->authorize('reviewOffer', $offer);

        return response()->json($this->offerBLL->reviewOffering($offer, $request));
    }

    /**
     * Update Finance
     */
    public function financeOffering(Offer $offer, FinanceOfferRequest $request): JsonResponse
    {
        $this->authorize('financeOffer', Offer::class);

        return response()->json($this->offerBLL->updateFinanceOffer($offer, $request));
    }

    /**
     * show detail offer
     */
    public function show(Offer $offer): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewOffer', Offer::class);

        $negotiates = OfferEnum::Negotiation;
        $statuses = OfferEnum::Status;
        $transferStatuses = OfferEnum::TransferStatus;
        $offer = $offer->load('createdBy', 'approvedBy');
        return view('admin.offer.show', compact('offer', 'negotiates', 'statuses', 'transferStatuses'));
    }

    public function delete() {}

    /**
     * View sign form for KOL
     */
    public function signKOL(Request $request): View|ApplicationAlias|Factory|Application
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        return view('admin.offer.sign', ['offerId' => $request->query('offerId')]);
    }

    /**
     * Post sign
     */
    public function postSignKOL(int $offer, Request $request): View|ApplicationAlias|Factory|Application
    {
        $offer = Offer::withoutGlobalScopes()->where('id', $offer)->firstOrfail();
        $this->offerBLL->storeSign($offer, $request);

        return view('admin.offer.sign-success');
    }

    /**
     * Provide link private file
     */
    public function previewSign(Offer $offer): BinaryFileResponse
    {
        $media = $offer->getFirstMedia('sign');

        if (is_null($media)) {
            abort(404);
        }

        $pathToFile = Storage::disk('private')->path($media->id . '/' . $media->file_name);

        return response()->file($pathToFile);
    }

    /**
     * Upload Chat proof
     */
    public function uploadChatProof(Offer $offer, ChatProofRequest $request): RedirectResponse
    {
        $this->authorize('updateOffer', $offer);

        $this->offerBLL->storeChatProof($offer, $request);

        return redirect()->route('offer.show', ['offer' => $offer, 'tab' => 'chat-proof'])->with([
            'alert' => 'success',
            'message' => trans('messages.success_upload', ['model' => trans('labels.chat_proof')]),
        ]);
    }

    /**
     * Provide link private file
     */
    public function previewChatProof(int $mediaId, string $filename): BinaryFileResponse
    {
        $pathToFile = Storage::disk('private')->path($mediaId . '/' . $filename);
        return response()->file($pathToFile);
    }

    /**
     * Delete Media
     */
    public function deleteChatProof(Offer $offer, Media $media): RedirectResponse
    {
        $this->authorize('updateOffer', $offer);

        $media->delete();

        return redirect()->route('offer.show', ['offer' => $offer, 'tab' => 'chat-proof'])->with([
            'alert' => 'success',
            'message' => trans('messages.success_delete', ['model' => trans('labels.chat_proof')]),
        ]);
    }

    /**
     * Upload Transfer proof
     */
    public function uploadTransferProof(Offer $offer, ChatProofRequest $request): RedirectResponse
    {
        $this->authorize('financeOffer', Offer::class);

        $this->offerBLL->storeTransferProof($offer, $request);

        return redirect()->route('offer.show', ['offer' => $offer, 'tab' => 'finance'])->with([
            'alert' => 'success',
            'message' => trans('messages.success_upload', ['model' => trans('labels.transfer_proof')]),
        ]);
    }

    /**
     * Delete Media
     */
    public function deleteTransferProof(Offer $offer, Media $media): RedirectResponse
    {
        $this->authorize('financeOffer', Offer::class);

        $media->delete();

        return redirect()->route('offer.show', ['offer' => $offer, 'tab' => 'finance'])->with([
            'alert' => 'success',
            'message' => trans('messages.success_delete', ['model' => trans('labels.transfer_proof')]),
        ]);
    }

    /**
     * Export Offer
     */
    public function export(Campaign $campaign): Response|BinaryFileResponse
    {
        $this->authorize('viewOffer', Offer::class);

        return (new OfferExport())
            ->forCampaign($campaign->id)
            ->download($campaign->title . ' offer.xlsx');
    }
}
