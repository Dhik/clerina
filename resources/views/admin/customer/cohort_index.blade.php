@extends('adminlte::page')
@section('title', trans('labels.customer'))
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Cohort Analysis</h1>
        <a href="{{ route('customer.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cohort Analysis Dashboard</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h5>Analysis Period: <span id="analysis-period">...</span></h5>
                            <p class="text-muted">Filters: Tenant ID: 1, Sales Channel ID: 1</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" id="retention-tab-btn" class="btn btn-primary active">Retention Rate</button>
                            <button type="button" id="revenue-tab-btn" class="btn btn-outline-primary">Average Order Value</button>
                        </div>
                    </div>

                    <!-- Tables Container with Loading Spinner -->
                    <div class="position-relative">
                        <div id="tables-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        
                        <!-- Retention Rate Table -->
                        <div id="retention-table-container" class="table-responsive" style="display: none;">
                            <table class="table table-bordered">
                                <thead id="retention-table-header">
                                    <tr>
                                        <th style="width: 120px;">Cohort</th>
                                        <th style="width: 100px;">Customers</th>
                                        <!-- Month headers will be dynamically inserted here -->
                                    </tr>
                                </thead>
                                <tbody id="retention-table-body">
                                    <!-- Table rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Revenue Table -->
                        <div id="revenue-table-container" class="table-responsive" style="display: none;">
                            <table class="table table-bordered">
                                <thead id="revenue-table-header">
                                    <tr>
                                        <th style="width: 120px;">Cohort</th>
                                        <th style="width: 100px;">Customers</th>
                                        <!-- Month headers will be dynamically inserted here -->
                                    </tr>
                                </thead>
                                <tbody id="revenue-table-body">
                                    <!-- Table rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Retention Over Time</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div id="retention-chart-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div style="height: 250px;">
                            <canvas id="retentionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cohort Size Distribution</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div id="cohort-size-chart-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="cohortSizeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Average Order Value by Cohort</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div id="aov-chart-loading" class="loading-spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="aovChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Recommendation Button -->
<div class="row mt-4 mb-2">
    <div class="col-12 text-center">
        <button id="show-ai-recommendation" class="btn btn-lg btn-primary">
            <i class="fas fa-robot mr-2"></i> Show AI Recommendation
        </button>
    </div>
</div>

    <!-- AI Recommendation Card - Initially Hidden -->
    <div id="ai-recommendation-cards" class="row mt-3" style="display: none;">
    <!-- Loading Animation -->
    <div id="ai-loading" class="col-12 text-center p-5">
        <div class="robot-animation mb-4">
            <lottie-player src="https://assets9.lottiefiles.com/packages/lf20_wFZ1zr.json" background="transparent" speed="1" style="width: 200px; height: 200px; margin: 0 auto;" loop autoplay></lottie-player>
        </div>
        <h4 class="text-muted">Sedang Menganalisis Data Cohort...</h4>
        <p class="text-muted">Mohon tunggu sementara AI memproses data dan menghasilkan rekomendasi.</p>
    </div>
    
    <!-- General Conclusion Card -->
    <div class="col-12 mb-4" id="conclusion-card" style="display: none;">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i> Kesimpulan Umum</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="conclusion-content" class="ai-content large-text">
                    <!-- AI conclusion will be inserted here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Business Implications Card -->
    <div class="col-md-5 mb-4" id="implications-card" style="display: none;">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase mr-2"></i> Implikasi Bisnis</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="implications-content" class="ai-content large-text">
                    <!-- AI implications will be inserted here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recommendations Card -->
    <div class="col-md-5 mb-4" id="recommendations-card" style="display: none;">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lightbulb mr-2"></i> Rekomendasi</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="recommendations-content" class="ai-content large-text">
                    <!-- AI recommendations will be inserted here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Close Button -->
    <div class="col-12 text-center mb-4">
        <button id="close-ai-recommendation" class="btn btn-secondary">
            <i class="fas fa-times mr-2"></i> Close Recommendations
        </button>
    </div>
</div>
@stop

@section('css')
<style>
    .retention-cell, .revenue-cell {
        text-align: center;
    }
    
    .retention-cell {
        font-weight: bold;
        color: white;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.7);
    }
    
    .revenue-cell {
        font-weight: bold;
    }

    .month-0 {
        background-color: #28a745 !important;
        color: white;
    }
    
    .loading-spinner-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 5;
    }
    
    /* Fixed first two columns for horizontal scrolling */
    .sticky-col {
        position: sticky;
        background-color: white;
        z-index: 1;
    }
    .first-col {
        left: 0;
    }
    .second-col {
        left: 120px;
    }
    
    /* AI Content Styling */
.ai-content {
    line-height: 1.8;
}

.large-text {
    font-size: 16px; /* Larger base font size */
}

.ai-content h2 {
    color: #007bff;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-size: 1.75rem; /* Larger heading */
    padding-bottom: 0.5rem;
}

.ai-content h3 {
    color: #4a5568;
    margin-top: 1.2rem;
    margin-bottom: 0.8rem;
    font-size: 1.5rem; /* Larger subheading */
}

.ai-content ul, .ai-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.ai-content li {
    margin-bottom: 0.75rem;
}

.ai-content p {
    margin-bottom: 1.2rem;
}

.ai-content strong {
    color: #2d3748;
    font-weight: 600;
}

/* Key metrics highlighting */
.metric-highlight {
    font-size: 110%;
    font-weight: bold;
    color: #007bff;
}

.positive-trend {
    color: #28a745;
}

.negative-trend {
    color: #dc3545;
}

/* Animated show/hide */
.fade-in {
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script>
    let cohortDataGlobal = null; // To store the cohort data globally
    let analysisPeriodGlobal = null; // To store the analysis period
    let aiGenerated = false; // Flag to track if AI has already been generated
    
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch cohort data from API
        fetch('{{ route("net-profit.cohort-data") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Store data globally for AI analysis
                cohortDataGlobal = data.cohort_data;
                analysisPeriodGlobal = data.analysis_period;
                
                // Update analysis period
                document.getElementById('analysis-period').textContent = 
                    `${data.analysis_period.start_date} to ${data.analysis_period.end_date}`;

                // Determine the maximum number of months to display
                const maxMonths = determineMaxMonths(data.cohort_data);
                
                // Create table headers for all months
                createTableHeaders(maxMonths);
                
                // Render cohort tables
                renderCohortTables(data.cohort_data, maxMonths);
                
                // Render charts (each with its own loading state)
                renderRetentionChart(data.cohort_data, maxMonths);
                renderCohortSizeChart(data.cohort_data);
                renderAOVChart(data.cohort_data);
                
                // Set up tab switching
                setupTabSwitching();
            })
            .catch(error => {
                console.error('Error fetching cohort data:', error);
                
                // Hide all loading indicators
                document.querySelectorAll('.loading-spinner-container').forEach(loader => {
                    loader.style.display = 'none';
                });
                
                // Display error notification using AdminLTE's toast
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Failed to load cohort data. Please try again later.',
                    autohide: true,
                    delay: 5000,
                    class: 'bg-danger'
                });
            });
            
        // Set up AI recommendation button
        document.getElementById('show-ai-recommendation').addEventListener('click', showAIRecommendation);
        
        // Set up close button for AI recommendation
        document.getElementById('close-ai-recommendation').addEventListener('click', function() {
            document.getElementById('ai-recommendation-cards').style.display = 'none';
        });
    });
    
    // Function to show AI recommendation
    function showAIRecommendation() {
        // Show the cards container
        document.getElementById('ai-recommendation-cards').style.display = 'block';
        
        // If AI has already been generated, just show the content
        if (aiGenerated) {
            document.getElementById('ai-loading').style.display = 'none';
            document.getElementById('conclusion-card').style.display = 'block';
            document.getElementById('implications-card').style.display = 'block';
            document.getElementById('recommendations-card').style.display = 'block';
            return;
        }
        
        // Show loading animation
        document.getElementById('ai-loading').style.display = 'block';
        document.getElementById('conclusion-card').style.display = 'none';
        document.getElementById('implications-card').style.display = 'none';
        document.getElementById('recommendations-card').style.display = 'none';
        
        // Generate AI analysis
        generateAIAnalysis();
    }
    function parseAIResponseSections(analysis) {
        // Default sections
        const sections = {
            conclusion: '<p>Tidak ada kesimpulan yang tersedia.</p>',
            implications: '<p>Tidak ada implikasi bisnis yang tersedia.</p>',
            recommendations: '<p>Tidak ada rekomendasi yang tersedia.</p>'
        };
        
        // Add debugging
        console.log("Raw AI response:", analysis);
        
        // Check if any response exists
        if (!analysis || analysis.trim() === '') {
            return sections;
        }
        
        // Try more flexible pattern matching
        // Extract conclusion section - try multiple patterns
        let conclusionMatch = analysis.match(/# Kesimpulan Umum([\s\S]*?)(?=# Implikasi|# Rekomendasi|$)/i);
        if (!conclusionMatch) {
            conclusionMatch = analysis.match(/Kesimpulan Umum:?([\s\S]*?)(?=Implikasi|Rekomendasi|$)/i);
        }
        if (conclusionMatch && conclusionMatch[1] && conclusionMatch[1].trim()) {
            sections.conclusion = formatAIResponseSection(conclusionMatch[1]);
        }
        
        // Extract implications section - try multiple patterns
        let implicationsMatch = analysis.match(/# Implikasi Bisnis([\s\S]*?)(?=# Kesimpulan|# Rekomendasi|$)/i);
        if (!implicationsMatch) {
            implicationsMatch = analysis.match(/Implikasi Bisnis:?([\s\S]*?)(?=Kesimpulan|Rekomendasi|$)/i);
        }
        if (implicationsMatch && implicationsMatch[1] && implicationsMatch[1].trim()) {
            sections.implications = formatAIResponseSection(implicationsMatch[1]);
        }
        
        // Extract recommendations section - try multiple patterns
        let recommendationsMatch = analysis.match(/# Rekomendasi([\s\S]*?)(?=# Kesimpulan|# Implikasi|$)/i);
        if (!recommendationsMatch) {
            recommendationsMatch = analysis.match(/Rekomendasi:?([\s\S]*?)(?=Kesimpulan|Implikasi|$)/i);
        }
        if (recommendationsMatch && recommendationsMatch[1] && recommendationsMatch[1].trim()) {
            sections.recommendations = formatAIResponseSection(recommendationsMatch[1]);
        }
        
        // If still no sections matched, use a fallback approach
        if (sections.conclusion === '<p>Tidak ada kesimpulan yang tersedia.</p>' &&
            sections.implications === '<p>Tidak ada implikasi bisnis yang tersedia.</p>' &&
            sections.recommendations === '<p>Tidak ada rekomendasi yang tersedia.</p>') {
                
            // Split the text by common separators
            const parts = analysis.split(/\n\s*\n|\r\n\s*\r\n/);
            
            if (parts.length >= 3) {
                sections.conclusion = formatAIResponseSection(parts[0]);
                sections.implications = formatAIResponseSection(parts[1]);
                sections.recommendations = formatAIResponseSection(parts[2]);
            } else if (parts.length === 2) {
                sections.conclusion = formatAIResponseSection(parts[0]);
                sections.recommendations = formatAIResponseSection(parts[1]);
            } else if (parts.length === 1) {
                sections.conclusion = formatAIResponseSection(parts[0]);
            }
        }
        
        return sections;
    }
    function generateAIAnalysis() {
        // Make sure we have data
        if (!cohortDataGlobal || !analysisPeriodGlobal) {
            document.getElementById('ai-loading').style.display = 'none';
            
            $(document).Toasts('create', {
                title: 'Error',
                body: 'Tidak ada data cohort untuk dianalisis.',
                autohide: true,
                delay: 5000,
                class: 'bg-danger'
            });
            return;
        }
        
        // Call the AI analysis endpoint with a simplified prompt
        fetch('{{ route("cohort-analysis.ai") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                cohort_data: cohortDataGlobal,
                analysis_period: analysisPeriodGlobal,
                format: 'executive' // Signal that we want an executive summary format
            })
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading animation
            document.getElementById('ai-loading').style.display = 'none';
            
            if (data.success) {
                // Parse sections from the AI response
                console.log("AI Response:", data.analysis);
                const sections = parseAIResponseSections(data.analysis);
                console.log("Parsed sections:", sections);
                
                // Populate the cards
                document.getElementById('conclusion-content').innerHTML = sections.conclusion;
                document.getElementById('implications-content').innerHTML = sections.implications;
                document.getElementById('recommendations-content').innerHTML = sections.recommendations;
                
                // Show the cards
                document.getElementById('conclusion-card').style.display = 'block';
                document.getElementById('implications-card').style.display = 'block';
                document.getElementById('recommendations-card').style.display = 'block';
                
                // Add fade-in effect
                document.querySelectorAll('.ai-content').forEach(el => {
                    el.classList.add('fade-in');
                });
                
                // Mark AI as generated
                aiGenerated = true;
            } else {
                // Show error message
                $(document).Toasts('create', {
                    title: 'Error',
                    body: data.message || 'Gagal menghasilkan analisis AI.',
                    autohide: true,
                    delay: 5000,
                    class: 'bg-danger'
                });
            }
        })
        .catch(error => {
            console.error('Error generating AI analysis:', error);
            
            // Hide loading animation
            document.getElementById('ai-loading').style.display = 'none';
            
            // Show error message
            $(document).Toasts('create', {
                title: 'Error',
                body: 'Gagal menghasilkan analisis. Silakan coba lagi nanti.',
                autohide: true,
                delay: 5000,
                class: 'bg-danger'
            });
        });
    }
    
    function formatAIResponseSection(section) {
    return section
        // Trim whitespace
        .trim()
        
        // Convert markdown headings
        .replace(/^## (.*?)$/gm, '<h3>$1</h3>')
        
        // Convert bold text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        
        // Convert lists
        .replace(/^\d+\. (.*?)$/gm, '<li>$1</li>')
        .replace(/^- (.*?)$/gm, '<li>$1</li>')
        
        // Wrap lists
        .replace(/<li>(.*?)<\/li>(?:\n<li>)/g, '<li>$1</li>\n<li>')
        .replace(/(<li>.*?<\/li>\n)+/g, function(match) {
            return match.includes('1. ') || match.includes('2. ') ? '<ol>' + match + '</ol>' : '<ul>' + match + '</ul>';
        })
        
        // Highlight key metrics
        .replace(/(\d+([,.]\d+)?%)/g, '<span class="metric-highlight">$1</span>')
        .replace(/(meningkat|naik|lebih tinggi|positif)/gi, '<span class="positive-trend">$1</span>')
        .replace(/(menurun|turun|lebih rendah|negatif)/gi, '<span class="negative-trend">$1</span>')
        
        // Convert paragraphs (any line that doesn't start with a special character)
        .replace(/^(?!<h|<ul|<ol|<li|<div)(.*?)$/gm, '<p>$1</p>')
        
        // Remove empty paragraphs
        .replace(/<p><\/p>/g, '');
}
    // Function to format the AI response with Markdown-like styling
    function formatAIResponse(analysis) {
        // Convert markdown-like syntax to HTML
        let formatted = analysis
            // Convert headlines
            .replace(/^# (.*?)$/gm, '<h2>$1</h2>')
            .replace(/^## (.*?)$/gm, '<h3>$1</h3>')
            
            // Convert bold
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            
            // Convert lists
            .replace(/^\d+\. (.*?)$/gm, '<li>$1</li>')
            .replace(/^- (.*?)$/gm, '<li>$1</li>')
            
            // Wrap lists
            .replace(/<li>(.*?)<\/li>(?:\n<li>)/g, '<li>$1</li>\n<li>')
            .replace(/(<li>.*?<\/li>\n)+/g, function(match) {
                return match.includes('1. ') || match.includes('2. ') ? '<ol>' + match + '</ol>' : '<ul>' + match + '</ul>';
            })
            
            // Apply special styling to sections
            .replace(/<h2>Kesimpulan(.*?)<\/h2>/g, '<h2>Kesimpulan$1</h2><div class="conclusion-box">')
            .replace(/<h2>Rekomendasi(.*?)<\/h2>/g, '</div><h2>Rekomendasi$1</h2><div class="recommendation-box">')
            
            // Convert paragraphs (any line that doesn't start with a special character)
            .replace(/^(?!<h|<ul|<ol|<li|<div)(.*?)$/gm, '<p>$1</p>')
            
            // Remove empty paragraphs
            .replace(/<p><\/p>/g, '');
            
        // Close any open recommendation box
        if (formatted.includes('<div class="recommendation-box">')) {
            formatted += '</div>';
        } else if (formatted.includes('<div class="conclusion-box">')) {
            formatted += '</div>';
        }
        
        return formatted;
    }
    
    // Function to determine the maximum number of months across all cohorts
    function determineMaxMonths(cohortData) {
        let maxMonths = 0;
        
        // Find the maximum month index across all cohorts
        Object.values(cohortData).forEach(cohort => {
            const monthIndices = Object.keys(cohort.months).map(Number);
            if (monthIndices.length > 0) {
                const cohortMax = Math.max(...monthIndices);
                maxMonths = Math.max(maxMonths, cohortMax);
            }
        });
        
        // Add 1 because we're 0-indexed (months 0-11 = 12 months)
        return maxMonths + 1;
    }
    
    // Function to create table headers for all months
    function createTableHeaders(maxMonths) {
        const retentionHeader = document.querySelector('#retention-table-header tr');
        const revenueHeader = document.querySelector('#revenue-table-header tr');
        
        // Clear existing month headers (after cohort and customers columns)
        while (retentionHeader.children.length > 2) {
            retentionHeader.removeChild(retentionHeader.lastChild);
        }
        
        while (revenueHeader.children.length > 2) {
            revenueHeader.removeChild(revenueHeader.lastChild);
        }
        
        // Add headers for each month
        for (let i = 0; i < maxMonths; i++) {
            // Retention header
            const retentionTh = document.createElement('th');
            retentionTh.style.width = '90px';
            retentionTh.textContent = `Month ${i}`;
            retentionHeader.appendChild(retentionTh);
            
            // Revenue header
            const revenueTh = document.createElement('th');
            revenueTh.style.width = '90px';
            revenueTh.textContent = `Month ${i}`;
            revenueHeader.appendChild(revenueTh);
        }
        
        // Make the first two columns sticky for better horizontal scrolling
        retentionHeader.children[0].className = 'sticky-col first-col';
        retentionHeader.children[1].className = 'sticky-col second-col';
        
        revenueHeader.children[0].className = 'sticky-col first-col';
        revenueHeader.children[1].className = 'sticky-col second-col';
    }

    function renderCohortTables(cohortData, maxMonths) {
        const tablesLoading = document.getElementById('tables-loading');
        const retentionTableBody = document.getElementById('retention-table-body');
        const revenueTableBody = document.getElementById('revenue-table-body');
        const retentionTableContainer = document.getElementById('retention-table-container');
        const revenueTableContainer = document.getElementById('revenue-table-container');
        
        // Clear existing content
        retentionTableBody.innerHTML = '';
        revenueTableBody.innerHTML = '';
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        sortedCohorts.forEach(cohortMonth => {
            const cohort = cohortData[cohortMonth];
            
            // Create retention rate row
            const retentionRow = document.createElement('tr');
            
            // Add cohort month and size
            retentionRow.innerHTML = `
                <td class="sticky-col first-col">${formatMonthYear(cohortMonth)}</td>
                <td class="sticky-col second-col">${formatNumber(cohort.total_customers)}</td>
            `;
            
            // Create revenue row
            const revenueRow = document.createElement('tr');
            
            // Add cohort month and size
            revenueRow.innerHTML = `
                <td class="sticky-col first-col">${formatMonthYear(cohortMonth)}</td>
                <td class="sticky-col second-col">${formatNumber(cohort.total_customers)}</td>
            `;
            
            // Add cells for each month (0 to maxMonths-1)
            for (let i = 0; i < maxMonths; i++) {
                const monthData = cohort.months[i];
                
                // Retention cell
                const retentionCell = document.createElement('td');
                retentionCell.className = 'retention-cell';
                
                if (monthData) {
                    const retentionRate = monthData.retention_rate;
                    retentionCell.textContent = `${retentionRate}%`;
                    
                    // Special color for month 0 (always 100%)
                    if (i === 0) {
                        retentionCell.classList.add('month-0');
                    } else {
                        // Color based on retention rate
                        retentionCell.style.backgroundColor = `rgba(0, 123, 255, ${retentionRate / 100})`;
                    }
                } else {
                    retentionCell.textContent = '-';
                    retentionCell.style.backgroundColor = '#f8f9fa';
                    retentionCell.style.color = '#6c757d';
                }
                
                retentionRow.appendChild(retentionCell);
                
                // Revenue cell
                const revenueCell = document.createElement('td');
                revenueCell.className = 'revenue-cell';
                
                if (monthData) {
                    revenueCell.textContent = formatCurrency(monthData.average_order_value);
                    
                    // Special color for month 0
                    if (i === 0) {
                        revenueCell.classList.add('month-0');
                    } else {
                        // Color based on AOV (assuming max AOV around 200,000)
                        const maxAOV = 200000;
                        const intensity = Math.min(1, monthData.average_order_value / maxAOV);
                        revenueCell.style.backgroundColor = `rgba(40, 167, 69, ${intensity})`;
                        if (intensity > 0.5) {
                            revenueCell.style.color = 'white';
                            revenueCell.style.textShadow = '0 0 2px rgba(0, 0, 0, 0.7)';
                        }
                    }
                } else {
                    revenueCell.textContent = '-';
                    revenueCell.style.backgroundColor = '#f8f9fa';
                    revenueCell.style.color = '#6c757d';
                }
                
                revenueRow.appendChild(revenueCell);
            }
            
            retentionTableBody.appendChild(retentionRow);
            revenueTableBody.appendChild(revenueRow);
        });
        
        // Hide loading indicator and show tables
        tablesLoading.style.display = 'none';
        retentionTableContainer.style.display = 'block';
    }

    function renderRetentionChart(cohortData, maxMonths) {
        const loadingSpinner = document.getElementById('retention-chart-loading');
        const ctx = document.getElementById('retentionChart').getContext('2d');
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        // Create labels for all months
        const labels = [];
        for (let i = 0; i < maxMonths; i++) {
            labels.push(`Month ${i}`);
        }
        
        // Prepare datasets
        const datasets = sortedCohorts.map((cohortMonth, index) => {
            const cohort = cohortData[cohortMonth];
            const data = [];
            
            // Get retention rates for each period
            for (let i = 0; i < maxMonths; i++) {
                if (cohort.months[i]) {
                    data.push(cohort.months[i].retention_rate);
                } else {
                    data.push(null);
                }
            }
            
            // Generate a color based on index
            const colors = [
                'rgba(255, 99, 132, 1)',   // Red
                'rgba(54, 162, 235, 1)',   // Blue
                'rgba(255, 206, 86, 1)',   // Yellow
                'rgba(75, 192, 192, 1)',   // Teal
                'rgba(153, 102, 255, 1)',  // Purple
                'rgba(255, 159, 64, 1)',   // Orange
                'rgba(201, 203, 207, 1)',  // Grey
                'rgba(0, 204, 150, 1)',    // Seafoam
                'rgba(215, 119, 0, 1)',    // Orange-brown
                'rgba(118, 17, 195, 1)',   // Purple
                'rgba(0, 139, 139, 1)',    // Teal-dark
                'rgba(205, 92, 92, 1)'     // Indian red
            ];
            
            return {
                label: `Cohort ${formatMonthYear(cohortMonth)}`,
                data: data,
                borderColor: colors[index % colors.length],
                backgroundColor: 'transparent',
                tension: 0.1
            };
        });
        
        // Create the chart
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Retention Rate (%)'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
        
        // Hide loading spinner once chart is rendered
        loadingSpinner.style.display = 'none';
    }

    function renderCohortSizeChart(cohortData) {
        const loadingSpinner = document.getElementById('cohort-size-chart-loading');
        const ctx = document.getElementById('cohortSizeChart').getContext('2d');
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        // Prepare data
        const labels = sortedCohorts.map(cohortMonth => formatMonthYear(cohortMonth));
        const data = sortedCohorts.map(cohortMonth => cohortData[cohortMonth].total_customers);
        
        // Create the chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Customers',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Customer Count'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Hide loading spinner once chart is rendered
        loadingSpinner.style.display = 'none';
    }

    function renderAOVChart(cohortData) {
        const loadingSpinner = document.getElementById('aov-chart-loading');
        const ctx = document.getElementById('aovChart').getContext('2d');
        
        // Sort cohorts chronologically
        const sortedCohorts = Object.keys(cohortData).sort();
        
        // Prepare data
        const labels = sortedCohorts.map(cohortMonth => formatMonthYear(cohortMonth));
        const data = sortedCohorts.map(cohortMonth => {
            // Use month 0 average order value
            return cohortData[cohortMonth].months[0] ? cohortData[cohortMonth].months[0].average_order_value : 0;
        });
        
        // Create the chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Order Value',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Average Order Value (IDR)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrencyShort(value);
                            }
                        }
                    }
                },
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Average Order Value: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
        
        // Hide loading spinner once chart is rendered
        loadingSpinner.style.display = 'none';
    }

    function setupTabSwitching() {
        const retentionTabBtn = document.getElementById('retention-tab-btn');
        const revenueTabBtn = document.getElementById('revenue-tab-btn');
        const retentionTableContainer = document.getElementById('retention-table-container');
        const revenueTableContainer = document.getElementById('revenue-table-container');
        
        retentionTabBtn.addEventListener('click', function() {
            retentionTabBtn.classList.add('btn-primary');
            retentionTabBtn.classList.remove('btn-outline-primary');
            revenueTabBtn.classList.remove('btn-primary');
            revenueTabBtn.classList.add('btn-outline-primary');
            
            retentionTableContainer.style.display = 'block';
            revenueTableContainer.style.display = 'none';
        });
        
        revenueTabBtn.addEventListener('click', function() {
            revenueTabBtn.classList.add('btn-primary');
            revenueTabBtn.classList.remove('btn-outline-primary');
            retentionTabBtn.classList.remove('btn-primary');
            retentionTabBtn.classList.add('btn-outline-primary');
            
            revenueTableContainer.style.display = 'block';
            retentionTableContainer.style.display = 'none';
        });
    }

    // Helper functions
    function formatMonthYear(dateString) {
        const [year, month] = dateString.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleString('en-US', { month: 'short', year: 'numeric' });
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function formatCurrencyShort(value) {
        if (value >= 1000000) {
            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return 'Rp ' + (value / 1000).toFixed(1) + 'K';
        } else {
            return 'Rp ' + value;
        }
    }
</script>
@stop