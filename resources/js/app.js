import './bootstrap';

import { createApp } from 'vue';
import Alpine from 'alpinejs';

// Import Charts
import Chart from 'chart.js/auto';
import VueApexCharts from 'vue3-apexcharts';

window.Alpine = Alpine;
Alpine.start();

const app = createApp({});

// Register Global Components
import ExampleComponent from './components/ExampleComponent.vue';
app.component('example-component', ExampleComponent);

app.use(VueApexCharts);
app.mount('#app');
