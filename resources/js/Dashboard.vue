<script setup lang="ts">
import EmailList from '@/components/EmailList.vue';
import EmailPreview from '@/components/EmailPreview.vue';
import Sidebar from '@/components/Sidebar.vue';
import SlidingPanel from '@/components/SlidingPanel.vue';
import { Button } from '@/components/ui/button';
import { Email } from '@/types/email';
import axios from 'axios';
import { formatDistanceToNow, parseISO } from 'date-fns';
import { Inbox, Mail, Menu } from 'lucide-vue-next';
import { computed, onMounted, provide, ref, watch } from 'vue';
import toast, { Toaster } from 'vue3-hot-toast';

const emails = ref<Email[]>([]);
const selectedEmail = ref<Email | null>(null);
const selectedEmailWithFullContent = ref<Email | null>(null);
const isLoadingEmailContent = ref<boolean>(false);
const searchQuery = ref<string>('');
const sidebarOpen = ref<boolean>(false);
const isMobile = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const isLoadingMore = ref<boolean>(false);
const error = ref<string | null>(null);
const currentPage = ref<number>(1);
const totalEmails = ref<number>(0);
const lastPage = ref<number>(1);
const userSettings = ref({
    paginationAmount: 25,
    dateFormat: 'timestamp',
});

// Filters (client-side)
const filters = ref<Record<string, any>>({
    hasAttachments: false,
    unread: false,
});

// Track if we're searching (for UI feedback)
const isSearching = ref(false);

// Create a ref to track pagination events
const paginationTriggered = ref(false);

// Create a ref to track polling state for components to access
const isPollingActive = ref(false);

const fetchEmails = (resetList = true): Promise<void> => {
    if (resetList) {
        isLoading.value = true;
        currentPage.value = 1;
        paginationTriggered.value = false;
    } else {
        isLoadingMore.value = true;
    }

    if (searchQuery.value) {
        isSearching.value = true;
    }

    const params = {
        page: currentPage.value,
        per_page: userSettings.value.paginationAmount,
        search: searchQuery.value || undefined,
    };

    return axios
        .get('/mailweb/emails', { params })
        .then((response) => {
            if (resetList) {
                emails.value = response.data.data;
            } else {
                emails.value = [...emails.value, ...response.data.data];
            }
            totalEmails.value = response.data.total;
            lastPage.value = response.data.last_page;
            isLoading.value = false;
            isLoadingMore.value = false;
            isSearching.value = false;
        })
        .catch((err) => {
            error.value = err.message;
            isLoading.value = false;
            isLoadingMore.value = false;
            isSearching.value = false;
        });
};

const loadMoreEmails = (): void => {
    if (currentPage.value < lastPage.value && !isLoadingMore.value) {
        paginationTriggered.value = true;
        currentPage.value++;
        fetchEmails(false);
    }
};

// Format date based on user settings
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);

    switch (userSettings.value.dateFormat) {
        case 'uk':
            return date.toLocaleDateString('en-GB');
        case 'us':
            return date.toLocaleDateString('en-US');
        case 'days-ago':
            return formatDistanceToNow(parseISO(dateString), { addSuffix: true });
        case 'timestamp':
        default:
            return date.toISOString();
    }
};

// Provide fetchEmails function, formatDate, pagination event, and polling state to child components
provide('fetchEmails', fetchEmails);
provide('formatDate', formatDate);
provide('paginationTriggered', paginationTriggered);
provide('isPollingActive', isPollingActive);

// Client-side filtering
const filteredEmails = computed<Email[]>(() => {
    let result = emails.value;

    if (filters.value.hasAttachments) {
        result = result.filter((e) => e.attachments_count && e.attachments_count > 0);
    }

    if (filters.value.unread) {
        result = result.filter((e) => !e.read);
    }

    return result;
});

const isFiltered = computed(() => filters.value.hasAttachments || filters.value.unread);

// Watch for search query changes to trigger new search (debounced)
let searchDebounceTimeout: ReturnType<typeof setTimeout> | null = null;
watch(
    searchQuery,
    () => {
        if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = setTimeout(() => {
            fetchEmails(true);
        }, 300);
    },
    { immediate: false },
);

// Watch for settings changes
watch(
    () => userSettings.value.paginationAmount,
    (newValue, oldValue) => {
        if (newValue !== oldValue) {
            fetchEmails(true);
        }
    },
    { immediate: false },
);

// Method to fetch full email content when an email is selected
const fetchEmailContent = (emailId: string): void => {
    isLoadingEmailContent.value = true;

    axios
        .get(`/mailweb/emails/${emailId}`)
        .then((response) => {
            selectedEmailWithFullContent.value = response.data;
            isLoadingEmailContent.value = false;
        })
        .catch((err) => {
            error.value = err.message;
            isLoadingEmailContent.value = false;
        });
};

// Watch for changes in selected email to fetch full content
watch(selectedEmail, (newEmail) => {
    if (newEmail) {
        fetchEmailContent(newEmail.id);
    } else {
        selectedEmailWithFullContent.value = null;
    }
});

// Update user settings
const updateSettings = (newSettings: any): void => {
    userSettings.value = { ...userSettings.value, ...newSettings };
};

// Update filters
const updateFilters = (newFilters: Record<string, any>): void => {
    filters.value = { ...filters.value, ...newFilters };
};

const checkMobile = (): void => {
    isMobile.value = window.innerWidth < 1024;
};

const handleDeleteEmail = (emailId: string): void => {
    emails.value = emails.value.filter((email) => email.id !== emailId);
    selectedEmail.value = null;
    selectedEmailWithFullContent.value = null;

    toast.success('Email deleted successfully');
};

// Lifecycle hooks
onMounted((): void => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    fetchEmails();
});
</script>

<template>
    <div class="flex h-screen flex-col overflow-hidden lg:flex-row">
        <!-- Mobile Header -->
        <div v-if="isMobile" class="flex items-center border-b px-4 py-3">
            <Button variant="ghost" size="icon" @click="sidebarOpen = true" class="mr-3">
                <Menu class="h-5 w-5" />
                <span class="sr-only">Toggle menu</span>
            </Button>
            <div class="flex items-center gap-2">
                <div class="bg-primary flex h-7 w-7 items-center justify-center rounded-md">
                    <Mail class="h-3.5 w-3.5 text-white" />
                </div>
                <h1 class="text-lg font-semibold tracking-tight">Mailweb</h1>
            </div>
        </div>

        <Sidebar
            v-model:searchQuery="searchQuery"
            v-model:isOpen="sidebarOpen"
            :isMobile="isMobile"
            :filters="filters"
            :isLoading="isLoading"
            @update:settings="updateSettings"
            @update:filters="updateFilters"
        />

        <div class="flex h-[calc(100vh-53px)] flex-1 flex-col overflow-hidden lg:h-screen">
            <div class="flex flex-1 flex-col overflow-hidden lg:flex-row">
                <template v-if="isMobile">
                    <SlidingPanel>
                        <EmailList
                            :emails="filteredEmails"
                            v-model:selectedEmail="selectedEmail"
                            :isLoading="isLoading"
                            :isLoadingMore="isLoadingMore"
                            :error="error"
                            :isMobile="isMobile"
                            :totalEmails="totalEmails"
                            :hasMoreEmails="currentPage < lastPage"
                            :isSearching="isSearching"
                            :searchQuery="searchQuery"
                            :isFiltered="isFiltered"
                            @loadMore="loadMoreEmails"
                        />
                        <template #preview>
                            <div v-if="selectedEmail" class="h-full overflow-y-auto">
                                <EmailPreview
                                    :email="selectedEmailWithFullContent || selectedEmail"
                                    :isLoading="isLoadingEmailContent"
                                    :isMobile="isMobile"
                                    @delete-email="handleDeleteEmail"
                                />
                            </div>
                            <div v-else class="flex h-full items-center justify-center p-8 text-center">
                                <div class="animate-fade-in-up">
                                    <div class="bg-muted mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl">
                                        <Inbox class="text-muted-foreground h-8 w-8" />
                                    </div>
                                    <h3 class="text-lg font-semibold tracking-tight">No email selected</h3>
                                    <p class="text-muted-foreground mt-1 text-sm">Tap an email to preview it here</p>
                                </div>
                            </div>
                        </template>
                    </SlidingPanel>
                </template>
                <template v-else>
                    <EmailList
                        :emails="filteredEmails"
                        v-model:selectedEmail="selectedEmail"
                        :isLoading="isLoading"
                        :isLoadingMore="isLoadingMore"
                        :error="error"
                        :isMobile="isMobile"
                        :totalEmails="totalEmails"
                        :hasMoreEmails="currentPage < lastPage"
                        :isSearching="isSearching"
                        :searchQuery="searchQuery"
                        @loadMore="loadMoreEmails"
                    />
                    <template v-if="selectedEmail">
                        <div class="flex flex-1 flex-col overflow-hidden">
                            <EmailPreview
                                :email="selectedEmailWithFullContent || selectedEmail"
                                :isLoading="isLoadingEmailContent"
                                :isMobile="isMobile"
                                @delete-email="handleDeleteEmail"
                            />
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex flex-1 items-center justify-center p-8 text-center">
                            <div class="animate-fade-in-up">
                                <div class="bg-muted mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-xl">
                                    <Inbox class="text-muted-foreground h-10 w-10" />
                                </div>
                                <h3 class="text-xl font-semibold tracking-tight">No email selected</h3>
                                <p class="text-muted-foreground mt-1.5 text-sm">Choose an email from the list to preview its contents</p>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
        <Toaster />
    </div>
</template>
