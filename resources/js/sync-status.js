import {
    getAllOffline,
    getOffline,
    saveOffline,
    deleteOffline
} from './offline-db';

import {
    syncPendingRecords
} from './offline-sync';


const connectionIndicator =
    document.getElementById(
        'connection-indicator'
    );

const connectionStatus =
    document.getElementById(
        'connection-status'
    );

const pendingCount =
    document.getElementById(
        'pending-count'
    );

const conflictCount =
    document.getElementById(
        'conflict-count'
    );

const pendingRecords =
    document.getElementById(
        'pending-records'
    );

const lastSync =
    document.getElementById(
        'last-sync'
    );

const syncButton =
    document.getElementById(
        'sync-now-button'
    );


/*
|--------------------------------------------------------------------------
| Connection Status
|--------------------------------------------------------------------------
*/

function updateConnectionStatus() {

    if (!connectionIndicator || !connectionStatus) {
        return;
    }

    if (navigator.onLine) {

        connectionIndicator.className =
            'h-3 w-3 rounded-full bg-green-500';

        connectionStatus.textContent =
            'Online';

    } else {

        connectionIndicator.className =
            'h-3 w-3 rounded-full bg-red-500';

        connectionStatus.textContent =
            'Offline';

    }

}


/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

function formatDate(date) {

    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleString();

}


/*
|--------------------------------------------------------------------------
| Record Type
|--------------------------------------------------------------------------
*/

function formatRecordType(type) {

    switch (type) {

        case 'weight_record':
            return 'Weight Record';

        case 'health_record':
            return 'Health Record';

        case 'movement':
            return 'Swine Movement';

        case 'swine':
            return 'Swine Record';

        case 'swine_update':
            return 'Swine Update';

        default:
            return type ?? 'Unknown Record';

    }

}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    if (value === null || value === undefined) {
        return '—';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


/*
|--------------------------------------------------------------------------
| Parse Server Response
|--------------------------------------------------------------------------
*/

function getServerData(record) {

    if (!record.server_response) {
        return null;
    }

    try {

        const response =
            typeof record.server_response === 'string'
                ? JSON.parse(record.server_response)
                : record.server_response;

        return response.server_data ?? null;

    } catch (error) {

        console.error(
            'Unable to parse server response:',
            error
        );

        return null;

    }

}


/*
|--------------------------------------------------------------------------
| Get Offline Value
|--------------------------------------------------------------------------
*/

function getOfflineValue(
    payload,
    field
) {

    if (!payload) {
        return null;
    }

    return payload[field] ?? null;

}


/*
|--------------------------------------------------------------------------
| Compare Offline and Server Data
|--------------------------------------------------------------------------
*/

function getDifferences(
    offlineData,
    serverData
) {

    if (!offlineData || !serverData) {
        return [];
    }


    const fields = [

        'farm_id',

        'current_location_id',

        'tag_number',

        'name',

        'sex',

        'breed',

        'birth_date',

        'acquisition_date',

        'source',

        'status',

        'notes'

    ];


    return fields
        .filter(field => {

            const offline =
                offlineData[field] ?? null;

            const server =
                serverData[field] ?? null;

            return String(offline) !== String(server);

        })
        .map(field => ({

            field: field,

            offline:
                offlineData[field] ?? null,

            server:
                serverData[field] ?? null

        }));

}


/*
|--------------------------------------------------------------------------
| Human-readable Field Name
|--------------------------------------------------------------------------
*/

function formatFieldName(field) {

    const names = {

        farm_id:
            'Farm',

        current_location_id:
            'Current Location',

        original_location_id:
            'Original Location',

        from_location_id:
            'From Location',

        to_location_id:
            'Destination Location',

        tag_number:
            'Tag Number',

        name:
            'Name',

        sex:
            'Sex',

        breed:
            'Breed',

        birth_date:
            'Birth Date',

        acquisition_date:
            'Acquisition Date',

        source:
            'Source',

        status:
            'Status',

        qr_token:
            'QR Token',

        movement_date:
            'Movement Date',

        reason:
            'Reason',

        notes:
            'Notes'

    };

    return names[field] ?? field;

}


/*
|--------------------------------------------------------------------------
| Render Movement Conflict
|--------------------------------------------------------------------------
*/

function renderMovementConflict(record) {

    const offlineData =
        record.payload ?? {};

    const serverData =
        record.server_data ??
        getServerData(record) ??
        {};


    /*
     * ----------------------------------------------------------
     * Swine Information
     * ----------------------------------------------------------
     */

    const swineTag =
        serverData?.tag_number ??
        offlineData?.tag_number ??
        'Unknown Swine';


    /*
     * ----------------------------------------------------------
     * Location Values
     * ----------------------------------------------------------
     */

    const originalLocation =
        offlineData?.original_location_id ??
        offlineData?.from_location_id ??
        null;


    const offlineDestination =
        offlineData?.to_location_id ??
        null;


    const serverLocation =
        serverData?.current_location_id ??
        null;


    /*
     * ----------------------------------------------------------
     * Movement Conflict Differences
     * ----------------------------------------------------------
     */

    const differences = [];


    /*
     * Original location
     */

    if (originalLocation !== null) {

        differences.push({

            field:
                'original_location_id',

            offline:
                originalLocation,

            server:
                '—'

        });

    }


    /*
     * Offline destination vs server's
     * current location.
     *
     * This is the important conflict.
     */

    differences.push({

        field:
            'to_location_id',

        offline:
            offlineDestination,

        server:
            serverLocation

    });


    /*
     * Movement date
     */

    if (offlineData?.movement_date) {

        differences.push({

            field:
                'movement_date',

            offline:
                offlineData.movement_date,

            server:
                '—'

        });

    }


    /*
     * Reason
     */

    if (offlineData?.reason) {

        differences.push({

            field:
                'reason',

            offline:
                offlineData.reason,

            server:
                '—'

        });

    }


    /*
     * Notes
     */

    if (offlineData?.notes) {

        differences.push({

            field:
                'notes',

            offline:
                offlineData.notes,

            server:
                '—'

        });

    }


    return `

        <div class="border-b border-red-200 bg-red-50 px-6 py-6">

            <div class="flex flex-col gap-5">


                <!-- Header -->

                <div>

                    <div class="flex w-full items-center
                                justify-between gap-3">

                        <p class="text-sm font-bold text-red-800">

                            Swine Movement

                        </p>


                        <span
                            class="inline-flex items-center
                                   rounded-full bg-red-100
                                   px-3 py-1 text-xs
                                   font-semibold text-red-700
                                   ring-1 ring-red-200"
                        >

                            Conflict

                        </span>

                    </div>


                    <!-- Swine -->

                    <div
                        class="mt-4 rounded-lg border
                               border-red-200 bg-white p-4"
                    >

                        <p
                            class="text-xs font-semibold
                                   uppercase tracking-wide
                                   text-gray-500"
                        >

                            Swine Being Moved

                        </p>


                        <div class="mt-2">

                            <p class="text-lg font-bold text-gray-900">

                                ${escapeHtml(swineTag)}

                            </p>


                            <p class="text-xs text-gray-500">

                                Tag Number

                            </p>

                        </div>

                    </div>


                    <p class="mt-4 text-sm text-red-700">

                        ${escapeHtml(
        record.error_message ??
        'This swine was moved by another user while this device was offline.'
    )}

                    </p>


                    <p class="mt-1 text-xs text-gray-500">

                        Conflict detected:
                        ${formatDate(record.conflict_at)}

                    </p>

                </div>


                <!-- Conflicting Data -->

                <div
                    class="overflow-hidden rounded-lg
                           border border-gray-200 bg-white"
                >

                    <div
                        class="border-b border-gray-200
                               bg-gray-50 px-4 py-3"
                    >

                        <h4
                            class="text-sm font-semibold
                                   text-gray-900"
                        >

                            Conflicting Movement

                        </h4>


                        <p class="mt-1 text-xs text-gray-500">

                            The swine's location was changed while
                            this device was offline.

                        </p>

                    </div>


                    <div class="overflow-x-auto">

                        <table
                            class="min-w-full divide-y
                                   divide-gray-200"
                        >

                            <thead class="bg-gray-50">

                                <tr>

                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs font-semibold
                                               uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Field

                                    </th>


                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs font-semibold
                                               uppercase tracking-wide
                                               text-red-600"
                                    >

                                        Offline Version

                                    </th>


                                    <th
                                        class="px-4 py-3 text-left
                                               text-xs font-semibold
                                               uppercase tracking-wide
                                               text-green-600"
                                    >

                                        Server Version

                                    </th>

                                </tr>

                            </thead>


                            <tbody
                                class="divide-y divide-gray-200
                                       bg-white"
                            >

                                ${differences.map(
        difference => `

                                        <tr>

                                            <td
                                                class="whitespace-nowrap
                                                       px-4 py-3 text-sm
                                                       font-medium
                                                       text-gray-900"
                                            >

                                                ${formatFieldName(
            difference.field
        )}

                                            </td>


                                            <td
                                                class="px-4 py-3 text-sm
                                                       text-red-700"
                                            >

                                                <div
                                                    class="rounded-md
                                                           bg-red-50
                                                           px-3 py-2"
                                                >

                                                    ${escapeHtml(
            formatMovementValue(
                difference.field,
                difference.offline
            )
        )}

                                                </div>

                                            </td>


                                            <td
                                                class="px-4 py-3 text-sm
                                                       text-green-700"
                                            >

                                                <div
                                                    class="rounded-md
                                                           bg-green-50
                                                           px-3 py-2"
                                                >

                                                    ${escapeHtml(
            formatMovementValue(
                difference.field,
                difference.server
            )
        )}

                                                </div>

                                            </td>

                                        </tr>

                                    `
    ).join('')}

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- Resolution Buttons -->

                <div
                    class="flex flex-wrap gap-3
                           border-t border-red-200 pt-4"
                >

                    <button
                        type="button"
                        class="keep-offline-button inline-flex
                               items-center rounded-lg
                               bg-indigo-600 px-4 py-2.5
                               text-sm font-semibold text-white
                               shadow-sm hover:bg-indigo-700
                               disabled:opacity-50"
                        data-id="${record.id}"
                    >

                        Keep Offline Version

                    </button>


                    <button
                        type="button"
                        class="keep-server-button inline-flex
                               items-center rounded-lg
                               border border-gray-300
                               bg-white px-4 py-2.5
                               text-sm font-semibold text-gray-700
                               shadow-sm hover:bg-gray-50
                               disabled:opacity-50"
                        data-id="${record.id}"
                    >

                        Keep Server Version

                    </button>

                </div>

            </div>

        </div>

    `;

}

/*
|--------------------------------------------------------------------------
| Format Movement Conflict Values
|--------------------------------------------------------------------------
*/

function formatMovementValue(field, value) {

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '—';
    }


    if (field === 'movement_date') {

        return formatDate(value);

    }


    return String(value);

}


/*
|--------------------------------------------------------------------------
| Keep Server Version
|--------------------------------------------------------------------------
*/

async function keepServerVersion(recordId) {

    const confirmed =
        confirm(
            'Keep the server version and discard your offline changes?'
        );

    if (!confirmed) {
        return;
    }


    try {

        const records =
            await getAllOffline(
                'sync_queue'
            );


        const record =
            records.find(
                item =>
                    Number(item.id) ===
                    Number(recordId)
            );


        if (!record) {

            throw new Error(
                'Synchronization record was not found.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | If this is a movement conflict,
        | restore the server location locally.
        |--------------------------------------------------------------------------
        */

        if (record.type === 'movement') {

            const swineId =
                record.payload?.swine_id ??
                record.swine_id;


            const serverData =
                record.server_data ??
                getServerData(record) ??
                {};


            const serverLocation =
                serverData.current_location_id;


            if (
                swineId &&
                serverLocation !== undefined &&
                serverLocation !== null
            ) {

                const localSwine =
                    await getOffline(
                        'swine',
                        Number(swineId)
                    );


                if (localSwine) {

                    localSwine.current_location_id =
                        Number(serverLocation);

                    localSwine.sync_status =
                        'synced';

                    localSwine.synced_at =
                        new Date().toISOString();

                    delete localSwine.conflict_at;


                    await saveOffline(
                        'swine',
                        localSwine
                    );

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Remove conflict from queue.
        |--------------------------------------------------------------------------
        */

        await deleteOffline(
            'sync_queue',
            Number(recordId)
        );


        alert(
            'The server version was kept. The offline movement was discarded.'
        );


        await loadPendingRecords();


    } catch (error) {

        console.error(
            'Unable to keep server version:',
            error
        );


        alert(
            'Unable to resolve the conflict.\n\n' +
            error.message
        );

    }

}


/*
|--------------------------------------------------------------------------
| Keep Offline Version
|--------------------------------------------------------------------------
*/

async function keepOfflineVersion(record) {

    const confirmed =
        confirm(
            'Keep the offline version and overwrite the current server version?'
        );

    if (!confirmed) {
        return;
    }


    try {

        /*
        |--------------------------------------------------------------------------
        | Get Swine ID
        |--------------------------------------------------------------------------
        */

        const swineId =
            record.payload?.swine_id ??
            record.swine_id;

        if (!swineId) {

            throw new Error(
                'Swine ID is missing from the offline record.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CSRF Token
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | MOVEMENT CONFLICT
        |--------------------------------------------------------------------------
        |
        | Movement conflicts MUST NOT use:
        |
        | /swine/{swine}/resolve-conflict
        |
        | That endpoint is for swine profile updates.
        |
        | Movement conflicts use:
        |
        | /swine-movements/sync
        |
        | with force = true.
        |
        */

        if (record.type === 'movement') {

            const movementPayload = {

                swine_id:
                    Number(swineId),

                original_location_id:
                    record.payload?.original_location_id ??
                    record.payload?.from_location_id ??
                    null,

                from_location_id:
                    record.payload?.from_location_id ??
                    record.payload?.original_location_id ??
                    null,

                to_location_id:
                    Number(
                        record.payload?.to_location_id
                    ),

                movement_date:
                    record.payload?.movement_date,

                reason:
                    record.payload?.reason ?? null,

                notes:
                    record.payload?.notes ?? null,

                force:
                    true

            };


            /*
             * Validate destination.
             */

            if (!movementPayload.to_location_id) {

                throw new Error(
                    'Offline destination location is missing.'
                );

            }


            /*
             * Validate movement date.
             */

            if (!movementPayload.movement_date) {

                throw new Error(
                    'Offline movement date is missing.'
                );

            }


            /*
             * Movement synchronization endpoint.
             */

            const endpoint =
                '/swine-movements/sync';


            console.log(
                'Keeping offline movement version:',
                {
                    swineId:
                        Number(swineId),

                    endpoint:
                        endpoint,

                    payload:
                        movementPayload
                }
            );


            /*
             * Send movement resolution.
             */

            const response =
                await fetch(
                    endpoint,
                    {

                        method:
                            'POST',

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
                                movementPayload
                            )

                    }
                );


            const responseText =
                await response.text();


            console.log(
                'Movement conflict resolution response:',
                response.status,
                responseText
            );


            /*
             * Check response.
             */

            if (!response.ok) {

                throw new Error(
                    responseText ||
                    `Server returned HTTP ${response.status}.`
                );

            }


            /*
             * Parse response if possible.
             */

            let responseData = null;

            try {

                responseData =
                    JSON.parse(responseText);

            } catch (error) {

                responseData = null;

            }


            if (
                responseData &&
                responseData.success === false
            ) {

                throw new Error(
                    responseData.message ||
                    'Unable to save the offline movement version.'
                );

            }


            /*
             * Remove the conflict from
             * the synchronization queue.
             */

            await deleteOffline(
                'sync_queue',
                Number(record.id)
            );


            /*
             * Update local swine location.
             *
             * The offline destination is now
             * the authoritative location.
             */

            const localSwine =
                await getOffline(
                    'swine',
                    Number(swineId)
                );


            if (localSwine) {

                localSwine.current_location_id =
                    Number(
                        movementPayload.to_location_id
                    );

                localSwine.sync_status =
                    'synced';

                localSwine.synced_at =
                    new Date().toISOString();

                delete localSwine.conflict_at;


                await saveOffline(
                    'swine',
                    localSwine
                );

            }


            /*
             * Success.
             */

            alert(
                'The offline movement version was successfully saved to the server.'
            );


            await loadPendingRecords();


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | SWINE UPDATE CONFLICT
        |--------------------------------------------------------------------------
        |
        | Everything below is for normal swine-update conflicts.
        |
        */

        const payload = {

            ...(record.payload ?? {}),

            swine_id:
                Number(swineId),

            force:
                true

        };


        /*
         * QR token must never be changed.
         */

        delete payload.qr_token;

        delete payload.id;

        delete payload.created_at;

        delete payload.updated_at;

        delete payload.sync_status;

        delete payload.synced_at;

        delete payload.conflict_at;


        /*
         * Swine update conflict endpoint.
         */

        const endpoint =
            `/swine/${Number(swineId)}/resolve-conflict`;


        console.log(
            'Keeping offline swine version:',
            {
                swineId:
                    Number(swineId),

                endpoint:
                    endpoint,

                payload:
                    payload
            }
        );


        /*
         * Send swine update resolution.
         */

        const response =
            await fetch(
                endpoint,
                {

                    method:
                        'PUT',

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
                        JSON.stringify(payload)

                }
            );


        const responseText =
            await response.text();


        console.log(
            'Swine conflict resolution response:',
            response.status,
            responseText
        );


        if (!response.ok) {

            throw new Error(
                responseText ||
                `Server returned HTTP ${response.status}.`
            );

        }


        /*
         * Remove conflict queue record.
         */

        await deleteOffline(
            'sync_queue',
            Number(record.id)
        );


        /*
         * Mark local swine as synced.
         */

        const localSwine =
            await getOffline(
                'swine',
                Number(swineId)
            );


        if (localSwine) {

            localSwine.sync_status =
                'synced';

            localSwine.synced_at =
                new Date().toISOString();

            delete localSwine.conflict_at;


            await saveOffline(
                'swine',
                localSwine
            );

        }


        alert(
            'The offline version was successfully saved to the server.'
        );


        await loadPendingRecords();


    } catch (error) {

        console.error(
            'Unable to keep offline version:',
            error
        );


        alert(
            'Unable to save the offline version to the server.\n\n' +
            error.message
        );

    }

}


/*
|--------------------------------------------------------------------------
| Attach Conflict Buttons
|--------------------------------------------------------------------------
*/

async function attachConflictButtons() {

    const records =
        await getAllOffline(
            'sync_queue'
        );


    const conflicts =
        records.filter(
            record =>
                record.status === 'conflict'
        );


    document
        .querySelectorAll(
            '.keep-server-button'
        )
        .forEach(button => {

            button.addEventListener(
                'click',
                async () => {

                    button.disabled = true;

                    await keepServerVersion(
                        button.dataset.id
                    );

                }
            );

        });


    document
        .querySelectorAll(
            '.keep-offline-button'
        )
        .forEach(button => {

            button.addEventListener(
                'click',
                async () => {

                    const record =
                        conflicts.find(
                            item =>
                                String(item.id) ===
                                String(button.dataset.id)
                        );


                    if (!record) {
                        return;
                    }


                    button.disabled = true;

                    await keepOfflineVersion(
                        record
                    );

                }
            );

        });

}


/*
|--------------------------------------------------------------------------
| Load Pending / Conflict Records
|--------------------------------------------------------------------------
*/

async function loadPendingRecords() {

    if (!pendingCount || !pendingRecords) {
        return;
    }

    try {

        const records =
            await getAllOffline(
                'sync_queue'
            );


        /*
         * Separate records by status.
         */
        const pending =
            records.filter(
                record =>
                    record.status === 'pending'
            );


        const conflicts =
            records.filter(
                record =>
                    record.status === 'conflict'
            );


        /*
|--------------------------------------------------------------------------
| Update Separate Counters
|--------------------------------------------------------------------------
*/

        /*
         * Pending card only counts records
         * that are actually waiting to sync.
         */
        if (pendingCount) {

            pendingCount.textContent =
                pending.length;

        }


        /*
         * Conflict card only counts records
         * that require conflict resolution.
         */
        if (conflictCount) {

            conflictCount.textContent =
                conflicts.length;

        }


        /*
         * No pending records and no conflicts.
         */
        if (
            pending.length === 0 &&
            conflicts.length === 0
        ) {

            pendingRecords.innerHTML = `

                <div class="px-6 py-12 text-center">

                    <div class="text-3xl">
                        ✓
                    </div>

                    <p class="mt-3 text-sm font-medium text-gray-900">
                        No pending records
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        All offline records have been synchronized.
                    </p>

                </div>

            `;

            return;

        }


        /*
         * Build Pending section.
         */
        const pendingSection =
            pending.length > 0

                ? `

                    <div>

                        <!-- Pending Header -->

                        <div
                            class="flex items-center justify-between
                                   border-b border-yellow-200
                                   bg-yellow-50 px-6 py-4"
                        >

                            <div>

                                <h3
                                    class="text-sm font-bold
                                           text-yellow-800"
                                >

                                    Pending Synchronization

                                </h3>

                                <p
                                    class="mt-1 text-xs
                                           text-yellow-700"
                                >

                                    These records are waiting to be
                                    synchronized with the server.

                                </p>

                            </div>


                            <span
                                class="inline-flex items-center
                                       rounded-full
                                       bg-yellow-100
                                       px-3 py-1
                                       text-xs font-semibold
                                       text-yellow-700
                                       ring-1 ring-yellow-200"
                            >

                                ${pending.length}

                            </span>

                        </div>


                        <!-- Pending Records -->

                        <div class="divide-y divide-gray-200">

                            ${pending.map(
                    record => `

                                    <div class="px-6 py-5">

                                        <div
                                            class="flex flex-col gap-3
                                                   sm:flex-row
                                                   sm:items-center
                                                   sm:justify-between"
                                        >

                                            <div>

                                                <p
                                                    class="text-sm
                                                           font-semibold
                                                           text-gray-900"
                                                >

                                                    ${formatRecordType(
                        record.type
                    )}

                                                </p>


                                                <p
                                                    class="mt-1 text-xs
                                                           text-gray-500"
                                                >

                                                    Created:
                                                    ${formatDate(
                        record.created_at
                    )}

                                                </p>

                                            </div>


                                            <span
                                                class="inline-flex w-fit
                                                       items-center
                                                       rounded-full
                                                       bg-yellow-100
                                                       px-3 py-1
                                                       text-xs
                                                       font-semibold
                                                       text-yellow-700
                                                       ring-1
                                                       ring-yellow-200"
                                            >

                                                Pending

                                            </span>

                                        </div>

                                    </div>

                                `
                ).join('')}

                        </div>

                    </div>

                `

                : '';


        /*
         * Build Conflict section.
         *
         * IMPORTANT:
         * Conflicts are intentionally placed in their
         * own section and are NOT rendered inside the
         * Pending section.
         */
        const conflictSection =
            conflicts.length > 0

                ? `

                    <div
                        class="border-t-4 border-red-500"
                    >

                        <!-- Conflict Header -->

                        <div
                            class="flex items-center justify-between
                                   bg-red-50
                                   border-b border-red-200
                                   px-6 py-4"
                        >

                            <div>

                                <h3
                                    class="text-sm font-bold
                                           text-red-800"
                                >

                                    Conflict Resolution Required

                                </h3>


                                <p
                                    class="mt-1 text-xs
                                           text-red-700"
                                >

                                    These records were modified on
                                    another device while this device
                                    was offline.

                                </p>

                            </div>


                            <span
                                class="inline-flex items-center
                                       rounded-full
                                       bg-red-100
                                       px-3 py-1
                                       text-xs font-semibold
                                       text-red-700
                                       ring-1 ring-red-200"
                            >

                                ${conflicts.length}

                            </span>

                        </div>


                        <!-- Conflict Records -->

                        <div>

                            ${conflicts.map(
                    record => {

                        if (record.type === 'movement') {

                            return renderMovementConflict(record);

                        }

                        return renderConflict(record);

                    }
                ).join('')}

                        </div>

                    </div>

                `

                : '';


        /*
         * Render the two sections separately.
         */
        pendingRecords.innerHTML = `

            <div>

                ${pendingSection}

                ${conflictSection}

            </div>

        `;


        /*
         * Attach Keep Server / Keep Offline buttons
         * only after conflict elements exist in DOM.
         */
        await attachConflictButtons();


    } catch (error) {

        console.error(
            'Unable to load synchronization records:',
            error
        );


        pendingCount.textContent =
            '—';


        pendingRecords.innerHTML = `

            <div class="px-6 py-10 text-center">

                <p class="text-sm text-red-600">

                    Unable to load offline records.

                </p>

            </div>

        `;

    }

}


/*
|--------------------------------------------------------------------------
| Last Synchronization
|--------------------------------------------------------------------------
*/

function loadLastSync() {

    if (!lastSync) {
        return;
    }


    const savedLastSync =
        localStorage.getItem(
            'swine_locate_last_sync'
        );


    if (!savedLastSync) {

        lastSync.textContent =
            'Not yet synchronized';

        return;

    }


    lastSync.textContent =
        formatDate(savedLastSync);

}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

async function refreshSyncStatus() {

    updateConnectionStatus();

    loadLastSync();

    await loadPendingRecords();

}


/*
|--------------------------------------------------------------------------
| Manual Sync
|--------------------------------------------------------------------------
*/

if (syncButton) {

    syncButton.addEventListener(
        'click',
        async () => {

            if (!navigator.onLine) {

                alert(
                    'You are currently offline.'
                );

                return;

            }


            if (syncButton.disabled) {
                return;
            }


            syncButton.disabled = true;


            const originalText =
                syncButton.textContent;


            syncButton.textContent =
                'Synchronizing...';


            try {

                await syncPendingRecords();


                await new Promise(
                    resolve =>
                        setTimeout(
                            resolve,
                            700
                        )
                );


                await refreshSyncStatus();


            } catch (error) {

                console.error(
                    'Synchronization failed:',
                    error
                );


                alert(
                    'Synchronization failed. Please try again.'
                );

            } finally {

                syncButton.disabled = false;

                syncButton.textContent =
                    originalText;

            }

        }
    );

}


/*
|--------------------------------------------------------------------------
| Online Event
|--------------------------------------------------------------------------
*/

window.addEventListener(
    'online',
    async () => {

        updateConnectionStatus();

        await syncPendingRecords();

        await refreshSyncStatus();

    }
);


/*
|--------------------------------------------------------------------------
| Offline Event
|--------------------------------------------------------------------------
*/

window.addEventListener(
    'offline',
    async () => {

        updateConnectionStatus();

        await loadPendingRecords();

    }
);


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

refreshSyncStatus();