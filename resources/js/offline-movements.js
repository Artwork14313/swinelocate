import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


function initializeOfflineMovement() {

    const form = document.getElementById(
        'swine-movement-form'
    );

    if (!form) {
        return;
    }


    /*
     * Prevent registering the submit listener
     * more than once.
     */
    if (form.dataset.offlineInitialized === 'true') {
        return;
    }

    form.dataset.offlineInitialized = 'true';


    form.addEventListener(
        'submit',
        async function (event) {

            /*
             * If online, allow Laravel to process
             * the form normally.
             */
            if (navigator.onLine) {
                return;
            }


            /*
             * Offline:
             * prevent the normal Laravel POST.
             */
            event.preventDefault();


            const formData =
                new FormData(form);


            /*
             * Get the swine ID from the form.
             *
             * The Blade form should contain:
             *
             * data-swine-id="{{ $swine->id }}"
             */
            const swineId =
                form.dataset.swineId;


            const toLocationId =
                formData.get(
                    'to_location_id'
                );


            const movementDate =
                formData.get(
                    'movement_date'
                );


            const reason =
                formData.get(
                    'reason'
                ) || null;


            const notes =
                formData.get(
                    'notes'
                ) || null;


            /*
             * Basic validation.
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
             * Generate one local ID.
             *
             * The same ID is used for the local
             * movement record.
             */
            const movementId =
                crypto.randomUUID();


            /*
             * Create local movement record.
             */
            const movement = {

                id:
                    movementId,

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
                 * ==========================================================
                 * 1. SAVE MOVEMENT LOCALLY
                 * ==========================================================
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
                 * ==========================================================
                 * 2. ADD MOVEMENT TO SYNC QUEUE
                 * ==========================================================
                 *
                 * The Laravel route already contains the
                 * swine ID because the form action is:
                 *
                 * /swine-movements/{swine}
                 *
                 * We still include swine_id in the payload
                 * for identification/debugging.
                 */
                const queueId =
                    await addToSyncQueue({

                        type:
                            'movement',

                        endpoint:
                            form.action,

                        method:
                            'POST',

                        payload: {

                            swine_id:
                                Number(swineId),

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
                 * ==========================================================
                 * 3. INFORM USER
                 * ==========================================================
                 */
                alert(
                    'No internet connection. The swine movement was saved locally and will be synchronized when the connection returns.'
                );


                /*
                 * Reset the form after successful
                 * local storage and queue creation.
                 */
                form.reset();


            } catch (error) {

                console.error(
                    'Offline swine movement error:',
                    error
                );


                /*
                 * Important:
                 * The user should know that the offline
                 * save failed.
                 */
                alert(
                    'Unable to save the swine movement offline. Please try again.'
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