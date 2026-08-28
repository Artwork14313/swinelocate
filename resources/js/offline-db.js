const DB_NAME = 'SwineLocateDB';
const DB_VERSION = 2;

const STORES = {
    swine: 'swine',
    healthRecords: 'health_records',
    weightRecords: 'weight_records',
    movements: 'movements',
    syncQueue: 'sync_queue',
};


export function openOfflineDB() {

    return new Promise((resolve, reject) => {

        const request = indexedDB.open(
            DB_NAME,
            DB_VERSION
        );


        request.onupgradeneeded = event => {

            const db = event.target.result;


            if (!db.objectStoreNames.contains(STORES.swine)) {

                db.createObjectStore(
                    STORES.swine,
                    { keyPath: 'id' }
                );

            }


            if (!db.objectStoreNames.contains(STORES.healthRecords)) {

                db.createObjectStore(
                    STORES.healthRecords,
                    { keyPath: 'id' }
                );

            }


            if (!db.objectStoreNames.contains(STORES.weightRecords)) {

                db.createObjectStore(
                    STORES.weightRecords,
                    { keyPath: 'id' }
                );

            }


            if (!db.objectStoreNames.contains(STORES.movements)) {

                db.createObjectStore(
                    STORES.movements,
                    { keyPath: 'id' }
                );

            }


            if (!db.objectStoreNames.contains(STORES.syncQueue)) {

                const store = db.createObjectStore(
                    STORES.syncQueue,
                    {
                        keyPath: 'id',
                        autoIncrement: true
                    }
                );

                store.createIndex(
                    'status',
                    'status',
                    { unique: false }
                );

                store.createIndex(
                    'created_at',
                    'created_at',
                    { unique: false }
                );

            }


        };


        request.onsuccess = event => {

            const db = event.target.result;

            db.onversionchange = () => {
                db.close();
            };

            resolve(db);

        };


        request.onerror = event => {

            reject(
                event.target.error
            );

        };

    });

}


/**
 * Save or update an offline record.
 */
export async function saveOffline(
    storeName,
    data
) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readwrite'
        );

        const store =
            transaction.objectStore(
                storeName
            );

        const request =
            store.put(data);


        request.onsuccess = () => {

            resolve(data);

        };


        request.onerror = event => {

            reject(
                event.target.error
            );

        };

    });

}


/**
 * Add an action to the synchronization queue.
 */
export async function addToSyncQueue(data) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            STORES.syncQueue,
            'readwrite'
        );

        const store =
            transaction.objectStore(
                STORES.syncQueue
            );


        const queueRecord = {

            type: data.type,

            endpoint: data.endpoint,

            method: data.method ?? 'POST',

            payload: data.payload ?? {},

            status: 'pending',

            created_at:
                new Date().toISOString(),

        };


        console.log(
            'Adding to sync queue:',
            queueRecord
        );


        const request =
            store.add(queueRecord);


        request.onsuccess = () => {

            console.log(
                'Sync queue record created:',
                request.result
            );

            resolve(request.result);

        };


        request.onerror = event => {

            console.error(
                'Failed to add sync queue record:',
                event.target.error
            );

            reject(
                event.target.error
            );

        };

    });

}


/**
 * Get one offline record.
 */
export async function getOffline(
    storeName,
    id
) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readonly'
        );

        const store =
            transaction.objectStore(
                storeName
            );

        const request =
            store.get(id);


        request.onsuccess = () => {

            resolve(
                request.result ?? null
            );

        };


        request.onerror = event => {

            reject(
                event.target.error
            );

        };

    });

}


/**
 * Get all records from a store.
 */
export async function getAllOffline(
    storeName
) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readonly'
        );

        const store =
            transaction.objectStore(
                storeName
            );

        const request =
            store.getAll();


        request.onsuccess = () => {

            resolve(
                request.result
            );

        };


        request.onerror = event => {

            reject(
                event.target.error
            );

        };

    });

}


/**
 * Delete an offline record.
 */
export async function deleteOffline(
    storeName,
    id
) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readwrite'
        );

        const store =
            transaction.objectStore(
                storeName
            );

        const request =
            store.delete(id);


        request.onsuccess = () => {

            resolve();

        };


        request.onerror = event => {

            reject(
                event.target.error
            );

        };

    });

}