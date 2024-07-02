<?php

namespace App\Domain\Marketing\BLL\SocialMedia;

use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Marketing\Requests\SocialMediaRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;

interface SocialMediaBLLInterface extends BaseBLLInterface
{
    /**
     * Return social media for DataTable
     */
    public function getSocialMediaDataTable(): Builder;

    /**
     * Create new social media
     */
    public function storeSocialMedia(SocialMediaRequest $request): SocialMedia;

    /**
     * Update social media
     */
    public function updateSocialMedia(SocialMedia $socialMedia, SocialMediaRequest $request): SocialMedia;

    /**
     * Delete social media
     */
    public function deleteSocialMedia(SocialMedia $socialMedia): bool;

    /**
     * Return all social media
     */
    public function getSocialMedia(): mixed;
}
