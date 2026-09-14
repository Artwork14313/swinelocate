import {
    syncPendingRecords
} from './offline-sync';

import {
    getAllOffline,
    getOffline,
    saveOffline,
    deleteOffline
} from './offline-db';

import {
    getCsrfToken
} from './offline-utils';


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

const LAST_SYNC_KEY =
    'swineLocate_last_sync';


function updateLastSync() {

    const element =
        document.getElementById(
            'last-sync'
        );


    if (!element) {
        return;
    }


    const savedTimestamp =
        localStorage.getItem(
            LAST_SYNC_KEY
        );


    if (!savedTimestamp) {

        element.textContent =
            'Never';

        return;
    }


    const date =
        new Date(
            savedTimestamp
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        element.textContent =
            'Never';

        return;
    }


    element.textContent =
        date.toLocaleString();
}


function recordLastSync() {

    const timestamp =
        new Date().toISOString();


    localStorage.setItem(
        LAST_SYNC_KEY,
        timestamp
    );


    updateLastSync(
        timestamp
    );
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
        record?.server_data?.movement_id ??
        record?.server_data?.id ??
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
                        class="keep-offline-btn inline-flex
                               items-center justify-center rounded-lg
                               bg-[#3368A0] px-4 py-2.5
                               text-sm font-semibold text-white
                               hover:bg-[#28557F]"
                        data-record-id="${record.id}"
                    >
                        Keep Offline Version
                    </button>

                    <button
                        type="button"
                        style="background-color: #76C457;"
                        onmouseover="this.style.backgroundColor='#2A7C13';"
                        onmouseout="this.style.backgroundColor='#76C457';"
                        class="keep-server-btn inline-flex
                               items-center justify-center rounded-lg
                               px-4 py-2.5
                               text-sm font-semibold text-white
                              "
                        data-record-id="${record.id}"
                    >
                        Keep Server Version
                    </button>

                </div>

            </div>

        </div>
    `;


    container.appendChild(wrapper);
}

// ============================================================
// SWINE UPDATE CONFLICT
// ============================================================

function renderSwineConflict(
    record,
    container
) {

    const payload =
        record.payload ?? {};

    const serverData =
        getServerData(record) ?? {};


    // --------------------------------------------------------
    // FORMAT VALUES
    // --------------------------------------------------------

    function displayValue(value, fallback = 'Not specified') {

        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return fallback;
        }

        return String(value);
    }


    function normalizeValue(value) {

        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '';
        }

        return String(value)
            .trim()
            .toLowerCase();
    }


    // --------------------------------------------------------
    // CHECK WHETHER A FIELD CONFLICTS
    // --------------------------------------------------------

    function isConflict(
        offlineValue,
        serverValue
    ) {

        return (
            normalizeValue(offlineValue) !==
            normalizeValue(serverValue)
        );
    }


    // --------------------------------------------------------
    // FIELD ROW
    // --------------------------------------------------------

    function fieldRow(
        label,
        offlineValue,
        serverValue,
        options = {}
    ) {

        const conflict =
            isConflict(
                offlineValue,
                serverValue
            );


        const offlineDisplay =
            options.format
                ? options.format(offlineValue)
                : displayValue(offlineValue);


        const serverDisplay =
            options.format
                ? options.format(serverValue)
                : displayValue(serverValue);


        const offlineRowClass =
            conflict
                ? 'bg-red-50 border border-red-200 rounded-lg px-3 py-2'
                : 'px-3 py-2';


        const serverRowClass =
            conflict
                ? 'bg-red-50 border border-red-200 rounded-lg px-3 py-2'
                : 'px-3 py-2';


        const conflictBadge =
            conflict
                ? `
                    <span class="ml-2 inline-flex items-center
                                 rounded-full bg-red-100
                                 px-2 py-0.5 text-[10px]
                                 font-semibold text-red-700">
                        Conflict
                    </span>
                  `
                : '';


        const offlineValueClass =
            conflict
                ? 'font-semibold text-red-700'
                : 'font-medium text-gray-900';


        const serverValueClass =
            conflict
                ? 'font-semibold text-red-700'
                : 'font-medium text-gray-900';


        return `

            <!-- OFFLINE ROW -->

            <div class="flex items-start justify-between
                        gap-3 ${offlineRowClass}">

                <span class="text-gray-500 shrink-0">
                    ${label}
                    ${conflictBadge}
                </span>

                <span class="${offlineValueClass}
                             text-right break-words">

                    ${offlineDisplay}

                </span>

            </div>

        `;
    }


    // --------------------------------------------------------
    // SERVER FIELD ROW
    // --------------------------------------------------------

    function serverFieldRow(
        label,
        offlineValue,
        serverValue,
        options = {}
    ) {

        const conflict =
            isConflict(
                offlineValue,
                serverValue
            );


        const serverDisplay =
            options.format
                ? options.format(serverValue)
                : displayValue(serverValue);


        const rowClass =
            conflict
                ? 'bg-red-50 border border-red-200 rounded-lg px-3 py-2'
                : 'px-3 py-2';


        const valueClass =
            conflict
                ? 'font-semibold text-red-700'
                : 'font-medium text-gray-900';


        const conflictBadge =
            conflict
                ? `
                    <span class="ml-2 inline-flex items-center
                                 rounded-full bg-red-100
                                 px-2 py-0.5 text-[10px]
                                 font-semibold text-red-700">
                        Conflict
                    </span>
                  `
                : '';


        return `

            <div class="flex items-start justify-between
                        gap-3 ${rowClass}">

                <span class="text-gray-500 shrink-0">
                    ${label}
                    ${conflictBadge}
                </span>

                <span class="${valueClass}
                             text-right break-words">

                    ${serverDisplay}

                </span>

            </div>

        `;
    }


    // --------------------------------------------------------
    // VALUES
    // --------------------------------------------------------

    const swineId =
        payload.swine_id ??
        serverData.swine_id ??
        '-';


    const fields = [

        {
            label: 'Tag Number',
            offline: payload.tag_number,
            server: serverData.tag_number
        },

        {
            label: 'Farm',
            offline: payload.farm_id,
            server: serverData.farm_id
        },

        {
            label: 'Location',
            offline: payload.current_location_id,
            server: serverData.current_location_id,
            format: locationLabel
        },

        {
            label: 'Name',
            offline: payload.name,
            server: serverData.name
        },

        {
            label: 'Sex',
            offline: payload.sex,
            server: serverData.sex
        },

        {
            label: 'Breed',
            offline: payload.breed,
            server: serverData.breed
        },

        {
            label: 'Status',
            offline: payload.status,
            server: serverData.status
        },

        {
            label: 'Birth Date',
            offline: payload.birth_date,
            server: serverData.birth_date
        },

        {
            label: 'Acquisition Date',
            offline: payload.acquisition_date,
            server: serverData.acquisition_date
        },

        {
            label: 'Source',
            offline: payload.source,
            server: serverData.source
        },

        {
            label: 'Notes',
            offline: payload.notes,
            server: serverData.notes
        }

    ];


    // --------------------------------------------------------
    // COUNT CONFLICTS
    // --------------------------------------------------------

    const conflictCount =
        fields.filter(
            field =>
                isConflict(
                    field.offline,
                    field.server
                )
        ).length;


    // --------------------------------------------------------
    // GENERATE OFFLINE ROWS
    // --------------------------------------------------------

    const offlineRows =
        fields.map(
            field =>
                fieldRow(
                    field.label,
                    field.offline,
                    field.server,
                    {
                        format:
                            field.format
                    }
                )
        ).join('');


    // --------------------------------------------------------
    // GENERATE SERVER ROWS
    // --------------------------------------------------------

    const serverRows =
        fields.map(
            field =>
                serverFieldRow(
                    field.label,
                    field.offline,
                    field.server,
                    {
                        format:
                            field.format
                    }
                )
        ).join('');


    // --------------------------------------------------------
    // CREATE WRAPPER
    // --------------------------------------------------------

    const wrapper =
        document.createElement('div');


    wrapper.className =
        'border border-red-300 bg-red-50 rounded-xl p-5 mb-4';


    wrapper.innerHTML = `

        <div class="flex flex-col gap-4">


            <!-- HEADER -->

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

                            Swine Information

                        </span>

                    </div>


                    <p class="mt-2 text-sm text-gray-600">

                        This swine was modified by another user
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


            <!-- CONFLICT SUMMARY -->

            <div class="rounded-lg border border-red-200
                        bg-white px-4 py-3">

                <div class="flex items-center gap-2">

                    <span class="text-sm font-semibold
                                 text-gray-900">

                        ${conflictCount}
                        ${conflictCount === 1
            ? 'field has'
            : 'fields have'}
                        a conflict

                    </span>

                </div>


                <p class="mt-1 text-xs text-gray-500">

                    Conflicting fields are highlighted in red.
                    Fields with matching values are shown normally.

                </p>

            </div>


            <!-- SWINE ID -->

            <div class="rounded-lg border border-gray-200
                        bg-white px-4 py-3">

                <div class="flex items-center
                            justify-between gap-3">

                    <span class="text-sm text-gray-500">
                        Swine ID
                    </span>

                    <span class="font-semibold text-gray-900">
                        ${swineId}
                    </span>

                </div>

            </div>


            <!-- VERSION COMPARISON -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


                <!-- OFFLINE VERSION -->

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

                        ${offlineRows}

                    </div>

                </div>


                <!-- SERVER VERSION -->

                <div class="rounded-lg bg-white
                            border border-gray-300 p-4">

                    <div class="flex items-center
                                justify-between mb-3">

                        <h4 class="font-semibold text-gray-700">

                            Server Version

                        </h4>

                        <span class="text-xs font-medium
                                     rounded-full
                                     px-2 py-1 text-gray-700" style="background-color: #76C457;">

                            Online

                        </span>

                    </div>


                    <div class="space-y-2 text-sm">

                        ${serverRows}


                        <!-- SERVER UPDATED -->

                        <div class="flex items-start
                                    justify-between gap-3 px-3 py-2">

                            <span class="text-gray-500">
                                Updated
                            </span>

                            <span class="font-medium
                                         text-gray-900 text-right">

                                ${displayValue(
                serverData.updated_at,
                '-'
            )}

                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- LEGEND -->

            <div class="flex items-center gap-2 text-xs
                        text-gray-500">

                <span class="inline-block w-3 h-3 rounded
                             bg-red-100 border border-red-200">
                </span>

                <span>
                    Different value between offline and server versions
                </span>

            </div>


            <!-- ACTIONS -->

            <div class="border-t border-red-200 pt-4">

                <p class="text-xs text-gray-500 mb-3">

                    Choose which version of the swine information
                    should be retained.

                </p>


                <div class="flex flex-col sm:flex-row gap-3">


                    <button
                        type="button"
                        class="keep-swine-offline-btn
                               inline-flex items-center
                               justify-center rounded-lg
                               bg-[#3368A0] px-4 py-2.5
                               text-sm font-semibold text-white
                               hover:bg-[#28557F]"
                        data-record-id="${record.id}"
                    >

                        Keep Offline Version

                    </button>


                    <button
                        type="button"
                        style="background-color: #76C457;"
                        onmouseover="this.style.backgroundColor='#2A7C13';"
                        onmouseout="this.style.backgroundColor='#76C457';"
                        class="keep-swine-server-btn
                               inline-flex items-center
                               justify-center rounded-lg
                               px-4 py-2.5
                               text-sm font-semibold text-white
                               "
                        data-record-id="${record.id}"
                    >

                        Keep Server Version

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
// KEEP SERVER VERSION - SWINE
// ============================================================

async function keepSwineServerVersion(recordId) {

    if (!navigator.onLine) {

        alert(
            'You are currently offline. Please reconnect before resolving this conflict.'
        );

        return;
    }


    const confirmed =
        confirm(
            'Keep the server version?\n\n' +
            'The current online swine information will remain unchanged and the offline update will be discarded.'
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


        const serverData =
            getServerData(record);


        if (!serverData) {

            alert(
                'The server swine data is missing.'
            );

            return;
        }


        const swineId =
            serverData.swine_id ??
            record.payload?.swine_id;


        if (!swineId) {

            alert(
                'The swine ID is missing.'
            );

            return;
        }


        /*
         * Update the local IndexedDB copy so it matches
         * the server version that the user selected.
         */

        await saveOffline(
            SWINE_STORE,
            {
                ...serverData,

                id:
                    Number(swineId),

                sync_status:
                    'synced',

                conflict:
                    false,

                conflict_data:
                    null,

                synced_at:
                    new Date().toISOString()
            }
        );


        /*
         * Remove the offline update from the queue.
         */

        await deleteOffline(
            SYNC_QUEUE_STORE,
            Number(recordId)
        );


        alert(
            'The server version was kept successfully.\n\n' +
            'The offline swine update was discarded.'
        );


        await loadPendingRecords();


    } catch (error) {

        console.error(
            'Keep Swine Server Version error:',
            error
        );


        alert(
            'Unable to keep the server version.\n\n' +
            error.message
        );
    }
}
// ============================================================
// KEEP OFFLINE VERSION - SWINE
// ============================================================

async function keepSwineOfflineVersion(recordId) {

    if (!navigator.onLine) {

        alert(
            'You are currently offline. Please reconnect before resolving this conflict.'
        );

        return;
    }


    const confirmed =
        confirm(
            'Keep the offline version?\n\n' +
            'The offline swine information will replace the current server version.'
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


        if (!swineId) {

            alert(
                'The swine ID is missing.'
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
            `/swine/${Number(swineId)}/resolve-conflict`;


        const resolvePayload = {

            farm_id:
                payload.farm_id,

            current_location_id:
                payload.current_location_id ?? null,

            tag_number:
                payload.tag_number,

            name:
                payload.name ?? null,

            sex:
                payload.sex,

            breed:
                payload.breed ?? null,

            birth_date:
                payload.birth_date ?? null,

            acquisition_date:
                payload.acquisition_date ?? null,

            source:
                payload.source ?? null,

            status:
                payload.status,

            notes:
                payload.notes ?? null
        };


        console.log(
            'Keeping offline swine version:',
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

                    credentials:
                        'same-origin',

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


        if (!response.ok) {

            throw new Error(
                result?.message ??
                `Server returned HTTP ${response.status}.`
            );
        }


        /*
         * The offline version has now become the
         * accepted server version.
         */

        const localSwine =
            await getOffline(
                SWINE_STORE,
                Number(swineId)
            );


        if (localSwine) {

            await saveOffline(
                SWINE_STORE,
                {
                    ...localSwine,

                    ...payload,

                    id:
                        Number(swineId),

                    qr_token:
                        result?.qr_token ??
                        localSwine.qr_token,

                    sync_status:
                        'synced',

                    conflict:
                        false,

                    conflict_data:
                        null,

                    synced_at:
                        new Date().toISOString()
                }
            );

        } else {

            await saveOffline(
                SWINE_STORE,
                {
                    ...payload,

                    id:
                        Number(swineId),

                    qr_token:
                        result?.qr_token ?? null,

                    sync_status:
                        'synced',

                    conflict:
                        false,

                    conflict_data:
                        null,

                    synced_at:
                        new Date().toISOString()
                }
            );
        }


        /*
         * Remove the conflict queue item.
         */

        await deleteOffline(
            SYNC_QUEUE_STORE,
            Number(recordId)
        );


        alert(
            'The offline swine version was successfully saved.\n\n' +
            'The server version has been replaced by the offline version.'
        );


        await loadPendingRecords();


    } catch (error) {

        console.error(
            'Keep Swine Offline Version error:',
            error
        );


        alert(
            'Unable to keep the offline version.\n\n' +
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

    document
        .querySelectorAll('.keep-swine-server-btn')
        .forEach(button => {

            button.addEventListener(
                'click',
                async function () {

                    const recordId =
                        this.dataset.recordId;


                    this.disabled =
                        true;


                    await keepSwineServerVersion(
                        recordId
                    );


                    this.disabled =
                        false;
                }
            );
        });


    document
        .querySelectorAll('.keep-swine-offline-btn')
        .forEach(button => {

            button.addEventListener(
                'click',
                async function () {

                    const recordId =
                        this.dataset.recordId;


                    this.disabled =
                        true;


                    await keepSwineOfflineVersion(
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

                } else if (
                    record.status === 'conflict' &&
                    record.type === 'swine_update'
                ) {

                    renderSwineConflict(
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

        // Use the centralized synchronization engine
        // from offline-sync.js.
        const result =
            await syncPendingRecords();


        // Record the time after synchronization completes.
        recordLastSync();


        // Refresh the Sync Status page after synchronization.
        await loadPendingRecords();


        // Show a warning in the console if conflicts remain.
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

    // --------------------------------------------------------
    // Initial connection status
    // --------------------------------------------------------

    updateConnectionStatus();


    // --------------------------------------------------------
    // Load last synchronization timestamp
    // --------------------------------------------------------

    updateLastSync();


    // --------------------------------------------------------
    // Load existing queue records
    // --------------------------------------------------------

    await loadPendingRecords();


    // --------------------------------------------------------
    // INTERNET RESTORED
    // --------------------------------------------------------
    //
    // IMPORTANT:
    // sync-status.js MUST NOT synchronize here.
    //
    // offline-sync.js is responsible for:
    // - Refreshing server state
    // - Synchronizing pending records
    // - Handling conflicts
    //
    // sync-status.js only refreshes the display.
    // --------------------------------------------------------

    window.addEventListener(
        'online',
        async () => {

            console.log(
                'Internet connection restored.'
            );

            updateConnectionStatus();


            /*
            |--------------------------------------------------------------------------
            | Wait briefly for offline-sync.js
            |--------------------------------------------------------------------------
            |
            | offline-sync.js owns the actual synchronization.
            | We only reload the queue display after it has had
            | time to process the records.
            |
            |--------------------------------------------------------------------------
            */

            setTimeout(
                async () => {

                    await loadPendingRecords();

                },
                1000
            );
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
    //
    // Manual synchronization is allowed here.
    //
    // performSync() should call the imported
    // syncPendingRecords() from offline-sync.js.
    // --------------------------------------------------------

    const syncButton =
        document.getElementById(
            'sync-now-button'
        );


    if (syncButton) {

        syncButton.addEventListener(
            'click',
            async () => {

                syncButton.disabled = true;

                try {

                    await performSync();

                } finally {

                    syncButton.disabled = false;

                }
            }
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