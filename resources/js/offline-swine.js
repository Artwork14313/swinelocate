import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


/**
 * Initialize offline swine registration.
 */
function initializeOfflineSwine() {

    const form = document.getElementById(
        'swine-form'
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
             * prevent normal Laravel POST.
             */
            event.preventDefault();


            const formData =
                new FormData(form);


            /*
             * Get form values.
             */
            const farmId =
                formData.get('farm_id');


            const currentLocationId =
                formData.get(
                    'current_location_id'
                ) || null;


            const tagNumber =
                formData.get('tag_number');


            const name =
                formData.get('name')
                || null;


            const sex =
                formData.get('sex');


            const breed =
                formData.get('breed')
                || null;


            const birthDate =
                formData.get('birth_date')
                || null;


            const acquisitionDate =
                formData.get(
                    'acquisition_date'
                ) || null;


            const source =
                formData.get('source')
                || null;


            const notes =
                formData.get('notes')
                || null;


            /*
             * Basic validation.
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


            /*
             * Generate QR token locally.
             *
             * Laravel normally generates this
             * during SwineController::store().
             *
             * Since we are offline, generate it
             * here so the offline record already
             * has its QR identity.
             */
            const qrToken =
                crypto.randomUUID();


            /*
             * Generate a temporary local ID.
             *
             * This is NOT the Laravel database ID.
             * Laravel will create the real ID when
             * the record is synchronized.
             */
            const localId =
                crypto.randomUUID();


            /*
             * Create local swine record.
             */
            const swine = {

                id:
                    localId,

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
                    'active',

                qr_token:
                    qrToken,

                notes:
                    notes,

                sync_status:
                    'pending',

                created_at:
                    new Date().toISOString()

            };


            try {

                /*
                 * ----------------------------------------------------------
                 * 1. Save swine locally
                 * ----------------------------------------------------------
                 */
                await saveOffline(
                    'swine',
                    swine
                );


                console.log(
                    'Swine saved locally:',
                    swine
                );


                /*
                 * ----------------------------------------------------------
                 * 2. Add swine to synchronization queue
                 * ----------------------------------------------------------
                 */
                await addToSyncQueue({

                    type:
                        'swine',

                    endpoint:
                        form.action,

                    method:
                        'POST',

                    payload: {

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
                            'active',

                        qr_token:
                            qrToken,

                        notes:
                            notes

                    }

                });


                console.log(
                    'Swine added to sync queue.'
                );


                /*
                 * ----------------------------------------------------------
                 * 3. Inform user
                 * ----------------------------------------------------------
                 */
                alert(
                    'No internet connection. The swine was saved locally and will be synchronized when the connection returns.'
                );


                /*
                 * Clear form.
                 */
                form.reset();


            } catch (error) {

                console.error(
                    'Offline swine registration error:',
                    error
                );


                alert(
                    'Unable to save the swine offline.'
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
        initializeOfflineSwine
    );

} else {

    initializeOfflineSwine();

}