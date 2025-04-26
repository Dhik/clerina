<style>
/* KPI Table Styles */
.kpi-table {
    border-collapse: separate;
    border-spacing: 10px;
    margin-bottom: 20px;
}

.kpi-table td {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    padding: 0;
    height: 100px;
    position: relative;
    overflow: hidden;
}

.kpi-table td:hover {
    transform: translateY(-5px);
}

.kpi-cell {
    padding: 20px;
    color: white;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.kpi-icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 45px;
    opacity: 0.3;
    color: rgba(255, 255, 255, 0.85);
}

.kpi-value {
    font-size: 1.7rem;
    font-weight: bold;
    margin-bottom: 5px;
    position: relative;
    z-index: 2;
}

.kpi-label {
    font-size: 1rem;
    margin: 0;
    position: relative;
    z-index: 2;
}

/* Channel Table Styles */
#channelRevenueCards tr td:first-child,
#channelHppCards tr td:first-child,
#channelFeeAdminCards tr td:first-child {
    position: relative;
    padding-left: 40px;
}

/* Larger and bolder font for amount values */
#channelRevenueCards tr td:nth-child(2),
#channelHppCards tr td:nth-child(2),
#channelFeeAdminCards tr td:nth-child(2) {
    font-size: 1.1rem;
    font-weight: 700;
}

/* Styling for percentage values */
#channelHppCards tr td:nth-child(3),
#channelFeeAdminCards tr td:nth-child(3) {
    font-size: 1rem;
    font-weight: 600;
}

.channel-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
}

/* Responsive table styles */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
    background-color: #f8f9fa;
    font-weight: 600;
}

.table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6;
}

.thead-light th {
    background-color: #e9ecef;
    color: #495057;
}

.text-right {
    text-align: right;
}

/* Card styles */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.card-header {
    background-color: rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 12px 15px;
}

.card-title {
    margin-bottom: 0;
    font-weight: 600;
}

.card-body {
    padding: 15px;
}

/* Channel color indicators for all tables */
.shopee-indicator .channel-icon { color: #ee4d2d; }
.shopee-2-indicator .channel-icon { color: #d93b1c; }
.shopee-3-indicator .channel-icon { color: #c52d0e; }
.lazada-indicator .channel-icon { color: #0f146d; }
.tokopedia-indicator .channel-icon { color: #03ac0e; }
.tiktok-indicator .channel-icon { color: #333333; }
.b2b-indicator .channel-icon { color: #6a7d90; }
.crm-indicator .channel-icon { color: #7b68ee; }
.other-indicator .channel-icon { color: #607d8b; }

/* Matching value colors for rows with specific channel indicators */
.shopee-indicator + td, 
.shopee-hpp-indicator + td, 
.shopee-fee-indicator + td { color: #ee4d2d; font-weight: 700; }

.shopee-2-indicator + td, 
.shopee-2-hpp-indicator + td, 
.shopee-2-fee-indicator + td { color: #d93b1c; font-weight: 700; }

.shopee-3-indicator + td, 
.shopee-3-hpp-indicator + td, 
.shopee-3-fee-indicator + td { color: #c52d0e; font-weight: 700; }

.lazada-indicator + td, 
.lazada-hpp-indicator + td, 
.lazada-fee-indicator + td { color: #0f146d; font-weight: 700; }

.tokopedia-indicator + td, 
.tokopedia-hpp-indicator + td, 
.tokopedia-fee-indicator + td { color: #03ac0e; font-weight: 700; }

.tiktok-indicator + td, 
.tiktok-hpp-indicator + td, 
.tiktok-fee-indicator + td { color: #333333; font-weight: 700; }

.b2b-indicator + td, 
.b2b-hpp-indicator + td, 
.b2b-fee-indicator + td { color: #6a7d90; font-weight: 700; }

.crm-indicator + td, 
.crm-hpp-indicator + td, 
.crm-fee-indicator + td { color: #7b68ee; font-weight: 700; }

.other-indicator + td, 
.other-hpp-indicator + td, 
.other-fee-indicator + td { color: #607d8b; font-weight: 700; }

/* Make percentage values match the same color */
.shopee-hpp-indicator + td + td,
.shopee-fee-indicator + td + td { color: #ee4d2d; font-weight: 600; }

.shopee-2-hpp-indicator + td + td,
.shopee-2-fee-indicator + td + td { color: #d93b1c; font-weight: 600; }

.shopee-3-hpp-indicator + td + td,
.shopee-3-fee-indicator + td + td { color: #c52d0e; font-weight: 600; }

.lazada-hpp-indicator + td + td,
.lazada-fee-indicator + td + td { color: #0f146d; font-weight: 600; }

.tokopedia-hpp-indicator + td + td,
.tokopedia-fee-indicator + td + td { color: #03ac0e; font-weight: 600; }

.tiktok-hpp-indicator + td + td,
.tiktok-fee-indicator + td + td { color: #333333; font-weight: 600; }

.b2b-hpp-indicator + td + td,
.b2b-fee-indicator + td + td { color: #6a7d90; font-weight: 600; }

.crm-hpp-indicator + td + td,
.crm-fee-indicator + td + td { color: #7b68ee; font-weight: 600; }

.other-hpp-indicator + td + td,
.other-fee-indicator + td + td { color: #607d8b; font-weight: 600; }

/* Modal styles */
.modal-content {
    border-radius: 10px;
    overflow: hidden;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-title {
    font-weight: 600;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
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
    width: 3rem;
    height: 3rem;
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
</style>