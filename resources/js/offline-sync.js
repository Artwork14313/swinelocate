import {
    getAllOffline,
    deleteOffline,
} from './offline-db';

let isSynchronizing = false;

/**
 * Get pending synchronization records.
 */
async function getPendingSync() {

    const records = await getAllOffline(
        'sync_queue'
    );

    return records.filter(
        record => record.status === 'pending'
    );
}


/**
 * Remove successfully synchronized record.
 */
async function removeFromSyncQueue(id) {

    await deleteOffline(
        'sync_queue',
        id
    );

}

//save last sync date
function saveLastSync() {

    localStorage.setItem(
        'swine_locate_last_sync',
        new Date().toISOString()
    );

}

/**
 * Synchronize pending records with Laravel.
 */
export async function syncPendingRecords() {

    if (isSynchronizing) {
        console.log('Synchronization already in progress.');
        return;
    }

    if (!navigator.onLine) {
        console.log('Offline. Synchronization skipped.');
        return;
    }

    isSynchronizing = true;

    try {

        console.log(
            'Checking for pending offline records...'
        );

        const pendingRecords =
            await getPendingSync();

        if (pendingRecords.length === 0) {
            console.log(
                'No pending records to synchronize.'
            );
            return;
        }

        console.log(
            `Found ${pendingRecords.length} pending record(s).`
        );

        for (const record of pendingRecords) {

            try {

                console.log(
                    'Synchronizing:',
                    record
                );

                const response = await fetch(
                    record.endpoint,
                    {
                        method: record.method ?? 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    ?.getAttribute(
                                        'content'
                                    ),
                        },

                        body: JSON.stringify(
                            record.payload ?? {}
                        ),
                    }
                );
                if (response.ok) {

                    await removeFromSyncQueue(record.id);

                    saveLastSync();

                    console.log(
                        'Successfully synchronized record:',
                        record.id
                    );

                } else {
                    const errorText =
                        await response.text();


                    console.error(
                        'Synchronization failed:',
                        response.status,
                        errorText
                    );
                }

            } catch (error) {

                console.error(
                    'Synchronization error:',
                    error
                );
            }
        }

    } finally {

        isSynchronizing = false;

    }
}


/**
 * Internet connection restored.
 */
window.addEventListener(
    'online',
    async () => {

        console.log(
            'Internet connection restored.'
        );

        await syncPendingRecords();

    }
);


/**
 * Try synchronization when application loads.
 */
document.addEventListener(
    'DOMContentLoaded',
    async () => {

        await syncPendingRecords();

    }
);


/**
 * Expose synchronization functions.
 */
window.SwineLocateOffline = {

    syncPendingRecords,

};