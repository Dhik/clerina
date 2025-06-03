<style>
    #salesPieChart {
        height: 400px !important;
        width: 100% !important;
    }
    .modal-content {
    border-radius: 8px;
}

.modal-header {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    border-bottom: 1px solid #dee2e6;
}

.table th, .table td {
    padding: 12px;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

#salesDetailTable td {
    border-top: 1px solid #dee2e6;
}

.chart-container {
    position: relative;
    height: 400px;
    width: 100%;
}
#funnelMetrics, #googleFunnelMetrics, #tiktokFunnelMetrics, #overallPerformanceMetrics {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
.text-muted {
    color: #6c757d;
}
.font-weight-bold {
    font-weight: 600;
}
.ml-2 {
    margin-left: 0.5rem;
}
.mb-2 {
    margin-bottom: 0.5rem;
}
/* Add this to your CSS file or in a <style> section */

.score-breakdown {
    min-width: 200px;
    max-width: 250px;
}

.score-breakdown .total-score {
    font-size: 14px;
    font-weight: bold;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 5px;
    margin-bottom: 8px;
}

.score-breakdown .score-details {
    line-height: 1.3;
}

.score-breakdown .score-details br {
    margin-bottom: 2px;
}

/* DataTable responsive adjustments for score column */
@media (max-width: 768px) {
    .score-breakdown {
        min-width: 150px;
        max-width: 180px;
    }
    
    .score-breakdown .score-details {
        font-size: 10px;
    }
}

/* Tooltip for better score viewing on mobile */
.score-tooltip {
    position: relative;
    cursor: help;
}

.score-tooltip:hover .score-details {
    position: absolute;
    z-index: 1000;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    white-space: nowrap;
}
</style>