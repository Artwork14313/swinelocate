
import {
    getAllOffline,
    getOffline,
    saveOffline,
    deleteOffline,
} from './offline-db';


let isSynchronizing = false;


/*
|--------------------------------------------------------------------------
| Get Pending Synchronization Records
|--------------------------------------------------------------------------
*/

async function getPendingSync() {

    const records =
        await getAllOffline(
            'sync_queue'
        );

    return records.filter(
        record =>
            record.status === 'pending'
    );

}


/*
|--------------------------------------------------------------------------
| Remove Successfully Synchronized Queue Record
|--------------------------------------------------------------------------
*/

async function removeFromSyncQueue(id) {

    await deleteOffline(
        'sync_queue',
        id
    );

}


/*
|--------------------------------------------------------------------------
| Update Queue Status
|--------------------------------------------------------------------------
*/

async function updateQueueStatus(
    id,
    status,
    extra = {}
) {

    const record =
        await getOffline(
            'sync_queue',
            id
        );

    if (!record) {

        console.warn(
            'Sync queue record not found:',
            id
        );

        return;

    }


    record.status = status;

    Object.assign(
        record,
        extra
    );


    await saveOffline(
        'sync_queue',
        record
    );

}


/*
|--------------------------------------------------------------------------
| Find Local Swine
|--------------------------------------------------------------------------
|
| A locally-created swine may have a UUID as its IndexedDB key,
| while an existing server swine normally has a numeric database ID.
|
| Therefore, do not assume that:
|
| getOffline('swine', Number(swineId))
|
| will always work.
|
*/

async function findLocalSwine(swineId) {

    if (!swineId) {
        return null;
    }


    /*
     * First try the normal numeric ID.
     */
    const numericId =
        Number(swineId);


    if (!Number.isNaN(numericId)) {

        const direct =
            await getOffline(
                'swine',
                numericId
            );

        if (direct) {
            return direct;
        }

    }


    /*
     * If not found, search all local swine.
     */
    const swineRecords =
        await getAllOffline(
            'swine'
        );


    return swineRecords.find(
        swine =>
            String(swine.id) ===
                String(swineId)
            ||
            String(swine.swine_id) ===
                String(swineId)
    ) ?? null;

}


/*
|--------------------------------------------------------------------------
| Mark Local Record As Synced
|--------------------------------------------------------------------------
*/

async function markLocalRecordAsSynced(record) {

    /*
     * Movement
     */
    if (
        record.type === 'movement'
    ) {

        const localId =
            record.payload?.local_id;

        if (!localId) {

            console.warn(
                'Movement has no local_id:',
                record
            );

            return;
        }


        const movement =
            await getOffline(
                'movements',
                localId
            );

        if (!movement) {

            console.warn(
                'Local movement not found:',
                localId
            );

            return;
        }


        movement.sync_status =
            'synced';

        movement.synced_at =
            new Date().toISOString();


        await saveOffline(
            'movements',
            movement
        );


        console.log(
            'Movement marked as synced:',
            localId
        );

        return;

    }


    /*
     * Health Record
     */
    if (
        record.type === 'health_record'
    ) {

        const localId =
            record.payload?.local_id;

        if (!localId) {
            return;
        }


        const healthRecord =
            await getOffline(
                'health_records',
                localId
            );

        if (!healthRecord) {
            return;
        }


        healthRecord.sync_status =
            'synced';

        healthRecord.synced_at =
            new Date().toISOString();


        await saveOffline(
            'health_records',
            healthRecord
        );


        console.log(
            'Health record marked as synced:',
            localId
        );

        return;

    }


    /*
     * Weight Record
     */
    if (
        record.type === 'weight_record'
    ) {

        const localId =
            record.payload?.local_id;

        if (!localId) {
            return;
        }


        const weightRecord =
            await getOffline(
                'weight_records',
                localId
            );

        if (!weightRecord) {
            return;
        }


        weightRecord.sync_status =
            'synced';

        weightRecord.synced_at =
            new Date().toISOString();


        await saveOffline(
            'weight_records',
            weightRecord
        );


        console.log(
            'Weight record marked as synced:',
            localId
        );

        return;

    }


    /*
     * Swine Update
     */
    if (
        record.type === 'swine_update'
    ) {

        const swineId =
            record.payload?.swine_id ??
            record.payload?.id;


        if (!swineId) {

            console.warn(
                'Swine update has no swine ID:',
                record
            );

            return;

        }


        const swine =
            await findLocalSwine(
                swineId
            );


        if (!swine) {

            console.warn(
                'Local swine not found:',
                swineId
            );

            return;

        }


        swine.sync_status =
            'synced';

        swine.synced_at =
            new Date().toISOString();


        /*
         * Remove conflict information if
         * this record was previously conflicted.
         */
        delete swine.conflict_at;

        delete swine.conflict_data;


        await saveOffline(
            'swine',
            swine
        );


        console.log(
            'Swine update marked as synced:',
            swineId
        );

    }

}


/*
|--------------------------------------------------------------------------
| Mark Local Swine As Conflict
|--------------------------------------------------------------------------
*/

async function markSwineAsConflict(
    record,
    serverData = null
) {

    if (
        record.type !== 'swine_update'
    ) {
        return;
    }


    const swineId =
        record.payload?.swine_id ??
        record.payload?.id;


    if (!swineId) {

        console.warn(
            'Cannot mark swine conflict because swine ID is missing:',
            record
        );

        return;

    }


    const swine =
        await findLocalSwine(
            swineId
        );


    if (!swine) {

        console.warn(
            'Cannot mark local swine as conflict. Local record not found:',
            swineId
        );

        return;

    }


    swine.sync_status =
        'conflict';

    swine.conflict_at =
        new Date().toISOString();


    /*
     * Save the server version locally.
     *
     * This allows the Sync Status page to display
     * offline values versus server values.
     */
    if (serverData) {

        swine.conflict_data = {

            server:
                serverData,

            offline:
                {
                    ...record.payload
                }

        };

    }


    await saveOffline(
        'swine',
        swine
    );


    console.log(
        'Local swine marked as conflict:',
        swineId
    );

}


/*
|--------------------------------------------------------------------------
| Save Latest Successful Synchronization Time
|--------------------------------------------------------------------------
*/

function saveLastSync() {

    localStorage.setItem(
        'swine_locate_last_sync',
        new Date().toISOString()
    );

}


/*
|--------------------------------------------------------------------------
| Synchronize Pending Records With Laravel
|--------------------------------------------------------------------------
*/

export async function syncPendingRecords() {

    /*
     * Prevent duplicate synchronization.
     */
    if (isSynchronizing) {

        console.log(
            'Synchronization already in progress.'
        );

        return;

    }


    /*
     * Do not synchronize while offline.
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

        console.log(
            'Checking for pending offline records...'
        );


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


        console.log(
            `Found ${pendingRecords.length} pending record(s).`
        );


        /*
         * Process every pending record.
         */
        for (
            const record of pendingRecords
        ) {

            try {

                console.log(
                    'Synchronizing:',
                    record
                );


                /*
                 * Get CSRF token.
                 */
                const csrfToken =
                    document
                        .querySelector(
                            'meta[name="csrf-token"]'
                        )
                        ?.getAttribute(
                            'content'
                        );


                /*
                 * Make sure payload exists.
                 */
                const payload =
                    record.payload ?? {};


                /*
                 * IMPORTANT:
                 *
                 * Swine updates require swine_id.
                 *
                 * If the queue record somehow does not have
                 * swine_id but the endpoint contains:
                 *
                 * /swine/4/sync
                 *
                 * extract the ID from the URL.
                 */
                if (
                    record.type === 'swine_update' &&
                    !payload.swine_id
                ) {

                    const match =
                        record.endpoint?.match(
                            /\/swine\/(\d+)\/sync/
                        );


                    if (match) {

                        payload.swine_id =
                            Number(match[1]);

                        record.payload =
                            payload;


                        console.log(
                            'Recovered swine_id from endpoint:',
                            payload.swine_id
                        );

                    }

                }


                /*
                 * Send request to Laravel.
                 */
                const response =
                    await fetch(
                        record.endpoint,
                        {
                            method:
                                record.method ??
                                'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest',

                            },

                            body:
                                JSON.stringify(
                                    payload
                                ),

                        }
                    );


                /*
                 * Read server response.
                 */
                const responseText =
                    await response.text();


                console.log(
                    'Sync response:',
                    response.status,
                    response.url,
                    responseText
                );


                /*
                 |--------------------------------------------------------------------------
                 | SUCCESS
                 |--------------------------------------------------------------------------
                 */

                if (response.ok) {

                    await markLocalRecordAsSynced(
                        record
                    );


                    await removeFromSyncQueue(
                        record.id
                    );


                    synchronizedCount++;


                    console.log(
                        'Successfully synchronized record:',
                        record.id
                    );


                    continue;

                }


                /*
                 |--------------------------------------------------------------------------
                 | CONFLICT
                 |--------------------------------------------------------------------------
                 |
                 | Laravel returns HTTP 409 when another user
                 | modified the same swine while this device
                 | was offline.
                 |
                 | DO NOT DELETE THE QUEUE RECORD.
                 |
                 */

                if (
                    response.status === 409
                ) {

                    console.warn(
                        'Synchronization conflict detected:',
                        record.id
                    );


                    let serverResponse = null;


                    /*
                     * Convert JSON response into an object.
                     */
                    try {

                        serverResponse =
                            JSON.parse(
                                responseText
                            );

                    } catch (parseError) {

                        console.error(
                            'Unable to parse conflict response:',
                            parseError
                        );

                    }


                    /*
                     * Extract server version.
                     */
                    const serverData =
                        serverResponse?.server_data ??
                        null;


                    /*
                     * Extract swine ID.
                     */
                    const swineId =
                        serverResponse?.swine_id ??
                        record.payload?.swine_id ??
                        record.payload?.id;


                    /*
                     * Save detailed conflict information
                     * in the synchronization queue.
                     */
                    await updateQueueStatus(
                        record.id,
                        'conflict',
                        {

                            conflict_at:
                                new Date().toISOString(),

                            error_message:
                                serverResponse?.message ??
                                'This record was modified by another user while this device was offline.',

                            server_response:
                                responseText,

                            server_data:
                                serverData,

                            swine_id:
                                swineId,

                        }
                    );


                    /*
                     * Mark the local swine as conflicted.
                     */
                    await markSwineAsConflict(
                        record,
                        serverData
                    );


                    console.warn(
                        'Conflict preserved in IndexedDB:',
                        {
                            queue_id:
                                record.id,

                            swine_id:
                                swineId,

                            offline_data:
                                record.payload,

                            server_data:
                                serverData

                        }
                    );


                    continue;

                }


                /*
                 |--------------------------------------------------------------------------
                 | VALIDATION ERROR
                 |--------------------------------------------------------------------------
                 */

                if (
                    response.status === 422
                ) {

                    console.error(
                        'Validation error during synchronization:',
                        responseText
                    );


                    await updateQueueStatus(
                        record.id,
                        'failed',
                        {

                            failed_at:
                                new Date().toISOString(),

                            error_message:
                                'The server rejected the record because of validation errors.',

                            server_response:
                                responseText,

                        }
                    );


                    continue;

                }


                /*
                 |--------------------------------------------------------------------------
                 | OTHER SERVER ERROR
                 |--------------------------------------------------------------------------
                 */

                console.error(
                    'Synchronization failed:',
                    response.status,
                    responseText
                );


                /*
                 * Keep the queue record pending so it
                 * can be retried later.
                 */

            } catch (error) {

                console.error(
                    'Synchronization error:',
                    error
                );

            }

        }


        /*
         |--------------------------------------------------------------------------
         | Synchronization Summary
         |--------------------------------------------------------------------------
         */

        if (
            synchronizedCount > 0
        ) {

            saveLastSync();


            console.log(
                `Synchronization completed. ${synchronizedCount} record(s) synchronized.`
            );

        } else {

            console.log(
                'Synchronization completed. No records were synchronized.'
            );

        }


    } finally {

        isSynchronizing = false;

    }

}


/*
|--------------------------------------------------------------------------
| Internet Connection Restored
|--------------------------------------------------------------------------
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


/*
|--------------------------------------------------------------------------
| Try Synchronization When Application Loads
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    async () => {

        await syncPendingRecords();

    }
);


/*
|--------------------------------------------------------------------------
| Expose Synchronization Function
|--------------------------------------------------------------------------
*/

window.SwineLocateOffline = {

    syncPendingRecords,

};

