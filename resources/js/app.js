import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './offline-health-records.js';

import {
    openOfflineDB
} from './offline-db';

import './offline-sync.js';
import './offline-weight-records.js';
import './offline-movements.js';
import './offline-swine.js';

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