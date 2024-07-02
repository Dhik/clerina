<?php

namespace App\Domain\Marketing\BLL\SocialMedia;

use App\Domain\Marketing\DAL\SocialMedia\SocialMediaDALInterface;
use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Marketing\Requests\SocialMediaRequest;
use App\Domain\Sales\DAL\AdSpentSocialMedia\AdSpentSocialMediaDALInterface;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property SocialMediaDALInterface DAL
 */
class SocialMediaBLL extends BaseBLL implements SocialMediaBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(
        SocialMediaDALInterface $socialMediaDAL,
        protected AdSpentSocialMediaDALInterface $adSpentSocialMediaDAL
    ) {
        $this->dal = $socialMediaDAL;
    }

    /**
     * Return social media for DataTable
     */
    public function getSocialMediaDataTable(): Builder
    {
        return $this->dal->getSocialMediaDataTable();
    }

    /**
     * Create new social media
     */
    public function storeSocialMedia(SocialMediaRequest $request): SocialMedia
    {
        return $this->dal->storeSocialMedia($request);
    }

    /**
     * Update social media
     */
    public function updateSocialMedia(SocialMedia $socialMedia, SocialMediaRequest $request): SocialMedia
    {
        return $this->dal->updateSocialMedia($socialMedia, $request);
    }

    /**
     * Delete social media
     */
    public function deleteSocialMedia(SocialMedia $socialMedia): bool
    {
        $checkAdSpent = $this->adSpentSocialMediaDAL->checkAdSpentBySocialMedia($socialMedia->id);

        if (! empty($checkAdSpent)) {
            return false;
        }

        $this->dal->deleteSocialMedia($socialMedia);

        return true;
    }

    /**
     * Return all social media
     */
    public function getSocialMedia(): mixed
    {
        return $this->dal->getSocialMedia();
    }
}
