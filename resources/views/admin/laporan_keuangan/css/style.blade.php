<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
    }
    
    .small-box .inner {
        padding: 20px;
    }
    
    .small-box .icon {
        right: 15px;
        top: 15px;
        font-size: 60px;
        opacity: 0.3;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .card-header {
        background-color: rgba(0,0,0,0.03);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table th, .table td {
        padding: 12px;
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .modal-content {
        border-radius: 10px;
    }
    
    .modal-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    
    a.show-details, a.show-gross-revenue-details {
        color: inherit;
        text-decoration: none;
        cursor: pointer;
    }
    
    a.show-details:hover, a.show-gross-revenue-details:hover {
        text-decoration: underline;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .text-primary {
        color: #007bff !important;
    }
    
    .daterange {
        border-radius: 5px;
        padding: 10px;
    }
    
    /* Marketplace specific styles */
    .marketplace-card {
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.2s;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .marketplace-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .marketplace-card .inner {
        padding: 15px;
        position: relative;
        z-index: 10;
    }
    
    .marketplace-card h5 {
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 8px;
        color: #fff;
    }
    
    .marketplace-card p {
        font-size: 1rem;
        margin-bottom: 0;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .marketplace-card .logo {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        opacity: 0.8;
        color: rgba(255, 255, 255, 0.85);
    }
    
    /* Shopee specific styles */
    .shopee-card {
        background: linear-gradient(135deg, #ee4d2d, #ff7337);
    }
    
    .shopee-2-card {
        background: linear-gradient(135deg, #d93b1c, #ee4d2d);
    }
    
    .shopee-3-card {
        background: linear-gradient(135deg, #c52d0e, #d93b1c);
    }
    
    /* Lazada specific styles */
    .lazada-card {
        background: linear-gradient(135deg, #0f146d, #2026b2);
    }
    
    /* Tokopedia specific styles */
    .tokopedia-card {
        background: linear-gradient(135deg, #03ac0e, #42d149);
    }
    
    /* TikTok specific styles */
    .tiktok-card {
        background: linear-gradient(135deg, #010101, #333333);
    }
    
    /* B2B specific styles */
    .b2b-card {
        background: linear-gradient(135deg, #6a7d90, #8ca3ba);
    }
    
    /* CRM specific styles */
    .crm-card {
        background: linear-gradient(135deg, #7b68ee, #9370db);
    }
    
    /* Generic style for other channels */
    .other-card {
        background: linear-gradient(135deg, #607d8b, #90a4ae);
    }
    
    /* Tab styles */
    .nav-tabs .nav-link {
        border-radius: 0.25rem 0.25rem 0 0;
    }
    
    .nav-tabs .nav-link.active {
        font-weight: bold;
    }
    
    .tab-content {
        padding: 15px;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-top: 0;
        border-radius: 0 0 0.25rem 0.25rem;
    }
    
    .channel-summary {
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .channel-summary h5 {
        margin-bottom: 0;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        border-radius: 10px;
    }
    
    .loading-spinner {
        width: 4rem;
        height: 4rem;
        border-width: 0.25em;
    }
    /* HPP Card styles - using different color schemes */
.shopee-hpp-card {
    background: linear-gradient(135deg, #6f42c1, #9370db);
}

.shopee-2-hpp-card {
    background: linear-gradient(135deg, #5e37a6, #6f42c1);
}

.shopee-3-hpp-card {
    background: linear-gradient(135deg, #4b2d89, #5e37a6);
}

.lazada-hpp-card {
    background: linear-gradient(135deg, #fd7e14, #f8a064);
}

.tokopedia-hpp-card {
    background: linear-gradient(135deg, #007bff, #59a6ff);
}

.tiktok-hpp-card {
    background: linear-gradient(135deg, #6c757d, #8f9193);
}

.b2b-hpp-card {
    background: linear-gradient(135deg, #20c997, #68e3c5);
}

.crm-hpp-card {
    background: linear-gradient(135deg, #e83e8c, #f493c3);
}

.other-hpp-card {
    background: linear-gradient(135deg, #17a2b8, #6ad7e5);
}
/* Channel Table Styles */
#channelRevenueCards tr td:first-child,
#channelHppCards tr td:first-child {
    position: relative;
    padding-left: 40px;
}

.channel-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
}

/* Channel color indicators */
.shopee-indicator .channel-icon { color: #ee4d2d; }
.shopee-2-indicator .channel-icon { color: #d93b1c; }
.shopee-3-indicator .channel-icon { color: #c52d0e; }
.lazada-indicator .channel-icon { color: #0f146d; }
.tokopedia-indicator .channel-icon { color: #03ac0e; }
.tiktok-indicator .channel-icon { color: #333333; }
.b2b-indicator .channel-icon { color: #6a7d90; }
.crm-indicator .channel-icon { color: #7b68ee; }
.other-indicator .channel-icon { color: #607d8b; }

/* HPP indicators */
.shopee-hpp-indicator .channel-icon { color: #6f42c1; }
.shopee-2-hpp-indicator .channel-icon { color: #5e37a6; }
.shopee-3-hpp-indicator .channel-icon { color: #4b2d89; }
.lazada-hpp-indicator .channel-icon { color: #fd7e14; }
.tokopedia-hpp-indicator .channel-icon { color: #007bff; }
.tiktok-hpp-indicator .channel-icon { color: #6c757d; }
.b2b-hpp-indicator .channel-icon { color: #20c997; }
.crm-hpp-indicator .channel-icon { color: #e83e8c; }
.other-hpp-indicator .channel-icon { color: #17a2b8; }

/* Fee Admin indicators */
.shopee-fee-indicator .channel-icon { color: #2c3e50; }
.shopee-2-fee-indicator .channel-icon { color: #34495e; }
.shopee-3-fee-indicator .channel-icon { color: #1e2a37; }
.lazada-fee-indicator .channel-icon { color: #8e44ad; }
.tokopedia-fee-indicator .channel-icon { color: #16a085; }
.tiktok-fee-indicator .channel-icon { color: #2980b9; }
.b2b-fee-indicator .channel-icon { color: #f39c12; }
.crm-fee-indicator .channel-icon { color: #d35400; }
.other-fee-indicator .channel-icon { color: #7f8c8d; }
</style>