<?php

namespace App\Domain\Marketing\Controllers;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Marketing\Requests\SocialMediaRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class SocialMediaController extends Controller
{
    public function __construct(public SocialMediaBLLInterface $socialMediaBLL)
    {
    }

    /**
     * @throws Exception
     */
    public function get(): JsonResponse
    {
        $this->authorize('viewAnySocialMedia', SocialMedia::class);

        $socialMediaQuery = $this->socialMediaBLL->getSocialMediaDataTable();

        return DataTables::of($socialMediaQuery)
            ->addColumn(
                'actions',
                '<button class="btn btn-primary btn-xs updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-danger btn-xs deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>'
            )
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Show index page social media
     */
    public function index(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewAnySocialMedia', SocialMedia::class);

        return view('admin.socialMedia.index');
    }

    /**
     * Create new social media
     */
    public function store(SocialMediaRequest $request): JsonResponse
    {
        $this->authorize('createSocialMedia', SocialMedia::class);

        $this->socialMediaBLL->storeSocialMedia($request);

        return response()->json($request->all());
    }

    /**
     * Update social media
     */
    public function update(SocialMedia $socialMedia, SocialMediaRequest $request): JsonResponse
    {
        $this->authorize('updateSocialMedia', SocialMedia::class);

        $this->socialMediaBLL->updateSocialMedia($socialMedia, $request);

        return response()->json($request->all());
    }

    /**
     * Delete social media
     */
    public function delete(SocialMedia $socialMedia): JsonResponse
    {
        $this->authorize('deleteSocialMedia', SocialMedia::class);

        $result = $this->socialMediaBLL->deleteSocialMedia($socialMedia);

        if (! $result) {
            return response()->json(['message' => trans('messages.social_media_failed_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }
}
