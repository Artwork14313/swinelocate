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
| Mark Local Record As Synced
|--------------------------------------------------------------------------
*/

async function markLocalRecordAsSynced(record) {

    /*
     * Movement
     */
    if (record.type === 'movement') {

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

    }


    /*
     * Health Record
     */
    else if (
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

    }


    /*
     * Weight Record
     */
    else if (
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

    }

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


        if (pendingRecords.length === 0) {

            console.log(
                'No pending records to synchronize.'
            );

            return;

        }


        console.log(
            `Found ${pendingRecords.length} pending record(s).`
        );


        /*
         * Process each pending record.
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
                                    record.payload ??
                                    {}
                                ),

                        }
                    );


                /*
                 * Read response.
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
                 * Laravel successfully
                 * processed the record.
                 */
                if (response.ok) {

                    /*
                     * Update local record.
                     */
                    await markLocalRecordAsSynced(
                        record
                    );


                    /*
                     * Remove from queue.
                     */
                    await removeFromSyncQueue(
                        record.id
                    );


                    /*
                     * IMPORTANT:
                     * Increase successful count.
                     */
                    synchronizedCount++;


                    console.log(
                        'Successfully synchronized record:',
                        record.id
                    );

                }


                /*
                 * Laravel rejected the record.
                 */
                else {

                    console.error(
                        'Synchronization failed:',
                        response.status,
                        responseText
                    );

                }


            } catch (error) {

                console.error(
                    'Synchronization error:',
                    error
                );

            }

        }


        /*
         * Save synchronization time
         * only if at least one record
         * was successfully synchronized.
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