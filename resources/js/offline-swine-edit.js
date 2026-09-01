
import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


/**
 * Initialize offline swine editing.
 */
function initializeOfflineSwineEdit() {

    const form = document.getElementById(
        'swine-edit-form'
    );

    if (!form) {
        return;
    }


    form.addEventListener(
        'submit',
        async function (event) {

            /*
             * If online, allow Laravel
             * to process the normal update.
             */
            if (navigator.onLine) {
                return;
            }


            /*
             * Offline:
             * prevent normal Laravel PUT request.
             */
            event.preventDefault();


            const formData =
                new FormData(form);


            /*
             * ----------------------------------------------------------
             * Get swine ID
             * ----------------------------------------------------------
             */

            const swineId =
                form.dataset.swineId;


            if (!swineId) {

                alert(
                    'Unable to identify the swine.'
                );

                return;
            }


            /*
             * ----------------------------------------------------------
             * Get synchronization endpoint
             * ----------------------------------------------------------
             */

            const syncEndpoint =
                form.dataset.syncEndpoint;


            if (!syncEndpoint) {

                alert(
                    'Unable to determine the synchronization endpoint.'
                );

                return;
            }


            /*
             * ----------------------------------------------------------
             * Get original server timestamp
             * ----------------------------------------------------------
             *
             * This is important for conflict detection.
             *
             * Laravel will compare this value with the
             * current updated_at value on the server.
             */

            const originalUpdatedAt =
                formData.get(
                    'original_updated_at'
                );


            if (!originalUpdatedAt) {

                alert(
                    'Unable to determine the original swine record version.'
                );

                return;
            }


            /*
             * ----------------------------------------------------------
             * Get form values
             * ----------------------------------------------------------
             */

            const farmId =
                formData.get(
                    'farm_id'
                );


            const currentLocationId =
                formData.get(
                    'current_location_id'
                ) || null;


            const tagNumber =
                formData.get(
                    'tag_number'
                );


            const name =
                formData.get(
                    'name'
                ) || null;


            const sex =
                formData.get(
                    'sex'
                );


            const breed =
                formData.get(
                    'breed'
                ) || null;


            const birthDate =
                formData.get(
                    'birth_date'
                ) || null;


            const acquisitionDate =
                formData.get(
                    'acquisition_date'
                ) || null;


            const source =
                formData.get(
                    'source'
                ) || null;


            const status =
                formData.get(
                    'status'
                );


            const notes =
                formData.get(
                    'notes'
                ) || null;


            /*
             * ----------------------------------------------------------
             * Basic validation
             * ----------------------------------------------------------
             */

            if (!farmId) {

                alert(
                    'Please select a farm.'
                );

                return;
            }


            if (!tagNumber) {

                alert(
                    'Please enter the tag number.'
                );

                return;
            }


            if (!sex) {

                alert(
                    'Please select the sex of the swine.'
                );

                return;
            }


            if (!status) {

                alert(
                    'Please select the swine status.'
                );

                return;
            }


            /*
             * ----------------------------------------------------------
             * Create local updated swine record
             * ----------------------------------------------------------
             *
             * IMPORTANT:
             *
             * We keep the real Laravel swine ID here.
             *
             * Unlike offline creation, this is NOT a new
             * swine record. We are editing an existing swine.
             */

            const swine = {

                id:
                    Number(swineId),

                farm_id:
                    Number(farmId),

                current_location_id:
                    currentLocationId
                        ? Number(currentLocationId)
                        : null,

                tag_number:
                    tagNumber,

                name:
                    name,

                sex:
                    sex,

                breed:
                    breed,

                birth_date:
                    birthDate,

                acquisition_date:
                    acquisitionDate,

                source:
                    source,

                status:
                    status,

                notes:
                    notes,

                sync_status:
                    'pending',

                original_updated_at:
                    originalUpdatedAt,

                updated_at:
                    new Date().toISOString(),

                offline_updated_at:
                    new Date().toISOString()

            };


            try {

                /*
                 * ------------------------------------------------------
                 * 1. Save updated swine locally
                 * ------------------------------------------------------
                 */

                await saveOffline(
                    'swine',
                    swine
                );


                console.log(
                    'Swine update saved locally:',
                    swine
                );


                /*
                 * ------------------------------------------------------
                 * 2. Add update to synchronization queue
                 * ------------------------------------------------------
                 */

                await addToSyncQueue({

                    type:
                        'swine_update',

                    endpoint:
                        syncEndpoint,

                    method:
                        'PUT',

                    payload: {

                        /*
                         * IMPORTANT:
                         * Laravel syncUpdate() requires swine_id
                         * in the request payload.
                         */
                        swine_id:
                            Number(swineId),

                        farm_id:
                            Number(farmId),

                        current_location_id:
                            currentLocationId
                                ? Number(currentLocationId)
                                : null,

                        tag_number:
                            tagNumber,

                        name:
                            name,

                        sex:
                            sex,

                        breed:
                            breed,

                        birth_date:
                            birthDate,

                        acquisition_date:
                            acquisitionDate,

                        source:
                            source,

                        status:
                            status,

                        notes:
                            notes,

                        /*
                         * Used by Laravel to detect
                         * whether another user changed
                         * the record while this device
                         * was offline.
                         */
                        original_updated_at:
                            originalUpdatedAt

                    }

                });




                console.log(
                    'Swine update added to sync queue.'
                );


                /*
                 * ------------------------------------------------------
                 * 3. Inform user
                 * ------------------------------------------------------
                 */

                alert(
                    'No internet connection. The swine information was updated locally and will be synchronized when the connection returns.'
                );


                /*
                 * ------------------------------------------------------
                 * 4. Return to swine list
                 * ------------------------------------------------------
                 */

                window.location.href =
                    form.dataset.redirectUrl ||
                    '/swine';


            } catch (error) {

                console.error(
                    'Offline swine update error:',
                    error
                );


                alert(
                    'Unable to save the swine update offline.'
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
        initializeOfflineSwineEdit
    );

} else {

    initializeOfflineSwineEdit();

}
