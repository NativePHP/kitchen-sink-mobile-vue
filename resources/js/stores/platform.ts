import { ref, computed } from 'vue';
import { system, device } from '#nativephp'

const STORAGE_KEY = 'nativephp_platform_info';

// Try to load cached platform info from localStorage
const cachedPlatform = (() => {
    try {
        const cached = localStorage.getItem(STORAGE_KEY);
        return cached ? JSON.parse(cached) : null;
    } catch {
        return null;
    }
})();

const _isIos = ref(cachedPlatform?.isIos || false);
const isAndroid = ref(cachedPlatform?.isAndroid || false);
const isLoaded = ref(false);
const osVersion = ref<number | null>(cachedPlatform?.osVersion || null);
const shouldApplyTopPadding = ref(cachedPlatform?.shouldApplyTopPadding ?? true);

let initPromise: Promise<void> | null = null;

export async function initializePlatform() {
    if (initPromise) {
        return initPromise;
    }

    initPromise = (async () => {
        try {
            _isIos.value = await system.isIos();
            isAndroid.value = await system.isAndroid();

            const deviceInfo = await device.getInfo();
            if (deviceInfo && deviceInfo.info) {
                const info = JSON.parse(deviceInfo.info);

                // Parse OS version - could be "18.5" or "26" etc
                const versionString = info.osVersion?.toString() || '0';
                const majorVersion = parseInt(versionString.split('.')[0]);
                osVersion.value = majorVersion;

                if (_isIos.value && osVersion.value < 26) {
                    shouldApplyTopPadding.value = false;
                }

                // Cache the platform info for instant access on next load
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    isIos: _isIos.value,
                    isAndroid: isAndroid.value,
                    osVersion: osVersion.value,
                    shouldApplyTopPadding: shouldApplyTopPadding.value
                }));
            }
        } catch {
            // Error detecting platform
        } finally {
            isLoaded.value = true;
        }
    })();

    return initPromise;
}

// Computed to override isIos behavior for old iOS versions
export const isIos = computed(() => {
    // If iOS < 26, don't apply the extra iOS padding
    if (_isIos.value && !shouldApplyTopPadding.value) {
        return false;
    }
    return _isIos.value;
});

export const platformStore = {
    isIos,
    isAndroid,
    isLoaded,
    osVersion,
    shouldApplyTopPadding,
};
