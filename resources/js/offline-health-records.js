import {
    saveOffline,
    addToSyncQueue
} from './offline-db';


function initializeOfflineHealthRecords() {

    const form = document.getElementById(
        'health-record-form'
    );

    if (!form) {
        return;
    }


    form.addEventListener(
        'submit',
        async function (event) {

            // Online: let Laravel handle the form normally.
            if (navigator.onLine) {
                return;
            }


            // Offline: prevent normal POST.
            event.preventDefault();


            const formData = new FormData(form);


            const swineId =
                formData.get('swine_id');

            const recordDate =
                formData.get('record_date');

            const recordType =
                formData.get('record_type');

            const healthStatus =
                formData.get('health_status');


            // Basic validation
            if (!swineId) {

                alert(
                    'Please select a swine.'
                );

                return;
            }


            if (!recordDate) {

                alert(
                    'Please select a record date.'
                );

                return;
            }


            if (!recordType) {

                alert(
                    'Please select a record type.'
                );

                return;
            }


            if (!healthStatus) {

                alert(
                    'Please select the health status.'
                );

                return;
            }


            /*
             * Vaccination requires vaccine name,
             * just like Laravel validation.
             */
            const vaccineName =
                formData.get('vaccine_name') || null;


            if (
                recordType === 'Vaccination' &&
                !vaccineName
            ) {

                alert(
                    'Please enter the vaccine name.'
                );

                return;
            }


            const data = {

                swine_id:
                    Number(swineId),

                record_date:
                    recordDate,

                record_type:
                    recordType,

                vaccine_name:
                    vaccineName,

                dose:
                    formData.get('dose') || null,

                batch_number:
                    formData.get('batch_number') || null,

                next_due_date:
                    formData.get('next_due_date') || null,

                symptoms:
                    formData.get('symptoms') || null,

                diagnosis:
                    formData.get('diagnosis') || null,

                treatment:
                    formData.get('treatment') || null,

                observations:
                    formData.get('observations') || null,

                veterinary_assessment:
                    formData.get(
                        'veterinary_assessment'
                    ) || null,

                health_status:
                    healthStatus,

                notes:
                    formData.get('notes') || null,

                recorded_by:
                    null,

                sync_status:
                    'pending',

                created_at:
                    new Date().toISOString()

            };


            /*
             * For non-vaccination records,
             * match the Laravel update behavior
             * and clear vaccination-specific data.
             */
            if (recordType !== 'Vaccination') {

                data.vaccine_name = null;
                data.dose = null;
                data.batch_number = null;
                data.next_due_date = null;

            }


            try {

                /*
                 * Save the complete health record
                 * locally in IndexedDB.
                 */
                await saveOffline(
                    'health_records',
                    {
                        id: crypto.randomUUID(),
                        ...data
                    }
                );


                /*
                 * Add the record to the
                 * synchronization queue.
                 */
                await addToSyncQueue({

                    type:
                        'health_record',

                    endpoint:
                        '/health-records',

                    method:
                        'POST',

                    payload: {

                        swine_id:
                            data.swine_id,

                        record_date:
                            data.record_date,

                        record_type:
                            data.record_type,

                        vaccine_name:
                            data.vaccine_name,

                        dose:
                            data.dose,

                        batch_number:
                            data.batch_number,

                        next_due_date:
                            data.next_due_date,

                        symptoms:
                            data.symptoms,

                        diagnosis:
                            data.diagnosis,

                        treatment:
                            data.treatment,

                        observations:
                            data.observations,

                        veterinary_assessment:
                            data.veterinary_assessment,

                        health_status:
                            data.health_status,

                        notes:
                            data.notes

                    }

                });


                alert(
                    'No internet connection. The health record was saved locally and will be synchronized when the connection returns.'
                );


                form.reset();


                /*
                 * Hide vaccination fields again
                 * after resetting the form.
                 */
                const vaccinationFields =
                    document.getElementById(
                        'vaccination-fields'
                    );

                if (vaccinationFields) {

                    vaccinationFields.classList.add(
                        'hidden'
                    );

                }


            } catch (error) {

                console.error(
                    'Offline health record error:',
                    error
                );


                alert(
                    'Unable to save the health record offline.'
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
        initializeOfflineHealthRecords
    );

} else {

    initializeOfflineHealthRecords();

}