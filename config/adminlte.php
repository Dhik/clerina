<?php

use App\Domain\User\Enums\PermissionEnum as PermissionEnum;

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Cleora',
    'title_prefix' => '',
    'title_postfix' => '- Cleora',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'Clerina',
    'logo_img' => 'img/cleora-small.png',
    'logo_img_class' => 'brand-image elevation-3 squ',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'img/cleora-logo-auth.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 360,
            'height' => 40,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'header' => 'DASHBOARD',
            'can' => [
                PermissionEnum::ViewSales,
                PermissionEnum::ViewOrder,
                PermissionEnum::ViewMarketing,
                PermissionEnum::ViewCustomer,
            ],
        ],
        [
            'text' => 'Sales',
            'url' => 'admin/sales',
            'icon' => 'nav-icon far fa-circle text-info',
            'can' => [PermissionEnum::ViewSales],
        ],
        [
            'text' => 'Order',
            'url' => 'admin/order',
            'icon' => 'nav-icon far fa-circle text-info',
            'can' => [PermissionEnum::ViewOrder],
        ],
        // [
        //     'text' => 'Marketing',
        //     'url' => 'admin/marketing',
        //     'icon' => 'nav-icon far fa-circle text-info',
        //     'can' => [PermissionEnum::ViewMarketing],
        // ],
        [
            'text' => 'Customer',
            'url' => 'admin/cstmr_analysis',
            'icon' => 'nav-icon far fa-circle text-info',
            'can' => [PermissionEnum::ViewCustomer],
        ],
        [
            'text' => 'Product',
            'url' => 'admin/product',
            'icon' => 'nav-icon far fa-circle text-info',
            'can' => [PermissionEnum::ViewCustomer],
        ],

        [
            'text' => 'Report',
            'can' => [
                PermissionEnum::ViewAdSpentMarketPlace,
                PermissionEnum::ViewAdSpentSocialMedia,
                PermissionEnum::ViewVisit,
            ],
            'icon'    => 'fas fa-fw fa-book',
            'submenu' => [
                [
                    'text' => 'Main Report',
                    'url' => 'admin/report',
                    'can' => [PermissionEnum::ViewAdSpentMarketPlace],
                    'icon' => 'nav-icon far fa-circle',
                ],
                [
                    'text' => 'Spent Target',
                    'url' => 'admin/spentTarget',
                    'can' => [PermissionEnum::ViewAdSpentMarketPlace],
                    'icon' => 'nav-icon far fa-circle',
                ],
                [
                    'text' => 'Ad Spent Market Place',
                    'url' => 'admin/ad-spent-market-place',
                    'can' => [PermissionEnum::ViewAdSpentMarketPlace],
                    'icon' => 'nav-icon far fa-circle',
                ],
                [
                    'text' => 'Ad Spent Social Media',
                    'url' => 'admin/ad-spent-social-media',
                    'can' => [PermissionEnum::ViewAdSpentSocialMedia],
                    'icon' => 'nav-icon far fa-circle',
                ],
                [
                    'text' => 'Visit',
                    'url' => 'admin/visit',
                    'can' => [PermissionEnum::ViewVisit],
                    'icon' => 'nav-icon far fa-circle',
                ],
            ]
        ],
        [
            'text' => 'Campaign',
            'can' => [
                PermissionEnum::ViewCampaign,
                PermissionEnum::ViewOffer,
                PermissionEnum::ViewKOL
            ],
            'icon'    => 'fas fa-fw fa-map',
            'submenu' => [
                // [
                //     'text' => 'KOL/Influencer',
                //     'url' => 'admin/kol',
                //     'can' => [PermissionEnum::ViewKOL],
                //     'icon' => 'nav-icon far fa-circle',
                //     'active' => ['admin/kol*']
                // ],
                [
                    'text' => 'Campaign',
                    'url' => 'admin/campaign',
                    'icon' => 'nav-icon far fa-circle',
                    'can' => [PermissionEnum::ViewCampaign],
                    'active' => ['admin/campaign*']
                ],
                [
                    'text' => 'Account',
                    'url' => 'admin/kol',
                    'can' => [PermissionEnum::ViewOffer],
                    'icon' => 'nav-icon far fa-circle',
                    'active' => ['admin/kol*']
                ],
                [
                    'text' => 'Budget',
                    'url' => 'admin/budgets',
                    'can' => [PermissionEnum::ViewOffer],
                    'icon' => 'nav-icon far fa-circle',
                    'active' => ['admin/budgets*']
                ],
                // [
                //     'text' => 'Product',
                //     'url' => 'admin/products',
                //     'can' => [PermissionEnum::ViewOffer],
                //     'icon' => 'nav-icon far fa-circle',
                //     'active' => ['admin/products*']
                // ],
                // [
                //     'text' => 'Brief',
                //     'url' => 'admin/brief',
                //     'can' => [PermissionEnum::ViewOffer],
                //     'icon' => 'nav-icon far fa-circle',
                //     'active' => ['admin/brief*']
                // ],
                // [
                //     'text' => 'Influencer',
                //     'url' => 'admin/budgets',
                //     'can' => [PermissionEnum::ViewOffer],
                //     'icon' => 'nav-icon far fa-circle',
                //     'active' => ['admin/budgets*']
                // ],
                // [
                //     'text' => 'Offer',
                //     'url' => 'admin/offer',
                //     'can' => [PermissionEnum::ViewOffer],
                //     'icon' => 'nav-icon far fa-circle',
                //     'active' => ['admin/offer*']
                // ],
            ]
        ],
        [
            'text' => 'KOL',
            'can' => [PermissionEnum::ViewOrder, PermissionEnum::ViewOffer],
            'icon'    => 'fas fa-fw fa-play',
            'submenu' => [
                [
                    'text' => 'Talents',
                    'url' => 'admin/talent',
                    'icon' => 'nav-icon far fa-circle',
                    'active' => ['admin/talent*']
                ],
                [
                    'text' => 'Content',
                    'url' => 'admin/tlnt-content',
                    'icon' => 'nav-icon far fa-circle',
                    'active' => ['admin/tlnt-content*']
                ],
                [
                    'text' => 'Payment',
                    'url' => 'admin/talnt-payments',
                    'icon' => 'nav-icon far fa-circle',
                    'can' => [PermissionEnum::ViewVisit, PermissionEnum::ViewOffer],
                ],
                [
                    'text' => 'Debt Report',
                    'url' => 'admin/talnt-payments/report',
                    'can' => [PermissionEnum::ViewVisit, PermissionEnum::ViewOffer],
                    'icon' => 'nav-icon far fa-circle',
                ],
                [
                    'text' => 'Approval',
                    'url' => 'admin/approval',
                    'icon' => 'nav-icon far fa-circle',
                    'active' => ['admin/approval*']
                ],
            ]
        ],
        [
            'text' => 'Product Development',
            'can' => [PermissionEnum::ViewOrder],
            'icon'    => 'fas fa-fw fa-table',
            'submenu' => [
                [
                    'text' => 'Keyword Monitoring',
                    'url' => 'admin/keywordMonitoring',
                    'icon' => 'nav-icon far fa-circle',
                    'active' => ['admin/offer*']
                ],
            ]
        ],
        // [
        //     'text' => 'Funnel',
        //     'can' => [PermissionEnum::ViewFunnel],
        //     'icon'    => 'fas fa-fw fa-funnel-dollar',
        //     'submenu' => [
        //         [
        //             'text' => 'Input Data',
        //             'url' => 'admin/funnel/input',
        //             'can' => [PermissionEnum::ViewFunnel],
        //             'icon' => 'nav-icon far fa-circle',
        //         ],
        //         [
        //             'text' => 'Recap',
        //             'url' => 'admin/funnel/recap',
        //             'can' => [PermissionEnum::ViewFunnel],
        //             'icon' => 'nav-icon far fa-circle',
        //         ],
        //         [
        //             'text' => 'Total',
        //             'url' => 'admin/funnel/total',
        //             'can' => [PermissionEnum::ViewFunnel],
        //             'icon' => 'nav-icon far fa-circle',
        //         ],
        //     ],
        // ],
        [
            'text' => 'Contest',
            'url' => 'admin/contest',
            'icon' => 'nav-icon fas fa-trophy',
            'can' => [PermissionEnum::ViewSales],
            'active' => ['admin/contest*']
        ],
        [
            'text' => 'Competitor Analysis',
            'url' => 'admin/competitor_brands',
            'icon' => 'nav-icon fas fa-certificate',
            'can' => [PermissionEnum::ViewSales],
            'active' => ['admin/competitor_brands*']
        ],
        [
            'text'    => 'Master Data',
            'icon'    => 'fas fa-fw fa-database',
            'can' => [PermissionEnum::ViewUser,
                PermissionEnum::ViewMarketingCategory,
                PermissionEnum::ViewSalesChannel,
                PermissionEnum::ViewSocialMedia,
            ],
            'submenu' => [
                [
                    'text' => 'Brand',
                    'url' => '/admin/tenant',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewTenant],
                ],
                [
                    'text' => 'User',
                    'url' => '/admin/users',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewUser],
                    'active' => ['admin/users*']
                ],
                [
                    'text' => 'Marketing Category',
                    'url' => '/admin/marketing-category',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewMarketingCategory],
                    'active' => ['admin/marketing-category*']
                ],
                [
                    'text' => 'Sales Channel',
                    'url' => '/admin/sales-channel',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewSalesChannel],
                ],
                [
                    'text' => 'Social Media',
                    'url' => '/admin/social-media',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewSocialMedia],
                ]
            ],
        ],
        [
            'text' => 'Employees',
            'icon' => 'nav-icon fas fa-id-badge',
            'can' => [PermissionEnum::ViewEmployee,
                      PermissionEnum::ViewAttendance,
            ],
            'active' => ['admin/employee*'],
            'submenu' => [
                [
                    'text' => 'Data',
                    'url' => 'admin/employees',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewEmployee],
                ],
                [
                    'text' => 'Attendances',
                    'url' => '/admin/attendance',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
                [
                    'text' => 'Performances',
                    'url' => '/admin/performances',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
                [
                    'text' => 'Location',
                    'url' => '/admin/location',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
                [
                    'text' => 'Shift',
                    'url' => '/admin/shift',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
            ],
        ],
        [
            'text' => 'Requests',
            'icon' => 'nav-icon fas fa-bell',
            'can' => [PermissionEnum::ViewEmployee,
                      PermissionEnum::ViewAttendance,
            ],
            'submenu' => [
                [
                    'text' => 'Attendances',
                    'url' => 'admin/attendance/approval',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewEmployee],
                ],
                [
                    'text' => 'Overtimes',
                    'url' => '/admin/overtime/approval',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
                [
                    'text' => 'TimeOffs',
                    'url' => '/admin/timeOff/approval',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
                [
                    'text' => 'Change Shift',
                    'url' => '/admin/requestChangeShifts/approval',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
            ],
        ],
        [
            'text' => 'Payroll',
            'icon' => 'nav-icon fas fa-credit-card',
            'can' => [PermissionEnum::ViewEmployee],
            'submenu' => [
                [
                    'text' => 'Recap',
                    'url' => 'admin/payroll',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
                [
                    'text' => 'Salary',
                    'url' => 'admin/payroll/import',
                    'icon' => 'far fa-circle nav-icon',
                    'can' => [PermissionEnum::ViewAttendance],
                ],
            ],
        ],
        [
            'text'    => 'Account Settings',
            'icon'    => 'fas fa-fw fa-user',
            'can' => [PermissionEnum::ChangeOwnPassword],
            'submenu' => [
                [
                    'text' => 'Profile',
                    'url' => 'admin/profile',
                    'icon' => 'far fa-circle nav-icon',
                ],
                [
                    'text' => 'Change Password',
                    'url' => 'admin/changeOwnPassword',
                    'icon' => 'far fa-circle nav-icon',
                ],
            ],
        ],
        
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables/js/jquery.dataTables.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/responsive/js/dataTables.responsive.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/responsive/js/responsive.bootstrap4.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables/css/dataTables.bootstrap4.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables-plugins/responsive/css/responsive.bootstrap4.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/select2/js/select2.full.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2/css/select2.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2-bootstrap4-theme/select2-bootstrap4.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/chart.js/Chart.bundle.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/chart.js/Chart.css',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/sweetalert2/sweetalert2.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/sweetalert2-theme-bootstrap-4/bootstrap-4.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/sweetalert2/sweetalert2.js',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'Toastr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/toastr/toastr.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/toastr/toastr.min.js',
                ],
            ],
        ],
        'Moment' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/moment/moment.min.js',
                ],
            ],
        ],
        'DateRangePicker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/daterangepicker/daterangepicker.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/daterangepicker/daterangepicker.js',
                ],
            ],
        ],
        'Datepicker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datepicker/css/datepicker.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datepicker/js/bootstrap-datepicker.js',
                ],
            ],
        ],
        'JqueryMask' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/jquery/jquery.mask.min.js',
                ],
            ],
        ],
        'JqueryDebounce' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/jquery/jquery.ba-throttle-debounce.min.js',
                ],
            ],
        ],
        'InputMask' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/inputmask/jquery.inputmask.min.js',
                ],
            ],
        ],
        'ICheck' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/icheck-bootstrap/icheck-bootstrap.css',
                ],
            ],
        ],
        'bsCustomFileInput' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/bs-custom-file-input/bs-custom-file-input.js',
                ],
            ],
        ],
        'JExcel' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/jexcel/jsuites.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/jexcel/jsuites.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/jexcel/jexcel.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/jexcel/jexcel.css',
                ],
            ],
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
