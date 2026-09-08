import {
    getAllOffline,
    getOffline,
    saveOffline,
    deleteOffline
} from './offline-db';

import {
    getCsrfToken
} from './offline-utils';


/*
|--------------------------------------------------------------------------
| Synchronization Lock
|--------------------------------------------------------------------------
|
| Only one synchronization process may run at a time.
|
*/
let isSynchronizing = false;


/*
|--------------------------------------------------------------------------
| Sync Queue Store
|--------------------------------------------------------------------------
*/

const SYNC_QUEUE_STORE =
    'sync_queue';

const SWINE_STORE =
    'swine';

const HEALTH_RECORD_STORE =
    'health_records';

const WEIGHT_RECORD_STORE =
    'weight_records';

const MOVEMENT_STORE =
    'movements';


/*
|--------------------------------------------------------------------------
| Get Pending Queue Records
|--------------------------------------------------------------------------
*/

async function getPendingSync() {

    const records =
        await getAllOffline(
            SYNC_QUEUE_STORE
        );

    return records.filter(
        record =>
            record.status === 'pending'
    );
}


/*
|--------------------------------------------------------------------------
| Update Queue Status
|--------------------------------------------------------------------------
*/

async function updateQueueStatus(
    record,
    status,
    additionalData = {}
) {

    await saveOffline(
        SYNC_QUEUE_STORE,
        {
            ...record,
            status,
            ...additionalData
        }
    );
}


/*
|--------------------------------------------------------------------------
| Mark Local Record as Synced
|--------------------------------------------------------------------------
*/

async function markLocalRecordAsSynced(
    record,
    responseData
) {

    const payload =
        record.payload || {};


    /*
     * Weight record
     */
    if (
        record.type ===
        'weight_record'
    ) {

        const localId =
            payload.local_id;

        if (!localId) {
            return;
        }


        const localRecord =
            await getOffline(
                WEIGHT_RECORD_STORE,
                localId
            );

        if (!localRecord) {
            return;
        }


        await saveOffline(
            WEIGHT_RECORD_STORE,
            {
                ...localRecord,

                sync_status:
                    'synced',

                synced_at:
                    new Date().toISOString(),

                server_id:
                    responseData
                        ?.weight_record_id
                        ?? null

            }
        );

        return;
    }


    /*
     * Health record
     */
    if (
        record.type ===
        'health_record'
    ) {

        const localId =
            payload.local_id;

        if (!localId) {
            return;
        }


        const localRecord =
            await getOffline(
                HEALTH_RECORD_STORE,
                localId
            );

        if (!localRecord) {
            return;
        }


        await saveOffline(
            HEALTH_RECORD_STORE,
            {
                ...localRecord,

                sync_status:
                    'synced',

                synced_at:
                    new Date().toISOString(),

                server_id:
                    responseData
                        ?.health_record_id
                        ?? null

            }
        );

        return;
    }


    /*
     * Movement
     */
    if (
        record.type ===
        'swine_movement'
    ) {

        const localId =
            payload.local_id;

        if (!localId) {
            return;
        }


        const localRecord =
            await getOffline(
                MOVEMENT_STORE,
                localId
            );

        if (!localRecord) {
            return;
        }


        await saveOffline(
            MOVEMENT_STORE,
            {
                ...localRecord,

                sync_status:
                    'synced',

                synced_at:
                    new Date().toISOString(),

                server_id:
                    responseData
                        ?.movement_id
                        ?? null

            }
        );

        return;
    }


    /*
     * Swine update
     */
    if (
        record.type ===
        'swine_update'
    ) {

        const localId =
            payload.local_id;

        if (!localId) {
            return;
        }


        const localSwine =
            await getOffline(
                SWINE_STORE,
                localId
            );

        if (!localSwine) {
            return;
        }


        await saveOffline(
            SWINE_STORE,
            {
                ...localSwine,

                sync_status:
                    'synced',

                synced_at:
                    new Date().toISOString(),

                conflict:
                    false,

                conflict_data:
                    null

            }
        );

    }

}


/*
|--------------------------------------------------------------------------
| Synchronize Pending Records
|--------------------------------------------------------------------------
*/

export async function syncPendingRecords() {

    if (navigator.locks?.request) {

        return navigator.locks.request(
            'swine-locate-sync',
            { ifAvailable: true },
            lock => lock
                ? syncPendingRecordsInternal()
                : 0
        );

    }

    return syncPendingRecordsInternal();
}


async function syncPendingRecordsInternal() {

    /*
     * Prevent two synchronization processes
     * from running simultaneously.
     */
    if (isSynchronizing) {

        console.log(
            'Synchronization already in progress.'
        );

        return;

    }


    /*
     * Don't synchronize while offline.
     */
    if (!navigator.onLine) {

        console.log(
            'Offline. Synchronization skipped.'
        );

        return;

    }


    isSynchronizing = true;


    let synchronizedCount = 0;


    try {

        const pendingRecords =
            await getPendingSync();


        if (
            pendingRecords.length === 0
        ) {

            console.log(
                'No pending records to synchronize.'
            );

            return;

        }


        const csrfToken =
            getCsrfToken();


        if (!csrfToken) {

            console.error(
                'CSRF token not found.'
            );

            return;

        }


        console.log(
            `Starting synchronization of ${pendingRecords.length} record(s).`
        );


        /*
         * Process one queue record at a time.
         *
         * This is intentional.
         *
         * It prevents multiple requests from
         * being sent simultaneously from this
         * synchronization engine.
         */
        for (
            const record
            of pendingRecords
        ) {

            /*
             * Make sure the device is still online.
             */
            if (!navigator.onLine) {

                console.log(
                    'Connection lost during synchronization.'
                );

                break;

            }


            /*
             * Mark the queue record as syncing.
             */
            await updateQueueStatus(
                record,
                'syncing'
            );


            try {

                const payload =
                    record.payload || {};


                console.log(
                    'Synchronizing record:',
                    record
                );


                const response =
                    await fetch(
                        record.endpoint,
                        {
                            method:
                                record.method || 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest'

                            },

                            credentials:
                                'same-origin',

                            body:
                                JSON.stringify(
                                    payload
                                )
                        }
                    );


                /*
                 * Read JSON response.
                 */
                let responseData = {};

                try {

                    responseData =
                        await response.json();

                } catch (error) {

                    responseData = {};

                }


                /*
                 * Successful synchronization.
                 *
                 * This includes:
                 *
                 * 201 Created
                 *
                 * and
                 *
                 * 200 Already Synced
                 */
                if (
                    response.ok
                ) {

                    console.log(
                        'Record synchronized successfully:',
                        responseData
                    );


                    /*
                     * Mark the corresponding
                     * local record as synced.
                     */
                    await markLocalRecordAsSynced(
                        record,
                        responseData
                    );


                    /*
                     * Remove the successfully
                     * processed queue item.
                     */
                    await deleteOffline(
                        SYNC_QUEUE_STORE,
                        Number(record.id)
                    );


                    synchronizedCount++;

                    continue;
                }


                /*
                 * Conflict response.
                 */
                if (
                    response.status === 409
                ) {

                    console.warn(
                        'Synchronization conflict:',
                        responseData
                    );


                    await updateQueueStatus(
                        record,
                        'conflict',
                        {
                            server_data:
                                responseData
                                    ?.server_data
                                    ?? null,

                            offline_data:
                                responseData
                                    ?.offline_data
                                    ?? payload,

                            server_movement_id:
                                responseData
                                    ?.server_movement_id
                                    ?? null,

                            conflict_at:
                                new Date()
                                    .toISOString()
                        }
                    );


                    continue;
                }


                /*
                 * Validation error.
                 */
                if (
                    response.status === 422
                ) {

                    console.error(
                        'Synchronization validation error:',
                        responseData
                    );


                    await updateQueueStatus(
                        record,
                        'pending',
                        {
                            last_error:
                                responseData
                                    ?.message
                                    ??
                                    'Validation failed.',

                            last_error_at:
                                new Date()
                                    .toISOString()
                        }
                    );


                    continue;
                }


                /*
                 * Unauthorized.
                 */
                if (
                    response.status === 401 ||
                    response.status === 419
                ) {

                    console.error(
                        'Authentication or CSRF error during synchronization.'
                    );


                    await updateQueueStatus(
                        record,
                        'pending',
                        {
                            last_error:
                                'Authentication or CSRF error.',

                            last_error_at:
                                new Date()
                                    .toISOString()
                        }
                    );


                    continue;
                }


                /*
                 * Other server error.
                 */
                console.error(
                    'Synchronization failed:',
                    response.status,
                    responseData
                );


                await updateQueueStatus(
                    record,
                    'pending',
                    {
                        last_error:
                            responseData
                                ?.message
                                ??
                                `Server returned ${response.status}.`,

                        last_error_at:
                            new Date()
                                .toISOString()
                    }
                );

            } catch (error) {

                console.error(
                    'Synchronization request failed:',
                    error
                );


                /*
                 * Put the record back into pending
                 * so it can be retried later.
                 */
                await updateQueueStatus(
                    record,
                    'pending',
                    {
                        last_error:
                            error.message
                            ??
                            'Network error.',

                        last_error_at:
                            new Date()
                                .toISOString()
                    }
                );

            }

        }


        console.log(
            `Synchronization completed. ${synchronizedCount} record(s) synchronized.`
        );


    } catch (error) {

        console.error(
            'Synchronization process failed:',
            error
        );

    } finally {

        /*
         * Always release the lock.
         */
        isSynchronizing = false;

    }


    return synchronizedCount;
}


/*
|--------------------------------------------------------------------------
| Internet Connection Restored
|--------------------------------------------------------------------------
*/

window.addEventListener(
    'online',
    async function () {

        console.log(
            'Internet connection restored.'
        );


        await syncPendingRecords();

    }
);


/*
|--------------------------------------------------------------------------
| Initial Synchronization
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    async function () {

        if (navigator.onLine) {

            await syncPendingRecords();

        }

    }
);


/*
|--------------------------------------------------------------------------
| Global Access
|--------------------------------------------------------------------------
|
| sync-status.js can use:
|
| window.SwineLocateOffline.syncPendingRecords()
|
*/

window.SwineLocateOffline = {

    syncPendingRecords

};