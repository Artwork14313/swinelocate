import {
    getAllOffline,
    getOffline,
    saveOffline,
} from './offline-db';

const CACHE_KEY_PREFIX = 'user:';

function currentUserId() {
    return document.querySelector('meta[name="offline-user-id"]')?.content;
}

function setReferenceStatus(message, isError = false) {
    const status = document.getElementById('offline-reference-data-status');

    if (!status) {
        return;
    }

    status.textContent = message;
    status.classList.toggle('hidden', !message);
    status.classList.toggle('border-blue-200', !isError);
    status.classList.toggle('bg-blue-50', !isError);
    status.classList.toggle('text-blue-900', !isError);
    status.classList.toggle('border-amber-200', isError);
    status.classList.toggle('bg-amber-50', isError);
    status.classList.toggle('text-amber-900', isError);
}

function selectedValue(select) {
    return select.value;
}

function replaceOptions(select, placeholderText, records, labelForRecord) {
    const currentValue = selectedValue(select);
    const placeholder = document.createElement('option');

    placeholder.value = '';
    placeholder.textContent = placeholderText;

    const options = [placeholder];

    for (const record of records) {
        const option = document.createElement('option');

        option.value = String(record.id);
        option.textContent = labelForRecord(record);

        if (record.farm_id !== undefined) {
            option.dataset.farmId = String(record.farm_id);
        }

        options.push(option);
    }

    select.replaceChildren(...options);

    if (options.some(option => option.value === currentValue)) {
        select.value = currentValue;
    }
}

async function updateSelects(referenceData) {
    const localSwine = await getAllOffline('swine');
    const userId = currentUserId();
    const localSwineOptions = localSwine
        .filter(swine =>
            String(swine.owner_user_id ?? '') === String(userId)
            && swine.status === 'active'
            && !swine.server_id
        )
        .map(swine => ({
            id: swine.id,
            tag_number: swine.tag_number,
            name: swine.name,
            farm_id: swine.farm_id,
            local_only: true,
        }));
    const swineRecords = [
        ...(referenceData.swine ?? []),
        ...localSwineOptions.filter(localSwineRecord =>
            !(referenceData.swine ?? []).some(serverSwine =>
                String(serverSwine.id) === String(localSwineRecord.server_id)
                || serverSwine.tag_number === localSwineRecord.tag_number
            )
        ),
    ];

    document.querySelectorAll('[data-offline-reference]').forEach(select => {
        const type = select.dataset.offlineReference;
        const records = type === 'swine'
            ? swineRecords
            : referenceData[type];

        if (!Array.isArray(records)) {
            return;
        }

        if (type === 'farms') {
            replaceOptions(
                select,
                'Select farm',
                records,
                farm => `${farm.farm_code} - ${farm.name}`,
            );
            return;
        }

        if (type === 'farmers') {
            if (records.length === 0) {
                return;
            }

            replaceOptions(
                select,
                'Select farmer',
                records,
                farmer => farmer.farm_name
                    ? `${farmer.name} — ${farmer.farm_name}`
                    : farmer.name,
            );
            return;
        }

        if (type === 'locations') {
            const farmId = select.dataset.offlineFarmId;
            const locations = farmId
                ? records.filter(location => String(location.farm_id) === farmId)
                : records;

            replaceOptions(
                select,
                select.dataset.offlinePlaceholder || 'Select location',
                locations,
                location => location.location_code
                    ? `${location.location_code} - ${location.name}`
                    : location.name,
            );
            return;
        }

        if (type === 'swine') {
            replaceOptions(
                select,
                'Select swine',
                records,
                swine => swine.name
                    ? `${swine.tag_number} — ${swine.name}`
                    : swine.tag_number,
            );
        }
    });

    document.querySelectorAll('[data-offline-reference="farms"]').forEach(select => {
        const updateFarmOptions = () => {
            const farmId = select.value;

            document.querySelectorAll('[data-offline-reference="locations"]').forEach(locationSelect => {
                locationSelect.dataset.offlineFarmId = farmId;
                const locations = (referenceData.locations ?? [])
                    .filter(location => String(location.farm_id) === String(farmId));

                replaceOptions(
                    locationSelect,
                    locationSelect.dataset.offlinePlaceholder || 'Select location',
                    locations,
                    location => location.location_code
                        ? `${location.location_code} - ${location.name}`
                        : location.name,
                );
            });

            document.querySelectorAll('[data-offline-reference="farmers"]').forEach(farmerSelect => {
                const farmers = referenceData.farmers ?? [];

                if (farmers.length === 0) {
                    updateFarmerAvailability(farmerSelect, farmId);
                    return;
                }

                const selectedFarmer = farmerSelect.value;
                const farmFarmers = farmers
                    .filter(farmer => String(farmer.farm_id) === String(farmId));

                replaceOptions(
                    farmerSelect,
                    'Select farmer',
                    farmFarmers,
                    farmer => farmer.name,
                );

                if ([...farmerSelect.options].some(option => option.value === selectedFarmer)) {
                    farmerSelect.value = selectedFarmer;
                }

                updateFarmerAvailability(farmerSelect, farmId);
            });
        };

        select.onchange = updateFarmOptions;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

function updateFarmerAvailability(farmerSelect, farmId) {
    const options = [...farmerSelect.options];
    let matchingFarmers = 0;

    options.forEach(option => {
        if (!option.value) {
            option.hidden = false;
            option.disabled = false;
            return;
        }

        const belongsToFarm = String(option.dataset.farmId) === String(farmId);
        option.hidden = !belongsToFarm;
        option.disabled = !belongsToFarm;

        if (belongsToFarm) {
            matchingFarmers++;
        }
    });

    const emptyState = document.getElementById('farmer-empty-state');

    if (emptyState) {
        emptyState.classList.toggle('hidden', !farmId || matchingFarmers > 0);
    }

    farmerSelect.disabled = !farmId || matchingFarmers === 0;

    const registerFarmerLink = document.getElementById('register-farmer-link');

    if (registerFarmerLink && farmId) {
        const createUrl = new URL(
            registerFarmerLink.dataset.createUrl,
            window.location.origin,
        );

        createUrl.searchParams.set('farm_id', farmId);
        registerFarmerLink.href = createUrl.toString();
    }

    const selectedOption = farmerSelect.options[farmerSelect.selectedIndex];

    if (selectedOption?.value && String(selectedOption.dataset.farmId) !== String(farmId)) {
        farmerSelect.value = '';
    }
}

async function loadCachedReferenceData(userId) {
    const cache = await getOffline(
        'reference_data',
        `${CACHE_KEY_PREFIX}${userId}`,
    );

    if (cache?.user_id === String(userId)) {
        await updateSelects(cache.data);
        return cache;
    }

    return null;
}

async function refreshReferenceData(userId) {
    const endpoint = document.querySelector(
        'meta[name="offline-reference-data-url"]',
    )?.content;

    if (!endpoint) {
        throw new Error('Offline reference data endpoint is not configured.');
    }

    const response = await fetch(endpoint, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        cache: 'no-store',
    });

    if (!response.ok) {
        throw new Error(`Reference data refresh failed with status ${response.status}.`);
    }

    const data = await response.json();

    await saveOffline('reference_data', {
        id: `${CACHE_KEY_PREFIX}${userId}`,
        user_id: String(userId),
        data,
        cached_at: new Date().toISOString(),
    });

    await updateSelects(data);
    setReferenceStatus('');
}

export async function initializeOfflineReferenceData() {
    const userId = currentUserId();

    if (!userId) {
        return;
    }

    try {
        if (navigator.onLine) {
            await refreshReferenceData(userId);
        } else {
            const cache = await loadCachedReferenceData(userId);

            setReferenceStatus(
                cache
                    ? `Offline: using reference lists last refreshed ${new Date(cache.cached_at).toLocaleString()}.`
                    : 'No saved reference lists are available on this device. Connect to the internet and reload before recording offline.',
                !cache,
            );
        }
    } catch (error) {
        console.error('Unable to refresh offline reference data:', error);

        try {
            const cache = await loadCachedReferenceData(userId);

            setReferenceStatus(
                cache
                    ? `Could not refresh reference lists; using the saved copy from ${new Date(cache.cached_at).toLocaleString()}.`
                    : 'Could not load reference lists. Connect to the internet and reload before recording offline.',
                true,
            );
        } catch (cacheError) {
            console.error('Unable to load saved offline reference data:', cacheError);
            setReferenceStatus('Saved reference lists could not be loaded on this device.', true);
        }
    }

    window.addEventListener('online', async () => {
        try {
            await refreshReferenceData(userId);
        } catch (error) {
            console.error('Unable to refresh offline reference data:', error);

            try {
                const cache = await loadCachedReferenceData(userId);

                setReferenceStatus(
                    cache
                        ? `Could not refresh reference lists; using the saved copy from ${new Date(cache.cached_at).toLocaleString()}.`
                        : 'Could not load reference lists. Connect to the internet and reload before recording offline.',
                    true,
                );
            } catch (cacheError) {
                console.error('Unable to load saved offline reference data:', cacheError);
                setReferenceStatus('Saved reference lists could not be loaded on this device.', true);
            }
        }
    });
}
