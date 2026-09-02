import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


/*
|--------------------------------------------------------------------------
| Initialize Offline Swine Movement
|--------------------------------------------------------------------------
*/

function initializeOfflineMovement() {

    const form = document.getElementById(
        'swine-movement-form'
    );

    if (!form) {
        return;
    }


    form.addEventListener(
        'submit',
        async function (event) {

            /*
             * ----------------------------------------------------------
             * ONLINE
             * ----------------------------------------------------------
             *
             * Let Laravel's normal form submission handle
             * the movement.
             */

            if (navigator.onLine) {
                return;
            }


            /*
             * ----------------------------------------------------------
             * OFFLINE
             * ----------------------------------------------------------
             *
             * Prevent the normal HTTP request.
             */

            event.preventDefault();


            const formData =
                new FormData(form);


            /*
             * ----------------------------------------------------------
             * Get Swine ID
             * ----------------------------------------------------------
             */

            const swineId =
                form.dataset.swineId;


            /*
             * ----------------------------------------------------------
             * Get Original Location
             * ----------------------------------------------------------
             *
             * IMPORTANT:
             *
             * This is the location the device saw BEFORE
             * the offline movement was created.
             *
             * Laravel will use this value to determine
             * whether another user moved the swine while
             * this device was offline.
             */

            const originalLocationId =
                form.dataset.currentLocationId
                    ? Number(
                        form.dataset.currentLocationId
                    )
                    : null;


            /*
             * ----------------------------------------------------------
             * Get Movement Information
             * ----------------------------------------------------------
             */

            const toLocationId =
                formData.get('to_location_id');


            const movementDate =
                formData.get('movement_date');


            const reason =
                formData.get('reason') || null;


            const notes =
                formData.get('notes') || null;


            /*
             * ----------------------------------------------------------
             * Validation
             * ----------------------------------------------------------
             */

            if (!swineId) {

                alert(
                    'Unable to identify the swine.'
                );

                return;
            }


            if (!toLocationId) {

                alert(
                    'Please select a destination location.'
                );

                return;
            }


            if (!movementDate) {

                alert(
                    'Please select the movement date.'
                );

                return;
            }


            /*
             * ----------------------------------------------------------
             * Prevent Same Location
             * ----------------------------------------------------------
             */

            if (
                originalLocationId !== null &&
                Number(originalLocationId) ===
                Number(toLocationId)
            ) {

                alert(
                    'The swine is already assigned to this location.'
                );

                return;
            }


            /*
             * ----------------------------------------------------------
             * Offline Synchronization Endpoint
             * ----------------------------------------------------------
             *
             * IMPORTANT:
             *
             * Do NOT use:
             *
             * /swine/{swine}/move
             *
             * That is the normal online movement endpoint.
             *
             * Offline movements must use:
             *
             * POST /swine-movements/sync
             *
             * which points to:
             *
             * SwineMovementController@syncStore
             *
             */

            const syncEndpoint =
                new URL(
                    '/swine-movements/sync',
                    window.location.origin
                ).toString();


            console.log(
                'Offline movement synchronization endpoint:',
                syncEndpoint
            );


            /*
             * ----------------------------------------------------------
             * Create Local Movement ID
             * ----------------------------------------------------------
             */

            const localMovementId =
                crypto.randomUUID();


            /*
             * ----------------------------------------------------------
             * Create Offline Movement
             * ----------------------------------------------------------
             */

            const movement = {

                id:
                    localMovementId,

                swine_id:
                    Number(swineId),

                /*
                 * Location before the offline movement.
                 */
                original_location_id:
                    originalLocationId,

                /*
                 * Same value used for movement history.
                 */
                from_location_id:
                    originalLocationId,

                /*
                 * Location selected while offline.
                 */
                to_location_id:
                    Number(toLocationId),

                movement_date:
                    movementDate,

                reason:
                    reason,

                notes:
                    notes,

                recorded_by:
                    null,

                sync_status:
                    'pending',

                created_at:
                    new Date().toISOString()

            };


            try {

                /*
                 * ------------------------------------------------------
                 * 1. Save Movement Locally
                 * ------------------------------------------------------
                 */

                await saveOffline(
                    'movements',
                    movement
                );


                console.log(
                    'Offline movement saved locally:',
                    movement
                );


                /*
                 * ------------------------------------------------------
                 * 2. Add To Synchronization Queue
                 * ------------------------------------------------------
                 */

                const queueId =
                    await addToSyncQueue({

                        type:
                            'movement',

                        endpoint:
                            syncEndpoint,

                        method:
                            'POST',

                        payload: {

                            /*
                             * Swine being moved.
                             */
                            swine_id:
                                Number(swineId),


                            /*
                             * Local IndexedDB movement ID.
                             */
                            local_id:
                                localMovementId,


                            /*
                             * CRITICAL:
                             *
                             * Location that the device saw
                             * BEFORE going through the offline
                             * movement.
                             *
                             * Laravel compares this against
                             * the current server location.
                             */
                            original_location_id:
                                originalLocationId,


                            /*
                             * Original movement location.
                             */
                            from_location_id:
                                originalLocationId,


                            /*
                             * Offline destination.
                             */
                            to_location_id:
                                Number(toLocationId),


                            movement_date:
                                movementDate,


                            reason:
                                reason,


                            notes:
                                notes

                        }

                    });


                console.log(
                    'Offline movement added to sync queue:',
                    queueId
                );


                /*
                 * ------------------------------------------------------
                 * 3. Confirm Local Save
                 * ------------------------------------------------------
                 */

                alert(
                    'No internet connection. The swine movement was saved locally and will be synchronized when the connection returns.'
                );


                /*
                 * Clear the form.
                 */

                form.reset();


            } catch (error) {

                console.error(
                    'Offline swine movement error:',
                    error
                );


                alert(
                    'Unable to save the swine movement offline. Please check the browser console for details.'
                );

            }

        }
    );

}


/*
|--------------------------------------------------------------------------
| Initialize After DOM Is Ready
|--------------------------------------------------------------------------
*/

if (
    document.readyState === 'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        initializeOfflineMovement
    );

} else {

    initializeOfflineMovement();

}