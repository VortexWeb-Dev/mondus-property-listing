<div class="w-4/5 mx-auto py-8">
    <h1 class="text-3xl font-semibold text-gray-800">Reports</h1>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="flex justify-center items-center my-12">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500"></div>
    </div>

    <div id="charts-container" class="container mx-auto flex flex-col md:flex-row justify-center gap-8 mt-6" style="display: none;">
        <!-- Residential Section -->
        <div class="flex flex-col justify-center items-center p-6 rounded-lg shadow-sm bg-white">
            <h2 class="text-xl font-medium text-gray-700 mb-4">Residential Properties</h2>
            <div id="residential-chart" class="h-64 w-64"></div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-6">
                <div class="flex flex-col items-center p-3 rounded-lg bg-blue-50">
                    <span class="text-sm font-medium text-gray-500">Sale</span>
                    <span id="residential-sale" class="text-lg font-semibold text-blue-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-cyan-50">
                    <span class="text-sm font-medium text-gray-500">Rent</span>
                    <span id="residential-rent" class="text-lg font-semibold text-cyan-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-orange-50">
                    <span class="text-sm font-medium text-gray-500">PF</span>
                    <span id="residential-property-finder" class="text-lg font-semibold text-orange-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-green-50">
                    <span class="text-sm font-medium text-gray-500">Bayut</span>
                    <span id="residential-bayut" class="text-lg font-semibold text-green-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-purple-50">
                    <span class="text-sm font-medium text-gray-500">Dubizzle</span>
                    <span id="residential-dubizzle" class="text-lg font-semibold text-purple-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-pink-50">
                    <span class="text-sm font-medium text-gray-500">Website</span>
                    <span id="residential-website" class="text-lg font-semibold text-pink-600">0</span>
                </div>
            </div>
        </div>

        <!-- Commercial Section -->
        <div class="flex flex-col justify-center items-center p-6 rounded-lg shadow-sm bg-white">
            <h2 class="text-xl font-medium text-gray-700 mb-4">Commercial Properties</h2>
            <div id="commercial-chart" class="h-64 w-64"></div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-6">
                <div class="flex flex-col items-center p-3 rounded-lg bg-blue-50">
                    <span class="text-sm font-medium text-gray-500">Sale</span>
                    <span id="commercial-sale" class="text-lg font-semibold text-blue-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-cyan-50">
                    <span class="text-sm font-medium text-gray-500">Rent</span>
                    <span id="commercial-rent" class="text-lg font-semibold text-cyan-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-orange-50">
                    <span class="text-sm font-medium text-gray-500">PF</span>
                    <span id="commercial-property-finder" class="text-lg font-semibold text-orange-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-green-50">
                    <span class="text-sm font-medium text-gray-500">Bayut</span>
                    <span id="commercial-bayut" class="text-lg font-semibold text-green-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-purple-50">
                    <span class="text-sm font-medium text-gray-500">Dubizzle</span>
                    <span id="commercial-dubizzle" class="text-lg font-semibold text-purple-600">0</span>
                </div>
                <div class="flex flex-col items-center p-3 rounded-lg bg-pink-50">
                    <span class="text-sm font-medium text-gray-500">Website</span>
                    <span id="commercial-website" class="text-lg font-semibold text-pink-600">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- No Data Message -->
    <div id="no-data-message" class="text-center py-12" style="display: none;">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No data available</h3>
        <p class="mt-1 text-sm text-gray-500">We couldn't find any property data to display.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    window.addEventListener('load', async () => {
        // Show loading spinner initially
        const loadingSpinner = document.getElementById('loading-spinner');
        const chartsContainer = document.getElementById('charts-container');
        const noDataMessage = document.getElementById('no-data-message');

        // Fetch data for charts
        const filters = [{
                label: 'residential',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15OfferingType': ['RS', 'RR']
                }
            },
            {
                label: 'residentialSale',
                filter: {
                    'ufCrm15OfferingType': 'RS',
                    'ufCrm15Status': 'PUBLISHED'
                }
            },
            {
                label: 'residentialRent',
                filter: {
                    'ufCrm15OfferingType': 'RR',
                    'ufCrm15Status': 'PUBLISHED'
                }
            },
            {
                label: 'residentialPropertyFinder',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15PfEnable': true,
                    'ufCrm15OfferingType': ['RS', 'RR']
                }
            },
            {
                label: 'residentialBayut',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15BayutEnable': true,
                    'ufCrm15OfferingType': ['RS', 'RR']
                }
            },
            {
                label: 'residentialDubizzle',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15DubizzleEnable': true,
                    'ufCrm15OfferingType': ['RS', 'RR']
                }
            },
            {
                label: 'residentialWebsite',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15WebsiteEnable': true,
                    'ufCrm15OfferingType': ['RS', 'RR']
                }
            },
            {
                label: 'commercial',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15OfferingType': ['CS', 'CR']
                }
            },
            {
                label: 'commercialSale',
                filter: {
                    'ufCrm15OfferingType': 'CS',
                    'ufCrm15Status': 'PUBLISHED'
                }
            },
            {
                label: 'commercialRent',
                filter: {
                    'ufCrm15OfferingType': 'CR',
                    'ufCrm15Status': 'PUBLISHED'
                }
            },
            {
                label: 'commercialPropertyFinder',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15PfEnable': true,
                    'ufCrm15OfferingType': ['CS', 'CR']
                }
            },
            {
                label: 'commercialBayut',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15BayutEnable': true,
                    'ufCrm15OfferingType': ['CS', 'CR']
                }
            },
            {
                label: 'commercialDubizzle',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15DubizzleEnable': true,
                    'ufCrm15OfferingType': ['CS', 'CR']
                }
            },
            {
                label: 'commercialWebsite',
                filter: {
                    'ufCrm15Status': 'PUBLISHED',
                    'ufCrm15WebsiteEnable': true,
                    'ufCrm15OfferingType': ['CS', 'CR']
                }
            }
        ];

        let stats = {};

        try {
            for (const {
                    label,
                    filter
                }
                of filters) {
                const response = await fetch(`${API_BASE_URL}/crm.item.list`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        entityTypeId: LISTINGS_ENTITY_TYPE_ID,
                        filter,
                        select: ['id']
                    })
                });

                if (!response.ok) {
                    throw new Error(`API request failed for ${label}: ${response.statusText}`);
                }

                const data = await response.json();
                stats[label] = data.total ?? (data.result?.items?.length || 0);
            }
            renderCharts(stats);

        } catch (error) {
            console.error('Error fetching data:', error);
            loadingSpinner.style.display = 'none';
            noDataMessage.style.display = 'block';
        }

        function renderCharts(stats) {
            // Hide loading spinner
            loadingSpinner.style.display = 'none';

            // Check if we have any data
            const hasResidentialData = stats.residentialSale > 0 || stats.residentialRent > 0 ||
                stats.residentialPropertyFinder > 0 || stats.residentialBayut > 0 ||
                stats.residentialDubizzle > 0 || stats.residentialWebsite > 0;

            const hasCommercialData = stats.commercialSale > 0 || stats.commercialRent > 0 ||
                stats.commercialPropertyFinder > 0 || stats.commercialBayut > 0 ||
                stats.commercialDubizzle > 0 || stats.commercialWebsite > 0;

            if (!hasResidentialData && !hasCommercialData) {
                noDataMessage.style.display = 'block';
                return;
            }

            // Show charts container
            chartsContainer.style.display = 'flex';

            // Update DOM with stats
            document.getElementById('residential-sale').textContent = stats.residentialSale || 0;
            document.getElementById('residential-rent').textContent = stats.residentialRent || 0;
            document.getElementById('residential-property-finder').textContent = stats.residentialPropertyFinder || 0;
            document.getElementById('residential-bayut').textContent = stats.residentialBayut || 0;
            document.getElementById('residential-dubizzle').textContent = stats.residentialDubizzle || 0;
            document.getElementById('residential-website').textContent = stats.residentialWebsite || 0;

            document.getElementById('commercial-sale').textContent = stats.commercialSale || 0;
            document.getElementById('commercial-rent').textContent = stats.commercialRent || 0;
            document.getElementById('commercial-property-finder').textContent = stats.commercialPropertyFinder || 0;
            document.getElementById('commercial-bayut').textContent = stats.commercialBayut || 0;
            document.getElementById('commercial-dubizzle').textContent = stats.commercialDubizzle || 0;
            document.getElementById('commercial-website').textContent = stats.commercialWebsite || 0;

            // Create modern donut charts
            if (hasResidentialData) {
                createDonutChart('residential-chart', [
                    stats.residential || 0,
                    stats.residentialSale || 0,
                    stats.residentialRent || 0,
                    stats.residentialPropertyFinder || 0,
                    stats.residentialBayut || 0,
                    stats.residentialDubizzle || 0,
                    stats.residentialWebsite || 0
                ]);
            }

            if (hasCommercialData) {
                createDonutChart('commercial-chart', [
                    stats.commercial || 0,
                    stats.commercialSale || 0,
                    stats.commercialRent || 0,
                    stats.commercialPropertyFinder || 0,
                    stats.commercialBayut || 0,
                    stats.commercialDubizzle || 0,
                    stats.commercialWebsite || 0
                ]);
            }
        }

        function createDonutChart(elementId, seriesData) {
            // Calculate the correct total based on which chart we're rendering
            let correctTotal;
            if (elementId === 'residential-chart') {
                correctTotal = stats.residential;
            } else {
                correctTotal = stats.commercial;
            }

            const options = {
                series: seriesData,
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'Inter, system-ui, sans-serif',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    dropShadow: {
                        enabled: true,
                        top: 0,
                        left: 0,
                        blur: 3,
                        opacity: 0.1
                    }
                },
                labels: ['Sale', 'Rent', 'PF', 'Bayut', 'Dubizzle', 'Website'],
                colors: ['#3b82f6', '#06b6d4', '#f97316', '#10b981', '#8b5cf6', '#ec4899'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total',
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    color: '#374151',
                                    formatter: function() {
                                        // Return our pre-calculated correct total
                                        return correctTotal;
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 600,
                                    color: '#374151',
                                    offsetY: 0,
                                }
                            }
                        }
                    }
                },
                stroke: {
                    width: 2,
                    colors: ['#fff']
                },
                legend: {
                    show: false
                },
                tooltip: {
                    enabled: true,
                    fillSeriesColor: false,
                    style: {
                        fontSize: '14px'
                    },
                    y: {
                        formatter: function(value) {
                            return value;
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                states: {
                    hover: {
                        filter: {
                            type: 'darken',
                            value: 0.85
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 200
                        }
                    }
                }]
            };

            const chart = new ApexCharts(document.getElementById(elementId), options);
            chart.render();
        }
    });
</script>