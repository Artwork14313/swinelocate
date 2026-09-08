import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


function initializeOfflineWeightRecords() {

    const form = document.getElementById(
        'weight-record-form'
    );

    if (!form) {
        return;
    }

    if (form.dataset.offlineWeightRecordsInitialized === 'true') {
        return;
    }

    form.dataset.offlineWeightRecordsInitialized = 'true';


    form.addEventListener(
        'submit',
        async function (event) {

            /*
             * If the device is online,
             * let Laravel process the form normally.
             */
            if (navigator.onLine) {
                return;
            }


            /*
             * Device is offline.
             * Stop the normal HTTP submission.
             */
            event.preventDefault();


            const formData =
                new FormData(form);


            const swineId =
                formData.get('swine_id');


            /*
             * Validate swine.
             */
            if (!swineId) {

                alert(
                    'Please select a swine.'
                );

                return;
            }


            const recordDate =
                formData.get('record_date');

            const weight =
                formData.get('weight');

            const notes =
                formData.get('notes') || null;


            /*
             * Validate record date.
             */
            if (!recordDate) {

                alert(
                    'Please enter the record date.'
                );

                return;
            }


            /*
             * Validate weight.
             */
            if (
                !weight ||
                Number(weight) <= 0
            ) {

                alert(
                    'Please enter a valid weight.'
                );

                return;
            }


            /*
             * Generate one permanent identifier
             * for this offline record.
             *
             * This same ID will be used in:
             *
             * 1. IndexedDB
             * 2. sync_queue
             * 3. Laravel database
             *
             * This prevents duplicate synchronization.
             */
            const localId =
                crypto.randomUUID();


            /*
             * Build the local weight record.
             */
            const data = {

                id: localId,

                local_id: localId,

                swine_id:
                    Number(swineId),

                record_date:
                    recordDate,

                weight:
                    weight,

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
                 * Save weight record locally.
                 */
                await saveOffline(
                    'weight_records',
                    data
                );


                /*
                 * Add the synchronization
                 * instruction to IndexedDB.
                 *
                 * IMPORTANT:
                 *
                 * The endpoint is /weight-records/sync
                 * rather than /weight-records.
                 *
                 * The local_id allows Laravel to
                 * identify this exact offline record.
                 */
                await addToSyncQueue({

                    type:
                        'weight_record',

                    endpoint:
                        '/weight-records/sync',

                    method:
                        'POST',

                    payload: {

                        local_id:
                            localId,

                        swine_id:
                            Number(swineId),

                        record_date:
                            recordDate,

                        weight:
                            weight,

                        notes:
                            notes

                    }

                });


                console.log(
                    'Offline weight record saved:',
                    data
                );


                alert(
                    'No internet connection. The weight record was saved locally and will be synchronized when the connection returns.'
                );


                /*
                 * Clear the form after
                 * successful local saving.
                 */
                form.reset();


            } catch (error) {

                console.error(
                    'Offline weight record error:',
                    error
                );


                alert(
                    'Unable to save the weight record offline.'
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
        initializeOfflineWeightRecords
    );

} else {

    initializeOfflineWeightRecords();

}