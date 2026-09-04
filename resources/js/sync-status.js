import {
    getAllOffline,
    getOffline,
    saveOffline,
    deleteOffline
} from './offline-db';


// ============================================================
// CONSTANTS
// ============================================================

const SYNC_QUEUE_STORE = 'sync_queue';
const SWINE_STORE = 'swine';


// ============================================================
// CONNECTION STATUS
// ============================================================

function updateConnectionStatus() {

    const statusElement =
        document.getElementById('connection-status');

    const indicatorElement =
        document.getElementById('connection-indicator');


    if (!statusElement) return;


    if (navigator.onLine) {

        statusElement.textContent = 'Online';

        statusElement.classList.remove(
            'text-red-600',
            'text-gray-900'
        );

        statusElement.classList.add(
            'text-green-600'
        );


        if (indicatorElement) {

            indicatorElement.classList.remove(
                'bg-red-500',
                'bg-gray-400'
            );

            indicatorElement.classList.add(
                'bg-green-500'
            );
        }

    } else {

        statusElement.textContent = 'Offline';

        statusElement.classList.remove(
            'text-green-600',
            'text-gray-900'
        );

        statusElement.classList.add(
            'text-red-600'
        );


        if (indicatorElement) {

            indicatorElement.classList.remove(
                'bg-green-500',
                'bg-gray-400'
            );

            indicatorElement.classList.add(
                'bg-red-500'
            );
        }
    }
}


// ============================================================
// LAST SYNC
// ============================================================

function updateLastSync() {

    const element =
        document.getElementById('last-sync');

    if (!element) return;


    element.textContent =
        new Date().toLocaleString();
}


// ============================================================
// CSRF TOKEN
// ============================================================

function getCsrfToken() {

    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
}


// ============================================================
// PARSE SERVER RESPONSE
// ============================================================

function parseServerResponse(record) {

    if (!record?.server_response) {
        return null;
    }


    try {

        return typeof record.server_response === 'string'
            ? JSON.parse(record.server_response)
            : record.server_response;

    } catch (error) {

        console.error(
            'Unable to parse server response:',
            error
        );

        return null;
    }
}


// ============================================================
// GET SERVER DATA
// ============================================================

function getServerData(record) {

    const response =
        parseServerResponse(record);


    return (
        record?.server_data ??
        response?.server_data ??
        null
    );
}


// ============================================================
// GET SERVER MOVEMENT ID
// ============================================================

function getServerMovementId(record) {

    const response =
        parseServerResponse(record);


    return (
        record?.server_movement_id ??
        response?.server_movement_id ??
        response?.server_data?.movement_id ??
        response?.server_data?.id ??
        null
    );
}


// ============================================================
// LOCATION LABEL
// ============================================================

function locationLabel(locationId) {

    if (
        locationId === null ||
        locationId === undefined ||
        locationId === ''
    ) {
        return 'Not specified';
    }


    return `Location ${locationId}`;
}


// ============================================================
// ACTUAL SYNCHRONIZATION
// ============================================================

async function syncPendingRecords() {

    if (!navigator.onLine) {

        console.log(
            'Synchronization skipped because device is offline.'
        );

        return {
            successful: 0,
            conflicts: 0,
            failed: 0
        };
    }


    console.log(
        'Checking IndexedDB sync queue...'
    );


    const records =
        await getAllOffline(
            SYNC_QUEUE_STORE
        );


    console.log(
        'All sync queue records:',
        records
    );


    const pendingRecords =
        records.filter(
            record =>
                record.status === 'pending'
        );


    console.log(
        'Pending records found:',
        pendingRecords.length
    );


    if (pendingRecords.length === 0) {

        console.log(
            'No pending records to synchronize.'
        );

        return {
            successful: 0,
            conflicts: 0,
            failed: 0
        };
    }


    const csrfToken =
        getCsrfToken();


    if (!csrfToken) {

        throw new Error(
            'CSRF token was not found. Please refresh the page.'
        );
    }


    let successfulSync = 0;
    let conflictCount = 0;
    let failedCount = 0;


    for (const record of pendingRecords) {

        try {

            console.log(
                'Synchronizing queue record:',
                record.id,
                record
            );


            // ------------------------------------------------
            // MARK AS SYNCING
            // ------------------------------------------------

            record.status =
                'syncing';


            await saveOffline(
                SYNC_QUEUE_STORE,
                record
            );


            // ------------------------------------------------
            // SEND TO LARAVEL
            // ------------------------------------------------

            const response =
                await fetch(
                    record.endpoint,
                    {
                        method:
                            record.method ?? 'POST',

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

                        body:
                            JSON.stringify(
                                record.payload ?? {}
                            )
                    }
                );


            const responseText =
                await response.text();


            let result = null;


            try {

                result =
                    responseText
                        ? JSON.parse(responseText)
                        : null;

            } catch (error) {

                console.error(
                    'Unable to parse synchronization response:',
                    responseText
                );
            }


            console.log(
                'Synchronization response:',
                {
                    queueId: record.id,
                    httpStatus: response.status,
                    ok: response.ok,
                    result
                }
            );


            // =================================================
            // CONFLICT
            // =================================================

            if (response.status === 409) {

                record.status =
                    'conflict';


                record.conflict_at =
                    new Date().toISOString();


                record.error_message =
                    result?.message ??
                    'Synchronization conflict detected.';


                record.server_response =
                    responseText;


                record.server_data =
                    result?.server_data ??
                    null;


                record.server_movement_id =
                    result?.server_movement_id ??
                    result?.server_data?.movement_id ??
                    result?.server_data?.id ??
                    null;


                await saveOffline(
                    SYNC_QUEUE_STORE,
                    record
                );


                conflictCount++;


                console.warn(
                    'Movement synchronization conflict:',
                    {
                        queueId: record.id,
                        serverMovementId:
                            record.server_movement_id,
                        serverData:
                            record.server_data
                    }
                );


                continue;
            }


            // =================================================
            // OTHER SERVER ERROR
            // =================================================

            if (!response.ok) {

                record.status =
                    'pending';


                record.error_message =
                    result?.message ??
                    `Server returned HTTP ${response.status}.`;


                record.last_attempt_at =
                    new Date().toISOString();


                await saveOffline(
                    SYNC_QUEUE_STORE,
                    record
                );


                failedCount++;


                console.error(
                    'Synchronization failed:',
                    {
                        queueId: record.id,
                        status: response.status,
                        result
                    }
                );


                continue;
            }


            // =================================================
            // SUCCESS
            // =================================================

            console.log(
                'Synchronization successful:',
                record.id,
                result
            );


            // ------------------------------------------------
            // UPDATE LOCAL MOVEMENT/SWINE
            // ------------------------------------------------

            if (
                record.type ===
                'movement'
            ) {

                const swineId =
                    record.payload?.swine_id;


                const destinationId =
                    record.payload?.to_location_id;


                if (
                    swineId &&
                    destinationId
                ) {

                    const localSwine =
                        await getOffline(
                            SWINE_STORE,
                            Number(swineId)
                        );


                    if (localSwine) {

                        localSwine.current_location_id =
                            Number(destinationId);


                        localSwine.sync_status =
                            'synced';


                        await saveOffline(
                            SWINE_STORE,
                            localSwine
                        );


                        console.log(
                            'Local swine location updated:',
                            {
                                swineId,
                                destinationId
                            }
                        );
                    }
                }


                // ------------------------------------------------
                // MARK LOCAL MOVEMENT AS SYNCED
                // ------------------------------------------------

                const localMovementId =
                    record.payload?.local_id;


                if (localMovementId) {

                    const localMovement =
                        await getOffline(
                            'movements',
                            localMovementId
                        );


                    if (localMovement) {

                        localMovement.sync_status =
                            'synced';


                        localMovement.server_id =
                            result?.movement?.id ??
                            result?.movement_id ??
                            result?.id ??
                            null;


                        await saveOffline(
                            'movements',
                            localMovement
                        );
                    }
                }
            }


            // ------------------------------------------------
            // REMOVE SUCCESSFULLY SYNCHRONIZED QUEUE ITEM
            // ------------------------------------------------

            await deleteOffline(
                SYNC_QUEUE_STORE,
                Number(record.id)
            );


            successfulSync++;


        } catch (error) {

            console.error(
                'Error synchronizing queue record:',
                record.id,
                error
            );


            record.status =
                'pending';


            record.error_message =
                error?.message ??
                'Unable to synchronize record.';


            record.last_attempt_at =
                new Date().toISOString();


            await saveOffline(
                SYNC_QUEUE_STORE,
                record
            );


            failedCount++;
        }
    }


    if (successfulSync > 0) {

        updateLastSync();
    }


    console.log(
        'Synchronization summary:',
        {
            successful: successfulSync,
            conflicts: conflictCount,
            failed: failedCount
        }
    );


    return {
        successful: successfulSync,
        conflicts: conflictCount,
        failed: failedCount
    };
}


// ============================================================
// MOVEMENT CONFLICT
// ============================================================

function renderMovementConflict(
    record,
    container
) {

    const payload =
        record.payload ?? {};


    const serverData =
        getServerData(record);


    const serverMovementId =
        getServerMovementId(record);


    const offlineDestination =
        payload.to_location_id;


    const serverDestination =
        serverData?.to_location_id ??
        serverData?.current_location_id;


    const wrapper =
        document.createElement('div');


    wrapper.className =
        'border border-red-300 bg-red-50 rounded-xl p-5 mb-4';


    wrapper.innerHTML = `

        <div class="flex flex-col gap-4">

            <div class="flex flex-col sm:flex-row
                        sm:items-start sm:justify-between gap-3">

                <div>

                    <div class="flex items-center gap-2">

                        <span class="inline-flex items-center
                                     rounded-full bg-red-100
                                     px-2.5 py-1 text-xs
                                     font-semibold text-red-700">

                            Conflict

                        </span>

                        <span class="text-sm font-semibold
                                     text-gray-900">

                            Swine Movement

                        </span>

                    </div>

                    <p class="mt-2 text-sm text-gray-600">

                        This swine was moved by another user
                        while this device was offline.

                    </p>

                </div>

                <div class="text-xs text-gray-500">

                    Queue ID:

                    <span class="font-semibold">
                        ${record.id}
                    </span>

                </div>

            </div>


            <!-- Versions -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


                <!-- Offline Version -->

                <div class="rounded-lg bg-white
                            border border-blue-200 p-4">

                    <div class="flex items-center
                                justify-between mb-3">

                        <h4 class="font-semibold text-blue-700">
                            Offline Version
                        </h4>

                        <span class="text-xs font-medium
                                     rounded-full bg-blue-100
                                     px-2 py-1 text-blue-700">

                            Local

                        </span>

                    </div>


                    <div class="space-y-2 text-sm">

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Swine ID
                            </span>

                            <span class="font-medium text-gray-900">
                                ${payload.swine_id ?? '-'}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                From
                            </span>

                            <span class="font-medium text-gray-900">
                                ${locationLabel(
        payload.from_location_id ??
        payload.original_location_id
    )}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                To
                            </span>

                            <span class="font-medium text-blue-700">
                                ${locationLabel(
        offlineDestination
    )}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Movement Date
                            </span>

                            <span class="font-medium text-gray-900">
                                ${payload.movement_date ?? '-'}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Reason
                            </span>

                            <span class="font-medium text-gray-900">
                                ${payload.reason ?? 'Not specified'}
                            </span>
                        </div>

                    </div>

                </div>


                <!-- Server Version -->

                <div class="rounded-lg bg-white
                            border border-gray-300 p-4">

                    <div class="flex items-center
                                justify-between mb-3">

                        <h4 class="font-semibold text-gray-700">
                            Server Version
                        </h4>

                        <span class="text-xs font-medium
                                     rounded-full bg-gray-100
                                     px-2 py-1 text-gray-700">

                            Online

                        </span>

                    </div>


                    <div class="space-y-2 text-sm">

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Swine ID
                            </span>

                            <span class="font-medium text-gray-900">
                                ${serverData?.swine_id ??
        payload.swine_id ??
        '-'}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                From
                            </span>

                            <span class="font-medium text-gray-900">
                                ${locationLabel(
            serverData?.from_location_id
        )}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                To
                            </span>

                            <span class="font-medium text-gray-700">
                                ${locationLabel(
            serverDestination
        )}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Movement Date
                            </span>

                            <span class="font-medium text-gray-900">
                                ${serverData?.movement_date ?? '-'}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Reason
                            </span>

                            <span class="font-medium text-gray-900">
                                ${serverData?.reason ??
        'Not specified'}
                            </span>
                        </div>


                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500">
                                Server Movement ID
                            </span>

                            <span class="font-semibold text-gray-900">
                                ${serverMovementId ?? '-'}
                            </span>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Actions -->

            <div class="border-t border-red-200 pt-4">

                <p class="text-xs text-gray-500 mb-3">

                    Choose which movement should become
                    the accepted version in the system.

                </p>


                <div class="flex flex-col sm:flex-row gap-3">

                    <button
                        type="button"
                        class="keep-server-btn inline-flex
                               items-center justify-center rounded-lg
                               bg-gray-700 px-4 py-2.5
                               text-sm font-semibold text-white
                               hover:bg-gray-800"
                        data-record-id="${record.id}"
                    >
                        Keep Server Version
                    </button>


                    <button
                        type="button"
                        class="keep-offline-btn inline-flex
                               items-center justify-center rounded-lg
                               bg-[#3368A0] px-4 py-2.5
                               text-sm font-semibold text-white
                               hover:bg-[#28557F]"
                        data-record-id="${record.id}"
                    >
                        Keep Offline Version
                    </button>

                </div>

            </div>

        </div>
    `;


    container.appendChild(wrapper);
}


// ============================================================
// NORMAL PENDING RECORD
// ============================================================

function renderPendingRecord(
    record,
    container
) {

    const wrapper =
        document.createElement('div');


    wrapper.className =
        'border border-gray-200 rounded-xl p-5 mb-4 bg-white';


    const payload =
        record.payload ?? {};


    wrapper.innerHTML = `

        <div class="flex flex-col sm:flex-row
                    sm:items-center sm:justify-between gap-3">

            <div>

                <div class="flex items-center gap-2">

                    <span class="inline-flex items-center
                                 rounded-full bg-yellow-100
                                 px-2.5 py-1 text-xs
                                 font-semibold text-yellow-700">

                        Pending

                    </span>


                    <span class="font-semibold text-gray-900">

                        ${record.type ?? 'Record'}

                    </span>

                </div>


                <p class="mt-2 text-sm text-gray-500">

                    Waiting for synchronization with the server.

                </p>


                <p class="mt-1 text-xs text-gray-400">

                    Created:
                    ${record.created_at ?? '-'}

                </p>

            </div>


            <div class="text-sm text-gray-500">

                Queue ID:

                <span class="font-semibold text-gray-700">
                    ${record.id}
                </span>

            </div>

        </div>
    `;


    container.appendChild(wrapper);
}


// ============================================================
// UPDATE COUNTS
// ============================================================

function updateCounts(records) {

    const pendingCount =
        records.filter(
            record =>
                record.status === 'pending'
        ).length;


    const conflictCount =
        records.filter(
            record =>
                record.status === 'conflict'
        ).length;


    const pendingElement =
        document.getElementById(
            'pending-count'
        );


    const conflictElement =
        document.getElementById(
            'conflict-count'
        );


    if (pendingElement) {

        pendingElement.textContent =
            pendingCount;
    }


    if (conflictElement) {

        conflictElement.textContent =
            conflictCount;
    }
}


// ============================================================
// KEEP SERVER VERSION
// ============================================================

async function keepServerVersion(recordId) {

    if (!navigator.onLine) {

        alert(
            'You are currently offline. Please reconnect before keeping the server version.'
        );

        return;
    }


    const confirmed =
        confirm(
            'Keep the server version?\n\n' +
            'The server movement will remain the accepted version and the offline movement will be discarded.'
        );


    if (!confirmed) {
        return;
    }


    try {

        const record =
            await getOffline(
                SYNC_QUEUE_STORE,
                Number(recordId)
            );


        if (!record) {

            alert(
                'The synchronization record could not be found.'
            );

            return;
        }


        const serverMovementId =
            getServerMovementId(record);


        if (!serverMovementId) {

            alert(
                'The server movement ID is missing.'
            );

            return;
        }


        const serverData =
            getServerData(record);


        if (!serverData) {

            alert(
                'The server movement data is missing.'
            );

            return;
        }


        const toLocationId =
            serverData.to_location_id ??
            serverData.current_location_id ??
            null;


        if (!toLocationId) {

            alert(
                'The server destination location is missing.'
            );

            console.error(
                'Missing server destination:',
                serverData
            );

            return;
        }


        const csrfToken =
            getCsrfToken();


        if (!csrfToken) {

            alert(
                'CSRF token was not found. Please refresh the page.'
            );

            return;
        }


        const endpoint =
            `/swine-movements/${serverMovementId}/resolve-conflict`;


        const resolvePayload = {

            resolution:
                'keep_online',

            server_movement_id:
                Number(serverMovementId),

            swine_id:
                Number(
                    serverData.swine_id ??
                    record.payload?.swine_id
                ),

            from_location_id:
                serverData.from_location_id ??
                null,

            to_location_id:
                Number(toLocationId),

            movement_date:
                serverData.movement_date ??
                record.payload?.movement_date,

            reason:
                serverData.reason ??
                null,

            notes:
                serverData.notes ??
                null
        };


        console.log(
            'Keeping server movement:',
            resolvePayload
        );


        const response =
            await fetch(
                endpoint,
                {
                    method: 'PUT',

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

                    body:
                        JSON.stringify(
                            resolvePayload
                        )
                }
            );


        const responseText =
            await response.text();


        let result = null;


        try {

            result =
                responseText
                    ? JSON.parse(responseText)
                    : null;

        } catch (error) {

            console.error(
                'Invalid server response:',
                responseText
            );
        }


        console.log(
            'Keep Server Version response:',
            {
                status: response.status,
                result
            }
        );


        if (!response.ok) {

            throw new Error(
                result?.message ??
                `Server returned HTTP ${response.status}.`
            );
        }


        // ----------------------------------------------------
        // UPDATE LOCAL SWINE
        // ----------------------------------------------------

        const swineId =
            record.payload?.swine_id ??
            serverData.swine_id;


        if (swineId) {

            const localSwine =
                await getOffline(
                    SWINE_STORE,
                    Number(swineId)
                );


            if (localSwine) {

                localSwine.current_location_id =
                    Number(toLocationId);


                localSwine.sync_status =
                    'synced';


                await saveOffline(
                    SWINE_STORE,
                    localSwine
                );
            }
        }


        // ----------------------------------------------------
        // REMOVE OFFLINE QUEUE RECORD
        // ----------------------------------------------------

        await deleteOffline(
            SYNC_QUEUE_STORE,
            Number(recordId)
        );


        alert(
            'The server version was kept successfully.\n\n' +
            'The offline movement was discarded.'
        );


        await loadPendingRecords();


    } catch (error) {

        console.error(
            'Keep Server Version error:',
            error
        );


        alert(
            'Unable to keep the server version.\n\n' +
            error.message
        );
    }
}


// ============================================================
// KEEP OFFLINE VERSION
// ============================================================

async function keepOfflineVersion(recordId) {

    if (!navigator.onLine) {

        alert(
            'You are currently offline. Please reconnect before resolving this conflict.'
        );

        return;
    }


    const confirmed =
        confirm(
            'Keep the offline version?\n\n' +
            'The server movement will be marked as superseded and the offline movement will become the accepted version.'
        );


    if (!confirmed) {
        return;
    }


    try {

        const record =
            await getOffline(
                SYNC_QUEUE_STORE,
                Number(recordId)
            );


        if (!record) {

            alert(
                'The synchronization record could not be found.'
            );

            return;
        }


        const payload =
            record.payload ?? {};


        const swineId =
            payload.swine_id;


        const serverMovementId =
            getServerMovementId(record);


        if (!swineId) {

            alert(
                'The swine ID is missing.'
            );

            return;
        }


        if (!serverMovementId) {

            alert(
                'The server movement ID is missing.'
            );

            return;
        }


        if (!payload.to_location_id) {

            alert(
                'The offline destination location is missing.'
            );

            return;
        }


        const csrfToken =
            getCsrfToken();


        if (!csrfToken) {

            alert(
                'CSRF token was not found. Please refresh the page.'
            );

            return;
        }


        const syncPayload = {

            swine_id:
                Number(swineId),

            local_id:
                payload.local_id ??
                record.id,

            original_location_id:
                payload.original_location_id ??
                null,

            from_location_id:
                payload.from_location_id ??
                payload.original_location_id ??
                null,

            to_location_id:
                Number(payload.to_location_id),

            movement_date:
                payload.movement_date,

            reason:
                payload.reason ??
                null,

            notes:
                payload.notes ??
                null,

            server_movement_id:
                Number(serverMovementId),

            force:
                true
        };


        console.log(
            'Keeping offline movement:',
            syncPayload
        );


        const response =
            await fetch(
                '/swine-movements/sync',
                {
                    method: 'POST',

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

                    body:
                        JSON.stringify(
                            syncPayload
                        )
                }
            );


        const responseText =
            await response.text();


        let result = null;


        try {

            result =
                responseText
                    ? JSON.parse(responseText)
                    : null;

        } catch (error) {

            console.error(
                'Invalid server response:',
                responseText
            );
        }


        if (!response.ok) {

            throw new Error(
                result?.message ??
                `Server returned HTTP ${response.status}.`
            );
        }


        const localSwine =
            await getOffline(
                SWINE_STORE,
                Number(swineId)
            );


        if (localSwine) {

            localSwine.current_location_id =
                Number(payload.to_location_id);


            localSwine.sync_status =
                'synced';


            await saveOffline(
                SWINE_STORE,
                localSwine
            );
        }


        await deleteOffline(
            SYNC_QUEUE_STORE,
            Number(recordId)
        );


        alert(
            'The offline movement version was successfully saved.\n\n' +
            'The online movement was marked as superseded.'
        );


        await loadPendingRecords();


    } catch (error) {

        console.error(
            'Keep Offline Version error:',
            error
        );


        alert(
            'Unable to keep the offline version.\n\n' +
            error.message
        );
    }
}


// ============================================================
// ATTACH CONFLICT BUTTONS
// ============================================================

function attachConflictButtons() {

    document
        .querySelectorAll('.keep-server-btn')
        .forEach(button => {

            button.addEventListener(
                'click',
                async function () {

                    const recordId =
                        this.dataset.recordId;


                    this.disabled =
                        true;


                    await keepServerVersion(
                        recordId
                    );


                    this.disabled =
                        false;
                }
            );
        });


    document
        .querySelectorAll('.keep-offline-btn')
        .forEach(button => {

            button.addEventListener(
                'click',
                async function () {

                    const recordId =
                        this.dataset.recordId;


                    this.disabled =
                        true;


                    await keepOfflineVersion(
                        recordId
                    );


                    this.disabled =
                        false;
                }
            );
        });
}


// ============================================================
// LOAD RECORDS
// ============================================================

async function loadPendingRecords() {

    try {

        const records =
            await getAllOffline(
                SYNC_QUEUE_STORE
            );


        console.log(
            'Sync queue records:',
            records
        );


        updateCounts(records);


        const container =
            document.getElementById(
                'pending-records'
            );


        if (!container) {

            console.error(
                '#pending-records was not found.'
            );

            return;
        }


        container.innerHTML = '';


        const visibleRecords =
            records.filter(
                record =>
                    record.status === 'pending' ||
                    record.status === 'conflict'
            );


        if (visibleRecords.length === 0) {

            container.innerHTML = `

                <div class="px-6 py-10 text-center">

                    <p class="text-sm text-gray-500">

                        No pending synchronization
                        records or conflicts.

                    </p>

                </div>

            `;

            return;
        }


        visibleRecords
            .sort(
                (a, b) =>
                    Number(b.id) -
                    Number(a.id)
            )
            .forEach(record => {

                if (
                    record.status === 'conflict' &&
                    record.type === 'movement'
                ) {

                    renderMovementConflict(
                        record,
                        container
                    );

                } else {

                    renderPendingRecord(
                        record,
                        container
                    );
                }
            });


        attachConflictButtons();


    } catch (error) {

        console.error(
            'Unable to load synchronization records:',
            error
        );


        const container =
            document.getElementById(
                'pending-records'
            );


        if (container) {

            container.innerHTML = `

                <div class="px-6 py-10 text-center">

                    <p class="text-sm text-red-600">

                        Unable to load synchronization records.

                    </p>

                    <p class="mt-1 text-xs text-gray-500">

                        Check the browser console for details.

                    </p>

                </div>

            `;
        }
    }
}


// ============================================================
// MANUAL SYNC
// ============================================================

async function performSync() {

    if (!navigator.onLine) {

        alert(
            'There is currently no internet connection.'
        );

        await loadPendingRecords();

        return;
    }


    const button =
        document.getElementById(
            'sync-now-button'
        );


    if (button) {

        button.disabled =
            true;

        button.textContent =
            'Synchronizing...';
    }


    try {

        const result =
            await syncPendingRecords();


        await loadPendingRecords();


        if (
            result &&
            result.conflicts > 0
        ) {

            console.warn(
                'Synchronization completed with conflicts:',
                result.conflicts
            );
        }


    } catch (error) {

        console.error(
            'Synchronization error:',
            error
        );


        alert(
            'Synchronization failed.\n\n' +
            error.message
        );


    } finally {

        if (button) {

            button.disabled =
                false;

            button.textContent =
                'Sync Now';
        }
    }
}


// ============================================================
// INITIALIZATION
// ============================================================

async function initializeSyncStatus() {

    updateConnectionStatus();


    // --------------------------------------------------------
    // Load existing records first
    // --------------------------------------------------------

    await loadPendingRecords();


    // --------------------------------------------------------
    // IMPORTANT:
    // If page loads while ONLINE, immediately synchronize.
    // --------------------------------------------------------

    if (navigator.onLine) {

        console.log(
            'Page loaded while online. Checking pending records...'
        );


        try {

            await syncPendingRecords();

            await loadPendingRecords();

        } catch (error) {

            console.error(
                'Initial automatic synchronization failed:',
                error
            );
        }
    }


    // --------------------------------------------------------
    // INTERNET RESTORED
    // --------------------------------------------------------

    window.addEventListener(
        'online',
        async () => {

            console.log(
                'Internet connection restored.'
            );


            updateConnectionStatus();


            try {

                await syncPendingRecords();

                await loadPendingRecords();

            } catch (error) {

                console.error(
                    'Automatic synchronization failed:',
                    error
                );
            }
        }
    );


    // --------------------------------------------------------
    // INTERNET LOST
    // --------------------------------------------------------

    window.addEventListener(
        'offline',
        async () => {

            console.log(
                'Device is now offline.'
            );


            updateConnectionStatus();


            await loadPendingRecords();
        }
    );


    // --------------------------------------------------------
    // MANUAL SYNC BUTTON
    // --------------------------------------------------------

    const syncButton =
        document.getElementById(
            'sync-now-button'
        );


    if (syncButton) {

        syncButton.addEventListener(
            'click',
            performSync
        );
    }
}


// ============================================================
// START
// ============================================================

if (
    document.readyState ===
    'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initializeSyncStatus
    );

} else {

    initializeSyncStatus();

}