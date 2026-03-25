<script setup lang="ts">
import Github from '@/components/icons/Github.vue';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useMailwebConfig } from '@/composables/useMailwebConfig';
import axios from 'axios';
import { ArrowLeft, Loader2, Mail, Paperclip, Pause, Play, RefreshCw, Search, Settings, X } from 'lucide-vue-next';
import { type Ref, computed, inject, onMounted, onUnmounted, ref, watch } from 'vue';
import toast from 'vue3-hot-toast';
import AnimatedSendButton from './partials/AnimatedSendButton.vue';
import SettingsDialog from './partials/SettingsDialog.vue';

const props = defineProps<{
    searchQuery: string;
    filters: Record<string, any>;
    isMobile: boolean;
    isLoading?: boolean;
}>();

const emit = defineEmits(['update:searchQuery', 'update:filters', 'close-sidebar', 'update:settings']);

const localSearchQuery = ref(props.searchQuery);
const fetchEmails = inject('fetchEmails') as (resetList?: boolean) => Promise<void>;
const isRefreshing = ref<boolean>(false);
const isSending = ref<boolean>(false);
const isPolling = ref<boolean>(false);
const pollingInterval = ref<number | null>(null);
const paginationTriggered = inject<Ref<boolean> | null>('paginationTriggered', null);
const isPollingActive = inject<Ref<boolean> | null>('isPollingActive', null);
const showSettingsDialog = ref<boolean>(false);
const isSearchFocused = ref<boolean>(false);

// Filter state
const filterHasAttachments = ref(false);
const filterUnread = ref(false);

const settings = ref({
    paginationAmount: 25,
    dateFormat: 'days-ago',
});

const { getReturnConfig, isDeleteAllEnabled: checkDeleteAllEnabled, isSendSampleButtonEnabled: checkSendSampleButtonEnabled } = useMailwebConfig();
const returnConfig = ref({ appName: '', appUrl: '' });
const isSendSampleButtonEnabled = ref<boolean>(false);
const isDeleteAllEnabled = ref<boolean>(false);

const activeFilterCount = computed(() => {
    let count = 0;
    if (filterHasAttachments.value) count++;
    if (filterUnread.value) count++;
    return count;
});

const emitFilters = () => {
    emit('update:filters', {
        hasAttachments: filterHasAttachments.value,
        unread: filterUnread.value,
    });
};

const toggleFilter = (filter: 'attachments' | 'unread') => {
    if (filter === 'attachments') filterHasAttachments.value = !filterHasAttachments.value;
    if (filter === 'unread') filterUnread.value = !filterUnread.value;
    emitFilters();
};

const clearFilters = () => {
    filterHasAttachments.value = false;
    filterUnread.value = false;
    emitFilters();
};

const clearSearch = () => {
    localSearchQuery.value = '';
    emit('update:searchQuery', '');
};

onMounted(() => {
    isDeleteAllEnabled.value = checkDeleteAllEnabled();
    returnConfig.value = getReturnConfig();
    isSendSampleButtonEnabled.value = checkSendSampleButtonEnabled();
    loadSettings();
    if (isPollingActive) startPolling();
    if (paginationTriggered) {
        watch(paginationTriggered, (triggered) => {
            if (triggered && isPolling.value) {
                stopPolling();
                toast.success("Auto-refresh paused while browsing. Re-enable it anytime.");
            }
        });
    }
});

watch(localSearchQuery, (newValue) => emit('update:searchQuery', newValue));
watch(
    () => props.searchQuery,
    (newValue) => {
        if (newValue !== localSearchQuery.value) localSearchQuery.value = newValue;
    },
);

const POLLING_INTERVAL_MS = 5000;

const startPolling = (): void => {
    if (pollingInterval.value !== null) return;
    if (paginationTriggered) paginationTriggered.value = false;
    handleRefresh();
    pollingInterval.value = window.setInterval(() => {
        if (document.visibilityState === 'visible') handleRefresh();
    }, POLLING_INTERVAL_MS);
    isPolling.value = true;
    if (isPollingActive) isPollingActive.value = true;
};

const stopPolling = (): void => {
    if (pollingInterval.value !== null) {
        window.clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
    isPolling.value = false;
    if (isPollingActive) isPollingActive.value = false;
};

const togglePolling = (): void => (isPolling.value ? stopPolling() : startPolling());

const handleRefresh = async (): Promise<void> => {
    isRefreshing.value = true;
    if (fetchEmails) await fetchEmails();
    isRefreshing.value = false;
};

const loadSettings = (): void => {
    const savedSettings = localStorage.getItem('mailweb-settings');
    if (savedSettings) {
        try {
            settings.value = { ...settings.value, ...JSON.parse(savedSettings) };
            emit('update:settings', settings.value);
        } catch (e) {
            console.error('Failed to parse settings from localStorage:', e);
        }
    }
};

const sendTestEmail = async (): Promise<void> => {
    isSending.value = true;
    try {
        const response = await axios.get('/mailweb/send-test-email', {
            headers: { Accept: 'application/json' },
        });
        toast.success(response.status === 200 ? 'Test email sent successfully' : 'Failed to send test email');
    } catch (error) {
        toast.error('Failed to send test email');
        console.error('Error sending test email:', error);
    } finally {
        isSending.value = false;
    }
};

onUnmounted(() => stopPolling());
</script>

<template>
    <div class="flex h-full flex-col">
        <!-- Header -->
        <div class="p-5 pb-4">
            <div class="mb-5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="bg-primary flex h-8 w-8 items-center justify-center rounded-md">
                        <Mail class="h-4 w-4 text-white" />
                    </div>
                    <h1 class="text-[1.1rem] font-bold tracking-tight">Mailweb</h1>
                </div>
                <Button v-if="isMobile" variant="ghost" size="icon" @click="$emit('close-sidebar')" class="h-8 w-8">
                    <X class="h-4 w-4" />
                    <span class="sr-only">Close</span>
                </Button>
            </div>

            <!-- Search -->
            <div
                class="flex items-center gap-2 rounded-md border px-3 py-2 transition-all duration-200"
                :class="isSearchFocused
                    ? 'border-ring/50 bg-accent shadow-sm'
                    : 'border-border bg-muted/50'"
            >
                <Search v-if="!isLoading || !localSearchQuery" class="text-muted-foreground h-3.5 w-3.5 shrink-0" />
                <Loader2 v-else class="text-primary h-3.5 w-3.5 shrink-0 animate-spin" />
                <input
                    placeholder="Search emails..."
                    class="text-foreground placeholder-muted-foreground/60 w-full bg-transparent text-sm focus:outline-none"
                    v-model="localSearchQuery"
                    @focus="isSearchFocused = true"
                    @blur="isSearchFocused = false"
                />
                <button
                    v-if="localSearchQuery"
                    @click="clearSearch"
                    class="text-muted-foreground hover:text-foreground shrink-0 rounded p-0.5 transition-colors"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>

            <!-- Filters -->
            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                <button
                    @click="toggleFilter('unread')"
                    class="flex items-center gap-1 rounded-md border px-2 py-1 text-xs font-medium transition-colors duration-150"
                    :class="filterUnread
                        ? 'border-primary/30 bg-primary/10 text-primary'
                        : 'border-border text-muted-foreground hover:bg-muted hover:text-foreground'"
                >
                    <span class="h-1.5 w-1.5 rounded-full" :class="filterUnread ? 'bg-primary' : 'bg-muted-foreground/40'"></span>
                    Unread
                </button>
                <button
                    @click="toggleFilter('attachments')"
                    class="flex items-center gap-1 rounded-md border px-2 py-1 text-xs font-medium transition-colors duration-150"
                    :class="filterHasAttachments
                        ? 'border-primary/30 bg-primary/10 text-primary'
                        : 'border-border text-muted-foreground hover:bg-muted hover:text-foreground'"
                >
                    <Paperclip class="h-3 w-3" />
                    Has files
                </button>
                <button
                    v-if="activeFilterCount > 0"
                    @click="clearFilters"
                    class="text-muted-foreground hover:text-foreground flex items-center gap-0.5 rounded-md px-1.5 py-1 text-xs transition-colors"
                >
                    <X class="h-3 w-3" />
                    Clear
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-auto">
            <!-- Return link -->
            <div class="border-t px-5 py-2.5">
                <a :href="returnConfig.appUrl || '/'" class="text-muted-foreground hover:text-primary group flex items-center gap-1.5 text-xs transition-colors">
                    <ArrowLeft class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5" />
                    <span>Return to {{ returnConfig.appName || 'App' }}</span>
                </a>
            </div>

            <!-- Controls -->
            <div class="border-t px-4 py-3.5">
                <div class="flex items-center justify-between">
                    <!-- Polling toggle with status -->
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <button
                                    @click="togglePolling"
                                    class="group flex items-center gap-2 rounded-md px-2.5 py-1.5 transition-all duration-200"
                                    :class="isPolling
                                        ? 'bg-primary/10 text-primary'
                                        : 'text-muted-foreground hover:bg-accent hover:text-foreground'"
                                >
                                    <div class="relative">
                                        <Play v-if="!isPolling" class="h-3.5 w-3.5" />
                                        <Pause v-else-if="!isRefreshing" class="h-3.5 w-3.5" />
                                        <RefreshCw v-if="isRefreshing" class="h-3.5 w-3.5 animate-spin" />
                                        <span v-if="isPolling && !isRefreshing" class="absolute -top-0.5 -right-0.5 h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse-dot"></span>
                                    </div>
                                    <span class="text-xs font-medium">{{ isPolling ? 'Live' : 'Paused' }}</span>
                                </button>
                            </TooltipTrigger>
                            <TooltipContent side="top">{{ isPolling ? 'Pause' : 'Start' }} auto-refresh</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <div class="flex items-center gap-0.5">
                        <template v-if="!isMobile">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button variant="ghost" size="icon" @click="showSettingsDialog = true" class="h-8 w-8">
                                            <Settings class="h-3.5 w-3.5" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent side="top">Settings</TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                            <TooltipProvider v-if="isSendSampleButtonEnabled">
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <AnimatedSendButton :disabled="isSending" @click="sendTestEmail" />
                                    </TooltipTrigger>
                                    <TooltipContent side="top">Send test email</TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            as="a"
                                            href="https://github.com/appoly/mail-web"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                        >
                                            <Github class="h-3.5 w-3.5" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent side="top">GitHub</TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <SettingsDialog
        :show-settings-dialog="showSettingsDialog"
        :is-delete-all-enabled="isDeleteAllEnabled"
        :initial-settings="settings"
        @update:show-settings-dialog="showSettingsDialog = $event"
        @update:settings="
            settings = $event;
            emit('update:settings', $event);
        "
    />
</template>
