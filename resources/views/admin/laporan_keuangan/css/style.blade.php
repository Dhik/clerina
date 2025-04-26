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

/* Unified Channel color indicators for all tables */
.shopee-indicator .channel-icon,
.shopee-hpp-indicator .channel-icon,
.shopee-fee-indicator .channel-icon { color: #ee4d2d; }
.shopee-indicator,
.shopee-hpp-indicator,
.shopee-fee-indicator { color: #ee4d2d; font-weight: 600; }

.shopee-2-indicator .channel-icon,
.shopee-2-hpp-indicator .channel-icon,
.shopee-2-fee-indicator .channel-icon { color: #d93b1c; }
.shopee-2-indicator,
.shopee-2-hpp-indicator,
.shopee-2-fee-indicator { color: #d93b1c; font-weight: 600; }

.shopee-3-indicator .channel-icon,
.shopee-3-hpp-indicator .channel-icon,
.shopee-3-fee-indicator .channel-icon { color: #c52d0e; }
.shopee-3-indicator,
.shopee-3-hpp-indicator,
.shopee-3-fee-indicator { color: #c52d0e; font-weight: 600; }

.lazada-indicator .channel-icon,
.lazada-hpp-indicator .channel-icon,
.lazada-fee-indicator .channel-icon { color: #0f146d; }
.lazada-indicator,
.lazada-hpp-indicator,
.lazada-fee-indicator { color: #0f146d; font-weight: 600; }

.tokopedia-indicator .channel-icon,
.tokopedia-hpp-indicator .channel-icon,
.tokopedia-fee-indicator .channel-icon { color: #03ac0e; }
.tokopedia-indicator,
.tokopedia-hpp-indicator,
.tokopedia-fee-indicator { color: #03ac0e; font-weight: 600; }

.tiktok-indicator .channel-icon,
.tiktok-hpp-indicator .channel-icon,
.tiktok-fee-indicator .channel-icon { color: #333333; }
.tiktok-indicator,
.tiktok-hpp-indicator,
.tiktok-fee-indicator { color: #333333; font-weight: 600; }

.b2b-indicator .channel-icon,
.b2b-hpp-indicator .channel-icon,
.b2b-fee-indicator .channel-icon { color: #6a7d90; }
.b2b-indicator,
.b2b-hpp-indicator,
.b2b-fee-indicator { color: #6a7d90; font-weight: 600; }

.crm-indicator .channel-icon,
.crm-hpp-indicator .channel-icon,
.crm-fee-indicator .channel-icon { color: #7b68ee; }
.crm-indicator,
.crm-hpp-indicator,
.crm-fee-indicator { color: #7b68ee; font-weight: 600; }

.other-indicator .channel-icon,
.other-hpp-indicator .channel-icon,
.other-fee-indicator .channel-icon { color: #607d8b; }
.other-indicator,
.other-hpp-indicator,
.other-fee-indicator { color: #607d8b; font-weight: 600; }

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