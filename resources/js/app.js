import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import {
    openOfflineDB
} from './offline-db';

import './offline-sync.js';

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

const CACHE_NAME = 'swine-locate-v1';

const APP_SHELL = [
    '/dashboard',
    '/manifest.json'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => cacheName !== CACHE_NAME)
                    .map(cacheName => caches.delete(cacheName))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {

    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {

                const responseClone = response.clone();

                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, responseClone);
                });

                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );

});

