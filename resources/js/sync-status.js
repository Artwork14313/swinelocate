import {
    getAllOffline
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


/**
 * Update connection status.
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


/**
 * Format date.
 */
function formatDate(date) {

    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleString();

}


/**
 * Convert internal record type
 * into a readable name.
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

        default:
            return type ?? 'Unknown Record';

    }

}


/**
 * Load pending synchronization records.
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


        const pending =
            records.filter(
                record =>
                    record.status === 'pending'
            );


        /*
         * Update pending count.
         */
        pendingCount.textContent =
            pending.length;


        /*
         * No pending records.
         */
        if (pending.length === 0) {

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
         * Display pending records.
         */
        pendingRecords.innerHTML = `

            <div class="divide-y divide-gray-200">

                ${pending.map(record => `

                    <div class="px-6 py-5">

                        <div class="flex flex-col gap-3 sm:flex-row
                                    sm:items-center sm:justify-between">

                            <div>

                                <p class="text-sm font-semibold text-gray-900">
                                    ${formatRecordType(record.type)}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Created:
                                    ${formatDate(record.created_at)}
                                </p>

                            </div>


                            <span class="inline-flex w-fit items-center
                                         rounded-full bg-yellow-100
                                         px-3 py-1 text-xs font-semibold
                                         text-yellow-700 ring-1
                                         ring-yellow-200">

                                Pending

                            </span>

                        </div>

                    </div>

                `).join('')}

            </div>

        `;

    } catch (error) {

        console.error(
            'Unable to load pending records:',
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


/**
 * Update Last Synchronization display.
 *
 * This uses browser local storage instead of
 * sync_history because we decided that a
 * synchronization history module is unnecessary.
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



/**
 * Refresh Sync Status page.
 */
async function refreshSyncStatus() {

    updateConnectionStatus();

    loadLastSync();

    await loadPendingRecords();

}


/**
 * Manual synchronization.
 */
if (syncButton) {

    syncButton.addEventListener(
        'click',
        async () => {

            if (!navigator.onLine) {

                alert('You are currently offline.');

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

                /*
                 * Give the synchronization
                 * a moment to finish before
                 * refreshing the display.
                 */
                await new Promise(
                    resolve =>
                        setTimeout(resolve, 300)
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


/**
 * Internet connection restored.
 */
window.addEventListener(
    'online',
    async () => {

        updateConnectionStatus();

        await syncPendingRecords();

        await refreshSyncStatus();

    }
);


/**
 * Internet connection lost.
 */
window.addEventListener(
    'offline',
    async () => {

        updateConnectionStatus();

        await loadPendingRecords();

    }
);


/**
 * Initial load.
 */
refreshSyncStatus();