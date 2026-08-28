import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

import {
    openOfflineDB
} from './offline-db';

import './offline-sync.js';
import './offline-weight-records.js';

openOfflineDB()
    .then(() => {
        console.log('SwineLocate offline database ready.');
    })
    .catch(error => {
        console.error(
            'Failed to initialize offline database:',
            error
        );
    });

window.Chart = Chart;

window.Alpine = Alpine;

Alpine.start();