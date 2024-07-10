<?php

namespace App\Domain\User\Enums;

enum PermissionEnum
{
    // User
    const CreateUser = 'create user';

    const UpdateUser = 'update user';

    const ViewUser = 'view user';

    const DeleteUser = 'delete user';

    // Marketing Category
    const CreateMarketingCategory = 'create marketing category';

    const UpdateMarketingCategory = 'update marketing category';

    const ViewMarketingCategory = 'view marketing category';

    const DeleteMarketingCategory = 'delete marketing category';

    // Sales Channel
    const CreateSalesChannel = 'create sales channel';

    const UpdateSalesChannel = 'update sales channel';

    const ViewSalesChannel = 'view sales channel';

    const DeleteSalesChannel = 'delete sales channel';

    // Social media
    const CreateSocialMedia = 'create social media';

    const UpdateSocialMedia = 'update social media';

    const ViewSocialMedia = 'view social media';

    const DeleteSocialMedia = 'delete social media';

    // Marketing
    const CreateMarketing = 'create marketing';

    const UpdateMarketing = 'update marketing';

    const ViewMarketing = 'view marketing';

    const DeleteMarketing = 'delete marketing';

    // Sales
    const CreateSales = 'create sales';

    const UpdateSales = 'update sales';

    const ViewSales = 'view sales';

    const DeleteSales = 'delete sales';

    // Order
    const CreateOrder = 'create order';

    const UpdateOrder = 'update order';

    const ViewOrder = 'view order';

    const DeleteOrder = 'delete order';

    // AdSpent MarketPlace
    const ViewAdSpentMarketPlace = 'view ad spent marketplace';

    const CreateAdSpentMarketPlace = 'create ad spent marketplace';

    // AdSpent Social Media
    const ViewAdSpentSocialMedia = 'view ad spent social media';

    const CreateAdSpentSocialMedia = 'create and spent social media';

    // Visit
    const ViewVisit = 'view visit';

    const CreateVisit = 'create visit';

    // Profile
    const ViewProfile = 'view profile';

    const ChangeOwnPassword = 'change own password';

    // Tenant
    const ViewTenant = 'view tenant';

    const CreateTenant = 'create tenant';

    const UpdateTenant = 'update tenant';

    const DeleteTenant = 'delete tenant';

    const AssignTenantUser = 'assign tenant user';

    // FUNNEL
    const ViewFunnel = 'view funnel';

    const CreateFunnel = 'create funnel';

    const DeleteFunnel = 'delete funnel';

    // Customer
    const ViewCustomer = 'view customer';

    const CreateCustomerNote = 'view customer note';

    const UpdateCustomerNote = 'Edit customer note';

    const DeleteCustomerNote = 'Edit customer note';

    // Campaign
    const ViewCampaign = 'view campaign';
    const CreateCampaign = 'create campaign';
    const UpdateCampaign = 'update campaign';
    const DeleteCampaign = 'delete campaign';

    // Offer
    const ViewOffer = 'view offer';
    const CreateOffer = 'create offer';
    const UpdateOffer = 'update offer';
    const DeleteOffer = 'delete offer';
    const ApproveRejectOffer = 'approve reject offer';
    const ReviewOffer = 'review offer';
    const FinanceOffer = 'finance offer';

    // Influencer campaign
    const ViewInfluencerCampaign = 'view influencer campaign';
    const CreateInfluencerCampaign = 'create influencer campaign';
    const UpdateInfluencerCampaign = 'update influencer campaign';
    const DeleteInfluencerCampaign = 'delete influencer campaign';

    // KOL
    const ViewKOL = 'view kol';
    const CreateKOL = 'create kol';
    const UpdateKOL = 'update kol';
    const DeleteKOL = 'delete kol';

    // Campaign Content
    const ViewCampaignContent = 'view campaign content';
    const CreateCampaignContent = 'create campaign content';
    const UpdateCampaignContent = 'update campaign content';
    const DeleteCampaignContent = 'delete campaign content';

    // Contest
    const ViewContest = 'view contest';
    const CreateContest = 'create contest';
    const UpdateContest = 'update contest';
    const DeleteContest = 'delete contest';


    //Attendance
    const AccessAttendance = 'access attendance';
    const ViewAttendance = 'view attendance';
    const UpdateAttendance = 'update attendance';
    const DeleteAttendance = 'delete attendance';

    //Employee
    const ViewEmployee = 'view employee';
    const UpdateEmployee = 'update employee';

    //Keyword Monitoring
    const AccessKeyword = 'access keyword';
}
