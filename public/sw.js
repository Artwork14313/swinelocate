const CACHE_NAME = 'swine-locate-v2';

const APP_SHELL = [
    '/dashboard',
    '/manifest.json',
];


// ================================================================
// INSTALL
// ================================================================

self.addEventListener('install', event => {

    event.waitUntil(

        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())

    );

});


// ================================================================
// ACTIVATE
// ================================================================

self.addEventListener('activate', event => {

    event.waitUntil(

        caches.keys()
            .then(cacheNames => {

                return Promise.all(

                    cacheNames
                        .filter(cacheName => {
                            return cacheName !== CACHE_NAME;
                        })
                        .map(cacheName => {
                            return caches.delete(cacheName);
                        })

                );

            })
            .then(() => self.clients.claim())

    );

});


// ================================================================
// FETCH
// ================================================================

self.addEventListener('fetch', event => {

    if (event.request.method !== 'GET') {
        return;
    }


    event.respondWith(

        fetch(event.request)

            .then(response => {

                /*
                 * Only cache successful responses.
                 */
                if (
                    response &&
                    response.status === 200 &&
                    response.type !== 'opaque'
                ) {

                    const responseClone =
                        response.clone();

                    caches.open(CACHE_NAME)
                        .then(cache => {

                            cache.put(
                                event.request,
                                responseClone
                            );

                        });

                }


                return response;

            })

            .catch(() => {

                /*
                 * Internet unavailable.
                 * Try to return the cached version.
                 */
                return caches.match(
                    event.request
                );

            })

    );

});