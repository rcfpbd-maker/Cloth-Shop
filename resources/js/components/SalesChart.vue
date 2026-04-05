<template>
    <div v-if="loading" class="flex justify-center items-center py-20 h-[350px]">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
    <div v-else class="h-[350px]">
        <apexchart type="area" height="350" :options="chartOptions" :series="series"></apexchart>
    </div>
</template>

<script>
export default {
    data() {
        return {
            loading: true,
            series: [],
            chartOptions: {
                chart: {
                    type: 'area',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#4f46e5', '#10b981'], // Indigo, Emerald
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: [],
                    tooltip: { enabled: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => {
                            if (val >= 1000) {
                                return (val / 1000).toFixed(1) + 'k';
                            }
                            return parseInt(val);
                        }
                    }
                },
                tooltip: {
                    x: { format: 'dd MMM' },
                    y: {
                        formatter: function (val) {
                            return "৳" + val.toLocaleString();
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                }
            }
        }
    },
    mounted() {
        this.fetchChartData();
    },
    methods: {
        async fetchChartData() {
            try {
                const response = await window.axios.get('/api/dashboard/charts');
                const data = response.data;
                
                if (Array.isArray(data)) {
                    // Extract data for ApexCharts
                    const categories = data.map(item => item.date);
                    const salesData = data.map(item => item.sales);
                    const profitData = data.map(item => item.profit);
                    
                    this.chartOptions = {
                        ...this.chartOptions,
                        xaxis: {
                            ...this.chartOptions.xaxis,
                            categories: categories
                        }
                    };
                    
                    this.series = [
                        { name: 'Revenue', data: salesData },
                        { name: 'Profit', data: profitData }
                    ];
                }
            } catch (error) {
                console.error("Failed to load chart analytics:", error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
