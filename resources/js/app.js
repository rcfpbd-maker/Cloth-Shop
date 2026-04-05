import './bootstrap';

import { createApp } from 'vue';
import Alpine from 'alpinejs';

// Import Charts
import Chart from 'chart.js/auto';
import VueApexCharts from 'vue3-apexcharts';

window.Alpine = Alpine;
// Alpine.start() moved to app.blade.php to ensure all data components are registered first

const app = createApp({});

// Register Global Components
import ExampleComponent from './components/ExampleComponent.vue';
app.component('example-component', ExampleComponent);

app.use(VueApexCharts);

// Mount Vue selectively to avoid conflicts with Alpine
if (document.getElementById('vue-app')) {
    app.mount('#vue-app');
}
