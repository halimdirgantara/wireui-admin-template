<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- User Registration Trend Chart -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Registration Trend (30 Days)</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="relative h-80">
                <canvas 
                    id="registrationChart" 
                    class="w-full h-full"
                    wire:ignore
                ></canvas>
            </div>
        </div>
    </x-card>

    <!-- Role Distribution Chart -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Role Distribution</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="relative h-80">
                <canvas 
                    id="roleChart" 
                    class="w-full h-full"
                    wire:ignore
                ></canvas>
            </div>
        </div>
    </x-card>

    <!-- Activity Trend Chart -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Activity Trend (7 Days)</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="relative h-80">
                <canvas 
                    id="activityChart" 
                    class="w-full h-full"
                    wire:ignore
                ></canvas>
            </div>
        </div>
    </x-card>

    <!-- Monthly Comparison Chart -->
    <x-card class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Monthly Comparison</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="relative h-80">
                <canvas 
                    id="comparisonChart" 
                    class="w-full h-full"
                    wire:ignore
                ></canvas>
            </div>
        </div>
    </x-card>

    <!-- Refresh Button -->
    <div class="lg:col-span-2 flex justify-end mt-4">
        <x-button 
            wire:click="refreshCharts" 
            size="sm" 
            secondary 
            icon="arrow-path"
            spinner="refreshCharts"
        >
            Refresh Charts
        </x-button>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Charts component loaded');
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    console.log('Chart.js loaded successfully');
    
    let registrationChart = null;
    let roleChart = null;
    let activityChart = null;
    let comparisonChart = null;

    // Chart.js default configuration for dark mode support
    Chart.defaults.color = document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151';
    Chart.defaults.borderColor = document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb';

    function getChartOptions(type) {
        const isDark = document.documentElement.classList.contains('dark');
        
        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        color: isDark ? '#e5e7eb' : '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#f3f4f6' : '#111827',
                    bodyColor: isDark ? '#e5e7eb' : '#374151',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                }
            },
            scales: {},
            elements: {}
        };

        if (type === 'line') {
            baseOptions.scales = {
                x: {
                    grid: {
                        color: isDark ? '#374151' : '#f3f4f6'
                    },
                    ticks: {
                        color: isDark ? '#9ca3af' : '#6b7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDark ? '#374151' : '#f3f4f6'
                    },
                    ticks: {
                        color: isDark ? '#9ca3af' : '#6b7280'
                    }
                }
            };
        } else if (type === 'bar') {
            baseOptions.scales = {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: isDark ? '#9ca3af' : '#6b7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDark ? '#374151' : '#f3f4f6'
                    },
                    ticks: {
                        color: isDark ? '#9ca3af' : '#6b7280'
                    }
                }
            };
        }

        return baseOptions;
    }

    function initializeCharts() {
        console.log('Initializing charts...');
        const chartData = @json($chartData);
        console.log('Chart data:', chartData);
        
        try {
            // Registration Trend Chart (Line)
            console.log('Creating registration chart...');
            if (registrationChart) registrationChart.destroy();
            const registrationCtx = document.getElementById('registrationChart');
            if (!registrationCtx) {
                console.error('Registration chart canvas not found!');
                return;
            }
            const ctx = registrationCtx.getContext('2d');
            registrationChart = new Chart(ctx, {
                type: 'line',
                data: chartData.registration_trend,
                options: getChartOptions('line')
            });
            console.log('Registration chart created successfully');
        } catch (error) {
            console.error('Error creating registration chart:', error);
        }

        // Role Distribution Chart (Doughnut)
        if (roleChart) roleChart.destroy();
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        roleChart = new Chart(roleCtx, {
            type: 'doughnut',
            data: chartData.role_distribution,
            options: {
                ...getChartOptions('doughnut'),
                cutout: '60%',
                plugins: {
                    ...getChartOptions('doughnut').plugins,
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151'
                        }
                    }
                }
            }
        });

        // Activity Trend Chart (Bar)
        if (activityChart) activityChart.destroy();
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        activityChart = new Chart(activityCtx, {
            type: 'bar',
            data: chartData.activity_trend,
            options: getChartOptions('bar')
        });

        // Monthly Comparison Chart (Bar)
        if (comparisonChart) comparisonChart.destroy();
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        comparisonChart = new Chart(comparisonCtx, {
            type: 'bar',
            data: chartData.monthly_comparison,
            options: {
                ...getChartOptions('bar'),
                plugins: {
                    ...getChartOptions('bar').plugins,
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151'
                        }
                    }
                }
            }
        });
    }

    // Initialize charts with a small delay to ensure DOM is ready
    setTimeout(() => {
        initializeCharts();
    }, 100);

    // Listen for Livewire updates
    document.addEventListener('livewire:init', () => {
        console.log('Livewire initialized, reinitializing charts');
        setTimeout(() => {
            initializeCharts();
        }, 200);
    });
    
    // Also listen for the custom refresh event
    if (typeof Livewire !== 'undefined') {
        Livewire.on('charts-refreshed', () => {
            console.log('Charts refresh event received');
            initializeCharts();
        });
    }

    // Handle dark mode changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                // Reinitialize charts when dark mode changes
                setTimeout(() => {
                    initializeCharts();
                }, 100);
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});
</script>
@endpush
