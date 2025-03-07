<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Menu } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import Sidebar from '@/components/Sidebar.vue';
import EmailList from '@/components/EmailList.vue';
import EmailPreview from '@/components/EmailPreview.vue';
import EmailDetails from '@/components/EmailDetails.vue';
import SlidingPanel from '@/components/SlidingPanel.vue';
import { Email, EmailAddress } from '@/types/email';
import axios from 'axios';

const emails = ref<Email[]>([]);
const selectedEmail = ref<Email | null>(null);
const searchQuery = ref<string>('');
const sidebarOpen = ref<boolean>(false);
const isMobile = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const error = ref<string>('');
const filters = ref<Record<string, any>>({});

const fetchEmails = (): void => {
    isLoading.value = true;
    axios.get('/mailweb/emails')
        .then((response) => {
            emails.value = response.data;
            isLoading.value = false;
        })
        .catch((err) => {
            error.value = err.message;
            isLoading.value = false;
        });
};

// Helper function to format email addresses for search
const formatEmailAddressesForSearch = (addresses: EmailAddress[]): string => {
    return addresses.map(addr => `${addr.name} ${addr.address}`).join(' ');
};

// Computed filtered emails
const filteredEmails = computed<Email[]>(() =>
    emails.value.filter(email => {
        const query = searchQuery.value.toLowerCase();
        const fromText = formatEmailAddressesForSearch(email.from).toLowerCase();
        const toText = formatEmailAddressesForSearch(email.to).toLowerCase();
        const bodyText = email.body_text.toLowerCase();
        
        return email.subject.toLowerCase().includes(query) ||
            fromText.includes(query) ||
            toText.includes(query) ||
            bodyText.includes(query);
    })
);

// Methods
const handleShare = (): void => {
    if (selectedEmail.value) {
        navigator.clipboard.writeText(`https://mailweb.example.com/share/${selectedEmail.value.id}`);
        // toast({
        //   title: "Share link copied!",
        //   description: "Email preview link has been copied to clipboard",
        // });
    }
};

const handleDelete = (): void => {
    // toast({
    //   title: "Email deleted",
    //   description: "The email has been moved to trash",
    // });
};

const checkMobile = (): void => {
    isMobile.value = window.innerWidth < 1024;
};

// Lifecycle hooks
onMounted((): void => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    fetchEmails();
});
</script>

<template>
    <div class="flex flex-col h-screen overflow-hidden lg:flex-row">
        <div v-if="isMobile" class="flex items-center p-4 border-b">
            <Button variant="ghost" size="icon" @click="sidebarOpen = true" class="mr-2">
                <Menu class="h-5 w-5" />
                <span class="sr-only">Toggle menu</span>
            </Button>
            <div class="flex items-center">
                <h1 class="text-xl font-bold">Mailweb</h1>
            </div>
        </div>

        <Sidebar v-model:searchQuery="searchQuery" v-model:filters="filters" v-model:isOpen="sidebarOpen"
            :isMobile="isMobile" />

        <div class="flex flex-col flex-1 h-[calc(100vh-57px)] lg:h-screen overflow-hidden">
            <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">
                <template v-if="isMobile">
                    <SlidingPanel>
                        <EmailList :emails="filteredEmails" v-model:selectedEmail="selectedEmail" :isLoading="isLoading"
                            :error="error" :isMobile="isMobile" />
                        <template #preview>
                            <div v-if="selectedEmail" class="h-full overflow-y-auto">
                                <EmailPreview :email="selectedEmail" :isMobile="isMobile" @share="handleShare"
                                    @delete="handleDelete" />
                                <!-- <EmailDetails :email="selectedEmail" :isMobile="isMobile" /> -->
                            </div>
                            <div v-else class="flex items-center justify-center h-full p-6 text-center bg-muted/30">
                                <div>
                                    <h3 class="text-xl font-medium">No email selected</h3>
                                    <p class="text-muted-foreground">Select an email from the list to preview it</p>
                                </div>
                            </div>
                        </template>
                    </SlidingPanel>
                </template>
                <template v-else>
                    <EmailList :emails="filteredEmails" v-model:selectedEmail="selectedEmail" :isLoading="isLoading"
                        :error="error" :isMobile="isMobile" />
                    <template v-if="selectedEmail">
                        <div class="flex flex-col flex-1 overflow-hidden">
                            <EmailPreview :email="selectedEmail" :isMobile="isMobile" @share="handleShare"
                                @delete="handleDelete" />
                            <!-- <EmailDetails :email="selectedEmail" :isMobile="isMobile" /> -->
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex items-center justify-center flex-1 p-6 text-center bg-muted/30">
                            <div>
                                <h3 class="text-xl font-medium">No email selected</h3>
                                <p class="text-muted-foreground">Select an email from the list to preview it</p>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>