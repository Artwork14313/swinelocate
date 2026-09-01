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

        notes:
            'Notes'

    };

    return names[field] ?? field;

}


/*
|--------------------------------------------------------------------------
| Render Conflict
|--------------------------------------------------------------------------
*/
function renderConflict(record) {

    const serverData =
        getServerData(record);

    const offlineData =
        record.payload ?? {};

    const differences =
        getDifferences(
            offlineData,
            serverData
        );


    /*
     * Identify the swine involved in the conflict.
     *
     * Prefer the server tag number because it is
     * the current authoritative identifier.
     */
    const swineTag =
        serverData?.tag_number ??
        offlineData?.tag_number ??
        'Unknown Swine';

    return `

        <div class="border-b border-red-200 bg-red-50 px-6 py-6">

            <div class="flex flex-col gap-5">

                <div>

                    <div class="flex w-full items-center justify-between gap-3">

                        <p class="text-sm font-bold text-red-800">

                            ${formatRecordType(record.type)}

                        </p>


                        <span
                            class="inline-flex items-center rounded-full
                                   bg-red-100 px-3 py-1 text-xs
                                   font-semibold text-red-700
                                   ring-1 ring-red-200"
                        >

                            Conflict

                        </span>

                    </div>


                    <div
                        class="mt-4 rounded-lg border border-red-200
                               bg-white p-4"
                    >

                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-gray-500"
                        >

                            Swine Being Edited

                        </p>


                        <div class="mt-2 flex flex-wrap
                                    items-center gap-x-6 gap-y-2">

                            <div>

                                <p class="text-lg font-bold text-gray-900">

                                    ${escapeHtml(swineTag)}

                                </p>

                                <p class="text-xs text-gray-500">

                                    Tag Number

                                </p>

                            </div>


                        </div>

                    </div>


                    <p class="mt-4 text-sm text-red-700">

                        ${escapeHtml(
        record.error_message ??
        'This record was modified by another user while this device was offline.'
    )}

                    </p>


                    <p class="mt-1 text-xs text-gray-500">

                        Conflict detected:
                        ${formatDate(record.conflict_at)}

                    </p>

                </div>



                ${differences.length > 0

            ? `

                            <div class="overflow-hidden rounded-lg
                                        border border-gray-200
                                        bg-white">

                                <div class="border-b border-gray-200
                                            bg-gray-50 px-4 py-3">

                                    <h4 class="text-sm font-semibold
                                               text-gray-900">

                                        Conflicting Data

                                    </h4>

                                    <p class="mt-1 text-xs text-gray-500">

                                        Compare the values from this device
                                        with the values currently stored
                                        on the server.

                                    </p>

                                </div>


                                <div class="overflow-x-auto">

                                    <table class="min-w-full
                                                  divide-y divide-gray-200">

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
                    difference.offline
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
                    difference.server
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

                        `

            : `

                            <div
                                class="rounded-lg border border-gray-200
                                       bg-white p-4"
                            >

                                <p class="text-sm text-gray-600">

                                    No field-level differences were detected.

                                </p>

                            </div>

                        `
        }


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
| Keep Server Version
|--------------------------------------------------------------------------
*/

async function keepServerVersion(
    recordId
) {

    const confirmed =
        confirm(
            'Keep the server version and discard your offline changes?'
        );


    if (!confirmed) {
        return;
    }


    try {

        await deleteOffline(
            'sync_queue',
            Number(recordId)
        );


        alert(
            'The server version was kept. The offline update was discarded.'
        );


        await loadPendingRecords();

    } catch (error) {

        console.error(
            'Unable to keep server version:',
            error
        );

        alert(
            'Unable to resolve the conflict.'
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
            record.payload?.id;

        if (!swineId) {

            throw new Error(
                'Swine ID is missing from the offline record.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Prepare Offline Data
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | QR token is intentionally NOT included.
        |
        | The QR token belongs to the original swine
        | and must never be changed during editing
        | or conflict resolution.
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
        |--------------------------------------------------------------------------
        | Remove Fields That Must Never Be Changed
        |--------------------------------------------------------------------------
        */

        delete payload.id;

        delete payload.qr_token;

        delete payload.created_at;

        delete payload.updated_at;

        delete payload.sync_status;

        delete payload.synced_at;

        delete payload.conflict_at;


        /*
        |--------------------------------------------------------------------------
        | Conflict Resolution Endpoint
        |--------------------------------------------------------------------------
        */

        const endpoint =
            `/swine/${Number(swineId)}/resolve-conflict`;


        console.log(
            'Keeping offline version:',
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
        | Send Offline Changes
        |--------------------------------------------------------------------------
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
            'Conflict resolution response:',
            response.status,
            responseText
        );


        /*
        |--------------------------------------------------------------------------
        | Check Response
        |--------------------------------------------------------------------------
        */

        if (!response.ok) {

            throw new Error(
                responseText ||
                `Server returned HTTP ${response.status}.`
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Remove Conflict Queue
        |--------------------------------------------------------------------------
        */

        await deleteOffline(
            'sync_queue',
            Number(record.id)
        );


        /*
        |--------------------------------------------------------------------------
        | Mark Local Swine As Synced
        |--------------------------------------------------------------------------
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


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

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
         * Pending count represents all records
         * that still require synchronization or
         * conflict resolution.
         */
        pendingCount.textContent =
            pending.length + conflicts.length;


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
                    record =>
                        renderConflict(record)
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