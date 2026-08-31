import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


/**
 * Initialize offline swine movement.
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
             * If online, allow Laravel
             * to process the form normally.
             */
            if (navigator.onLine) {
                return;
            }


            /*
             * Offline:
             * stop the normal Laravel request.
             */
            event.preventDefault();


            const formData =
                new FormData(form);


            /*
             * Get swine ID from data attribute.
             */
            const swineId =
                form.dataset.swineId;


            /*
             * Get movement information.
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
             * Laravel synchronization endpoint
             * ----------------------------------------------------------
             *
             * Controller:
             *
             * POST /swine/{swine}/move
             *
             * Example:
             *
             * /swine/2/move
             *
             * We intentionally do NOT use:
             *
             * /swine-movements?2
             */

            const syncEndpoint =
                new URL(
                    `/swine/${encodeURIComponent(swineId)}/move`,
                    window.location.origin
                ).toString();


            console.log(
                'Movement synchronization endpoint:',
                syncEndpoint
            );


            /*
             * ----------------------------------------------------------
             * Create local movement record
             * ----------------------------------------------------------
             */

            const movement = {

                id:
                    crypto.randomUUID(),

                swine_id:
                    Number(swineId),

                from_location_id:
                    null,

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
                 * 1. Save movement locally
                 * ------------------------------------------------------
                 */

                await saveOffline(
                    'movements',
                    movement
                );


                console.log(
                    'Movement saved locally:',
                    movement
                );


                /*
                 * ------------------------------------------------------
                 * 2. Add movement to sync queue
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
                    'Movement added to sync queue:',
                    queueId
                );


                /*
                 * ------------------------------------------------------
                 * 3. Confirm local save
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
                    'Unable to save the swine movement offline.'
                );

            }

        }
    );

}


/*
 * Initialize after DOM is ready.
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