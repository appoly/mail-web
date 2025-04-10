<script setup lang="ts">
import EmailList from '@/components/EmailList.vue';
import EmailPreview from '@/components/EmailPreview.vue';
import Sidebar from '@/components/Sidebar.vue';
import SlidingPanel from '@/components/SlidingPanel.vue';
import { Button } from '@/components/ui/button';
import { Email } from '@/types/email';
import axios from 'axios';
import { formatDistanceToNow, parseISO } from 'date-fns';
import { Menu } from 'lucide-vue-next';
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
const error = ref<Error | null>(null);
const currentPage = ref<number>(1);
const totalEmails = ref<number>(0);
const lastPage = ref<number>(1);
const userSettings = ref({
    paginationAmount: 25,
    dateFormat: 'timestamp',
});

// Create a ref to track pagination events
const paginationTriggered = ref(false);

// Create a ref to track polling state for components to access
const isPollingActive = ref(false);

const fetchEmails = (resetList = true): void => {
    if (resetList) {
        isLoading.value = true;
        currentPage.value = 1;
        // Reset pagination trigger when doing a fresh fetch
        paginationTriggered.value = false;
    } else {
        isLoadingMore.value = true;
    }

    const params = {
        page: currentPage.value,
        per_page: userSettings.value.paginationAmount,
        search: searchQuery.value || undefined,
    };

    axios
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
        })
        .catch((err) => {
            error.value = err.message;
            isLoading.value = false;
            isLoadingMore.value = false;
        });
};

const loadMoreEmails = (): void => {
    if (currentPage.value < lastPage.value && !isLoadingMore.value) {
        // Signal that pagination has been triggered
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

// We'll use server-side filtering instead of client-side
const filteredEmails = computed<Email[]>(() => emails.value);

// Watch for search query changes to trigger new search
watch(
    searchQuery,
    (newValue, oldValue) => {
        if (newValue !== oldValue) {
            // Debounce search to avoid too many requests
            const debounceTimeout = setTimeout(() => {
                fetchEmails(true);
            }, 300);

            return () => clearTimeout(debounceTimeout);
        }
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
        <div v-if="isMobile" class="flex items-center border-b p-4">
            <Button variant="ghost" size="icon" @click="sidebarOpen = true" class="mr-2">
                <Menu class="h-5 w-5" />
                <span class="sr-only">Toggle menu</span>
            </Button>
            <div class="flex items-center">
                <h1 class="text-xl font-bold">Mailweb</h1>
            </div>
        </div>

        <Sidebar
            v-model:searchQuery="searchQuery"
            v-model:isOpen="sidebarOpen"
            :isMobile="isMobile"
            :filters="{}"
            @update:settings="updateSettings"
        />

        <div class="flex h-[calc(100vh-57px)] flex-1 flex-col overflow-hidden lg:h-screen">
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
                            <div v-else class="bg-muted/30 flex h-full items-center justify-center p-6 text-center">
                                <div>
                                    <h3 class="text-xl font-medium">No email selected</h3>
                                    <p class="text-muted-foreground">Select an email from the list to preview it</p>
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
                        <div class="bg-muted/30 flex flex-1 items-center justify-center p-6 text-center">
                            <div>
                                <h3 class="text-xl font-medium">No email selected</h3>
                                <p class="text-muted-foreground">Select an email from the list to preview it</p>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
        <Toaster />
    </div>
</template>
