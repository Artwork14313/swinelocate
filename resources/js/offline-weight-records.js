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


    form.addEventListener(
        'submit',
        async function (event) {

            // If online, let Laravel process the form normally.
            if (navigator.onLine) {
                return;
            }


            // Offline: stop the normal POST request.
            event.preventDefault();


            const formData = new FormData(form);


            const swineId =
                formData.get('swine_id');


            // Basic validation
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


            // Basic validation
            if (!recordDate) {

                alert(
                    'Please enter the record date.'
                );

                return;
            }


            if (!weight || Number(weight) <= 0) {

                alert(
                    'Please enter a valid weight.'
                );

                return;
            }


            const recordId =
                crypto.randomUUID();


            const data = {

                id: recordId,

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
                 * Save the actual weight record
                 * in IndexedDB.
                 */
                await saveOffline(
                    'weight_records',
                    data
                );


                /*
                 * Add synchronization instruction
                 * to the synchronization queue.
                 */
                await addToSyncQueue({

                    type:
                        'weight_record',

                    endpoint:
                        '/weight-records',

                    method:
                        'POST',

                    payload: {

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