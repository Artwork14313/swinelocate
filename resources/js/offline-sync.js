const DB_NAME = 'SwineLocateDB';
const DB_VERSION = 1;
const STORE_NAME = 'sync_queue';

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains(STORE_NAME)) {
                const store = db.createObjectStore(STORE_NAME, {
                    keyPath: 'id',
                    autoIncrement: true,
                });

                store.createIndex('status', 'status', {
                    unique: false,
                });

                store.createIndex('created_at', 'created_at', {
                    unique: false,
                });
            }
        };

        request.onsuccess = () => {
            resolve(request.result);
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
}


/**
 * Add an offline action to the synchronization queue.
 */
export async function addToSyncQueue(data) {
    const db = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction(
            STORE_NAME,
            'readwrite'
        );

        const store = transaction.objectStore(STORE_NAME);

        const request = store.add({
            type: data.type,
            endpoint: data.endpoint,
            method: data.method ?? 'POST',
            payload: data.payload ?? {},
            status: 'pending',
            created_at: new Date().toISOString(),
        });

        request.onsuccess = () => {
            resolve(request.result);
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
}


/**
 * Get all pending synchronization records.
 */
export async function getPendingSync() {
    const db = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction(
            STORE_NAME,
            'readonly'
        );

        const store = transaction.objectStore(STORE_NAME);

        const request = store.getAll();

        request.onsuccess = () => {
            resolve(
                request.result.filter(
                    item => item.status === 'pending'
                )
            );
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
}


/**
 * Remove successfully synchronized record.
 */
export async function removeFromSyncQueue(id) {
    const db = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction(
            STORE_NAME,
            'readwrite'
        );

        const store = transaction.objectStore(STORE_NAME);

        const request = store.delete(id);

        request.onsuccess = () => {
            resolve();
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
}


/**
 * Synchronize pending records with Laravel.
 */
export async function syncPendingRecords() {

    if (!navigator.onLine) {
        return;
    }

    const pendingRecords = await getPendingSync();

    for (const record of pendingRecords) {

        try {

            const response = await fetch(
                record.endpoint,
                {
                    method: record.method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute('content'),
                    },
                    body: JSON.stringify(
                        record.payload
                    ),
                }
            );


            if (response.ok) {

                await removeFromSyncQueue(
                    record.id
                );

                console.log(
                    'Synced offline record:',
                    record.id
                );

            } else {

                console.error(
                    'Sync failed:',
                    record.id
                );

            }

        } catch (error) {

            console.error(
                'Unable to synchronize record:',
                error
            );

        }

    }
}


/**
 * Automatically synchronize when
 * the internet connection returns.
 */
window.addEventListener(
    'online',
    () => {
        console.log(
            'Internet connection restored.'
        );

        syncPendingRecords();
    }
);


/**
 * Try synchronization when the
 * application loads.
 */
document.addEventListener(
    'DOMContentLoaded',
    () => {
        syncPendingRecords();
    }
);

window.SwineLocateOffline = {
    addToSyncQueue,
    getPendingSync,
    removeFromSyncQueue,
    syncPendingRecords,
};