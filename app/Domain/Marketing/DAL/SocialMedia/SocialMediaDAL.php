<?php

namespace App\Domain\Marketing\DAL\SocialMedia;

use App\Domain\Marketing\Enums\SocialMediaEnum;
use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Marketing\Requests\SocialMediaRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class SocialMediaDAL extends BaseDAL implements SocialMediaDALInterface
{
    public function __construct(SocialMedia $socialMedia)
    {
        $this->model = $socialMedia;
    }

    /**
     * Return social media for DataTable
     */
    public function getSocialMediaDataTable(): Builder
    {
        return $this->model->query();
    }

    /**
     * Create new social media
     */
    public function storeSocialMedia(SocialMediaRequest $request): SocialMedia
    {
        $this->forgetCache();

        return $this->model->create($request->only('name'));
    }

    /**
     * Update social media
     */
    public function updateSocialMedia(SocialMedia $socialMedia, SocialMediaRequest $request): SocialMedia
    {
        $socialMedia->name = $request->name;
        $socialMedia->update();

        $this->forgetCache();

        return $socialMedia;
    }

    /**
     * Delete social media
     */
    public function deleteSocialMedia(SocialMedia $socialMedia): void
    {
        $socialMedia->delete();
        $this->forgetCache();
    }

    /**
     * Return all social media
     */
    public function getSocialMedia(): mixed
    {
        return Cache::rememberForever(SocialMediaEnum::AllSocialMediaCacheTag, function () {
            return SocialMedia::orderBy('name')->get();
        });
    }

    /**
     * Forget cache
     */
    protected function forgetCache(): void
    {
        Cache::forget(SocialMediaEnum::AllSocialMediaCacheTag);
    }
}
