<?php

namespace Database\Seeders;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $userPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateUser]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateUser]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewUser]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteUser]),
        ];

        $marketingCategoryPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateMarketingCategory]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateMarketingCategory]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewMarketingCategory]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteMarketingCategory]),
        ];

        $salesChannelPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateSalesChannel]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateSalesChannel]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewSalesChannel]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteSalesChannel]),
        ];

        $socialMediaPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateSocialMedia]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateSocialMedia]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewSocialMedia]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteSocialMedia]),
        ];

        $marketingPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateMarketing]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateMarketing]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewMarketing]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteMarketing]),
        ];

        $salesPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateSales]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateSales]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewSales]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteSales]),
        ];

        $orderPermissions = [
            Permission::updateOrCreate(['name' => PermissionEnum::CreateOrder]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateOrder]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewOrder]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteOrder]),
        ];

        $adSpentMarketPlace = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewAdSpentMarketPlace]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateAdSpentMarketPlace]),
        ];

        $adSpentSocialMedia = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewAdSpentSocialMedia]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateAdSpentSocialMedia]),
        ];

        $visit = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewVisit]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateVisit]),
        ];

        $profile = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewProfile]),
            Permission::updateOrCreate(['name' => PermissionEnum::ChangeOwnPassword]),
        ];

        $tenant = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewTenant]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateTenant]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateTenant]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteTenant]),
            Permission::updateOrCreate(['name' => PermissionEnum::AssignTenantUser]),
        ];

        $funnel = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewFunnel]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateFunnel]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteFunnel]),
        ];

        $customer = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewCustomer]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateCustomerNote]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateCustomerNote]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteCustomerNote]),
        ];

        $campaign = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteCampaign])
        ];

        $campaignContent = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewCampaignContent]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateCampaignContent]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateCampaignContent]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteCampaignContent])
        ];

        $offerMarketing = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewOffer]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateOffer]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateOffer]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteOffer]),
            Permission::updateOrCreate(['name' => PermissionEnum::ReviewOffer]),
        ];

        $offerAdmin = [
            Permission::updateOrCreate(['name' => PermissionEnum::ApproveRejectOffer]),
            Permission::updateOrCreate(['name' => PermissionEnum::FinanceOffer]),
        ];

        $campaignFinance = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::ViewCampaignContent]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateCampaignContent]),
        ];

        $offerFinance = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewOffer]),
            Permission::updateOrCreate(['name' => PermissionEnum::FinanceOffer]),
        ];

        $campaignInfluencer = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewInfluencerCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateInfluencerCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateInfluencerCampaign]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteInfluencerCampaign])
        ];

        $kol = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewKOL]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateKOL]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateKOL]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteKOL]),
        ];

        $contest = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewContest]),
            Permission::updateOrCreate(['name' => PermissionEnum::CreateContest]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateContest]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteContest]),
        ];

        $employee = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewEmployee]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateEmployee]),
        ];

        $attendance = [
            Permission::updateOrCreate(['name' => PermissionEnum::ViewAttendance]),
            Permission::updateOrCreate(['name' => PermissionEnum::UpdateAttendance]),
            Permission::updateOrCreate(['name' => PermissionEnum::DeleteAttendance]),
        ];

        $attendance_app = [
            Permission::updateOrCreate(['name' => PermissionEnum::AccessAttendance]),
        ];

        $keywordMonitoring = [
            Permission::updateOrCreate(['name' => PermissionEnum::AccessKeyword]),
        ];

        $superadmin = Role::findByName(RoleEnum::SuperAdmin);
        $superadmin->givePermissionTo($userPermissions);
        $superadmin->givePermissionTo($marketingCategoryPermissions);
        $superadmin->givePermissionTo($salesChannelPermissions);
        $superadmin->givePermissionTo($socialMediaPermissions);
        $superadmin->givePermissionTo($marketingPermissions);
        $superadmin->givePermissionTo($salesPermissions);
        $superadmin->givePermissionTo($orderPermissions);
        $superadmin->givePermissionTo($adSpentMarketPlace);
        $superadmin->givePermissionTo($adSpentSocialMedia);
        $superadmin->givePermissionTo($visit);
        $superadmin->givePermissionTo($profile);
        $superadmin->givePermissionTo($tenant);
        $superadmin->givePermissionTo($funnel);
        $superadmin->givePermissionTo($customer);
        $superadmin->givePermissionTo($campaign);
        $superadmin->givePermissionTo($campaignContent);
        $superadmin->givePermissionTo($campaignInfluencer);
        $superadmin->givePermissionTo($kol);
        $superadmin->givePermissionTo($offerAdmin);
        $superadmin->givePermissionTo($offerMarketing);
        $superadmin->givePermissionTo($offerFinance);
        $superadmin->givePermissionTo($contest);
        $superadmin->givePermissionTo($attendance_app);

        $brandManager = Role::findByName(RoleEnum::BrandManager);
        $brandManager->givePermissionTo($userPermissions);
        $brandManager->givePermissionTo($marketingPermissions);
        $brandManager->givePermissionTo($salesPermissions);
        $brandManager->givePermissionTo($orderPermissions);
        $brandManager->givePermissionTo($adSpentMarketPlace);
        $brandManager->givePermissionTo($adSpentSocialMedia);
        $brandManager->givePermissionTo($visit);
        $brandManager->givePermissionTo($profile);
        $brandManager->givePermissionTo($funnel);
        $brandManager->givePermissionTo($customer);
        $brandManager->givePermissionTo($campaign);
        $brandManager->givePermissionTo($campaignContent);
        $brandManager->givePermissionTo($campaignInfluencer);
        $brandManager->givePermissionTo($kol);
        $brandManager->givePermissionTo($offerAdmin);
        $brandManager->givePermissionTo($offerMarketing);
        $brandManager->givePermissionTo($offerFinance);
        $brandManager->givePermissionTo($contest);
        $brandManager->givePermissionTo($attendance_app);

        $marketing = Role::findByName(RoleEnum::Marketing);
        $marketing->givePermissionTo($marketingPermissions);
        $marketing->givePermissionTo($salesPermissions);
        $marketing->givePermissionTo($orderPermissions);
        $marketing->givePermissionTo($adSpentMarketPlace);
        $marketing->givePermissionTo($adSpentSocialMedia);
        $marketing->givePermissionTo($visit);
        $marketing->givePermissionTo($profile);
        $marketing->givePermissionTo($funnel);
        $marketing->givePermissionTo($customer);
        $marketing->givePermissionTo($campaign);
        $marketing->givePermissionTo($campaignContent);
        $marketing->givePermissionTo($campaignInfluencer);
        $marketing->givePermissionTo($kol);
        $marketing->givePermissionTo($offerMarketing);
        $marketing->givePermissionTo($contest);
        $marketing->givePermissionTo($attendance_app);
        $marketing->givePermissionTo($keywordMonitoring);

        $hr = Role::findByName(RoleEnum::HR);
        $hr->givePermissionTo($userPermissions);
        $hr->givePermissionTo($profile);
        $hr->givePermissionTo($employee);
        $hr->givePermissionTo($attendance);
        $hr->givePermissionTo($attendance_app);

        $finance = Role::findByName(RoleEnum::Finance);
        $finance->givePermissionTo($profile);
        $finance->givePermissionTo($offerFinance);
        $finance->givePermissionTo($campaignFinance);
        $finance->givePermissionTo($attendance_app);

        $staff = Role::findByName(RoleEnum::Staff);
        $staff->givePermissionTo($profile);
        $staff->givePermissionTo($attendance_app);
    }
}
