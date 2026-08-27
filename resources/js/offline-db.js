const DB_NAME = 'SwineLocateDB';
const DB_VERSION = 1;

const STORES = {
    swine: 'swine',
    healthRecords: 'health_records',
    weightRecords: 'weight_records',
    movements: 'movements',
    syncQueue: 'sync_queue',
};

export function openOfflineDB() {
    return new Promise((resolve, reject) => {

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = event => {

            const db = event.target.result;

            if (!db.objectStoreNames.contains(STORES.swine)) {
                db.createObjectStore(STORES.swine, {
                    keyPath: 'id'
                });
            }

            if (!db.objectStoreNames.contains(STORES.healthRecords)) {
                db.createObjectStore(STORES.healthRecords, {
                    keyPath: 'id'
                });
            }

            if (!db.objectStoreNames.contains(STORES.weightRecords)) {
                db.createObjectStore(STORES.weightRecords, {
                    keyPath: 'id'
                });
            }

            if (!db.objectStoreNames.contains(STORES.movements)) {
                db.createObjectStore(STORES.movements, {
                    keyPath: 'id'
                });
            }

            if (!db.objectStoreNames.contains(STORES.syncQueue)) {
                db.createObjectStore(STORES.syncQueue, {
                    keyPath: 'id',
                    autoIncrement: true
                });
            }
        };

        request.onsuccess = event => {
            resolve(event.target.result);
        };

        request.onerror = event => {
            reject(event.target.error);
        };

    });
}

export async function saveOffline(storeName, data) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readwrite'
        );

        const store = transaction.objectStore(storeName);

        const request = store.put(data);

        request.onsuccess = () => resolve(data);

        request.onerror = event => {
            reject(event.target.error);
        };

    });
}


export async function getOffline(storeName, id) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readonly'
        );

        const store = transaction.objectStore(storeName);

        const request = store.get(id);

        request.onsuccess = () => {
            resolve(request.result ?? null);
        };

        request.onerror = event => {
            reject(event.target.error);
        };

    });
}


export async function getAllOffline(storeName) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readonly'
        );

        const store = transaction.objectStore(storeName);

        const request = store.getAll();

        request.onsuccess = () => {
            resolve(request.result);
        };

        request.onerror = event => {
            reject(event.target.error);
        };

    });
}


export async function deleteOffline(storeName, id) {

    const db = await openOfflineDB();

    return new Promise((resolve, reject) => {

        const transaction = db.transaction(
            storeName,
            'readwrite'
        );

        const store = transaction.objectStore(storeName);

        const request = store.delete(id);

        request.onsuccess = () => resolve();

        request.onerror = event => {
            reject(event.target.error);
        };

    });
}