import {
    addToSyncQueue,
    getOffline,
    saveOffline,
} from './offline-db';

import {
    getCsrfToken,
} from './offline-utils';

const EDIT_FORMS = [
    {
        selector: '#health-record-edit-form',
        store: 'health_records',
        queueType: 'health_record_update',
        fields: [
            'swine_id',
            'record_date',
            'record_type',
            'vaccine_name',
            'dose',
            'batch_number',
            'next_due_date',
            'symptoms',
            'diagnosis',
            'treatment',
            'observations',
            'veterinary_assessment',
            'health_status',
            'notes',
        ],
    },
    {
        selector: '#weight-record-edit-form',
        store: 'weight_records',
        queueType: 'weight_record_update',
        fields: [
            'record_date',
            'weight',
            'notes',
        ],
    },
];

function normalizeFormValue(value) {
    return value === '' ? null : value;
}

function valuesFromForm(form, config) {
    const formData = new FormData(form);
    const values = {};

    for (const field of config.fields) {
        const value = formData.get(field);

        if (value !== null) {
            values[field] = normalizeFormValue(value);
        }
    }

    if (values.swine_id) {
        const parsedSwineId = Number(values.swine_id);

        if (Number.isNaN(parsedSwineId)) {
            values.swine_local_id = String(values.swine_id);
            values.swine_id = null;
        } else {
            values.swine_id = parsedSwineId;
        }
    }

    if (config.queueType === 'health_record_update'
        && values.record_type !== 'Vaccination') {
        values.vaccine_name = null;
        values.dose = null;
        values.batch_number = null;
        values.next_due_date = null;
    }

    if (config.queueType === 'weight_record_update') {
        values.weight = Number(values.weight);
    }

    return values;
}

async function restorePendingEdit(form, config, recordId) {
    const localRecord = await getOffline(config.store, recordId);

    if (localRecord?.sync_status !== 'pending') {
        return;
    }

    for (const field of config.fields) {
        const input = form.elements.namedItem(field);

        if (input && localRecord[field] !== undefined && localRecord[field] !== null) {
            input.value = localRecord[field];
        }
    }

    const swineSelect = form.elements.namedItem('swine_id');

    if (swineSelect && localRecord.swine_local_id) {
        swineSelect.value = localRecord.swine_local_id;
    }
}

function initializeEditForm(config) {
    const form = document.querySelector(config.selector);

    if (!form) {
        return;
    }

    const recordId = Number(form.dataset.recordId);

    if (!Number.isInteger(recordId) || recordId <= 0) {
        console.error('Offline record edit form has an invalid record ID.');
        return;
    }

    restorePendingEdit(form, config, recordId).catch(error => {
        console.error('Could not restore the saved offline record edit:', error);
    });

    form.addEventListener('submit', async event => {
        if (navigator.onLine) {
            return;
        }

        event.preventDefault();

        const csrfToken = getCsrfToken();

        if (!csrfToken) {
            alert('CSRF token was not found. Reload the edit page while online before saving offline.');
            return;
        }

        const payload = valuesFromForm(form, config);
        const swineLocalId = payload.swine_local_id ?? null;

        if (config.queueType === 'health_record_update' && !payload.swine_id && !swineLocalId) {
            alert('Please select a swine.');
            return;
        }

        try {
            const previousRecord = await getOffline(config.store, recordId);
            const localRecord = {
                ...(previousRecord ?? {}),
                ...payload,
                id: recordId,
                server_id: recordId,
                swine_id: (
                    payload.swine_id
                    ?? previousRecord?.swine_id
                    ?? Number(form.dataset.swineId || 0)
                ) || null,
                swine_local_id: payload.swine_local_id
                    ?? previousRecord?.swine_local_id
                    ?? null,
                sync_status: 'pending',
                updated_offline_at: new Date().toISOString(),
            };

            await saveOffline(config.store, localRecord);
            await addToSyncQueue({
                type: config.queueType,
                endpoint: form.action,
                method: 'PUT',
                payload: {
                    ...payload,
                    local_id: recordId,
                },
            });

            alert('Saved on this device. This edit will synchronize when the connection returns.');
        } catch (error) {
            console.error('Unable to save the record edit offline:', error);
            alert(`Unable to save this edit offline: ${error.message}`);
        }
    });
}

for (const config of EDIT_FORMS) {
    initializeEditForm(config);
}
